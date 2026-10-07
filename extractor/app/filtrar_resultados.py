
import argparse
import json
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent.parent

ARCHIVO_ORIGINAL = (
    BASE_DIR / "scraping_vista_previa.json"
)

ARCHIVO_FILTRADO = (
    BASE_DIR / "scraping_filtrado.json"
)


def obtener_clasificacion(item):
    datos = item.get("clasificacion_tematica") or {}

    return (
        datos.get("clasificacion")
        or "REVISION_MANUAL"
    )


def filtrar_resultados(modo):
    if not ARCHIVO_ORIGINAL.exists():
        raise FileNotFoundError(
            "Primero ejecuta el scraper en "
            "modo Vista previa."
        )

    with ARCHIVO_ORIGINAL.open(
        encoding="utf-8"
    ) as archivo:
        resultados = json.load(archivo)

    categorias_permitidas = {
        "proyectos": {
            "POSIBLE_PROYECTO",
        },
        "proyectos_revision": {
            "POSIBLE_PROYECTO",
            "REVISION_MANUAL",
        },
        "otros": {
            "OTRO_TIPO",
        },
        "todos": {
            "POSIBLE_PROYECTO",
            "REVISION_MANUAL",
            "OTRO_TIPO",
        },
    }

    permitidas = categorias_permitidas[modo]

    seleccionadas = [
        item
        for item in resultados
        if obtener_clasificacion(item) in permitidas
    ]

    with ARCHIVO_FILTRADO.open(
        "w",
        encoding="utf-8",
    ) as archivo:
        json.dump(
            seleccionadas,
            archivo,
            ensure_ascii=False,
            indent=2,
        )

    print("\n=== FILTRO DE CONVOCATORIAS ===")
    print("Modo:", modo)
    print("Total original:", len(resultados))
    print("Seleccionadas:", len(seleccionadas))
    print(
        "No incluidas en el filtro:",
        len(resultados) - len(seleccionadas),
    )

    for numero, item in enumerate(
        seleccionadas,
        start=1,
    ):
        print(f"\n--- CONVOCATORIA {numero} ---")
        print("Título:", item.get("titulo"))
        print(
            "Clasificación:",
            obtener_clasificacion(item),
        )

        fecha = (
            item.get("fecha_cierre_candidata")
            or {}
        )

        print("Fecha candidata:", fecha.get("fecha"))

    print("\nArchivo filtrado:", ARCHIVO_FILTRADO)
    print(
        "El JSON original permanece sin cambios."
    )


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Filtrar convocatorias según su "
            "clasificación temática."
        )
    )

    parser.add_argument(
        "--modo",
        choices=[
            "proyectos",
            "proyectos_revision",
            "otros",
            "todos",
        ],
        default="proyectos",
    )

    argumentos = parser.parse_args()

    filtrar_resultados(argumentos.modo)


if __name__ == "__main__":
    main()
