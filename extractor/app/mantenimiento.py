"""Mantenimiento ligero al final de cada ciclo: que la base y el disco no
crezcan sin control con los años.

- Descubrimientos DESCARTADO sin cambios en 180 días.
- Notificaciones ya leídas de hace más de 180 días.
- Archivos temporales de la web (exportaciones a Excel) de más de un día.

Nunca toca convocatorias, propuestas ni registros pendientes.
"""

from __future__ import annotations

import time
import json
from pathlib import Path

from app.descubrimiento.guardar_descubrimientos import conectar

DIAS_DESCUBRIMIENTOS = 180
DIAS_NOTIFICACIONES = 180
HORAS_TEMPORALES = 24

TMP_WEB = Path(__file__).resolve().parents[2] / "web" / "storage" / "app" / "tmp"


def limpiar_temporales(carpeta: Path = TMP_WEB, horas: int = HORAS_TEMPORALES) -> int:
    if not carpeta.is_dir():
        return 0

    limite = time.time() - horas * 3600
    borrados = 0

    for archivo in carpeta.iterdir():
        if archivo.is_file() and archivo.name != ".gitignore" and archivo.stat().st_mtime < limite:
            archivo.unlink(missing_ok=True)
            borrados += 1

    return borrados


def clasificar_sin_tipo(conexion, limite: int = 200) -> int:
    """
    Tipo y audiencia de las convocatorias que no pasaron por el validador
    (p. ej. pendientes que el ciclo cerró por vencidas), para que el
    historial las muestre bien marcadas y oculte las que no aplican.
    """
    from app.tipo_convocatoria import clasificar_existente
    from app.validar_convocatoria import columnas_tipo_disponibles

    if not columnas_tipo_disponibles(conexion):
        return 0

    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT id, titulo, url_original, COALESCE(objetivo, ''), COALESCE(descripcion, ''), origen
            FROM convocatorias
            WHERE tipo IS NULL
            ORDER BY id
            LIMIT %s
            """,
            (limite,),
        )
        filas = cursor.fetchall()

        for id_, titulo, url, objetivo, descripcion, origen in filas:
            tipo, dirigido, _ = clasificar_existente({
                "titulo": titulo, "url_original": url, "objetivo": objetivo,
                "descripcion": descripcion, "origen": origen,
            })
            cursor.execute(
                "UPDATE convocatorias SET tipo = %s, dirigido_a = %s::jsonb WHERE id = %s AND tipo IS NULL",
                (tipo, json.dumps(dirigido), id_),
            )

    return len(filas)


def ejecutar_mantenimiento() -> dict:
    resultado = {"descubrimientos": 0, "notificaciones": 0, "temporales": 0, "clasificadas": 0, "error": None}

    try:
        conexion = conectar()

        try:
            with conexion.cursor() as cursor:
                cursor.execute(
                    """
                    DELETE FROM descubrimientos_web
                    WHERE estado = 'DESCARTADO'
                      AND convocatoria_id IS NULL
                      AND updated_at < NOW() - make_interval(days => %s)
                    """,
                    (DIAS_DESCUBRIMIENTOS,),
                )
                resultado["descubrimientos"] = cursor.rowcount

                cursor.execute(
                    """
                    DELETE FROM notificaciones
                    WHERE leida = TRUE
                      AND created_at < NOW() - make_interval(days => %s)
                    """,
                    (DIAS_NOTIFICACIONES,),
                )
                resultado["notificaciones"] = cursor.rowcount

            conexion.commit()

            resultado["clasificadas"] = clasificar_sin_tipo(conexion)
            conexion.commit()
        finally:
            conexion.close()

        resultado["temporales"] = limpiar_temporales()
    except Exception as error:  # noqa: BLE001 - el mantenimiento nunca debe tumbar el ciclo
        resultado["error"] = f"{type(error).__name__}: {error}"

    return resultado
