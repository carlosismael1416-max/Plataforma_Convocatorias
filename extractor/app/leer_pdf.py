from pathlib import Path

import pymupdf


# Archivos usados por la prueba original de 2025.
PDF = Path(
    "data/pdfs/convocatoria_investigacion_2025.pdf"
)

SALIDA = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)


def extraer_texto_pdf(
    ruta_pdf,
    ruta_salida=None,
    mostrar_progreso=False,
):
    """
    Extrae texto seleccionable de cualquier PDF.

    Devuelve todo el texto como str.

    Si se proporciona ruta_salida, también guarda
    el texto extraído en un archivo .txt.
    """

    ruta_pdf = Path(ruta_pdf)

    if not ruta_pdf.exists():
        raise FileNotFoundError(
            f"No se encontró el PDF: {ruta_pdf}"
        )

    textos = []

    with pymupdf.open(ruta_pdf) as documento:

        if mostrar_progreso:
            print(
                "Total de páginas:",
                len(documento),
            )

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

            if mostrar_progreso:
                print(
                    f"Página {numero}: "
                    f"{len(texto)} caracteres"
                )

    texto_completo = "\n".join(
        textos
    )

    if ruta_salida is not None:
        ruta_salida = Path(
            ruta_salida
        )

        ruta_salida.parent.mkdir(
            parents=True,
            exist_ok=True,
        )

        ruta_salida.write_text(
            texto_completo,
            encoding="utf-8",
        )

    return texto_completo


def extraer_texto():
    """
    Mantiene funcionando la prueba original de 2025.
    """

    texto = extraer_texto_pdf(
        ruta_pdf=PDF,
        ruta_salida=SALIDA,
        mostrar_progreso=True,
    )

    print()
    print(
        "Texto guardado en:",
        SALIDA,
    )

    print()
    print("=== INICIO DEL DOCUMENTO ===")
    print(
        texto[:1500]
    )


if __name__ == "__main__":
    extraer_texto()