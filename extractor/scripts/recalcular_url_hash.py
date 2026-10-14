"""
Recalcula convocatorias.url_hash con la normalización canónica
(app/urls.py). Sin --aplicar solo muestra los cambios y las colisiones.

Si dos convocatorias normalizan a la misma URL, no se actualiza
ninguna de las dos: se reportan para revisarlas a mano.

Uso (desde extractor/):
    ./.venv/bin/python -m scripts.recalcular_url_hash
    ./.venv/bin/python -m scripts.recalcular_url_hash --aplicar
"""

from __future__ import annotations

import argparse

from collections import defaultdict

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)
from app.urls import (
    hash_url,
)


def main() -> None:
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--aplicar",
        action="store_true",
    )
    aplicar = parser.parse_args().aplicar

    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT id, url_original, url_hash
        FROM convocatorias
        WHERE url_original IS NOT NULL
          AND BTRIM(url_original) <> ''
        ORDER BY id
        """
    )
    filas = cursor.fetchall()

    por_hash = defaultdict(list)

    for convocatoria_id, url, _ in filas:
        por_hash[hash_url(url)].append(convocatoria_id)

    colisiones = {
        nuevo: ids
        for nuevo, ids in por_hash.items()
        if len(ids) > 1
    }
    en_colision = {
        convocatoria_id
        for ids in colisiones.values()
        for convocatoria_id in ids
    }

    cambios = [
        (convocatoria_id, hash_url(url))
        for convocatoria_id, url, actual in filas
        if hash_url(url) != (actual or "").strip()
        and convocatoria_id not in en_colision
    ]

    print(f"Convocatorias con URL: {len(filas)}")
    print(f"Hashes a actualizar: {len(cambios)}")
    print(f"Colisiones (no se tocan): {len(colisiones)}")

    for ids in colisiones.values():
        print(f"  misma URL normalizada: ids {ids}")

    if not aplicar:
        print("Ensayo: no se modificó nada. Usa --aplicar.")
        conexion.rollback()
        conexion.close()
        return

    # Dos pasos para no chocar con el índice único si un hash nuevo
    # coincide con el hash viejo de otra fila.
    for convocatoria_id, _ in cambios:
        cursor.execute(
            "UPDATE convocatorias SET url_hash = %s WHERE id = %s",
            (f"tmp-{convocatoria_id}", convocatoria_id),
        )

    for convocatoria_id, nuevo in cambios:
        cursor.execute(
            "UPDATE convocatorias SET url_hash = %s WHERE id = %s",
            (nuevo, convocatoria_id),
        )

    conexion.commit()
    conexion.close()
    print(f"Actualizados: {len(cambios)}")


if __name__ == "__main__":
    main()
