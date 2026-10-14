from __future__ import annotations

from typing import Any
from urllib.parse import urlencode, urlparse
import xml.etree.ElementTree as ET

import requests


GOOGLE_NEWS_RSS = (
    "https://news.google.com/rss/search"
)


class GoogleNewsError(RuntimeError):
    pass


def _dominio(url: str) -> str:
    try:
        return (
            urlparse(url)
            .netloc
            .lower()
            .removeprefix("www.")
        )
    except Exception:
        return ""


def buscar_google_news(
    consulta: str,
    cantidad: int = 20,
) -> list[dict[str, Any]]:
    """
    Busca noticias/documentos mediante
    Google News RSS.

    No requiere API key.
    No modifica PostgreSQL.
    No abre navegador.
    """

    consulta = consulta.strip()

    if not consulta:
        raise ValueError(
            "La consulta no puede estar vacía."
        )

    if not 1 <= cantidad <= 100:
        raise ValueError(
            "cantidad debe estar entre 1 y 100."
        )

    parametros = {
        "q": consulta,
        "hl": "es-419",
        "gl": "MX",
        "ceid": "MX:es-419",
    }

    url = (
        GOOGLE_NEWS_RSS
        + "?"
        + urlencode(parametros)
    )

    headers = {
        "Accept": (
            "application/rss+xml,"
            "application/xml,text/xml"
        ),
        "User-Agent": (
            "ITSVA-Convocatorias/1.0"
        ),
    }

    try:
        respuesta = requests.get(
            url,
            headers=headers,
            timeout=(5, 20),
        )

    except requests.Timeout as error:
        raise GoogleNewsError(
            "Google News RSS tardó demasiado "
            "en responder."
        ) from error

    except requests.RequestException as error:
        raise GoogleNewsError(
            "No se pudo consultar "
            f"Google News RSS: {error}"
        ) from error

    if respuesta.status_code != 200:
        raise GoogleNewsError(
            "Google News RSS respondió "
            f"HTTP {respuesta.status_code}."
        )

    try:
        raiz = ET.fromstring(
            respuesta.content
        )
    except ET.ParseError as error:
        raise GoogleNewsError(
            "Google News no devolvió "
            "un RSS válido."
        ) from error

    resultados = []

    for item in raiz.findall(
        "./channel/item"
    ):
        titulo = (
            item.findtext("title")
            or ""
        ).strip()

        enlace = (
            item.findtext("link")
            or ""
        ).strip()

        fecha = (
            item.findtext("pubDate")
            or ""
        ).strip()

        guid = (
            item.findtext("guid")
            or ""
        ).strip()

        fuente_elemento = (
            item.find("source")
        )

        fuente_nombre = ""
        fuente_url = ""

        if fuente_elemento is not None:
            fuente_nombre = (
                fuente_elemento.text
                or ""
            ).strip()

            fuente_url = (
                fuente_elemento.attrib
                .get("url", "")
                .strip()
            )

        if not enlace:
            continue

        resultados.append({
            "titulo": titulo,
            "url_google": enlace,
            "guid": guid or None,
            "fecha_publicacion":
                fecha or None,
            "fuente_nombre":
                fuente_nombre or None,
            "fuente_url":
                fuente_url or None,
            "dominio":
                _dominio(fuente_url),
            "origen_descubrimiento":
                "GOOGLE_NEWS_RSS",
        })

        if len(resultados) >= cantidad:
            break

    return resultados
