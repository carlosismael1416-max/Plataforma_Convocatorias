from pathlib import Path

import pymupdf


PDF = Path(
    "data/pdfs/terminos_referencia_2025.pdf"
)

SALIDA = Path(
    "data/textos/terminos_referencia_2025.txt"
)


def main():
    if not PDF.exists():
        print("ERROR: No se encontró el PDF.")
        return

    SALIDA.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    textos = []

    with pymupdf.open(PDF) as documento:
        print("Total de páginas:", len(documento))
        print()

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

            print(
                f"Página {numero}: "
                f"{len(texto)} caracteres"
            )

            if len(texto.strip()) < 100:
                print(
                    "  AVISO: Revisar si necesita OCR."
                )

    SALIDA.write_text(
        "\n".join(textos),
        encoding="utf-8",
    )

    print()
    print("Texto guardado en:", SALIDA)


if __name__ == "__main__":
    main()
