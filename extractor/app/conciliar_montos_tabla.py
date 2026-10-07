
import argparse
import json
import re
import unicodedata

from decimal import Decimal
from pathlib import Path


def normalizar(texto):
    texto = unicodedata.normalize(
        "NFKD",
        (texto or "").lower(),
    )

    texto = "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )

    return re.sub(r"\s+", " ", texto).strip()


def obtener_importes(fragmento):
    encontrados = re.findall(
        r"\$\s*([\d,]+)(?:\.\d{2})?",
        fragmento or "",
    )

    return {
        int(valor.replace(",", ""))
        for valor in encontrados
    }


def conciliar_montos_tabla(resultado):
    tabla = resultado.get("tabla_financiera")

    tabla_confirmada = (
        tabla is not None
        and tabla.get("estado")
        == "COINCIDENCIA_DOCUMENTAL"
    )

    fuentes_tabla = set()
    importes_tabla = set()

    if tabla_confirmada:
        fuentes_tabla = {
            (
                fuente["documento"],
                int(fuente["pagina"]),
            )
            for fuente in tabla["fuentes"]
        }

        for modalidad in tabla["modalidades"].values():
            valores = (
                modalidad["etapas"]
                + [modalidad["total"]]
            )

            importes_tabla.update(
                int(Decimal(str(valor)))
                for valor in valores
            )

    originales_pendientes = 0
    cubiertos = 0
    pendientes_reales = 0

    for documento in resultado["documentos_pdf"]:
        for monto in documento["montos_clasificados"]:
            # La conciliación puede ejecutarse varias
            # veces sin acumular resultados anteriores.
            monto.pop("conciliacion_tabla", None)

            if monto.get("estado") != "PENDIENTE_REVISION":
                continue

            originales_pendientes += 1

            fuente = monto.get("fuente") or {}
            identidad = (
                fuente.get("documento"),
                fuente.get("pagina"),
            )

            fragmento = fuente.get("fragmento") or ""
            contexto = normalizar(fragmento)
            importes_contexto = obtener_importes(fragmento)

            coincide_fuente = identidad in fuentes_tabla

            # Exigimos que el fragmento contenga la
            # tabla y varios de sus importes, no solo
            # una cantidad aislada en la misma página.
            coincide_contexto = (
                "grupo de investigacion" in contexto
                and len(
                    importes_contexto & importes_tabla
                ) >= 4
            )

            coincide_importe = (
                int(Decimal(str(monto["valor"])))
                in importes_tabla
            )

            if (
                tabla_confirmada
                and coincide_fuente
                and coincide_contexto
                and coincide_importe
            ):
                monto["conciliacion_tabla"] = {
                    "estado": "CUBIERTO_POR_TABLA",
                    "moneda": tabla["moneda"],
                    "tipo": tabla["tipo"],
                }

                cubiertos += 1
            else:
                pendientes_reales += 1

    resumen = resultado["resumen"]

    resumen["pendientes_revision_originales"] = (
        originales_pendientes
    )

    resumen["apariciones_cubiertas_por_tabla"] = (
        cubiertos
    )

    resumen["pendientes_revision"] = pendientes_reales

    return resultado


def main():
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--entrada",
        type=Path,
        required=True,
    )

    parser.add_argument(
        "--salida",
        type=Path,
        required=True,
    )

    argumentos = parser.parse_args()

    datos = json.loads(
        argumentos.entrada.read_text(
            encoding="utf-8"
        )
    )

    resultado = conciliar_montos_tabla(datos)

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

    resumen = resultado["resumen"]

    print("=== CONCILIACIÓN FINANCIERA ===")
    print(
        "Montos originales:",
        resumen["montos_encontrados"],
    )
    print(
        "Pendientes originales:",
        resumen["pendientes_revision_originales"],
    )
    print(
        "Cubiertos por la tabla:",
        resumen["apariciones_cubiertas_por_tabla"],
    )
    print(
        "Pendientes reales:",
        resumen["pendientes_revision"],
    )
    print("JSON generado:", argumentos.salida)


if __name__ == "__main__":
    main()
