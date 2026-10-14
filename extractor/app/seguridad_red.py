from __future__ import annotations

import ipaddress
import socket
import time

from urllib.parse import (
    urljoin,
    urlparse,
)

import requests


class URLNoSeguraError(
    RuntimeError
):
    pass


def _normalizar_host(
    host: str,
) -> str:
    return (
        host
        .strip()
        .lower()
        .rstrip(".")
    )


def _validar_ip(
    ip_texto: str,
) -> None:
    try:
        ip = ipaddress.ip_address(
            ip_texto
        )

    except ValueError as error:
        raise URLNoSeguraError(
            "Dirección IP inválida."
        ) from error

    # is_global excluye, entre otros:
    # - loopback
    # - privadas
    # - link-local
    # - reservadas
    # - unspecified
    # - multicast
    # - CGNAT/no globales
    if not ip.is_global:
        raise URLNoSeguraError(
            "La URL resuelve a una IP "
            f"no pública: {ip}"
        )


def validar_url_publica(
    url: str,
) -> str:
    """
    Valida una URL antes de realizar una
    conexión de red.

    Se usa en datos procedentes de
    descubrimientos externos.
    """

    if not isinstance(
        url,
        str,
    ):
        raise URLNoSeguraError(
            "La URL debe ser texto."
        )

    url = url.strip()

    if not url:
        raise URLNoSeguraError(
            "URL vacía."
        )

    try:
        parsed = urlparse(
            url
        )

    except Exception as error:
        raise URLNoSeguraError(
            "URL inválida."
        ) from error

    if parsed.scheme.lower() not in {
        "http",
        "https",
    }:
        raise URLNoSeguraError(
            "Solo se permiten URLs "
            "HTTP/HTTPS."
        )

    if (
        parsed.username is not None
        or parsed.password is not None
    ):
        raise URLNoSeguraError(
            "No se permiten credenciales "
            "embebidas en la URL."
        )

    host = parsed.hostname

    if not host:
        raise URLNoSeguraError(
            "URL sin hostname."
        )

    host = _normalizar_host(
        host
    )

    if (
        host == "localhost"
        or host.endswith(
            ".localhost"
        )
        or host.endswith(
            ".local"
        )
        or host.endswith(
            ".internal"
        )
        or host == "home.arpa"
        or host.endswith(
            ".home.arpa"
        )
    ):
        raise URLNoSeguraError(
            "Hostname local bloqueado."
        )

    if "%" in host:
        raise URLNoSeguraError(
            "No se permiten identificadores "
            "de zona IPv6."
        )

    try:
        puerto = parsed.port

    except ValueError as error:
        raise URLNoSeguraError(
            "Puerto inválido."
        ) from error

    if puerto is None:
        puerto = (
            443
            if parsed.scheme.lower()
                == "https"
            else 80
        )

    # --------------------------------------------------------
    # IP escrita directamente
    # --------------------------------------------------------

    try:
        ipaddress.ip_address(
            host
        )

    except ValueError:
        pass

    else:
        _validar_ip(
            host
        )

        return url

    # --------------------------------------------------------
    # HOSTNAME → resolver TODAS las IP
    # --------------------------------------------------------

    try:
        resultados = (
            socket.getaddrinfo(
                host,
                puerto,
                type=socket.SOCK_STREAM,
            )
        )

    except socket.gaierror as error:
        raise URLNoSeguraError(
            "No se pudo resolver el dominio."
        ) from error

    ips = {
        resultado[4][0]
        for resultado in resultados
        if resultado
        and len(
            resultado
        ) >= 5
        and resultado[4]
    }

    if not ips:
        raise URLNoSeguraError(
            "El dominio no resolvió "
            "ninguna IP."
        )

    # Si UNA de las respuestas DNS es privada
    # o reservada, rechazamos el destino entero.
    for ip_texto in ips:
        _validar_ip(
            ip_texto
        )

    return url


class SesionSegura(
    requests.Session
):
    """
    Session de requests que valida:

    1. URL inicial.
    2. Cada Location antes de que requests
       siga la redirección.

    Mantiene compatibilidad con
    allow_redirects=True.
    """

    def request(
        self,
        method,
        url,
        *args,
        **kwargs,
    ):
        validar_url_publica(
            str(
                url
            )
        )

        return super().request(
            method,
            url,
            *args,
            **kwargs,
        )

    def get_redirect_target(
        self,
        response,
    ):
        destino = super().get_redirect_target(
            response
        )

        if not destino:
            return None

        absoluto = urljoin(
            response.url,
            destino,
        )

        validar_url_publica(
            absoluto
        )

        return destino


USER_AGENT = (
    "Mozilla/5.0 "
    "(compatible; ITSVA-Convocatorias/1.0)"
)

CODIGOS_REDIRECCION = {
    301,
    302,
    303,
    307,
    308,
}

CODIGOS_TEMPORALES = {
    429,
    500,
    502,
    503,
    504,
}


def _host_sin_www(
    url: str | None,
) -> str:
    try:
        return (
            urlparse(
                url or ""
            ).hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

    except Exception:
        return ""


def mismo_dominio(
    url_a: str,
    url_b: str,
) -> bool:
    a = _host_sin_www(
        url_a
    )
    b = _host_sin_www(
        url_b
    )

    if not a or not b:
        return False

    return (
        a == b
        or a.endswith(
            "." + b
        )
        or b.endswith(
            "." + a
        )
    )


def _get_con_reintentos(
    sesion: requests.Session,
    url: str,
    *,
    timeout: tuple[int, int],
    reintentos: int,
) -> requests.Response:
    for intento in range(
        reintentos
    ):
        ultimo = intento >= reintentos - 1

        try:
            respuesta = sesion.get(
                url,
                headers={
                    "User-Agent":
                        USER_AGENT,
                },
                timeout=timeout,
                allow_redirects=False,
                stream=True,
            )

        except (
            requests.exceptions.Timeout,
            requests.exceptions.ConnectionError,
        ) as error:
            if ultimo:
                raise

            espera = 2 * (
                intento + 1
            )

            print(
                "Error temporal de red - "
                f"reintento {intento + 2} de "
                f"{reintentos} en {espera}s:",
                type(error).__name__,
            )

            time.sleep(
                espera
            )
            continue

        if (
            respuesta.status_code
            in CODIGOS_TEMPORALES
            and not ultimo
        ):
            respuesta.close()

            espera = 2 * (
                intento + 1
            )

            print(
                "Respuesta temporal",
                respuesta.status_code,
                "- reintento en",
                espera,
                "s:",
                url,
            )

            time.sleep(
                espera
            )
            continue

        return respuesta

    raise RuntimeError(
        "No se obtuvo respuesta HTTP."
    )


def _leer_limitado(
    respuesta: requests.Response,
    max_bytes: int,
) -> bytes:
    # El límite se aplica mientras se descarga: una respuesta
    # enorme no llega a cargarse completa en memoria.
    declarado = respuesta.headers.get(
        "Content-Length"
    )

    if (
        declarado
        and declarado.isdigit()
        and int(declarado) > max_bytes
    ):
        raise ValueError(
            "Respuesta superior a "
            f"{max_bytes} bytes."
        )

    partes = []
    total = 0

    for parte in respuesta.iter_content(
        chunk_size=65536
    ):
        total += len(parte)

        if total > max_bytes:
            raise ValueError(
                "Respuesta superior a "
                f"{max_bytes} bytes."
            )

        partes.append(
            parte
        )

    return b"".join(
        partes
    )


def descargar_mismo_dominio(
    url: str,
    url_base: str,
    *,
    max_bytes: int,
    reintentos: int = 1,
    timeout: tuple[int, int] = (10, 40),
) -> tuple[str, bytes, str]:
    """
    Descarga una URL institucional siguiendo solo
    redirecciones públicas y del mismo dominio que
    url_base, sin leer más de max_bytes.

    Devuelve (url_final, contenido, content_type).
    """

    actual = url

    with requests.Session() as sesion:
        for _ in range(5):
            validar_url_publica(
                actual
            )

            if not mismo_dominio(
                actual,
                url_base,
            ):
                raise URLNoSeguraError(
                    "La URL salió del dominio "
                    "institucional permitido."
                )

            respuesta = _get_con_reintentos(
                sesion,
                actual,
                timeout=timeout,
                reintentos=reintentos,
            )

            with respuesta:
                if (
                    respuesta.status_code
                    in CODIGOS_REDIRECCION
                ):
                    destino = respuesta.headers.get(
                        "Location"
                    )

                    if not destino:
                        raise ValueError(
                            "Redirección sin Location."
                        )

                    actual = urljoin(
                        actual,
                        destino,
                    )
                    continue

                respuesta.raise_for_status()

                return (
                    actual,
                    _leer_limitado(
                        respuesta,
                        max_bytes,
                    ),
                    respuesta.headers.get(
                        "Content-Type",
                        "",
                    ),
                )

    raise ValueError(
        "Demasiadas redirecciones."
    )
