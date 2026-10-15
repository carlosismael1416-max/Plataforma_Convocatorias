"""Interruptor del buscador y motor de extracción (ajustes_sistema.motor_activo).

Lo cambia el administrador desde la web. Si la tabla no existe o no se puede
leer, el motor se considera encendido (no se detiene por un error de lectura).
"""

from __future__ import annotations

from app.descubrimiento.guardar_descubrimientos import conectar


def motor_activo() -> bool:
    try:
        conexion = conectar()
    except Exception:  # noqa: BLE001
        return True

    try:
        with conexion.cursor() as cursor:
            cursor.execute(
                "SELECT valor FROM ajustes_sistema WHERE clave = 'motor_activo'"
            )
            fila = cursor.fetchone()
    except Exception:  # noqa: BLE001 - p. ej. la tabla aún no existe
        return True
    finally:
        conexion.close()

    return fila is None or str(fila[0]).strip() != "0"
