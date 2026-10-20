"""
Bitácora de incidencias del motor (tabla bitacora_errores, la misma que ve
el administrador en «Bitácora de errores»).

Antes los errores de cada fase del ciclo solo quedaban en el reporte JSON
y la bitácora aparecía vacía. Ahora cada error se registra; si el mismo
error sigue abierto (mismo tipo, URL y mensaje en los últimos 7 días), no
se duplica: se actualiza y se anota que se repitió.
"""

from __future__ import annotations

from app.descubrimiento.guardar_descubrimientos import conectar

# created_at/updated_at son «timestamp sin zona» y la web (Laravel) los lee
# como UTC; NOW() de esta conexión está en hora de México.
AHORA_UTC = "(NOW() AT TIME ZONE 'UTC')"

MAX_DETALLE = 4000


def registrar_incidencia(
    conexion,
    *,
    tipo: str,
    mensaje: str,
    codigo: str | None = None,
    url: str | None = None,
    detalle: str | None = None,
    fuente_id: int | None = None,
) -> str:
    """Devuelve 'NUEVA' o 'REPETIDA'. No hace commit."""
    mensaje = (mensaje or "Error sin mensaje").strip()[:2000]

    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT id
            FROM bitacora_errores
            WHERE resuelto = FALSE
              AND tipo_error = %s
              AND COALESCE(url, '') = COALESCE(%s, '')
              AND LEFT(mensaje, 300) = LEFT(%s, 300)
              AND created_at > (NOW() AT TIME ZONE 'UTC') - INTERVAL '7 days'
            ORDER BY id DESC
            LIMIT 1
            """,
            (tipo, url, mensaje),
        )
        fila = cursor.fetchone()

        if fila:
            cursor.execute(
                """
                UPDATE bitacora_errores
                SET updated_at = (NOW() AT TIME ZONE 'UTC'),
                    detalle = RIGHT(COALESCE(detalle, '') || E'\\nSe repitió: ' || to_char(NOW() AT TIME ZONE 'America/Merida', 'DD/MM/YYYY HH24:MI'), %s)
                WHERE id = %s
                """,
                (MAX_DETALLE, fila[0]),
            )
            return "REPETIDA"

        cursor.execute(
            """
            INSERT INTO bitacora_errores (tipo_error, codigo_error, mensaje, detalle, url, fuente_id, resuelto, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, FALSE, NOW() AT TIME ZONE 'UTC', NOW() AT TIME ZONE 'UTC')
            """,
            (tipo, codigo, mensaje, (detalle or "")[:MAX_DETALLE] or None, url, fuente_id),
        )
        return "NUEVA"


def _url_de_convocatoria(cursor, convocatoria_id) -> str | None:
    if not convocatoria_id:
        return None
    cursor.execute("SELECT url_original FROM convocatorias WHERE id = %s", (convocatoria_id,))
    fila = cursor.fetchone()
    return fila[0] if fila else None


def incidencias_del_ciclo(reporte: dict) -> list[dict]:
    """Extrae del reporte del ciclo una incidencia por cada error de cada fase."""
    incidencias = []

    descubrimiento = reporte.get("descubrimiento") or {}
    if descubrimiento and not descubrimiento.get("ok", True):
        incidencias.append({
            "tipo": "BUSCADOR",
            "codigo": str(descubrimiento.get("codigo") or ""),
            "mensaje": descubrimiento.get("error") or "El buscador terminó con error.",
            "detalle": (descubrimiento.get("salida") or "")[-1500:],
        })

    for item in (reporte.get("publicacion") or {}).get("resultados", []):
        if item.get("estado") not in ("PUBLICADA", "REVISION_FINAL", "ERROR_TEMPORAL", "MARCO", "PUBLICADO", "DESCARTADO") and (item.get("error") or item.get("estado") == "ERROR"):
            incidencias.append({
                "tipo": "PUBLICACION",
                "mensaje": item.get("error") or item.get("mensaje") or f"Error al procesar el descubrimiento #{item.get('descubrimiento_id')}",
                "detalle": f"Descubrimiento #{item.get('descubrimiento_id')}",
                "url": item.get("url"),
            })

    for item in (reporte.get("fuentes") or {}).get("resultados", []):
        if not item.get("ok", True):
            incidencias.append({
                "tipo": "FUENTE",
                "mensaje": item.get("error") or "La consulta de la fuente falló.",
                "detalle": f"Fuente: {item.get('nombre')}",
                "fuente_id": item.get("fuente_id"),
            })

    for item in (reporte.get("seguimiento") or {}).get("resultados", []):
        if item.get("estado") == "ERROR":
            incidencias.append({
                "tipo": "SEGUIMIENTO",
                "mensaje": item.get("error") or "No se pudo volver a consultar la convocatoria.",
                "detalle": f"Convocatoria #{item.get('id')}",
                "convocatoria_id": item.get("id"),
            })

    for item in (reporte.get("revalidacion") or {}).get("resultados", []):
        if item.get("estado") == "ERROR":
            incidencias.append({
                "tipo": "REVALIDACION",
                "mensaje": item.get("error") or "No se pudo revalidar la convocatoria.",
                "detalle": f"Convocatoria #{item.get('convocatoria_id')}",
                "convocatoria_id": item.get("convocatoria_id"),
            })

    mantenimiento = reporte.get("mantenimiento") or {}
    if mantenimiento.get("error"):
        incidencias.append({"tipo": "MANTENIMIENTO", "mensaje": mantenimiento["error"]})

    return incidencias


def registrar_incidencias_del_ciclo(reporte: dict) -> dict:
    """Nunca rompe el ciclo: si la bitácora falla, lo informa en el resultado."""
    resultado = {"nuevas": 0, "repetidas": 0, "error": None}

    try:
        incidencias = incidencias_del_ciclo(reporte)
        if not incidencias:
            return resultado

        conexion = conectar()
        try:
            with conexion.cursor() as cursor:
                for inc in incidencias:
                    url = inc.get("url") or _url_de_convocatoria(cursor, inc.get("convocatoria_id"))
                    estado = registrar_incidencia(
                        conexion,
                        tipo=inc["tipo"],
                        mensaje=inc["mensaje"],
                        codigo=inc.get("codigo"),
                        url=url,
                        detalle=inc.get("detalle"),
                        fuente_id=inc.get("fuente_id"),
                    )
                    resultado["nuevas" if estado == "NUEVA" else "repetidas"] += 1
            conexion.commit()
        finally:
            conexion.close()
    except Exception as error:  # noqa: BLE001 - la bitácora nunca debe tumbar el ciclo
        resultado["error"] = f"{type(error).__name__}: {error}"

    return resultado
