import re
from urllib.parse import urldefrag, urlparse

import scrapy

from app.extraccion_detalle import extraer_detalle
from app.seguridad_red import USER_AGENT
from app.clasificar_convocatoria import clasificar_convocatoria
from app.procesar_revision_resultado import (
    procesar_revision_resultado,
)
from app.analizar_pdf import analizar_pdf_bytes
from app.resolver_fecha_cierre import resolver_fecha_cierre

# Rutas de SECIHTI que nunca son convocatorias para ITSVA (plazas y
# nombramientos). Se omiten para no descargarlas. Las becas sí se procesan
# desde el 18-oct-2026: se publican marcadas con su tipo.
RUTAS_NO_ELEGIBLES = (
    "/convocatoria/centros-publicos-de-investigacion/",
    "/convocatoria/investigadoras-e-investigadores-por-mexico/",
    "/convocatoria/catedras-conacyt/",
)


class ConvocatoriasSpider(scrapy.Spider):
    name = "convocatorias_itsva"

    custom_settings = {
        "ROBOTSTXT_OBEY": True,
        "DOWNLOAD_DELAY": 2,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 1,
        "DOWNLOAD_TIMEOUT": 25,
        # Sin esto Scrapy acepta hasta 1 GB por respuesta (p. ej. un
        # PDF enorme). 20 MB es el mismo tope del enriquecimiento.
        "DOWNLOAD_MAXSIZE": 20 * 1024 * 1024,
        "DOWNLOAD_WARNSIZE": 5 * 1024 * 1024,
        "LOG_LEVEL": "WARNING",
        # Identificación honesta del proyecto (algunos sitios oficiales
        # rechazan con 406 el User-Agent genérico de Scrapy).
        "USER_AGENT": USER_AGENT,
        "DEFAULT_REQUEST_HEADERS": {
            "Accept": "text/html,application/xhtml+xml,*/*;q=0.8",
            "Accept-Language": "es-MX,es;q=0.9",
        },
        "TELNETCONSOLE_ENABLED": False,
    }

    def __init__(self, url=None, limite=1, **kwargs):
        super().__init__(**kwargs)

        if not url:
            raise ValueError("Debes proporcionar una URL.")

        parsed = urlparse(url)

        if parsed.scheme not in ("http", "https"):
            raise ValueError(
                "La URL debe comenzar con http o https."
            )

        self.start_urls = [url]

        self.dominio = (
            parsed.hostname or ""
        ).removeprefix("www.")

        # Evita procesar dos veces la misma convocatoria.
        self.enlaces_encontrados = set()

        # Límite de convocatorias para las pruebas.
        # Utilizar 0 significa procesarlas todas.
        self.limite = int(limite)

    def parse(self, response):

        # PASO 1: Desde la página principal,
        # visitar las categorías seleccionadas.

        ruta_actual = urlparse(
            response.url
        ).path.rstrip("/")

         # Si la URL recibida ya corresponde a una
        # convocatoria individual, procesarla directamente.
        if ruta_actual.startswith("/convocatoria/"):
            yield from self.parse_detalle(
                response,
                titulo_enlace="",
            )
            return

        # Fuentes distintas de SECIHTI: listado genérico.
        if self.dominio != "secihti.mx":
            yield from self.convocatorias_de_listado(response)
            return

        if ruta_actual == "/convocatorias":
            # La portada lista primero las convocatorias más recientes
            # (donde están las vigentes). Se procesan antes que las
            # categorías para que el límite no se gaste en las antiguas.
            yield from self.convocatorias_de_portada(response)


            categorias_objetivo = {
                "/convocatoria_categoria/ciencias-y-humanidades/proyectos-de-investigacion",
                "/convocatoria_categoria/ciencias-y-humanidades/ciencia-basica-y-de-frontera",
                "/convocatoria_categoria/desarrollo-tecnologico-vinculacion-e-innovacion",
                "/convocatoria_categoria/ciencias-y-humanidades/investigacion-humanistica",
                "/convocatoria_categoria/ciencias-y-humanidades/ecos-nord",
                "/convocatoria_categoria/ciencias-y-humanidades/vinculacion-con-organismos-internacionales",
            }

            categorias_visitadas = set()

            for enlace in response.css("a[href]"):
                href = enlace.attrib.get(
                    "href", ""
                ).strip()

                if not href:
                    continue

                destino = response.urljoin(href)

                ruta = urlparse(
                    destino
                ).path.rstrip("/")

                if ruta not in categorias_objetivo:
                    continue

                if ruta in categorias_visitadas:
                    continue

                categorias_visitadas.add(ruta)

                yield scrapy.Request(
                    url=destino,
                    callback=self.parse,
                )

            # En la página principal únicamente
            # descubrimos las categorías objetivo.
            # No procesamos convocatorias individuales
            # enlazadas directamente desde la portada.
            return

        # Identificar la categoría actual, incluso
        # cuando estamos en su página 2, 3, etc.
        prefijos_por_categoria = {
            "/convocatoria_categoria/ciencias-y-humanidades/proyectos-de-investigacion":
                "/convocatoria/ciencias-y-humanidades/proyectos-de-investigacion/",

            "/convocatoria_categoria/ciencias-y-humanidades/ciencia-basica-y-de-frontera":
                "/convocatoria/ciencias-y-humanidades/ciencia-basica-y-de-frontera/",

            "/convocatoria_categoria/desarrollo-tecnologico-vinculacion-e-innovacion":
                "/convocatoria/desarrollo-tecnologico-vinculacion-e-innovacion/",
            "/convocatoria_categoria/ciencias-y-humanidades/investigacion-humanistica":
                "/convocatoria/ciencias-y-humanidades/investigacion-humanistica/",

            "/convocatoria_categoria/ciencias-y-humanidades/ecos-nord":
                "/convocatoria/ciencias-y-humanidades/ecos-nord/",

            "/convocatoria_categoria/ciencias-y-humanidades/vinculacion-con-organismos-internacionales":
                "/convocatoria/ciencias-y-humanidades/vinculacion-con-organismos-internacionales/",
        }

        prefijo = None
        categoria_base = None
        numero_pagina = 1

        for base, ruta_convocatoria in (
            prefijos_por_categoria.items()
        ):
            if ruta_actual == base:
                categoria_base = base
                prefijo = ruta_convocatoria
                break

            ruta_paginas = base + "/page/"

            if ruta_actual.startswith(ruta_paginas):
                numero = ruta_actual[len(ruta_paginas):]

                if numero.isdigit() and int(numero) >= 2:
                    categoria_base = base
                    prefijo = ruta_convocatoria
                    numero_pagina = int(numero)
                    break

        if prefijo is None:
            return

        # Buscar únicamente la siguiente página
        # de la categoría actual.
        if (
            self.limite == 0
            or len(self.enlaces_encontrados) < self.limite
        ):
            ruta_siguiente = (
                f"{categoria_base}/page/{numero_pagina + 1}"
            )

            for enlace in response.css("a[href]"):
                href = enlace.attrib.get(
                    "href", ""
                ).strip()

                if not href:
                    continue

                destino = response.urljoin(href)
                destino, _ = urldefrag(destino)

                parsed_siguiente = urlparse(destino)

                dominio_siguiente = (
                    parsed_siguiente.hostname or ""
                ).removeprefix("www.")

                if dominio_siguiente != self.dominio:
                    continue

                if (
                    parsed_siguiente.path.rstrip("/")
                    == ruta_siguiente
                ):
                    yield scrapy.Request(
                        url=destino,
                        callback=self.parse,
                        priority=-10,
                    )
                    break

        # PASO 2: Identificar los enlaces
        # que corresponden a convocatorias individuales.

        for enlace in response.css("a[href]"):
            href = enlace.attrib.get(
                "href", ""
            ).strip()

            if not href:
                continue

            url = response.urljoin(href)
            url, _ = urldefrag(url)

            parsed = urlparse(url)

            if parsed.scheme not in ("http", "https"):
                continue

            dominio = (
                parsed.hostname or ""
            ).removeprefix("www.")

            if dominio != self.dominio:
                continue

            ruta = parsed.path

            if not ruta.startswith("/convocatoria/"):
                continue

            if not ruta.startswith(prefijo):
                continue

            clave = (
                dominio,
                ruta.rstrip("/"),
            )

            if clave in self.enlaces_encontrados:
                continue

            titulo = (
                enlace.xpath(
                    "normalize-space(.)"
                ).get()
                or ""
            ).strip()

            if not titulo:
                titulo = enlace.attrib.get(
                    "title", ""
                ).strip()

            if not titulo:
                continue

            # Respetar el límite de la prueba.
            if (
                self.limite > 0
                and len(self.enlaces_encontrados) >= self.limite
            ):
                break

            self.enlaces_encontrados.add(clave)

            # PASO 3: Abrir la página individual
            # para obtener los detalles y sus PDF.

            yield scrapy.Request(
                url=url,
                callback=self.parse_detalle,
                cb_kwargs={
                    "titulo_enlace": titulo,
                },
            )

    def convocatorias_de_listado(self, response):
        """
        Listado de una fuente oficial cualquiera (p. ej. un sitio WordPress
        de una secretaría estatal): enlaces del mismo sitio que cuelgan de
        la ruta del listado o que mencionan «convocatoria».
        """

        ruta_listado = urlparse(response.url).path.rstrip("/")

        for enlace in response.css("a[href]"):
            if (
                self.limite > 0
                and len(self.enlaces_encontrados) >= self.limite
            ):
                return

            url, _ = urldefrag(
                response.urljoin(enlace.attrib.get("href", "").strip())
            )
            parsed = urlparse(url)
            ruta = parsed.path.rstrip("/")
            ruta_min = ruta.lower()

            if (
                parsed.scheme not in ("http", "https")
                or (parsed.hostname or "").removeprefix("www.") != self.dominio
                or not ruta
                or ruta == ruta_listado
                or parsed.query
                or re.search(r"/(tag|category|categoria|page|feed|author|wp-content|wp-json)(/|$)", ruta_min)
                or re.search(r"\.(pdf|docx?|xlsx?|jpe?g|png|zip)$", ruta_min)
            ):
                continue

            cuelga_del_listado = bool(ruta_listado) and ruta.startswith(ruta_listado + "/")

            if not cuelga_del_listado and "convocatoria" not in ruta_min:
                continue

            # Las secciones de listados («/convocatoria-abierta») no son una
            # convocatoria; sí lo son sus páginas internas.
            if not cuelga_del_listado and ruta.count("/") < 2:
                continue

            clave = (self.dominio, ruta)

            if clave in self.enlaces_encontrados:
                continue

            titulo = " ".join(
                (enlace.xpath("normalize-space(.)").get() or "").split()
            )

            self.enlaces_encontrados.add(clave)

            yield scrapy.Request(
                url=url,
                callback=self.parse_detalle,
                cb_kwargs={"titulo_enlace": titulo},
            )

    def convocatorias_de_portada(self, response):
        for enlace in response.css("a[href]"):
            if (
                self.limite > 0
                and len(self.enlaces_encontrados) >= self.limite
            ):
                return

            url, _ = urldefrag(
                response.urljoin(
                    enlace.attrib.get("href", "").strip()
                )
            )
            parsed = urlparse(url)

            if (
                parsed.scheme not in ("http", "https")
                or (parsed.hostname or "").removeprefix("www.") != self.dominio
                or not parsed.path.startswith("/convocatoria/")
                or parsed.path.startswith(RUTAS_NO_ELEGIBLES)
            ):
                continue

            clave = (self.dominio, parsed.path.rstrip("/"))

            if clave in self.enlaces_encontrados:
                continue

            titulo = " ".join(
                (enlace.xpath("normalize-space(.)").get() or "").split()
            )

            if not titulo:
                continue

            self.enlaces_encontrados.add(clave)

            yield scrapy.Request(
                url=url,
                callback=self.parse_detalle,
                cb_kwargs={"titulo_enlace": titulo},
                priority=10,
            )

    def parse_detalle(self, response, titulo_enlace):

        # Extraer los datos de la convocatoria.
        resultado = extraer_detalle(
            html=response.text,
            url_pagina=response.url,
        )

        # Utilizar el título del enlace como respaldo.
        if not resultado["titulo"]:
            resultado["titulo"] = titulo_enlace

        # Clasificación temática preliminar.
        # No descarta convocatorias ni modifica la BD.
        resultado["clasificacion_tematica"] = (
            clasificar_convocatoria(resultado)
        )

        tipo_fecha = resultado.get(
            "tipo_fecha_cierre"
        )

        documentos = resultado.get(
            "documentos",
            [],
        )

        # Resolver inmediatamente cuando el HTML ya
        # proporciona una fecha o cuando no hay PDF
        # disponible como respaldo.
        if (
            tipo_fecha != "SIN_FECHA"
            or not documentos
        ):
            resultado[
                "fecha_cierre_candidata"
            ] = resolver_fecha_cierre(
                resultado
            )

        # Si el HTML no contiene una fecha de cierre,
        # intentar analizar el PDF principal.
        if (
            tipo_fecha == "SIN_FECHA"
            and documentos
        ):
            documento_principal = next(
                (
                    documento
                    for documento in documentos
                    if "convocatoria" in (
                        documento.get(
                            "nombre",
                            "",
                        )
                    ).lower()
                ),
                documentos[0],
            )

            url_pdf = documento_principal.get(
                "url"
            )

            if url_pdf:
                yield scrapy.Request(
                    url=url_pdf,
                    callback=self.parse_pdf_cierre,
                    cb_kwargs={
                        "resultado": resultado,
                        "documento_pdf": (
                            documento_principal
                        ),
                    },
                )

                return

        # Si el HTML ya tiene una fecha,
        # procesar normalmente contra PostgreSQL.
        if self.crawler.settings.getbool(
            "SOLO_LECTURA", False
        ):
            resultado["revision_db"] = {
                "estado": "OMITIDO_VISTA_PREVIA",
                "revision_id": None,
            }
            yield resultado
            return

        try:
            revision_db = (
                procesar_revision_resultado(
                    resultado
                )
            )

            resultado["revision_db"] = (
                revision_db
            )

            self.logger.info(
                "Revisión de %s: %s",
                response.url,
                revision_db["estado"],
            )

        except Exception as error:
            resultado["revision_db"] = {
                "estado": "ERROR",
                "revision_id": None,
                "mensaje": str(error),
            }

            self.logger.error(
                "Error verificando revisión "
                "de %s: %s",
                response.url,
                error,
            )

        yield resultado

    def parse_pdf_cierre(
        self,
        response,
        resultado,
        documento_pdf,
    ):
        """
        Analiza un PDF cuando el HTML no contiene
        una fecha de cierre identificable.
        """

        try:
            analisis_pdf = analizar_pdf_bytes(
                response.body
            )

            analisis_pdf["url"] = (
                response.url
            )

            analisis_pdf["nombre"] = (
                documento_pdf.get("nombre")
            )

            resultado["cierre_pdf"] = (
                analisis_pdf
            )

            # Ahora que tenemos el análisis del PDF,
            # resolver la fecha candidata definitiva
            # para esta ejecución.
            resultado[
                "fecha_cierre_candidata"
            ] = resolver_fecha_cierre(
                resultado
            )

            if analisis_pdf.get(
                "usar_como_fecha_cierre"
            ):
                resultado[
                    "fecha_cierre_pdf"
                ] = analisis_pdf.get(
                    "fecha_cierre_postulacion"
                )

                resultado[
                    "hora_cierre_pdf"
                ] = analisis_pdf.get(
                    "hora_cierre_postulacion"
                )

                resultado["revision_db"] = {
                    "estado": (
                        "CIERRE_PDF_DETECTADO"
                    ),
                    "revision_id": None,
                }

            else:
                resultado[
                    "fecha_cierre_pdf"
                ] = None

                resultado[
                    "hora_cierre_pdf"
                ] = None

                resultado["revision_db"] = {
                    "estado": (
                        "SIN_CIERRE_POSTULACION"
                    ),
                    "revision_id": None,
                }

        except Exception as error:
            resultado["cierre_pdf"] = {
                "tipo": "ERROR_PDF",
                "mensaje": str(error),
                "url": response.url,
            }

            resultado["revision_db"] = {
                "estado": "ERROR_PDF",
                "revision_id": None,
                "mensaje": str(error),
            }

            self.logger.error(
                "Error analizando PDF %s: %s",
                response.url,
                error,
            )

        yield resultado
