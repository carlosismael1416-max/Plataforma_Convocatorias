from __future__ import annotations

import json
import time

from pathlib import Path
from urllib.parse import urlparse

from googlenewsdecoder import gnewsdecoder


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parents[2]
)

CACHE_PATH = (
    BASE_DIR
    / "google_news_url_cache.json"
)


def dominio_de(
    url: str,
) -> str:
    try:
        return (
            urlparse(url)
            .netloc
            .lower()
            .removeprefix("www.")
        )
    except Exception:
        return ""


def dominio_coincide(
    dominio: str,
    esperado: str | None,
) -> bool | None:
    esperado = (
        esperado
        or ""
    ).lower().removeprefix(
        "www."
    )

    if not esperado:
        return None

    return (
        dominio == esperado
        or dominio.endswith(
            "." + esperado
        )
        or esperado.endswith(
            "." + dominio
        )
    )


def cargar_cache() -> dict:
    if not CACHE_PATH.exists():
        return {}

    try:
        datos = json.loads(
            CACHE_PATH.read_text(
                encoding="utf-8"
            )
        )

        if isinstance(
            datos,
            dict,
        ):
            return datos

    except Exception:
        pass

    return {}


def guardar_cache(
    cache: dict,
) -> None:
    temporal = CACHE_PATH.with_suffix(
        ".json.tmp"
    )

    temporal.write_text(
        json.dumps(
            cache,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    temporal.replace(
        CACHE_PATH
    )


def resolver_google_news(
    url_google: str,
    dominio_esperado: str | None = None,
    reintentos: int = 3,
    usar_cache: bool = True,
) -> dict:
    if not url_google:
        return {
            "estado": "SIN_URL",
            "url_original": None,
            "dominio": None,
            "coincide_dominio": None,
            "desde_cache": False,
            "intentos": 0,
            "error": None,
        }

    cache = (
        cargar_cache()
        if usar_cache
        else {}
    )

    if (
        usar_cache
        and url_google in cache
    ):
        original = (
            cache[url_google]
            .get(
                "url_original"
            )
        )

        if original:
            dominio = dominio_de(
                original
            )

            return {
                "estado": "RESUELTO",
                "url_original":
                    original,

                "dominio":
                    dominio,

                "coincide_dominio":
                    dominio_coincide(
                        dominio,
                        dominio_esperado,
                    ),

                "desde_cache":
                    True,

                "intentos":
                    0,

                "error":
                    None,
            }

    ultimo_error = None

    reintentos = max(
        1,
        min(
            reintentos,
            5,
        ),
    )

    for intento in range(
        1,
        reintentos + 1,
    ):
        try:
            resultado = gnewsdecoder(
                url_google,
                interval=1,
                timeout=15,
            )

        except Exception as error:
            ultimo_error = (
                f"{type(error).__name__}: "
                f"{error}"
            )

        else:
            if (
                isinstance(
                    resultado,
                    dict,
                )
                and resultado.get(
                    "success"
                )
            ):
                original = (
                    resultado.get(
                        "decoded_url"
                    )
                    or ""
                ).strip()

                if original.startswith(
                    (
                        "http://",
                        "https://",
                    )
                ):
                    dominio = dominio_de(
                        original
                    )

                    if usar_cache:
                        cache[
                            url_google
                        ] = {
                            "url_original":
                                original,

                            "dominio":
                                dominio,

                            "actualizado_en":
                                time.strftime(
                                    "%Y-%m-%dT%H:%M:%S"
                                ),
                        }

                        try:
                            guardar_cache(
                                cache
                            )
                        except Exception:
                            # La resolución sigue siendo
                            # válida aunque falle el caché.
                            pass

                    return {
                        "estado":
                            "RESUELTO",

                        "url_original":
                            original,

                        "dominio":
                            dominio,

                        "coincide_dominio":
                            dominio_coincide(
                                dominio,
                                dominio_esperado,
                            ),

                        "desde_cache":
                            False,

                        "intentos":
                            intento,

                        "error":
                            None,
                    }

            if isinstance(
                resultado,
                dict,
            ):
                ultimo_error = (
                    resultado.get(
                        "message"
                    )
                    or
                    "Google News no pudo "
                    "resolver la URL."
                )
            else:
                ultimo_error = (
                    "Respuesta inesperada "
                    "del decoder."
                )

        if intento < reintentos:
            time.sleep(
                1.5 * intento
            )

    return {
        "estado":
            "NO_RESUELTO",

        "url_original":
            None,

        "dominio":
            None,

        "coincide_dominio":
            None,

        "desde_cache":
            False,

        "intentos":
            reintentos,

        "error":
            ultimo_error,
    }
