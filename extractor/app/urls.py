"""
Normalización canónica de URLs de convocatorias.

convocatorias.url_hash = hash_url(url_original) en todo el sistema
(extractor y Laravel: App\\Models\\Convocatoria::hashUrl). Así la misma
URL con o sin "www.", con mayúsculas en el host, barra final o
fragmento produce el mismo hash y el índice único detecta el duplicado.
"""

from __future__ import annotations

import hashlib

from urllib.parse import urlparse


def normalizar_url(
    url: str | None,
) -> str:
    if not url:
        return ""

    try:
        parsed = urlparse(
            url.strip()
        )

        host = (
            parsed.hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

        path = (
            parsed.path
            or "/"
        )

        if path != "/":
            path = path.rstrip("/")

        resultado = (
            f"{parsed.scheme.lower() or 'https'}"
            f"://{host}{path}"
        )

        if parsed.query:
            resultado += (
                "?"
                + parsed.query
            )

        return resultado

    except Exception:
        return url.strip().rstrip("/")


def hash_url(
    url: str,
) -> str:
    return hashlib.sha256(
        normalizar_url(
            url
        ).encode(
            "utf-8"
        )
    ).hexdigest()
