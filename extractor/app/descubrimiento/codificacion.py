"""Decodificación robusta de respuestas HTTP.

requests usa ISO-8859-1 cuando el servidor manda ``text/html`` sin charset
(regla de HTTP/1.1), aunque la página esté en UTF-8; eso produce títulos como
"AmÃ©rico". Aquí se prefiere UTF-8 si los bytes lo son y solo se usa el
charset declarado cuando el servidor realmente lo indicó.
"""

import re

_CHARSET_META = re.compile(
    rb"""<meta[^>]+charset=["']?\s*([a-zA-Z0-9_\-]+)""",
    re.IGNORECASE,
)


def decodificar_html(contenido: bytes, respuesta) -> str:
    tipo = (respuesta.headers.get("Content-Type") or "").lower()
    declarado = "charset=" in tipo

    if declarado and respuesta.encoding:
        candidatos = [respuesta.encoding]
    else:
        candidatos = []
        meta = _CHARSET_META.search(contenido[:4096])

        if meta:
            candidatos.append(meta.group(1).decode("ascii", "ignore"))

        candidatos.append("utf-8")

    for encoding in candidatos:
        try:
            return contenido.decode(encoding)
        except (LookupError, UnicodeDecodeError):
            continue

    # No es UTF-8 válido: página en latín sin declarar.
    return contenido.decode(
        respuesta.encoding or "cp1252",
        errors="replace",
    )
