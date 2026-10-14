from __future__ import annotations

import logging

from scrapy.exceptions import (
    IgnoreRequest,
)

from app.seguridad_red import (
    URLNoSeguraError,
    validar_url_publica,
)


LOGGER = logging.getLogger(
    __name__
)


class SSRFProtectionMiddleware:
    """
    Bloquea requests de Scrapy cuya URL
    no resuelva exclusivamente a destinos
    públicos permitidos.
    """

    def process_request(
        self,
        request,
    ):
        try:
            validar_url_publica(
                request.url
            )

        except URLNoSeguraError as error:
            LOGGER.warning(
                "SSRF_BLOQUEADO "
                "url=%s error=%s",
                request.url,
                error,
            )

            raise IgnoreRequest(
                "URL bloqueada por "
                "protección SSRF: "
                f"{error}"
            )

        return None
