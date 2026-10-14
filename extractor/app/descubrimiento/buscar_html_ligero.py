from __future__ import annotations

from app.seguridad_red import SesionSegura


import heapq
import html
import ipaddress
import re
from datetime import datetime

from html.parser import HTMLParser
from urllib.parse import (
    unquote,
    urljoin,
    urlparse,
    urlunparse,
)
from urllib.robotparser import RobotFileParser

import requests


USER_AGENT = "ITSVA-Convocatorias/1.0"

MAX_HTML_BYTES = 2_000_000


# Conceptos que hacen que una URL sea interesante.
CONCEPTOS_CANDIDATO = {
    "convocatoria": (
        6,
        (
            "convocatoria",
            "convocatorias",
        ),
    ),
    "investigacion": (
        4,
        (
            "investigacion",
            "investigaciones",
            "investigador",
            "investigadores",
        ),
    ),
    "financiamiento": (
        5,
        (
            "financiamiento",
            "financiacion",
            "fondo",
            "fondos",
        ),
    ),
    "proyecto": (
        3,
        (
            "proyecto",
            "proyectos",
        ),
    ),
    "innovacion": (
        3,
        (
            "innovacion",
        ),
    ),
    "ciencia": (
        3,
        (
            "ciencia",
            "cientifico",
            "cientifica",
        ),
    ),
    "tecnologia": (
        2,
        (
            "tecnologia",
        ),
    ),
    "apoyo": (
        2,
        (
            "apoyo",
            "apoyos",
        ),
    ),
    "beca": (
        2,
        (
            "beca",
            "becas",
        ),
    ),
}


# Secciones que vale la pena explorar aunque
# todavía no sean una convocatoria.
CONCEPTOS_SECCION = (
    "noticia",
    "noticias",
    "prensa",
    "comunicacion",
    "publicacion",
    "publicaciones",
    "convocatoria",
    "convocatorias",
    "ciencia",
    "tecnologia",
    "innovacion",
    "investigacion",
    "programa",
    "programas",
    "boletin",
    "vigente",
    "vigentes",
    "abierta",
    "abiertas",
    "oportunidad",
    "oportunidades",
    "fondo",
    "fondos",
)


EXTENSIONES_NO_HTML = (
    ".jpg",
    ".jpeg",
    ".png",
    ".gif",
    ".webp",
    ".svg",
    ".css",
    ".js",
    ".zip",
    ".rar",
    ".7z",
    ".mp3",
    ".mp4",
    ".avi",
    ".mov",
    ".doc",
    ".docx",
    ".xls",
    ".xlsx",
    ".ppt",
    ".pptx",
)


class HTMLLigeroError(RuntimeError):
    pass


class ExtractorEnlaces(HTMLParser):
    def __init__(self):
        super().__init__(
            convert_charrefs=True
        )

        self.enlaces = []

        self._href = None
        self._texto = []

    def handle_starttag(
        self,
        tag,
        attrs,
    ):
        if tag.lower() != "a":
            return

        atributos = dict(attrs)

        href = (
            atributos.get("href")
            or ""
        ).strip()

        if not href:
            return

        self._href = href
        self._texto = []

    def handle_data(
        self,
        data,
    ):
        if self._href is not None:
            self._texto.append(data)

    def handle_endtag(
        self,
        tag,
    ):
        if (
            tag.lower() == "a"
            and self._href is not None
        ):
            texto = " ".join(
                self._texto
            )

            texto = re.sub(
                r"\s+",
                " ",
                texto,
            ).strip()

            self.enlaces.append(
                (
                    self._href,
                    texto,
                )
            )

            self._href = None
            self._texto = []


def normalizar_texto(
    valor: str | None,
) -> str:
    valor = html.unescape(
        valor or ""
    )

    reemplazos = str.maketrans({
        "á": "a",
        "é": "e",
        "í": "i",
        "ó": "o",
        "ú": "u",
        "ü": "u",
        "ñ": "n",
        "Á": "a",
        "É": "e",
        "Í": "i",
        "Ó": "o",
        "Ú": "u",
        "Ü": "u",
        "Ñ": "n",
    })

    valor = (
        valor
        .translate(reemplazos)
        .casefold()
    )

    valor = re.sub(
        r"\s+",
        " ",
        valor,
    )

    return valor.strip()


def normalizar_host(
    host: str | None,
) -> str:
    host = (
        host
        or ""
    ).lower()

    if host.startswith("www."):
        host = host[4:]

    return host


def validar_base(
    url: str,
) -> str:
    url = url.strip()

    parsed = urlparse(url)

    if parsed.scheme not in (
        "http",
        "https",
    ):
        raise ValueError(
            "Solo se permiten URLs "
            "HTTP/HTTPS."
        )

    if not parsed.hostname:
        raise ValueError(
            "La URL no contiene dominio."
        )

    hostname = (
        parsed.hostname.lower()
    )

    if hostname in (
        "localhost",
        "localhost.localdomain",
    ):
        raise ValueError(
            "No se permite localhost."
        )

    try:
        ip = ipaddress.ip_address(
            hostname
        )

        if (
            ip.is_private
            or ip.is_loopback
            or ip.is_link_local
            or ip.is_reserved
        ):
            raise ValueError(
                "No se permiten IPs "
                "privadas o reservadas."
            )

    except ValueError as error:
        if (
            "No se permiten"
            in str(error)
        ):
            raise

    return (
        f"{parsed.scheme}://"
        f"{parsed.netloc}/"
    )


def limpiar_url(
    url: str,
) -> str:
    parsed = urlparse(url)

    # Quitamos fragmentos.
    parsed = parsed._replace(
        fragment=""
    )

    return urlunparse(parsed)


def mismo_dominio(
    url: str,
    hosts_permitidos: set[str],
) -> bool:
    parsed = urlparse(url)

    if parsed.scheme not in (
        "http",
        "https",
    ):
        return False

    host = normalizar_host(
        parsed.hostname
    )

    return any(
        host == permitido
        or host.endswith(
            "." + permitido
        )
        for permitido in hosts_permitidos
    )


def es_documento_pdf(
    url: str,
) -> bool:
    ruta = (
        urlparse(url)
        .path
        .casefold()
    )

    return ruta.endswith(".pdf")


def parece_html(
    url: str,
) -> bool:
    ruta = (
        urlparse(url)
        .path
        .casefold()
    )

    if es_documento_pdf(url):
        return False

    return not ruta.endswith(
        EXTENSIONES_NO_HTML
    )


def extraer_anios(
    texto: str,
) -> list[int]:
    texto = normalizar_texto(
        texto
    )

    return sorted({
        int(valor)
        for valor in re.findall(
            r"(?<!\d)20\d{2}(?!\d)",
            texto,
        )
        if 2000 <= int(valor) <= 2100
    })


def ultimo_segmento_url(
    url: str,
) -> str:
    ruta = unquote(
        urlparse(url).path
    )

    segmentos = [
        segmento
        for segmento in ruta.split("/")
        if segmento
    ]

    if not segmentos:
        return ""

    return segmentos[-1]


def anios_especificos_url(
    url: str,
) -> list[int]:
    """
    Prioriza el año del archivo o slug final.

    Si no existe, revisa carpetas desde la
    más cercana hacia la raíz.

    Evita usar rangos generales como:
    Convocatorias 2025-2030
    como año real del documento.
    """

    ruta = unquote(
        urlparse(url).path
    )

    segmentos = [
        segmento
        for segmento in ruta.split("/")
        if segmento
    ]

    if not segmentos:
        return []

    anios = extraer_anios(
        segmentos[-1]
    )

    if anios:
        return anios

    for segmento in reversed(
        segmentos[:-1]
    ):
        encontrados = extraer_anios(
            segmento
        )

        if not encontrados:
            continue

        segmento_limpio = (
            segmento.strip()
        )

        if re.fullmatch(
            r"20\d{2}",
            segmento_limpio,
        ):
            return encontrados

        # Una carpeta con un solo año puede
        # considerarse específica.
        if len(encontrados) == 1:
            return encontrados

        # Si es un rango como 2025-2030,
        # no lo usamos.
        if len(encontrados) > 1:
            continue

    return []


def analizar_temporalidad(
    url: str,
    texto_enlace: str,
) -> dict:
    anio_actual = datetime.now().year

    # Primero el título visible.
    anios_titulo = extraer_anios(
        texto_enlace
    )

    if anios_titulo:
        anios = anios_titulo
        origen_anio = "TITULO"

    else:
        anios = anios_especificos_url(
            url
        )

        origen_anio = (
            "URL"
            if anios
            else None
        )

    if anio_actual in anios:
        estado = "ACTUAL"

    elif (
        anio_actual + 1
        in anios
    ):
        estado = "FUTURA"

    elif (
        anios
        and max(anios)
        < anio_actual
    ):
        estado = "HISTORICA"

    else:
        estado = "SIN_FECHA"

    return {
        "estado": estado,
        "anios": anios,
        "anio_actual": anio_actual,
        "origen_anio": origen_anio,
    }


def analizar_relevancia_tematica(
    url: str,
    texto_enlace: str,
) -> dict:
    """
    Analiza solo el título y el último
    segmento de la URL.

    Así evitamos que carpetas padre como
    /Convocatorias_2025-2030/
    conviertan cualquier PDF en convocatoria.
    """

    texto_titulo = normalizar_texto(
        texto_enlace
    )

    texto_url = normalizar_texto(
        ultimo_segmento_url(
            url
        )
    )

    texto = (
        f"{texto_titulo} "
        f"{texto_url}"
    )

    tiene_convocatoria = any(
        palabra in texto
        for palabra in (
            "convocatoria",
            "convocatorias",
        )
    )

    tiene_investigacion = any(
        palabra in texto
        for palabra in (
            "investigacion",
            "investigaciones",
            "investigador",
            "investigadores",
            "cientifico",
            "cientifica",
            "cientificos",
            "cientificas",
        )
    )

    tiene_financiamiento = any(
        palabra in texto
        for palabra in (
            "financiamiento",
            "financiacion",
            "fondo",
            "fondos",
        )
    )

    tiene_proyecto = any(
        palabra in texto
        for palabra in (
            "proyecto",
            "proyectos",
        )
    )

    tiene_innovacion = (
        "innovacion" in texto
    )

    tiene_ciencia = (
        "ciencia" in texto
    )

    tiene_tecnologia = (
        "tecnologia" in texto
    )

    tiene_apoyo = any(
        palabra in texto
        for palabra in (
            "apoyo",
            "apoyos",
        )
    )

    tiene_beca = any(
        palabra in texto
        for palabra in (
            "beca",
            "becas",
        )
    )

    tema_investigacion = any((
        tiene_investigacion,
        tiene_financiamiento,
        tiene_proyecto,
        tiene_innovacion,
        tiene_ciencia,
        tiene_tecnologia,
    ))

    if (
        tiene_convocatoria
        and tema_investigacion
    ):
        relevancia = "ALTA"

    elif (
        tiene_financiamiento
        and any((
            tiene_investigacion,
            tiene_proyecto,
            tiene_innovacion,
            tiene_ciencia,
            tiene_tecnologia,
        ))
    ):
        relevancia = "ALTA"

    elif (
        tiene_convocatoria
        or tema_investigacion
    ):
        relevancia = "MEDIA"

    else:
        relevancia = "BAJA"

    return {
        "relevancia": relevancia,
        "texto": texto,
        "tiene_convocatoria":
            tiene_convocatoria,
        "tiene_investigacion":
            tiene_investigacion,
        "tiene_financiamiento":
            tiene_financiamiento,
        "tiene_proyecto":
            tiene_proyecto,
        "tiene_innovacion":
            tiene_innovacion,
        "tiene_ciencia":
            tiene_ciencia,
        "tiene_tecnologia":
            tiene_tecnologia,
        "tiene_apoyo":
            tiene_apoyo,
        "tiene_beca":
            tiene_beca,
    }


def puntuar_candidato(
    url: str,
    texto_enlace: str,
) -> tuple[int, list[str]]:
    relevancia = (
        analizar_relevancia_tematica(
            url,
            texto_enlace,
        )
    )

    texto = relevancia[
        "texto"
    ]

    puntaje = 0
    razones = []

    if relevancia[
        "tiene_convocatoria"
    ]:
        puntaje += 6
        razones.append(
            "+6 convocatoria"
        )

    if relevancia[
        "tiene_investigacion"
    ]:
        puntaje += 4
        razones.append(
            "+4 investigación"
        )

    if relevancia[
        "tiene_financiamiento"
    ]:
        puntaje += 5
        razones.append(
            "+5 financiamiento"
        )

    if relevancia[
        "tiene_proyecto"
    ]:
        puntaje += 3
        razones.append(
            "+3 proyecto"
        )

    if relevancia[
        "tiene_innovacion"
    ]:
        puntaje += 3
        razones.append(
            "+3 innovación"
        )

    if relevancia[
        "tiene_ciencia"
    ]:
        puntaje += 3
        razones.append(
            "+3 ciencia"
        )

    if relevancia[
        "tiene_tecnologia"
    ]:
        puntaje += 2
        razones.append(
            "+2 tecnología"
        )

    if relevancia[
        "tiene_apoyo"
    ]:
        puntaje += 2
        razones.append(
            "+2 apoyo"
        )

    if relevancia[
        "tiene_beca"
    ]:
        puntaje += 2
        razones.append(
            "+2 beca"
        )

    # Un PDF no es relevante por ser PDF.
    if (
        es_documento_pdf(url)
        and relevancia[
            "relevancia"
        ] != "BAJA"
    ):
        puntaje += 2
        razones.append(
            "+2 PDF"
        )

    # Documentos posteriores/accesorios.
    if any(
        frase in texto
        for frase in (
            "lista de admision",
            "lista de aspirantes",
            "lista de personas",
            "lista aceptados",
            "lista de aceptados",
            "aspirantes aceptados",
            "resultados",
            "resultado final",
            "ganadores",
            "seleccionados",
            "nota aclaratoria",
            "lineamientos",
            "ficha de inscripcion",
        )
    ):
        puntaje -= 10
        razones.append(
            "-10 documento accesorio/resultado"
        )

    if any(
        palabra in texto
        for palabra in (
            "policia",
            "vacante",
            "vacantes",
            "empleo",
            "licitacion",
            "licitaciones",
        )
    ):
        puntaje -= 12
        razones.append(
            "-12 ruido"
        )

    if any(
        palabra in texto
        for palabra in (
            "premio",
            "concurso",
            "cuento",
            "coloquio",
            "congreso",
            "encuentro nacional",
        )
    ):
        puntaje -= 6
        razones.append(
            "-6 premio/evento"
        )

    temporalidad = (
        analizar_temporalidad(
            url,
            texto_enlace,
        )
    )

    # El año solo suma si ya existe
    # relevancia temática.
    if relevancia[
        "relevancia"
    ] != "BAJA":

        if (
            temporalidad["estado"]
            == "ACTUAL"
        ):
            puntaje += 5
            razones.append(
                "+5 año actual"
            )

        elif (
            temporalidad["estado"]
            == "FUTURA"
        ):
            puntaje += 3
            razones.append(
                "+3 convocatoria futura"
            )

        elif (
            temporalidad["estado"]
            == "HISTORICA"
        ):
            puntaje -= 8
            razones.append(
                "-8 convocatoria histórica"
            )

    if any(
        palabra in texto
        for palabra in (
            "vigente",
            "vigentes",
            "abierta",
            "abiertas",
        )
    ):
        puntaje += 3
        razones.append(
            "+3 abierta/vigente"
        )

    return (
        puntaje,
        razones,
    )


def puntuar_seccion(
    url: str,
    texto_enlace: str,
) -> int:
    texto = normalizar_texto(
        f"{url} {texto_enlace}"
    )

    puntaje = 0

    for palabra in (
        CONCEPTOS_SECCION
    ):
        if palabra in texto:
            puntaje += 2

    return puntaje


def cargar_robots(
    session: requests.Session,
    base_url: str,
) -> dict:
    robots_url = urljoin(
        base_url,
        "/robots.txt",
    )

    try:
        respuesta = session.get(
            robots_url,
            timeout=(5, 12),
            allow_redirects=True,
        )

    except requests.Timeout:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                "Timeout consultando robots.txt",
        }

    except requests.RequestException as error:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                str(error),
        }

    # 404 significa que no existe robots.txt.
    if respuesta.status_code == 404:
        return {
            "estado":
                "NO_EXISTE",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                None,
        }

    # Conservador: no rastreamos si el propio
    # robots devuelve prohibición.
    if respuesta.status_code in (
        401,
        403,
    ):
        return {
            "estado":
                "BLOQUEADO",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                f"HTTP {respuesta.status_code}",
        }

    if respuesta.status_code >= 500:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                f"HTTP {respuesta.status_code}",
        }

    if respuesta.status_code != 200:
        return {
            "estado":
                "NO_EXISTE",

            "parser":
                None,

            "url":
                robots_url,

            "error":
                f"HTTP {respuesta.status_code}",
        }

    parser = RobotFileParser()

    parser.set_url(
        respuesta.url
    )

    parser.parse(
        respuesta.text.splitlines()
    )

    return {
        "estado":
            "OK",

        "parser":
            parser,

        "url":
            respuesta.url,

        "error":
            None,
    }


def permitido_por_robots(
    robots: dict,
    url: str,
) -> bool:
    if robots["estado"] == "NO_EXISTE":
        return True

    parser = robots.get(
        "parser"
    )

    if parser is None:
        return False

    return parser.can_fetch(
        USER_AGENT,
        url,
    )


def descargar_html(
    session: requests.Session,
    url: str,
) -> tuple[str, str]:
    with session.get(
        url,
        timeout=(5, 15),
        allow_redirects=True,
        stream=True,
    ) as respuesta:

        if respuesta.status_code != 200:
            raise HTMLLigeroError(
                f"HTTP "
                f"{respuesta.status_code}"
            )

        content_type = (
            respuesta.headers
            .get(
                "content-type",
                ""
            )
            .lower()
        )

        if (
            "text/html"
            not in content_type
            and
            "application/xhtml"
            not in content_type
        ):
            raise HTMLLigeroError(
                "El contenido no es HTML."
            )

        content_length = (
            respuesta.headers
            .get("content-length")
        )

        if content_length:
            try:
                if (
                    int(content_length)
                    > MAX_HTML_BYTES
                ):
                    raise HTMLLigeroError(
                        "HTML demasiado grande."
                    )
            except ValueError:
                pass

        partes = []

        total = 0

        for bloque in (
            respuesta.iter_content(
                chunk_size=65536
            )
        ):
            if not bloque:
                continue

            total += len(bloque)

            if total > MAX_HTML_BYTES:
                raise HTMLLigeroError(
                    "HTML demasiado grande."
                )

            partes.append(bloque)

        contenido = b"".join(
            partes
        )

        encoding = (
            respuesta.encoding
            or "utf-8"
        )

        texto = contenido.decode(
            encoding,
            errors="replace",
        )

        return (
            respuesta.url,
            texto,
        )


def buscar_html_ligero(
    url_base: str,
    max_paginas: int = 15,
    max_profundidad: int = 2,
    max_candidatas: int = 100,
) -> dict:
    if not 1 <= max_paginas <= 50:
        raise ValueError(
            "max_paginas debe estar "
            "entre 1 y 50."
        )

    if not 0 <= max_profundidad <= 3:
        raise ValueError(
            "max_profundidad debe estar "
            "entre 0 y 3."
        )

    base_url = validar_base(
        url_base
    )

    session = SesionSegura()

    session.headers.update({
        "User-Agent":
            USER_AGENT,

        "Accept":
            "text/html,"
            "application/xhtml+xml;"
            "q=0.9,*/*;q=0.5",
    })

    robots = cargar_robots(
        session,
        base_url,
    )

    if robots["estado"] in (
        "ERROR_TEMPORAL",
        "BLOQUEADO",
    ):
        return {
            "base_url":
                base_url,

            "estado":
                robots["estado"],

            "robots":
                robots,

            "paginas_visitadas":
                0,

            "paginas_descargadas":
                0,

            "total_candidatas":
                0,

            "candidatas":
                [],

            "errores":
                [],
        }

    host_inicial = normalizar_host(
        urlparse(
            base_url
        ).hostname
    )

    hosts_permitidos = {
        host_inicial
    }

    # Cola de prioridad:
    # (-prioridad, profundidad, URL)
    cola = [
        (
            -100,
            0,
            base_url,
        )
    ]

    en_cola = {
        base_url
    }

    visitadas = set()

    candidatas = {}

    errores = []

    paginas_descargadas = 0

    while (
        cola
        and len(visitadas)
        < max_paginas
    ):
        (
            _prioridad,
            profundidad,
            url,
        ) = heapq.heappop(
            cola
        )

        en_cola.discard(
            url
        )

        if url in visitadas:
            continue

        visitadas.add(
            url
        )

        if not permitido_por_robots(
            robots,
            url,
        ):
            errores.append({
                "url":
                    url,

                "error":
                    "Bloqueada por robots.txt",
            })
            continue

        try:
            (
                url_final,
                contenido,
            ) = descargar_html(
                session,
                url,
            )

        except (
            requests.RequestException,
            HTMLLigeroError,
        ) as error:
            errores.append({
                "url":
                    url,

                "error":
                    str(error),
            })
            continue

        paginas_descargadas += 1

        host_final = normalizar_host(
            urlparse(
                url_final
            ).hostname
        )

        # Permitimos www <-> dominio raíz
        # cuando una redirección legítima
        # nos lleva al mismo host lógico.
        if (
            host_final
            == host_inicial
        ):
            hosts_permitidos.add(
                host_final
            )

        parser = ExtractorEnlaces()

        try:
            parser.feed(
                contenido
            )
        except Exception as error:
            errores.append({
                "url":
                    url_final,

                "error":
                    "No se pudo analizar HTML: "
                    f"{error}",
            })
            continue

        for (
            href,
            texto_enlace,
        ) in parser.enlaces:

            if href.startswith(
                (
                    "mailto:",
                    "tel:",
                    "javascript:",
                    "data:",
                )
            ):
                continue

            absoluta = urljoin(
                url_final,
                href,
            )

            absoluta = limpiar_url(
                absoluta
            )

            if not mismo_dominio(
                absoluta,
                hosts_permitidos,
            ):
                continue

            (
                puntaje,
                razones,
            ) = puntuar_candidato(
                absoluta,
                texto_enlace,
            )

            relevancia_actual = (
                analizar_relevancia_tematica(
                    absoluta,
                    texto_enlace,
                )
            )

            if (
                puntaje >= 3
                and relevancia_actual[
                    "relevancia"
                ] != "BAJA"
            ):
                existente = (
                    candidatas.get(
                        absoluta
                    )
                )

                temporalidad = (
                    analizar_temporalidad(
                        absoluta,
                        texto_enlace,
                    )
                )

                candidato = {
                    "titulo_enlace":
                        texto_enlace,

                    "url":
                        absoluta,

                    "pagina_origen":
                        url_final,

                    "puntaje":
                        puntaje,

                    "razones":
                        razones,

                    "estado_temporal":
                        temporalidad[
                            "estado"
                        ],

                    "anios_detectados":
                        temporalidad[
                            "anios"
                        ],

                    "origen_anio":
                        temporalidad[
                            "origen_anio"
                        ],

                    "relevancia_tematica":
                        relevancia_actual[
                            "relevancia"
                        ],

                    "profundidad_descubrimiento":
                        profundidad,
                }

                if (
                    existente is None
                    or puntaje
                    > existente[
                        "puntaje"
                    ]
                ):
                    candidatas[
                        absoluta
                    ] = candidato

            if (
                profundidad
                >= max_profundidad
            ):
                continue

            if not parece_html(
                absoluta
            ):
                continue

            if (
                absoluta in visitadas
                or absoluta in en_cola
            ):
                continue

            puntaje_seccion = (
                puntuar_seccion(
                    absoluta,
                    texto_enlace,
                )
            )

            # Seguimos únicamente enlaces
            # relacionados con el objetivo.
            prioridad = max(
                puntaje,
                puntaje_seccion,
            )

            if prioridad < 2:
                continue

            heapq.heappush(
                cola,
                (
                    -prioridad,
                    profundidad + 1,
                    absoluta,
                ),
            )

            en_cola.add(
                absoluta
            )

        if (
            len(candidatas)
            >= max_candidatas
        ):
            break

    lista = list(
        candidatas.values()
    )

    lista.sort(
        key=lambda item: (
            item["puntaje"],
            item["url"],
        ),
        reverse=True,
    )

    lista = lista[
        :max_candidatas
    ]

    return {
        "base_url":
            base_url,

        "estado":
            "COMPLETADO",

        "robots":
            {
                "estado":
                    robots["estado"],

                "url":
                    robots["url"],

                "error":
                    robots["error"],
            },

        "paginas_visitadas":
            len(visitadas),

        "paginas_descargadas":
            paginas_descargadas,

        "total_candidatas":
            len(lista),

        "candidatas":
            lista,

        "errores":
            errores,
    }
