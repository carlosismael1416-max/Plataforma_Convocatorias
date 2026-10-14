from typing import Any

import requests


GDELT_ENDPOINT = (
    "https://api.gdeltproject.org/"
    "api/v2/doc/doc"
)


class GdeltError(RuntimeError):
    """Error general al consultar GDELT."""


class GdeltRateLimitError(GdeltError):
    """GDELT está limitando temporalmente las consultas."""

    def __init__(
        self,
        mensaje: str,
        retry_after: int | None = None,
    ):
        super().__init__(mensaje)
        self.retry_after = retry_after


class GdeltConnectionError(GdeltError):
    """Problema temporal de conexión con GDELT."""


def _obtener_retry_after(
    respuesta: requests.Response,
) -> int | None:
    valor = respuesta.headers.get(
        "Retry-After"
    )

    if not valor:
        return None

    try:
        return int(valor)
    except ValueError:
        return None


def buscar_gdelt(
    consulta: str,
    cantidad: int = 10,
    timespan: str = "30d",
) -> list[dict[str, Any]]:
    """
    Consulta GDELT DOC 2.0.

    Solo obtiene metadatos de URLs candidatas.

    No:
    - modifica PostgreSQL
    - descarga las páginas encontradas
    - ejecuta Scrapy
    - abre navegador
    """

    consulta = consulta.strip()

    if not consulta:
        raise ValueError(
            "La consulta no puede estar vacía."
        )

    if not 1 <= cantidad <= 250:
        raise ValueError(
            "cantidad debe estar entre 1 y 250."
        )

    parametros = {
        "query": consulta,
        "mode": "artlist",
        "format": "json",
        "maxrecords": cantidad,
        "timespan": timespan,
        "sort": "datedesc",
    }

    headers = {
        "Accept": "application/json",
        "User-Agent": (
            "ITSVA-Convocatorias/1.0"
        ),
    }

    try:
        respuesta = requests.get(
            GDELT_ENDPOINT,
            params=parametros,
            headers=headers,
            timeout=(10, 60),
        )

    except requests.Timeout as error:
        raise GdeltConnectionError(
            "GDELT tardó demasiado "
            "en responder."
        ) from error

    except requests.RequestException as error:
        raise GdeltConnectionError(
            "No se pudo conectar con GDELT: "
            f"{error}"
        ) from error

    if respuesta.status_code == 429:
        retry_after = (
            _obtener_retry_after(
                respuesta
            )
        )

        mensaje = (
            "GDELT respondió HTTP 429. "
            "Se detendrá la consulta para "
            "no insistir sobre el servicio."
        )

        if retry_after:
            mensaje += (
                f" Retry-After="
                f"{retry_after}s."
            )

        raise GdeltRateLimitError(
            mensaje,
            retry_after=retry_after,
        )

    if 500 <= respuesta.status_code <= 599:
        raise GdeltConnectionError(
            "GDELT presentó un error "
            "temporal del servidor: "
            f"HTTP {respuesta.status_code}."
        )

    if respuesta.status_code != 200:
        raise GdeltError(
            "GDELT respondió con HTTP "
            f"{respuesta.status_code}."
        )

    texto = respuesta.text.strip()

    if not texto:
        return []

    try:
        datos = respuesta.json()

    except ValueError as error:
        muestra = texto[:300]

        raise GdeltError(
            "GDELT no devolvió JSON válido. "
            f"Respuesta inicial: {muestra}"
        ) from error

    articulos = datos.get(
        "articles",
        [],
    )

    if not isinstance(
        articulos,
        list,
    ):
        raise GdeltError(
            "El campo 'articles' "
            "no contiene una lista."
        )

    resultados = []

    urls_vistas = set()

    for articulo in articulos:
        if not isinstance(
            articulo,
            dict,
        ):
            continue

        url = str(
            articulo.get("url")
            or ""
        ).strip()

        if not url:
            continue

        if url in urls_vistas:
            continue

        urls_vistas.add(url)

        resultados.append({
            "titulo": str(
                articulo.get("title")
                or ""
            ).strip(),

            "url": url,

            "dominio": str(
                articulo.get("domain")
                or ""
            ).strip().lower(),

            "fecha_indice": (
                articulo.get("seendate")
                or None
            ),

            "idioma": (
                articulo.get("language")
                or None
            ),

            "pais_fuente": (
                articulo.get(
                    "sourcecountry"
                )
                or None
            ),

            "imagen": (
                articulo.get(
                    "socialimage"
                )
                or None
            ),

            "origen_descubrimiento":
                "GDELT_DOC_2",
        })

    return resultados


def buscar_gdelt_seguro(
    consulta: str,
    cantidad: int = 10,
    timespan: str = "30d",
) -> dict[str, Any]:
    """
    Versión para ciclos automáticos.

    Nunca insiste ante HTTP 429.
    Devuelve el estado de la consulta.
    """

    try:
        resultados = buscar_gdelt(
            consulta=consulta,
            cantidad=cantidad,
            timespan=timespan,
        )

        return {
            "ok": True,
            "estado": "COMPLETADO",
            "consulta": consulta,
            "resultados": resultados,
            "total": len(resultados),
            "error": None,
        }

    except GdeltRateLimitError as error:
        return {
            "ok": False,
            "estado": "RATE_LIMIT",
            "consulta": consulta,
            "resultados": [],
            "total": 0,
            "error": str(error),
            "retry_after":
                error.retry_after,
        }

    except GdeltConnectionError as error:
        return {
            "ok": False,
            "estado": "ERROR_TEMPORAL",
            "consulta": consulta,
            "resultados": [],
            "total": 0,
            "error": str(error),
        }

    except GdeltError as error:
        return {
            "ok": False,
            "estado": "ERROR",
            "consulta": consulta,
            "resultados": [],
            "total": 0,
            "error": str(error),
        }
