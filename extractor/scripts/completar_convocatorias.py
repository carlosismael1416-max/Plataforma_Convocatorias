"""
Completa convocatorias ya guardadas que llegaron con poca información
(descripción, objetivo, fechas, montos o requisitos), con el mismo
enriquecimiento del motor: página oficial, sus PDF y OCR solo en páginas
que son imagen.

- Solo llena campos vacíos (nunca sobrescribe) y agrega requisitos solo si
  la convocatoria no tiene. No cambia estados ni publica: eso lo decide el
  ciclo con el validador.
- Sin --aplicar solo muestra lo que completaría.

Uso (desde extractor/):
    ./.venv/bin/python scripts/completar_convocatorias.py                 # vista previa, 10
    ./.venv/bin/python scripts/completar_convocatorias.py --aplicar --limite 30
    ./.venv/bin/python scripts/completar_convocatorias.py --ids 920 1109 --aplicar
"""

from __future__ import annotations

import argparse
import contextlib
import io
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))

from app.descubrimiento.guardar_descubrimientos import conectar  # noqa: E402
from app.enriquecer_convocatoria import (  # noqa: E402
    aplicar_enriquecimiento,
    cargar_contexto,
    construir_enriquecimiento,
)


def candidatas(limite: int, ids: list[int] | None) -> list[tuple[int, str, str]]:
    conexion = conectar()

    try:
        with conexion.cursor() as cursor:
            if ids:
                cursor.execute("SELECT id, estado, titulo FROM convocatorias WHERE id = ANY(%s) ORDER BY id", (ids,))
            else:
                # Primero las vigentes y pendientes; después el historial.
                cursor.execute(
                    """
                    SELECT c.id, c.estado, c.titulo
                    FROM convocatorias c
                    WHERE c.origen = 'SCRAPING'
                      AND c.estado IN ('PUBLICADA', 'PENDIENTE_REVISION', 'CERRADA')
                      AND c.url_original IS NOT NULL
                      AND (
                            COALESCE(LENGTH(c.descripcion), 0) < 200
                         OR COALESCE(LENGTH(c.objetivo), 0) < 40
                         OR c.fecha_cierre IS NULL
                         OR NOT EXISTS (SELECT 1 FROM convocatoria_requisitos r WHERE r.convocatoria_id = c.id)
                      )
                    ORDER BY (c.estado = 'CERRADA'), c.id DESC
                    LIMIT %s
                    """,
                    (limite,),
                )

            return cursor.fetchall()
    finally:
        conexion.close()


def main() -> None:
    parser = argparse.ArgumentParser(description="Completa convocatorias con poca información.")
    parser.add_argument("--limite", type=int, default=10)
    parser.add_argument("--ids", type=int, nargs="*")
    parser.add_argument("--aplicar", action="store_true", help="Guarda lo encontrado (sin esto, solo muestra).")
    args = parser.parse_args()

    filas = candidatas(max(1, min(args.limite, 200)), args.ids)
    print(f"{len(filas)} convocatoria(s). Modo: {'APLICAR' if args.aplicar else 'vista previa (no guarda)'}\n")

    completadas = 0

    for convocatoria_id, estado, titulo in filas:
        try:
            with contextlib.redirect_stdout(io.StringIO()):
                contexto = cargar_contexto(convocatoria_id, None)
                resultado = construir_enriquecimiento(contexto)

            faltantes = {
                "descripción": not contexto.get("descripcion") and resultado.get("descripcion"),
                "objetivo": not contexto.get("objetivo") and resultado.get("objetivo"),
                "cierre": contexto.get("fecha_cierre") is None and resultado.get("fecha_cierre"),
                "publicación": contexto.get("fecha_publicacion") is None and resultado.get("fecha_publicacion"),
                "monto": contexto.get("monto_maximo") is None and resultado.get("monto_maximo"),
                "requisitos": len(resultado.get("requisitos") or []),
            }
            nuevos = [f"{k}={v if not isinstance(v, str) else len(v)}" for k, v in faltantes.items() if v]

            if args.aplicar:
                with contextlib.redirect_stdout(io.StringIO()):
                    campos = aplicar_enriquecimiento(contexto, resultado)
                completadas += bool(campos)
                print(f"#{convocatoria_id} [{estado}] {titulo[:60]}\n    guardado: {', '.join(campos) or 'nada nuevo'}")
            else:
                print(f"#{convocatoria_id} [{estado}] {titulo[:60]}\n    encontraría: {', '.join(nuevos) or 'nada nuevo'}")

        except Exception as error:
            print(f"#{convocatoria_id} [{estado}] {titulo[:60]}\n    ERROR: {type(error).__name__}: {str(error)[:150]}")

    if args.aplicar:
        print(f"\nCompletadas: {completadas} de {len(filas)}")


if __name__ == "__main__":
    main()
