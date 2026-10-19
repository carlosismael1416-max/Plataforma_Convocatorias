"""
Clasifica las convocatorias existentes por tipo y a quién van dirigidas
(app/tipo_convocatoria.py), tras la migración 2026_10_18_220000. Las que
el motor nunca debió guardar como convocatoria (resultados, vacantes, no
elegibles para ITSVA) quedan NO_APLICA: no se publican ni salen en el
historial. No cambia estados. Las subidas a mano solo reciben tipo si no
tienen y nunca NO_APLICA.

Uso (desde extractor/):
    ./.venv/bin/python -m scripts.clasificar_tipos             # solo muestra
    ./.venv/bin/python -m scripts.clasificar_tipos --aplicar   # guarda
    ./.venv/bin/python -m scripts.clasificar_tipos --aplicar --todas  # reclasifica también las que ya tienen tipo
"""

from __future__ import annotations

import argparse
import json
from collections import Counter

from app.descubrimiento.elegibilidad import evaluar_elegibilidad
from app.descubrimiento.guardar_descubrimientos import conectar
from app.tipo_convocatoria import clasificar_tipo, excluye_mexicanos


def clasificar(fila: dict) -> tuple[str, list[str], str]:
    resultado = clasificar_tipo(fila["titulo"], fila["url_original"], fila["objetivo"], fila["descripcion"])
    tipo, dirigido, motivo = resultado["tipo"], resultado["dirigido_a"], resultado["motivo"] or ""

    if fila["origen"] == "MANUAL":
        return ("FINANCIAMIENTO" if tipo == "NO_APLICA" else tipo), dirigido, ""

    if tipo != "NO_APLICA":
        elegibilidad = evaluar_elegibilidad(fila["titulo"] or "", fila["url_original"] or "")
        if not elegibilidad["elegible"]:
            return "NO_APLICA", [], f"no elegible: {elegibilidad['motivo']}"
        if excluye_mexicanos(fila["objetivo"], fila["descripcion"]):
            return "NO_APLICA", [], "excluye a mexicanos"

    return tipo, dirigido, motivo


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument("--aplicar", action="store_true")
    parser.add_argument("--todas", action="store_true")
    args = parser.parse_args()

    conexion = conectar()
    cursor = conexion.cursor()
    cursor.execute(
        f"""
        SELECT id, titulo, url_original, COALESCE(objetivo, '') AS objetivo,
               COALESCE(descripcion, '') AS descripcion, origen, estado
        FROM convocatorias
        {'' if args.todas else 'WHERE tipo IS NULL'}
        ORDER BY id
        """
    )
    columnas = [c[0] for c in cursor.description]
    filas = [dict(zip(columnas, f)) for f in cursor.fetchall()]

    conteo = Counter()

    for fila in filas:
        tipo, dirigido, motivo = clasificar(fila)
        conteo[tipo] += 1
        print(f"{fila['id']:>5} {fila['estado']:<18} {tipo:<15} {','.join(dirigido) or '-':<32} {(fila['titulo'] or '')[:70]}"
              + (f"  ({motivo})" if motivo else ""))

        if args.aplicar:
            cursor.execute(
                "UPDATE convocatorias SET tipo = %s, dirigido_a = %s::jsonb WHERE id = %s",
                (tipo, json.dumps(dirigido), fila["id"]),
            )

    if args.aplicar:
        conexion.commit()
    else:
        conexion.rollback()

    print()
    print("Total:", len(filas), dict(conteo))
    print("Guardado." if args.aplicar else "Sin cambios (usa --aplicar para guardar).")
    conexion.close()


if __name__ == "__main__":
    main()
