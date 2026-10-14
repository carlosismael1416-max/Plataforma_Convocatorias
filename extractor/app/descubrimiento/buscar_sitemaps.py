from __future__ import annotations

from app.seguridad_red import SesionSegura


import gzip
import ipaddress
import re
import xml.etree.ElementTree as ET

from urllib.parse import (
    urljoin,
    urlparse,
)

import requests


USER_AGENT = (
    "ITSVA-Convocatorias/1.0"
)


PALABRAS_URL = (
    "convoc",
    "investig",
    "financ",
    "fondo",
    "fondos",
    "ciencia",
    "tecnologia",
    "tecnología",
    "innovac",
    "proyecto",
    "proyectos",
    "beca",
    "becas",
    "apoyo",
)


class SitemapError(
    RuntimeError
):
    pass


def normalizar_base(
    url: str,
) -> str:
    url = url.strip()

    if not url:
        raise ValueError(
            "La URL está vacía."
        )

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
        parsed.hostname
        .lower()
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
        ):
            raise ValueError(
                "No se permiten IPs privadas."
            )

    except ValueError as error:
        if (
            "No se permiten"
            in str(error)
        ):
            raise

        # No era una IP literal.
        pass

    return (
        f"{parsed.scheme}://"
        f"{parsed.netloc}/"
    )


def es_url_candidata(
    url: str,
) -> bool:
    texto = url.casefold()

    return any(
        palabra.casefold()
        in texto
        for palabra
        in PALABRAS_URL
    )


def descargar(
    session: requests.Session,
    url: str,
    max_bytes: int = 5_000_000,
) -> bytes:
    respuesta = session.get(
        url,
        timeout=(5, 20),
        allow_redirects=True,
    )

    if respuesta.status_code != 200:
        raise SitemapError(
            f"HTTP {respuesta.status_code}: "
            f"{url}"
        )

    contenido = respuesta.content

    if len(contenido) > max_bytes:
        raise SitemapError(
            "Sitemap demasiado grande "
            f"(>{max_bytes} bytes): "
            f"{url}"
        )

    if contenido.startswith(
        b"\x1f\x8b"
    ):
        try:
            contenido = gzip.decompress(
                contenido
            )
        except OSError as error:
            raise SitemapError(
                "No se pudo descomprimir "
                f"{url}"
            ) from error

    return contenido


def sitemaps_desde_robots(
    session: requests.Session,
    base_url: str,
) -> list[str]:
    robots_url = urljoin(
        base_url,
        "/robots.txt",
    )

    try:
        respuesta = session.get(
            robots_url,
            timeout=(5, 15),
        )
    except requests.RequestException:
        return []

    if respuesta.status_code != 200:
        return []

    encontrados = []

    for linea in (
        respuesta.text
        .splitlines()
    ):
        if not re.match(
            r"^\s*sitemap\s*:",
            linea,
            flags=re.I,
        ):
            continue

        _, valor = (
            linea.split(
                ":",
                1,
            )
        )

        valor = valor.strip()

        if valor.startswith(
            (
                "http://",
                "https://",
            )
        ):
            encontrados.append(
                valor
            )

    return encontrados


def extraer_xml(
    contenido: bytes,
) -> tuple[
    list[str],
    list[str],
]:
    try:
        raiz = ET.fromstring(
            contenido
        )
    except ET.ParseError as error:
        raise SitemapError(
            "El contenido no es XML válido."
        ) from error

    sitemaps = []

    urls = []

    for elemento in raiz.iter():
        etiqueta = (
            elemento.tag
            .split("}")[-1]
            .lower()
        )

        if etiqueta != "loc":
            continue

        valor = (
            elemento.text
            or ""
        ).strip()

        if not valor:
            continue

        padre_tipo = None

        # ElementTree no da acceso al padre.
        # La clasificación exacta se hará
        # usando el tipo de raíz.
        if valor.endswith(
            (
                ".xml",
                ".xml.gz",
            )
        ):
            sitemaps.append(
                valor
            )
        else:
            urls.append(
                valor
            )

    raiz_tipo = (
        raiz.tag
        .split("}")[-1]
        .lower()
    )

    if raiz_tipo == "urlset":
        return [], (
            sitemaps + urls
        )

    if raiz_tipo == "sitemapindex":
        return (
            sitemaps + urls,
            [],
        )

    return (
        sitemaps,
        urls,
    )


def buscar_sitemaps(
    url_base: str,
    max_sitemaps: int = 8,
    max_urls: int = 1000,
) -> dict:
    base_url = normalizar_base(
        url_base
    )

    session = SesionSegura()

    session.headers.update({
        "User-Agent": USER_AGENT,
        "Accept": (
            "application/xml,"
            "text/xml,text/plain,"
            "*/*;q=0.5"
        ),
    })

    iniciales = (
        sitemaps_desde_robots(
            session,
            base_url,
        )
    )

    candidatos_comunes = [
        urljoin(
            base_url,
            "/sitemap.xml",
        ),
        urljoin(
            base_url,
            "/sitemap_index.xml",
        ),
        urljoin(
            base_url,
            "/sitemap-index.xml",
        ),
        urljoin(
            base_url,
            "/wp-sitemap.xml",
        ),
    ]

    cola = []

    vistos = set()

    for url in (
        iniciales
        + candidatos_comunes
    ):
        if url not in cola:
            cola.append(url)

    sitemaps_ok = []

    errores = []

    urls_encontradas = set()

    while (
        cola
        and len(sitemaps_ok)
        < max_sitemaps
        and len(urls_encontradas)
        < max_urls
    ):
        sitemap_url = (
            cola.pop(0)
        )

        if sitemap_url in vistos:
            continue

        vistos.add(
            sitemap_url
        )

        try:
            contenido = descargar(
                session,
                sitemap_url,
            )

            hijos, urls = (
                extraer_xml(
                    contenido
                )
            )

        except (
            requests.RequestException,
            SitemapError,
        ) as error:
            errores.append({
                "url":
                    sitemap_url,
                "error":
                    str(error),
            })
            continue

        sitemaps_ok.append(
            sitemap_url
        )

        for hijo in hijos:
            if (
                hijo not in vistos
                and hijo not in cola
                and len(
                    cola
                ) < max_sitemaps * 4
            ):
                cola.append(
                    hijo
                )

        for url in urls:
            if len(
                urls_encontradas
            ) >= max_urls:
                break

            urls_encontradas.add(
                url
            )

    candidatas = sorted(
        url
        for url in urls_encontradas
        if es_url_candidata(
            url
        )
    )

    return {
        "base_url":
            base_url,

        "sitemaps_declarados":
            iniciales,

        "sitemaps_procesados":
            sitemaps_ok,

        "total_urls":
            len(
                urls_encontradas
            ),

        "total_candidatas":
            len(
                candidatas
            ),

        "urls_candidatas":
            candidatas,

        "errores":
            errores,
    }
