"""Registra un ciclo automático fallido (lo ejecuta systemd con ExecStopPost).

Es independiente del código del motor (solo psycopg y python-dotenv): si el
ciclo falló porque falta o se rompió el código de app/, esto igual funciona.

- SERVICE_RESULT != success: escribe en bitacora_errores y deja
  ciclo_automatico_ultimo.json con estado FALLIDO, que el dashboard del
  administrador muestra en rojo.
"""

from __future__ import annotations

import json
import os
import subprocess
from datetime import datetime
from pathlib import Path

BASE_DIR = Path(__file__).resolve().parents[1]
REPORTE = BASE_DIR / "ciclo_automatico_ultimo.json"


def ultimas_lineas() -> str:
    try:
        salida = subprocess.run(
            ["journalctl", "-u", "convocatorias-itsva.service", "-n", "25", "--no-pager", "-o", "cat"],
            capture_output=True, text=True, timeout=10,
        ).stdout
    except Exception:  # noqa: BLE001
        salida = ""

    return salida.strip()[-4000:]


def main() -> int:
    resultado = os.environ.get("SERVICE_RESULT", "")

    if resultado in ("", "success"):
        return 0

    estado_salida = os.environ.get("EXIT_STATUS", "")
    mensaje = f"El ciclo automático falló (resultado: {resultado}, salida: {estado_salida})."
    detalle = ultimas_lineas()
    ahora = datetime.now().astimezone().isoformat()

    REPORTE.write_text(
        json.dumps(
            {"estado": "FALLIDO", "inicio": ahora, "fin": ahora, "mensaje": mensaje, "detalle": detalle},
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    try:
        import psycopg
        from dotenv import load_dotenv

        load_dotenv(BASE_DIR / ".env")

        with psycopg.connect(
            host=os.getenv("DB_HOST", "127.0.0.1"),
            port=os.getenv("DB_PORT", "5432"),
            dbname=os.environ["DB_DATABASE"],
            user=os.environ["DB_USERNAME"],
            password=os.getenv("DB_PASSWORD"),
            connect_timeout=10,
        ) as conexion:
            conexion.execute(
                """
                INSERT INTO bitacora_errores (tipo_error, codigo_error, mensaje, detalle, resuelto, created_at, updated_at)
                VALUES ('CICLO_FALLIDO', %s, %s, %s, FALSE, NOW(), NOW())
                """,
                (estado_salida or resultado, mensaje, detalle or None),
            )
    except Exception as error:  # noqa: BLE001 - al menos queda el reporte
        print("No se pudo registrar en la bitácora:", error)

    print(mensaje)
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
