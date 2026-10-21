import pymupdf

from app.extraer_cierre_pdf import extraer_cierre_pdf


# Una página con menos texto que esto se considera imagen.
MIN_CARACTERES_PAGINA = 40
# Bajo consumo: el OCR (≈3 s por página) solo en las primeras páginas
# escaneadas de cada PDF.
MAX_PAGINAS_OCR = 4
SEGUNDOS_MAX_OCR_PAGINA = 30


def texto_por_ocr(pagina) -> str:
    """Texto de una página de PDF que es imagen (tesseract, español)."""
    try:
        import io

        import pytesseract
        from PIL import Image

        imagen = Image.open(io.BytesIO(pagina.get_pixmap(dpi=200).tobytes("png")))

        return pytesseract.image_to_string(
            imagen,
            lang="spa",
            timeout=SEGUNDOS_MAX_OCR_PAGINA,
        ).strip()

    except Exception:
        # Sin tesseract o si tarda demasiado: se sigue sin esa página.
        return ""


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
    paginas_ocr = 0

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

            # Página escaneada o cartel (imagen sin texto): OCR solo aquí,
            # con límite de páginas por documento.
            if (
                len(texto.strip()) < MIN_CARACTERES_PAGINA
                and paginas_ocr < MAX_PAGINAS_OCR
                and pagina.get_images()
            ):
                texto_ocr = texto_por_ocr(pagina)
                paginas_ocr += 1

                if texto_ocr:
                    texto = texto_ocr

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
