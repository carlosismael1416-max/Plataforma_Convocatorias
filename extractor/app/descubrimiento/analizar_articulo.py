from __future__ import annotations

from app.seguridad_red import SesionSegura


import html
import ipaddress
import re

from urllib.parse import (
    unquote,
    urljoin,
    urlparse,
)

import requests

from app.descubrimiento.buscar_html_ligero import (
    ExtractorEnlaces,
    cargar_robots,
    normalizar_host,
    permitido_por_robots,
)


USER_AGENT = "ITSVA-Convocatorias/1.0"

MAX_HTML_BYTES = 2_000_000


DOMINIOS_SOCIALES = {
    "facebook.com",
    "twitter.com",
    "x.com",
    "instagram.com",
    "linkedin.com",
    "youtube.com",
    "youtu.be",
    "whatsapp.com",
    "tiktok.com",
    "telegram.me",
    "t.me",
}


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


def dominio_de(
    url: str,
) -> str:
    try:
        return normalizar_host(
            urlparse(url).hostname
        )
    except Exception:
        return ""


def es_dominio_social(
    dominio: str,
) -> bool:
    return any(
        dominio == social
        or dominio.endswith(
            "." + social
        )
        for social in DOMINIOS_SOCIALES
    )


def es_institucional(
    dominio: str,
) -> bool:
    if not dominio:
        return False

    if dominio.endswith(
        ".gob.mx"
    ):
        return True

    if dominio.endswith(
        ".edu.mx"
    ):
        return True

    if ".edu." in dominio:
        return True

    if dominio.endswith(
        (
            ".edu.ar",
            ".edu.co",
            ".edu.pe",
            ".edu.ec",
            ".edu.uy",
            ".edu.cl",
        )
    ):
        return True

    DOMINIOS_INSTITUCIONALES = {
        "secihti.mx",
        "conahcyt.mx",
    }

    if any(
        dominio == institucional
        or dominio.endswith(
            "." + institucional
        )
        for institucional
        in DOMINIOS_INSTITUCIONALES
    ):
        return True

    return False


def parece_url_publica(
    url: str,
) -> bool:
    # Filtro barato de candidatos (sin DNS). La validación SSRF
    # real ocurre al descargar, con SesionSegura.
    try:
        parsed = urlparse(
            url
        )
    except Exception:
        return False

    if parsed.scheme not in (
        "http",
        "https",
    ):
        return False

    if not parsed.hostname:
        return False

    hostname = (
        parsed.hostname
        .lower()
    )

    if hostname in (
        "localhost",
        "localhost.localdomain",
    ):
        return False

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
            return False

    except ValueError:
        pass

    return True


def puntuar_enlace(
    url: str,
    texto_enlace: str,
    dominio_articulo: str,
) -> dict:
    dominio = dominio_de(
        url
    )

    texto = normalizar_texto(
        f"{texto_enlace} "
        f"{unquote(url)}"
    )

    puntaje = 0
    razones = []

    if es_dominio_social(
        dominio
    ):
        return {
            "puntaje": -100,
            "razones": [
                "red social"
            ],
        }

    if dominio == dominio_articulo:
        puntaje -= 3
        razones.append(
            "-3 mismo medio"
        )

    else:
        puntaje += 2
        razones.append(
            "+2 dominio externo"
        )

    if es_institucional(
        dominio
    ):
        puntaje += 8
        razones.append(
            "+8 dominio institucional"
        )

    ruta = (
        urlparse(url)
        .path
        .casefold()
    )

    if ruta.endswith(".pdf"):
        puntaje += 6
        razones.append(
            "+6 PDF"
        )

    conceptos = (
        (
            "convocatoria",
            7,
        ),
        (
            "convocatorias",
            7,
        ),
        (
            "investigacion",
            4,
        ),
        (
            "investigación",
            4,
        ),
        (
            "financiamiento",
            5,
        ),
        (
            "financiacion",
            5,
        ),
        (
            "fondo",
            4,
        ),
        (
            "proyecto",
            3,
        ),
        (
            "proyectos",
            3,
        ),
        (
            "ciencia",
            3,
        ),
        (
            "innovacion",
            3,
        ),
        (
            "innovación",
            3,
        ),
        (
            "tecnologia",
            2,
        ),
        (
            "tecnología",
            2,
        ),
        (
            "bases",
            4,
        ),
        (
            "registro",
            3,
        ),
        (
            "postulacion",
            4,
        ),
        (
            "postulación",
            4,
        ),
        (
            "aplicar",
            3,
        ),
    )

    vistos = set()

    for termino, valor in conceptos:
        clave = normalizar_texto(
            termino
        )

        if clave in vistos:
            continue

        if clave in texto:
            vistos.add(
                clave
            )

            puntaje += valor

            razones.append(
                f"+{valor} {clave}"
            )

    ruido = (
        "facebook",
        "instagram",
        "twitter",
        "linkedin",
        "youtube",
        "privacidad",
        "cookies",
        "publicidad",
        "suscribete",
        "suscríbete",
        "contacto",
    )

    if any(
        palabra in texto
        for palabra in ruido
    ):
        puntaje -= 8
        razones.append(
            "-8 ruido"
        )

    return {
        "puntaje": puntaje,
        "razones": razones,
    }


def descargar_articulo(
    url: str,
) -> dict:
    if not parece_url_publica(
        url
    ):
        return {
            "estado": "URL_INVALIDA",
            "url_final": None,
            "html": None,
            "error": (
                "La URL no es pública "
                "o válida."
            ),
        }

    session = SesionSegura()

    session.headers.update({
        "User-Agent":
            USER_AGENT,

        "Accept":
            "text/html,"
            "application/xhtml+xml,"
            "*/*;q=0.5",
    })

    base = (
        f"{urlparse(url).scheme}://"
        f"{urlparse(url).netloc}/"
    )

    robots = cargar_robots(
        session,
        base,
    )

    if robots["estado"] in (
        "ERROR_TEMPORAL",
        "BLOQUEADO",
    ):
        return {
            "estado":
                robots["estado"],

            "url_final":
                None,

            "html":
                None,

            "error":
                robots.get(
                    "error"
                ),
        }

    if not permitido_por_robots(
        robots,
        url,
    ):
        return {
            "estado":
                "BLOQUEADO_ROBOTS",

            "url_final":
                None,

            "html":
                None,

            "error":
                "robots.txt no permite "
                "consultar esta URL.",
        }

    try:
        with session.get(
            url,
            timeout=(5, 20),
            allow_redirects=True,
            stream=True,
        ) as respuesta:

            if respuesta.status_code != 200:
                return {
                    "estado":
                        "HTTP_ERROR",

                    "url_final":
                        respuesta.url,

                    "html":
                        None,

                    "error":
                        f"HTTP "
                        f"{respuesta.status_code}",
                }

            content_type = (
                respuesta.headers
                .get(
                    "content-type",
                    ""
                )
                .lower()
            )

            if "html" not in content_type:
                return {
                    "estado":
                        "NO_HTML",

                    "url_final":
                        respuesta.url,

                    "html":
                        None,

                    "error":
                        content_type,
                }

            partes = []
            total = 0

            for bloque in (
                respuesta.iter_content(
                    chunk_size=65536
                )
            ):
                if not bloque:
                    continue

                total += len(
                    bloque
                )

                if total > MAX_HTML_BYTES:
                    return {
                        "estado":
                            "DEMASIADO_GRANDE",

                        "url_final":
                            respuesta.url,

                        "html":
                            None,

                        "error":
                            "HTML supera 2 MB.",
                    }

                partes.append(
                    bloque
                )

            contenido = b"".join(
                partes
            )

            encoding = (
                respuesta.encoding
                or "utf-8"
            )

            return {
                "estado":
                    "COMPLETADO",

                "url_final":
                    respuesta.url,

                "html":
                    contenido.decode(
                        encoding,
                        errors="replace",
                    ),

                "error":
                    None,
            }

    except requests.Timeout:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "url_final":
                None,

            "html":
                None,

            "error":
                "Timeout",
        }

    except requests.RequestException as error:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "url_final":
                None,

            "html":
                None,

            "error":
                str(error),
        }


def analizar_articulo(
    url: str,
) -> dict:
    descarga = descargar_articulo(
        url
    )

    dominio_original = dominio_de(
        url
    )

    resultado = {
        "url_original":
            url,

        "dominio_original":
            dominio_original,

        "publicacion_institucional":
            es_institucional(
                dominio_original
            ),

        "estado":
            descarga[
                "estado"
            ],

        "url_final":
            descarga[
                "url_final"
            ],

        "error":
            descarga[
                "error"
            ],

        "enlaces_relevantes":
            [],
    }

    if descarga[
        "estado"
    ] != "COMPLETADO":
        return resultado

    parser = ExtractorEnlaces()

    try:
        parser.feed(
            descarga["html"]
        )

    except Exception as error:
        resultado[
            "estado"
        ] = "HTML_INVALIDO"

        resultado[
            "error"
        ] = str(error)

        return resultado

    dominio_articulo = dominio_de(
        descarga[
            "url_final"
        ]
    )

    encontrados = {}

    for href, texto in parser.enlaces:
        if not href:
            continue

        if href.startswith(
            (
                "mailto:",
                "tel:",
                "javascript:",
                "data:",
                "#",
            )
        ):
            continue

        absoluta = urljoin(
            descarga[
                "url_final"
            ],
            href,
        )

        if not parece_url_publica(
            absoluta
        ):
            continue

        evaluacion = puntuar_enlace(
            absoluta,
            texto,
            dominio_articulo,
        )

        if evaluacion[
            "puntaje"
        ] < 5:
            continue

        candidato = {
            "texto":
                re.sub(
                    r"\s+",
                    " ",
                    texto or "",
                ).strip(),

            "url":
                absoluta,

            "dominio":
                dominio_de(
                    absoluta
                ),

            "puntaje":
                evaluacion[
                    "puntaje"
                ],

            "institucional":
                es_institucional(
                    dominio_de(
                        absoluta
                    )
                ),

            "pdf":
                (
                    urlparse(
                        absoluta
                    )
                    .path
                    .casefold()
                    .endswith(".pdf")
                ),

            "razones":
                evaluacion[
                    "razones"
                ],
        }

        previo = encontrados.get(
            absoluta
        )

        if (
            previo is None
            or candidato[
                "puntaje"
            ] > previo[
                "puntaje"
            ]
        ):
            encontrados[
                absoluta
            ] = candidato

    lista = list(
        encontrados.values()
    )

    lista.sort(
        key=lambda item: (
            item[
                "institucional"
            ],
            item[
                "pdf"
            ],
            item[
                "puntaje"
            ],
        ),
        reverse=True,
    )

    resultado[
        "enlaces_relevantes"
    ] = lista[:20]

    return resultado
