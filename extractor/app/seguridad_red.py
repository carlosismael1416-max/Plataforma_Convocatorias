from __future__ import annotations

import ipaddress
import socket

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
