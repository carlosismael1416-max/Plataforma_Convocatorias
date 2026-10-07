
import argparse
import json
from datetime import date
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent.parent

ENTRADA = BASE_DIR / "scraping_filtrado.json"

SALIDA_COMPLETA = BASE_DIR / "scraping_vigencia.json"

SALIDA_FUTURAS = BASE_DIR / "scraping_fechas_futuras.json"


def clasificar_vigencia(item, fecha_referencia):
    candidata = item.get(
        "fecha_cierre_candidata"
    ) or {}

    fecha_texto = candidata.get("fecha")

    # Una fecha administrativa o informativa
    # no equivale al cierre de postulaciones.
    if (
        not fecha_texto
        or candidata.get("utilizable") is not True
    ):
        return "SIN_FECHA_VERIFICABLE"

    try:
        fecha_cierre = date.fromisoformat(
            fecha_texto
        )
    except (ValueError, TypeError):
        return "FECHA_INVALIDA"

    if fecha_cierre < fecha_referencia:
        return "VENCIDA"

    if fecha_cierre == fecha_referencia:
        return "CIERRA_HOY"

    return "FECHA_FUTURA"


def main():
    parser = argparse.ArgumentParser(
        description="Clasificar convocatorias por fecha."
    )

    parser.add_argument(
        "--fecha",
        default=date.today().isoformat(),
        help="Fecha de referencia: AAAA-MM-DD.",
    )

    argumentos = parser.parse_args()

    fecha_referencia = date.fromisoformat(
        argumentos.fecha
    )

    with ENTRADA.open(
        encoding="utf-8"
    ) as archivo:
        resultados = json.load(archivo)

    clasificados = []
    futuras = []

    conteos = {
        "FECHA_FUTURA": 0,
        "CIERRA_HOY": 0,
        "VENCIDA": 0,
        "SIN_FECHA_VERIFICABLE": 0,
        "FECHA_INVALIDA": 0,
    }

    for item in resultados:
        estado = clasificar_vigencia(
            item,
            fecha_referencia,
        )

        # Crear una copia para conservar
        # intacta la información original.
        registro = dict(item)

        registro["vigencia"] = {
            "estado": estado,
            "fecha_referencia": (
                fecha_referencia.isoformat()
            ),
        }

        clasificados.append(registro)
        conteos[estado] += 1

        if estado == "FECHA_FUTURA":
            futuras.append(registro)

        candidata = item.get(
            "fecha_cierre_candidata"
        ) or {}

        print()
        print("Título:", item.get("titulo"))
        print(
            "Fecha candidata:",
            candidata.get("fecha"),
        )
        print("Vigencia:", estado)

    SALIDA_COMPLETA.write_text(
        json.dumps(
            clasificados,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    SALIDA_FUTURAS.write_text(
        json.dumps(
            futuras,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("\n=== RESUMEN DE VIGENCIA ===")
    print("Fecha de referencia:", fecha_referencia)
    print("Total analizadas:", len(resultados))

    for estado, cantidad in conteos.items():
        print(f"{estado}: {cantidad}")

    print("\nJSON completo:", SALIDA_COMPLETA)
    print("JSON fechas futuras:", SALIDA_FUTURAS)


if __name__ == "__main__":
    main()
