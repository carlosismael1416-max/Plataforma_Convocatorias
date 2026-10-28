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
from app.urls import hash_url

# Páginas de una categoría de SECIHTI que se recorren buscando
# convocatorias nuevas (las registradas no cuentan para el límite).
MAX_PAGINAS_CATEGORIA = 5

# Noticias del sitio que anuncian una convocatoria con página propia
# («Realizarán Primer Congreso de Humanidades…» → /congresohumanidades/).
MAX_NOTAS_POR_FUENTE = 5
RUTAS_NOTA = re.compile(r"/(noticias?|prensa|comunicados?|boletin(es)?|blog)/", re.I)
PALABRAS_NOTA_CONVOCATORIA = re.compile(
    r"(?i)\b(convocatorias?|congreso|premios?|concurso|feria|foro|simposio|coloquio|encuentro|becas?|apoyos?|fondos?|registro|inscripciones|participa)\b"
)

# Micrositios anunciados con un banner (imagen sin texto) en la portada
# de la fuente: /congresohumanidades/. Solo rutas de un nivel cuyo nombre
# dice qué es (los nombres van pegados, por eso se busca como subcadena).
PALABRAS_MICROSITIO = (
    "convocatoria", "congreso", "premio", "concurso", "feria", "foro",
    "expo", "encuentro", "festival", "jornada", "simposio", "coloquio",
    "hackat", "reto", "innova", "beca", "fondo", "semana",
)

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

    def __init__(self, url=None, limite=1, conocidas=None, **kwargs):
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

        # url_hash de las convocatorias ya registradas. Se saltan sin
        # contarlas en el límite: si no, cada ciclo volvía a leer las
        # mismas (las primeras del listado) y nunca llegaba a las nuevas.
        # Las vigentes las revisa el seguimiento del ciclo.
        self.conocidas = set(conocidas or ())
        self.omitidas_conocidas = set()
        self.notas_visitadas = set()

    def ya_registrada(self, url):
        clave = hash_url(url)
        if clave in self.conocidas:
            self.omitidas_conocidas.add(clave)
            return True
        return False

    def closed(self, reason):
        if self.omitidas_conocidas:
            print(f"Ya registradas (omitidas): {len(self.omitidas_conocidas)}")

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

        # TecNM: tarjetas con título, estatus y PDF (sin página por
        # convocatoria).
        if self.dominio == "tecnm.mx":
            yield from self.convocatorias_tecnm(response)
            return

        # Fuentes distintas de SECIHTI: listado genérico y, en la portada
        # del sitio, los banners que llevan a micrositios.
        if self.dominio != "secihti.mx":
            yield from self.convocatorias_de_listado(response)

            portada = f"{urlparse(response.url).scheme}://{urlparse(response.url).netloc}/"
            if response.url.rstrip("/") + "/" == portada:
                yield from self.micrositios_de_portada(response)
            else:
                yield scrapy.Request(url=portada, callback=self.micrositios_de_portada, priority=-5)
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
        ) and numero_pagina < MAX_PAGINAS_CATEGORIA:
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

            if self.ya_registrada(url):
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

            titulo_enlace = " ".join(
                (enlace.xpath("normalize-space(.)").get() or "").split()
            )

            # Noticia del propio sitio que anuncia una convocatoria: se abre
            # para llegar a la página propia que enlaza.
            if (
                RUTAS_NOTA.search(ruta_min + "/")
                and PALABRAS_NOTA_CONVOCATORIA.search(titulo_enlace)
                and ruta not in self.notas_visitadas
                and len(self.notas_visitadas) < MAX_NOTAS_POR_FUENTE
            ):
                self.notas_visitadas.add(ruta)
                yield scrapy.Request(
                    url=url,
                    callback=self.parse_nota,
                    cb_kwargs={"titulo_nota": titulo_enlace},
                    priority=-5,
                )
                continue

            cuelga_del_listado = bool(ruta_listado) and ruta.startswith(ruta_listado + "/")

            if not cuelga_del_listado and "convocatoria" not in ruta_min:
                continue

            # Las secciones de listados («/convocatoria-abierta») no son una
            # convocatoria; sí lo son sus páginas internas.
            if not cuelga_del_listado and ruta.count("/") < 2:
                continue

            clave = (self.dominio, ruta)

            if clave in self.enlaces_encontrados or self.ya_registrada(url):
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

    def convocatorias_tecnm(self, response):
        """
        www.tecnm.mx/?vista=Convocatorias_Tecnm: cada convocatoria es un
        bloque con su título, el estatus («Abierta», «Concluida»,
        «Permanente») y los PDF (convocatoria, términos de referencia…).
        Solo se toman las abiertas o permanentes; su enlace es el PDF de la
        convocatoria sin «?doc=», que cambia entre visitas.
        """
        for bloque in response.css("div.row.justify-content-center.mb-3"):
            if (
                self.limite > 0
                and len(self.enlaces_encontrados) >= self.limite
            ):
                return

            titulo = " ".join(" ".join(bloque.css("div.bg-tecnm")[:1].xpath("normalize-space(.)").getall()).split())
            estatus = " ".join(" ".join(bloque.css("span.text-success::text, span.text-danger::text, span.text-warning::text, span.text-primary::text").getall()).split()).lower()

            if not titulo or not any(e in estatus for e in ("abierta", "permanente", "vigente")):
                continue

            documentos = []
            for enlace in bloque.css("a[href]"):
                url = response.urljoin(enlace.attrib.get("href", "").strip())
                parsed = urlparse(url)
                if not parsed.path.lower().endswith(".pdf"):
                    continue
                url = f"{parsed.scheme}://{parsed.netloc}{parsed.path}"
                nombre = " ".join((enlace.xpath("normalize-space(.)").get() or "").split())
                if url not in [d["url"] for d in documentos]:
                    documentos.append({"nombre": nombre or "Documento", "url": url})

            if not documentos:
                continue

            principal = next((d for d in documentos if "convocatoria" in d["nombre"].lower()), documentos[0])
            parsed = urlparse(principal["url"])
            clave = (self.dominio, parsed.path.rstrip("/"))

            if clave in self.enlaces_encontrados or self.ya_registrada(principal["url"]):
                continue

            self.enlaces_encontrados.add(clave)

            resultado = {
                "titulo": titulo,
                "url_original": principal["url"],
                "descripcion": None,
                "fecha_cierre_web": None,
                "hora_cierre_web": None,
                "tipo_fecha_cierre": "SIN_FECHA",
                "etiqueta_fecha_cierre": None,
                "documentos": documentos,
            }
            resultado["clasificacion_tematica"] = clasificar_convocatoria(resultado)

            yield scrapy.Request(
                url=principal["url"],
                callback=self.parse_pdf_cierre,
                cb_kwargs={"resultado": resultado, "documento_pdf": principal},
            )

    def micrositios_de_portada(self, response):
        for enlace in response.css("a[href]"):
            if (
                self.limite > 0
                and len(self.enlaces_encontrados) >= self.limite
            ):
                return

            # Banner: el enlace es una imagen, sin texto propio.
            if (enlace.xpath("normalize-space(.)").get() or "").strip() or not enlace.css("img"):
                continue

            url, _ = urldefrag(response.urljoin(enlace.attrib.get("href", "").strip()))
            parsed = urlparse(url)
            segmentos = [s for s in parsed.path.split("/") if s]

            if (
                parsed.scheme not in ("http", "https")
                or (parsed.hostname or "").removeprefix("www.") != self.dominio
                or len(segmentos) != 1
                or parsed.query
                or not any(p in segmentos[0].lower() for p in PALABRAS_MICROSITIO)
            ):
                continue

            clave = (self.dominio, parsed.path.rstrip("/"))

            if clave in self.enlaces_encontrados or self.ya_registrada(url):
                continue

            self.enlaces_encontrados.add(clave)

            yield scrapy.Request(
                url=url,
                callback=self.parse_detalle,
                cb_kwargs={"titulo_enlace": (enlace.css("img").attrib.get("alt") or "").strip()},
            )

    def parse_nota(self, response, titulo_nota):
        """Noticia del sitio → página propia de la convocatoria que anuncia."""
        from app.descubrimiento.pagina_propia import (
            buscar_pagina_propia,
            enlaces_del_cuerpo,
        )

        if (
            self.limite > 0
            and len(self.enlaces_encontrados) >= self.limite
        ):
            return

        titulo = titulo_nota or (response.css("h1::text").get() or "").strip()
        propia = buscar_pagina_propia(
            enlaces_del_cuerpo(response.text, response.url),
            titulo,
            response.url,
        )

        if propia is None or self.ya_registrada(propia["url"]):
            return

        parsed = urlparse(propia["url"])
        clave = ((parsed.hostname or "").removeprefix("www."), parsed.path.rstrip("/"))

        if clave in self.enlaces_encontrados:
            return

        self.enlaces_encontrados.add(clave)

        if propia["pdf"]:
            # Las bases en PDF se resuelven desde la página que las publica.
            return

        yield scrapy.Request(
            url=propia["url"],
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

            if clave in self.enlaces_encontrados or self.ya_registrada(url):
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

        # Utilizar el título del enlace como respaldo y, si tampoco hay
        # (banner de un micrositio), el og:title, h1 o <title> de la página.
        if not resultado["titulo"]:
            resultado["titulo"] = titulo_enlace

        if not resultado["titulo"]:
            from app.importar_desde_url import titulo_de_pagina

            resultado["titulo"] = titulo_de_pagina(response.text) or ""

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
