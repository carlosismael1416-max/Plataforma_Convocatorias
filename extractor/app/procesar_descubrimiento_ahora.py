"""
«Extraer ahora» del panel de administración: procesa un solo descubrimiento
(extrae, enriquece, valida y publica si el validador lo aprueba) sin esperar
al ciclo automático.

- Usa el mismo proceso que el ciclo (procesar_con_registro), con los mismos
  umbrales y defensas (URL pública, reintentos contados).
- No corre si el motor está apagado, si el ciclo automático está en curso o
  si ya hay otra extracción manual.
- Solo descubrimientos sin convocatoria y que no estén EN_PROCESO.

Uso: python -m app.procesar_descubrimiento_ahora --id 123
"""

from __future__ import annotations

import argparse
import json
import sys

from app.bitacora import registrar_incidencia
from app.ejecutar_ciclo_automatico import LOCK_ID as LOCK_CICLO
from app.interruptor_motor import motor_activo
from app.publicar_descubrimientos import conectar, procesar_con_registro

# Una sola extracción manual a la vez (bajo consumo).
LOCK_MANUAL = 9262029

ESTADOS_NO_PROCESABLES = {"EN_PROCESO", "PROCESADO"}

# Códigos de salida (la API los traduce a mensajes).
SALIDA_OK = 0
SALIDA_NO_PROCESABLE = 2
SALIDA_OCUPADO = 3
SALIDA_APAGADO = 4


def cargar_descubrimiento(conexion, descubrimiento_id: int) -> dict | None:
    cursor = conexion.cursor()
    cursor.execute(
        """
        SELECT id, titulo, url_original, dominio, evaluacion_score,
               fuente_id, estado, convocatoria_id
        FROM descubrimientos_web
        WHERE id = %s
        """,
        (descubrimiento_id,),
    )
    fila = cursor.fetchone()
    columnas = [c[0] for c in cursor.description]
    cursor.close()
    conexion.rollback()

    return dict(zip(columnas, fila)) if fila else None


def motivo_no_procesable(descubrimiento: dict | None) -> str | None:
    if descubrimiento is None:
        return "El descubrimiento no existe."

    if descubrimiento["convocatoria_id"] is not None:
        return "Este descubrimiento ya tiene una convocatoria; revísala desde Convocatorias."

    if descubrimiento["estado"] in ESTADOS_NO_PROCESABLES:
        return "El descubrimiento ya se está procesando."

    return None


def ciclo_en_curso(conexion) -> bool:
    """True si el ciclo automático tiene su bloqueo (no lo toma)."""
    cursor = conexion.cursor()
    cursor.execute("SELECT pg_try_advisory_lock(%s)", (LOCK_CICLO,))
    libre = bool(cursor.fetchone()[0])

    if libre:
        cursor.execute("SELECT pg_advisory_unlock(%s)", (LOCK_CICLO,))

    cursor.close()
    conexion.commit()

    return not libre


def tomar_bloqueo_manual(conexion) -> bool:
    cursor = conexion.cursor()
    cursor.execute("SELECT pg_try_advisory_lock(%s)", (LOCK_MANUAL,))
    tomado = bool(cursor.fetchone()[0])
    cursor.close()
    conexion.commit()

    return tomado


def comprobar(descubrimiento_id: int, conexion) -> tuple[int, str | None]:
    """Lo que la API revisa antes de lanzar el proceso (no toma bloqueos)."""
    if not motor_activo():
        return SALIDA_APAGADO, "El motor de extracción está apagado. Enciéndelo desde el panel."

    motivo = motivo_no_procesable(cargar_descubrimiento(conexion, descubrimiento_id))

    if motivo:
        return SALIDA_NO_PROCESABLE, motivo

    if ciclo_en_curso(conexion):
        return SALIDA_OCUPADO, "El ciclo automático está corriendo; intenta cuando termine."

    cursor = conexion.cursor()
    cursor.execute(
        # Un bloqueo con una sola llave bigint pequeña aparece con classid 0
        # y objid = la llave.
        "SELECT EXISTS (SELECT 1 FROM pg_locks WHERE locktype = 'advisory' "
        "AND classid = 0 AND objid = %s AND objsubid = 1 AND granted)",
        (LOCK_MANUAL,),
    )
    ocupado = bool(cursor.fetchone()[0])
    cursor.close()
    conexion.commit()

    if ocupado:
        return SALIDA_OCUPADO, "Ya hay otra extracción manual en curso; espera a que termine."

    return SALIDA_OK, None


def ejecutar(descubrimiento_id: int) -> tuple[int, dict]:
    if not motor_activo():
        return SALIDA_APAGADO, {"estado": "APAGADO"}

    conexion_bloqueo = conectar()

    try:
        if not tomar_bloqueo_manual(conexion_bloqueo):
            return SALIDA_OCUPADO, {"estado": "OCUPADO", "motivo": "Otra extracción manual en curso."}

        if ciclo_en_curso(conexion_bloqueo):
            return SALIDA_OCUPADO, {"estado": "OCUPADO", "motivo": "El ciclo automático está corriendo."}

        descubrimiento = cargar_descubrimiento(conexion_bloqueo, descubrimiento_id)
        motivo = motivo_no_procesable(descubrimiento)

        if motivo:
            return SALIDA_NO_PROCESABLE, {"estado": "NO_PROCESABLE", "motivo": motivo}

        print(f"Extracción manual del descubrimiento {descubrimiento_id}: {descubrimiento['url_original']}")
        resultado = procesar_con_registro(descubrimiento)

        if resultado.get("estado") == "ERROR":
            try:
                registrar_incidencia(
                    conexion_bloqueo,
                    tipo="PUBLICACION",
                    mensaje=f"Extracción manual del descubrimiento {descubrimiento_id}: {resultado.get('error')}",
                    url=descubrimiento["url_original"],
                )
                conexion_bloqueo.commit()
            except Exception:
                conexion_bloqueo.rollback()

        return SALIDA_OK, resultado

    finally:
        conexion_bloqueo.close()  # libera el bloqueo de sesión


def main() -> None:
    parser = argparse.ArgumentParser(description="Procesa ya un descubrimiento.")
    parser.add_argument("--id", type=int, required=True)
    args = parser.parse_args()

    codigo, resultado = ejecutar(args.id)
    print(json.dumps(resultado, ensure_ascii=False, default=str))
    sys.exit(codigo)


if __name__ == "__main__":
    main()
