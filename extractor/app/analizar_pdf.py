import pymupdf

from app.extraer_cierre_pdf import extraer_cierre_pdf


def extraer_texto_pdf_bytes(contenido):
    """
    Recibe los bytes de un PDF y devuelve su texto.

    Esto permitirá utilizar directamente response.body
    cuando Scrapy descargue un documento PDF.
    """

    if not contenido:
        raise ValueError(
            "El PDF recibido está vacío."
        )

    if not contenido.startswith(b"%PDF"):
        raise ValueError(
            "El contenido recibido no parece ser un PDF."
        )

    textos = []

    with pymupdf.open(
        stream=contenido,
        filetype="pdf",
    ) as documento:

        for numero, pagina in enumerate(
            documento,
            start=1,
        ):
            texto = pagina.get_text(
                "text",
                sort=True,
            )

            textos.append(
                f"\n{'=' * 40}\n"
                f"PÁGINA {numero}\n"
                f"{'=' * 40}\n"
                f"{texto}"
            )

    return "\n".join(textos)


def analizar_pdf_bytes(contenido):
    """
    Extrae el texto y busca posibles fechas de cierre.

    No modifica PostgreSQL.
    """

    texto = extraer_texto_pdf_bytes(
        contenido
    )

    resultado = extraer_cierre_pdf(
        texto
    )

    resultado["caracteres_extraidos"] = len(
        texto
    )

    return resultado
