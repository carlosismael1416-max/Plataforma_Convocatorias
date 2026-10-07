
import argparse
import json
from pathlib import Path

from app.extraer_pdf_generico import analizar_pdf
from app.clasificar_montos import clasificar_monto


def procesar_documentos(rutas):
    documentos = []

    total_montos = 0
    total_clasificados = 0

    for ruta in rutas:
        print(f"\nProcesando: {ruta.name}")

        # Primera fase: extraer el contenido del PDF.
        analisis = analizar_pdf(ruta)

        # Segunda fase: interpretar los montos
        # encontrados mediante reglas.
        clasificados = [
            clasificar_monto(
                monto,
                analisis["archivo"],
            )
            for monto in analisis["montos_candidatos"]
        ]

        correctos = sum(
            1
            for monto in clasificados
            if monto["estado"] == "REGLA_COINCIDENTE"
        )

        pendientes = len(clasificados) - correctos

        total_montos += len(clasificados)
        total_clasificados += correctos

        documentos.append({
            "archivo": analisis["archivo"],
            "total_paginas": analisis["total_paginas"],
            "caracteres_extraidos": (
                analisis["caracteres_extraidos"]
            ),
            "montos_clasificados": clasificados,
            "fragmentos": analisis["fragmentos"],
            "advertencias": analisis["advertencias"],
            "resumen": {
                "montos_encontrados": len(clasificados),
                "reglas_coincidentes": correctos,
                "pendientes_revision": pendientes,
            },
        })

        print("Páginas:", analisis["total_paginas"])
        print("Montos encontrados:", len(clasificados))
        print("Reglas coincidentes:", correctos)
        print("Pendientes:", pendientes)

        for monto in clasificados:
            print(
                " ",
                monto["valor"],
                monto["moneda"],
                "-",
                monto["concepto"],
                "-",
                monto["estado"],
            )

    return {
        "estado": "EXTRACCION_PRELIMINAR",
        "documentos": documentos,
        "resumen_general": {
            "documentos_procesados": len(documentos),
            "montos_encontrados": total_montos,
            "reglas_coincidentes": total_clasificados,
            "pendientes_revision": (
                total_montos - total_clasificados
            ),
        },
        "observacion": (
            "Una regla coincidente no constituye "
            "verificación definitiva del importe "
            "ni de la elegibilidad de la convocatoria."
        ),
    }


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Extraer y clasificar información "
            "de varios documentos PDF."
        )
    )

    parser.add_argument(
        "--pdf",
        nargs="+",
        required=True,
        type=Path,
        help="Uno o varios archivos PDF.",
    )

    parser.add_argument(
        "--salida",
        required=True,
        type=Path,
        help="Archivo JSON de resultados.",
    )

    argumentos = parser.parse_args()

    for ruta in argumentos.pdf:
        if not ruta.is_file():
            parser.error(
                f"No se encontró el PDF: {ruta}"
            )

    resultado = procesar_documentos(
        argumentos.pdf
    )

    argumentos.salida.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    argumentos.salida.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("\n=== RESUMEN GENERAL ===")

    for nombre, valor in (
        resultado["resumen_general"].items()
    ):
        print(f"{nombre}: {valor}")

    print("\nJSON generado:", argumentos.salida)


if __name__ == "__main__":
    main()
