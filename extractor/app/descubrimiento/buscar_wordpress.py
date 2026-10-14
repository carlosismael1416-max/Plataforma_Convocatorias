from __future__ import annotations

from app.seguridad_red import SesionSegura


import html
import ipaddress
import re
from typing import Any
from urllib.parse import urljoin, urlparse

import requests


USER_AGENT = "ITSVA-Convocatorias/1.0"


class WordPressError(RuntimeError):
    pass


def normalizar_base(url: str) -> str:
    url = url.strip()

    parsed = urlparse(url)

    if parsed.scheme not in ("http", "https"):
        raise ValueError(
            "Solo se permiten URLs HTTP/HTTPS."
        )

    if not parsed.hostname:
        raise ValueError(
            "La URL no contiene dominio."
        )

    hostname = parsed.hostname.lower()

    if hostname in (
        "localhost",
        "localhost.localdomain",
    ):
        raise ValueError(
            "No se permite localhost."
        )

    try:
        ip = ipaddress.ip_address(hostname)

        if (
            ip.is_private
            or ip.is_loopback
            or ip.is_link_local
            or ip.is_reserved
        ):
            raise ValueError(
                "No se permiten IPs privadas "
                "o reservadas."
            )

    except ValueError as error:
        if "No se permiten" in str(error):
            raise

    return (
        f"{parsed.scheme}://"
        f"{parsed.netloc}/"
    )


def limpiar_html(valor: str | None) -> str:
    valor = valor or ""

    valor = re.sub(
        r"<[^>]+>",
        " ",
        valor,
    )

    valor = html.unescape(valor)

    valor = re.sub(
        r"\s+",
        " ",
        valor,
    )

    return valor.strip()


def _get_json(
    session: requests.Session,
    url: str,
    params: dict | None = None,
) -> Any:
    respuesta = session.get(
        url,
        params=params,
        timeout=(5, 15),
        allow_redirects=True,
    )

    if respuesta.status_code != 200:
        raise WordPressError(
            f"HTTP {respuesta.status_code}: "
            f"{respuesta.url}"
        )

    try:
        return respuesta.json()

    except ValueError as error:
        raise WordPressError(
            "La respuesta no contiene JSON válido: "
            f"{respuesta.url}"
        ) from error


def detectar_wordpress(
    session: requests.Session,
    base_url: str,
) -> dict:
    endpoint = urljoin(
        base_url,
        "/wp-json/",
    )

    try:
        datos = _get_json(
            session,
            endpoint,
        )

    except (
        requests.RequestException,
        WordPressError,
    ) as error:
        return {
            "wordpress": False,
            "endpoint": endpoint,
            "error": str(error),
        }

    if not isinstance(datos, dict):
        return {
            "wordpress": False,
            "endpoint": endpoint,
            "error": (
                "wp-json respondió, pero "
                "no devolvió un objeto JSON."
            ),
        }

    namespaces = datos.get(
        "namespaces",
        [],
    )

    rutas = datos.get(
        "routes",
        {},
    )

    parece_wordpress = (
        any(
            str(ns).startswith("wp/v2")
            for ns in namespaces
        )
        or any(
            str(ruta).startswith(
                "/wp/v2/"
            )
            for ruta in rutas
        )
    )

    return {
        "wordpress": parece_wordpress,
        "endpoint": endpoint,
        "error": None,
    }


def buscar_tipo(
    session: requests.Session,
    base_url: str,
    tipo: str,
    termino: str,
    cantidad: int,
) -> list[dict]:
    endpoint = urljoin(
        base_url,
        f"/wp-json/wp/v2/{tipo}",
    )

    try:
        datos = _get_json(
            session,
            endpoint,
            params={
                "search": termino,
                "per_page": cantidad,
                "page": 1,
                "_fields": (
                    "id,date,modified,"
                    "link,title,excerpt,status"
                ),
            },
        )

    except (
        requests.RequestException,
        WordPressError,
    ):
        return []

    if not isinstance(datos, list):
        return []

    resultados = []

    for item in datos:
        if not isinstance(item, dict):
            continue

        titulo = item.get(
            "title",
            {},
        )

        if isinstance(titulo, dict):
            titulo = titulo.get(
                "rendered",
                "",
            )

        extracto = item.get(
            "excerpt",
            {},
        )

        if isinstance(extracto, dict):
            extracto = extracto.get(
                "rendered",
                "",
            )

        enlace = str(
            item.get("link")
            or ""
        ).strip()

        if not enlace:
            continue

        resultados.append({
            "id":
                item.get("id"),

            "tipo":
                tipo,

            "titulo":
                limpiar_html(
                    str(titulo or "")
                ),

            "descripcion":
                limpiar_html(
                    str(extracto or "")
                ),

            "url":
                enlace,

            "fecha":
                item.get("date"),

            "modificado":
                item.get("modified"),

            "origen_descubrimiento":
                "WORDPRESS_REST_API",
        })

    return resultados


def buscar_wordpress(
    url_base: str,
    termino: str = "convocatoria",
    cantidad: int = 20,
) -> dict:
    if not 1 <= cantidad <= 50:
        raise ValueError(
            "cantidad debe estar "
            "entre 1 y 50."
        )

    base_url = normalizar_base(
        url_base
    )

    session = SesionSegura()

    session.headers.update({
        "User-Agent": USER_AGENT,
        "Accept": "application/json",
    })

    deteccion = detectar_wordpress(
        session,
        base_url,
    )

    if not deteccion[
        "wordpress"
    ]:
        return {
            "base_url":
                base_url,

            "wordpress":
                False,

            "endpoint":
                deteccion[
                    "endpoint"
                ],

            "resultados":
                [],

            "total":
                0,

            "error":
                deteccion[
                    "error"
                ],
        }

    encontrados = {}

    for tipo in (
        "posts",
        "pages",
    ):
        resultados = buscar_tipo(
            session,
            base_url,
            tipo,
            termino,
            cantidad,
        )

        for resultado in resultados:
            url = resultado["url"]

            encontrados[url] = (
                resultado
            )

    lista = list(
        encontrados.values()
    )

    lista.sort(
        key=lambda item: (
            item.get("fecha")
            or ""
        ),
        reverse=True,
    )

    return {
        "base_url":
            base_url,

        "wordpress":
            True,

        "endpoint":
            deteccion[
                "endpoint"
            ],

        "resultados":
            lista,

        "total":
            len(lista),

        "error":
            None,
    }
