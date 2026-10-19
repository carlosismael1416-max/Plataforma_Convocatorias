"""Seguimiento de convocatorias publicadas.

Cada convocatoria vigente con enlace oficial se vuelve a consultar cada
~20 h (pocas por ciclo, solo HTTP; respeta robots.txt y las defensas SSRF de
descargar_articulo). Se compara con lo visto la vez anterior
(convocatoria_seguimientos):

- documentos nuevos (PDF): se agregan a convocatoria_archivos;
- fecha de cierre: si la página amplía el plazo, se actualiza la
  convocatoria; si lo adelanta, solo se avisa para verificar en el sitio;
- texto: un cambio de más del 2 % cuenta como actualización.

Si hubo cambios se avisa a quienes la siguen (Mis convocatorias) y a quienes
preparan una propuesta para ella, según su preferencia
«convocatorias_revision» (por defecto activa). La primera consulta solo
guarda la referencia, sin avisar.
"""

from __future__ import annotations

import difflib
import json
import time
from datetime import date

from app.descubrimiento.analizar_articulo import (
    descargar_articulo,
    extraer_texto_principal,
)
from app.descubrimiento.guardar_descubrimientos import conectar
from app.extraccion_detalle import extraer_detalle
from app.guardar_resultado_scrapy import guardar_documentos

HORAS_ENTRE_CONSULTAS = 20
MAX_POR_CICLO = 5
UMBRAL_CAMBIO_TEXTO = 0.98
MAX_TEXTO = 60000


def convocatorias_a_revisar(conexion, limite: int = MAX_POR_CICLO) -> list[dict]:
    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT
                c.id, c.titulo, c.url_original, c.fecha_cierre,
                (
                    c.fuente_id IS NOT NULL
                    AND (
                        NULLIF(BTRIM(COALESCE(c.descripcion, '')), '') IS NULL
                        OR NULLIF(BTRIM(COALESCE(c.objetivo, '')), '') IS NULL
                        OR c.organismo_id IS NULL
                        OR c.categoria_id IS NULL
                        OR c.monto_maximo IS NULL
                        OR c.fecha_inicio IS NULL
                        OR NOT EXISTS (
                            SELECT 1 FROM convocatoria_requisitos r
                            WHERE r.convocatoria_id = c.id
                        )
                    )
                ) AS incompleta
            FROM convocatorias c
            LEFT JOIN convocatoria_seguimientos s ON s.convocatoria_id = c.id
            WHERE c.estado IN ('PUBLICADA', 'ACTIVA')
              AND (c.fecha_cierre IS NULL OR c.fecha_cierre >= CURRENT_DATE)
              AND c.url_original ~* '^https?://'
              AND (
                  s.revisado_en IS NULL
                  OR s.revisado_en < NOW() - make_interval(hours => %s)
              )
            ORDER BY s.revisado_en NULLS FIRST, c.id
            LIMIT %s
            """,
            (HORAS_ENTRE_CONSULTAS, limite),
        )

        return [
            {"id": f[0], "titulo": f[1], "url": f[2], "fecha_cierre": f[3], "incompleta": f[4]}
            for f in cursor.fetchall()
        ]


def texto_cambio(anterior: str | None, actual: str) -> bool:
    if not anterior:
        return False

    similitud = difflib.SequenceMatcher(
        None,
        anterior.split(),
        actual.split(),
        autojunk=False,
    ).quick_ratio()

    if similitud >= UMBRAL_CAMBIO_TEXTO:
        return False

    return difflib.SequenceMatcher(
        None,
        anterior.split(),
        actual.split(),
        autojunk=False,
    ).ratio() < UMBRAL_CAMBIO_TEXTO


def comparar(previo: dict | None, actual: dict, fecha_registrada) -> dict:
    """Cambios entre lo guardado y lo descargado ahora (función pura)."""

    cambios = {
        "documentos_nuevos": [],
        "fecha_ampliada": None,
        "fecha_distinta": None,
        "texto": False,
    }

    if previo is None:
        return cambios

    urls_previas = {d.get("url") for d in previo.get("documentos") or []}
    cambios["documentos_nuevos"] = [
        d for d in actual["documentos"] if d.get("url") not in urls_previas
    ]

    fecha_web = actual.get("fecha_cierre_web")

    if fecha_web and fecha_web != fecha_registrada and fecha_web >= date.today():
        if fecha_registrada is None or fecha_web > fecha_registrada:
            cambios["fecha_ampliada"] = fecha_web
        else:
            cambios["fecha_distinta"] = fecha_web

    cambios["texto"] = texto_cambio(previo.get("texto"), actual["texto"])

    return cambios


def hay_cambios(cambios: dict) -> bool:
    return bool(
        cambios["documentos_nuevos"]
        or cambios["fecha_ampliada"]
        or cambios["fecha_distinta"]
        or cambios["texto"]
    )


def resumen(cambios: dict) -> str:
    partes = []

    if cambios["fecha_ampliada"]:
        partes.append(
            "La fecha de cierre ahora es el "
            + cambios["fecha_ampliada"].strftime("%d/%m/%Y") + "."
        )

    if cambios["fecha_distinta"]:
        partes.append(
            "La página muestra otra fecha de cierre ("
            + cambios["fecha_distinta"].strftime("%d/%m/%Y")
            + "); verifícala en el sitio oficial."
        )

    if cambios["documentos_nuevos"]:
        nombres = ", ".join(
            (d.get("nombre") or "Documento PDF")[:80]
            for d in cambios["documentos_nuevos"][:3]
        )
        partes.append(
            f"Documentos nuevos ({len(cambios['documentos_nuevos'])}): {nombres}."
        )

    if cambios["texto"]:
        partes.append("Se actualizó el texto de la convocatoria.")

    return " ".join(partes)


def descargar(url: str) -> dict:
    descarga = descargar_articulo(url)

    if descarga.get("estado") != "COMPLETADO" or not descarga.get("html"):
        return {"ok": False, "error": descarga.get("estado") or "ERROR"}

    html = descarga["html"]
    detalle = extraer_detalle(html, descarga.get("url_final") or url)

    fecha_web = detalle.get("fecha_cierre_web")

    if isinstance(fecha_web, str):
        try:
            fecha_web = date.fromisoformat(fecha_web[:10])
        except ValueError:
            fecha_web = None

    return {
        "ok": True,
        "texto": extraer_texto_principal(html, MAX_TEXTO),
        "documentos": [
            {"nombre": d.get("nombre"), "url": d.get("url")}
            for d in detalle.get("documentos") or []
        ],
        "fecha_cierre_web": fecha_web,
    }


def destinatarios(cursor, convocatoria_id: int) -> list[int]:
    cursor.execute(
        """
        SELECT DISTINCT u.id
        FROM users u
        LEFT JOIN notificacion_preferencias p ON p.user_id = u.id
        WHERE u.estado = TRUE
          AND COALESCE(p.convocatorias_revision, TRUE) = TRUE
          AND (
              u.id IN (SELECT user_id FROM usuario_convocatorias WHERE convocatoria_id = %s)
              OR u.id IN (SELECT user_id FROM propuestas WHERE convocatoria_id = %s)
          )
        """,
        (convocatoria_id, convocatoria_id),
    )

    return [fila[0] for fila in cursor.fetchall()]


def guardar_seguimiento(cursor, convocatoria_id, datos, resultado, texto_resumen, cambio: bool):
    cursor.execute(
        """
        INSERT INTO convocatoria_seguimientos (
            convocatoria_id, texto, documentos, fecha_cierre_web,
            revisado_en, ultimo_cambio_en, ultimo_resultado, ultimo_resumen,
            created_at, updated_at
        )
        VALUES (%s, %s, %s::jsonb, %s, NOW(), CASE WHEN %s THEN NOW() END, %s, %s, NOW(), NOW())
        ON CONFLICT (convocatoria_id) DO UPDATE SET
            texto = COALESCE(EXCLUDED.texto, convocatoria_seguimientos.texto),
            documentos = CASE
                WHEN EXCLUDED.ultimo_resultado = 'ERROR' THEN convocatoria_seguimientos.documentos
                ELSE EXCLUDED.documentos
            END,
            fecha_cierre_web = COALESCE(EXCLUDED.fecha_cierre_web, convocatoria_seguimientos.fecha_cierre_web),
            -- Un error se reintenta en el siguiente ciclo (6 h), no en 20 h.
            revisado_en = CASE
                WHEN EXCLUDED.ultimo_resultado = 'ERROR'
                    THEN NOW() - make_interval(hours => %s)
                ELSE NOW()
            END,
            ultimo_cambio_en = COALESCE(EXCLUDED.ultimo_cambio_en, convocatoria_seguimientos.ultimo_cambio_en),
            ultimo_resultado = EXCLUDED.ultimo_resultado,
            ultimo_resumen = COALESCE(EXCLUDED.ultimo_resumen, convocatoria_seguimientos.ultimo_resumen),
            updated_at = NOW()
        """,
        (
            convocatoria_id,
            datos.get("texto"),
            json.dumps(datos.get("documentos") or [], ensure_ascii=False),
            datos.get("fecha_cierre_web"),
            cambio,
            resultado,
            texto_resumen,
            HORAS_ENTRE_CONSULTAS - 6,
        ),
    )


def revisar_convocatoria(conexion, conv: dict) -> str:
    datos = descargar(conv["url"])

    with conexion.transaction():
        with conexion.cursor() as cursor:
            if not datos["ok"]:
                guardar_seguimiento(cursor, conv["id"], {}, "ERROR", None, False)
                return "ERROR"

            cursor.execute(
                "SELECT texto, documentos FROM convocatoria_seguimientos WHERE convocatoria_id = %s",
                (conv["id"],),
            )
            fila = cursor.fetchone()
            # Una fila que solo registra errores (sin texto) no sirve de
            # referencia: la siguiente consulta exitosa es la inicial.
            previo = (
                {"texto": fila[0], "documentos": fila[1] or []}
                if fila and fila[0]
                else None
            )

            cambios = comparar(previo, datos, conv["fecha_cierre"])

            if previo is None:
                guardar_seguimiento(cursor, conv["id"], datos, "INICIAL", None, False)
                return "INICIAL"

            if not hay_cambios(cambios):
                guardar_seguimiento(cursor, conv["id"], datos, "SIN_CAMBIOS", None, False)
                return "SIN_CAMBIOS"

            if cambios["documentos_nuevos"]:
                guardar_documentos(cursor, conv["id"], cambios["documentos_nuevos"])

            if cambios["fecha_ampliada"]:
                cursor.execute(
                    "UPDATE convocatorias SET fecha_cierre = %s, updated_at = NOW() WHERE id = %s",
                    (cambios["fecha_ampliada"], conv["id"]),
                )

            texto_resumen = resumen(cambios)
            guardar_seguimiento(cursor, conv["id"], datos, "CAMBIOS", texto_resumen, True)

            for usuario_id in destinatarios(cursor, conv["id"]):
                cursor.execute(
                    """
                    INSERT INTO notificaciones (
                        user_id, convocatoria_id, titulo, mensaje, tipo, leida, created_at, updated_at
                    )
                    VALUES (%s, %s, %s, %s, 'CONVOCATORIA_ACTUALIZADA', FALSE, NOW(), NOW())
                    """,
                    (
                        usuario_id,
                        conv["id"],
                        "Una convocatoria que sigues cambió",
                        "«" + (conv["titulo"] or "")[:200] + "» " + texto_resumen,
                    ),
                )

            return "CAMBIOS"


def completar_datos(convocatoria_id: int) -> list[str]:
    """Corre el enriquecimiento (solo llena campos vacíos)."""

    from app.enriquecer_convocatoria import (
        aplicar_enriquecimiento,
        cargar_contexto,
        construir_enriquecimiento,
    )

    contexto = cargar_contexto(convocatoria_id, None)

    return aplicar_enriquecimiento(
        contexto,
        construir_enriquecimiento(contexto),
    )


def retirar_no_publicables(conexion) -> list[dict]:
    """
    Publicadas por el motor que hoy el validador bloquearía por su objetivo
    (becas o apoyos a estudiantes): vuelven a PENDIENTE_REVISION, estado
    interno que nadie ve. Las subidas a mano por el personal no se tocan.
    """

    from app.validar_convocatoria import bloqueo_por_objetivo

    retiradas = []

    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT id, titulo, COALESCE(objetivo, ''), COALESCE(descripcion, '')
            FROM convocatorias
            WHERE estado IN ('PUBLICADA', 'ACTIVA')
              AND origen = 'SCRAPING'
            """
        )

        for convocatoria_id, titulo, objetivo, descripcion in cursor.fetchall():
            motivo = bloqueo_por_objetivo(objetivo, descripcion)

            if not motivo:
                continue

            cursor.execute(
                """
                UPDATE convocatorias
                SET estado = 'PENDIENTE_REVISION', updated_at = NOW()
                WHERE id = %s AND estado IN ('PUBLICADA', 'ACTIVA')
                """,
                (convocatoria_id,),
            )
            retiradas.append({"id": convocatoria_id, "motivo": motivo})
            print(f"  [RETIRADA] {convocatoria_id} {titulo[:80]} ({motivo})")

    return retiradas


def seguir_convocatorias(*, terminar_antes_de: float, limite: int = MAX_POR_CICLO) -> dict:
    resultado = {
        "revisadas": 0,
        "con_cambios": 0,
        "iniciales": 0,
        "errores": 0,
        "pospuestas": 0,
        "completadas": 0,
        "resultados": [],
    }

    conexion = conectar()
    # Cada convocatoria se confirma en su propia transacción
    # (conexion.transaction()); sin autocommit serían savepoints de una
    # transacción implícita que nunca se confirmaría.
    conexion.autocommit = True

    try:
        resultado["retiradas"] = retirar_no_publicables(conexion)
        pendientes = convocatorias_a_revisar(conexion, limite)

        for conv in pendientes:
            if terminar_antes_de - time.monotonic() < 120:
                resultado["pospuestas"] += 1
                continue

            try:
                estado = revisar_convocatoria(conexion, conv)
            except Exception as error:  # noqa: BLE001 - se registra y se sigue con la siguiente
                estado = "ERROR"
                print("Error al seguir la convocatoria", conv["id"], error)

            resultado["revisadas"] += 1
            resultado["con_cambios"] += estado == "CAMBIOS"
            resultado["iniciales"] += estado == "INICIAL"
            resultado["errores"] += estado == "ERROR"
            campos = []

            # A las publicadas incompletas se les completa la información
            # con el enriquecimiento (descripción, objetivo, organismo, montos
            # y fechas). Pesa más (lee los PDF), por eso solo cuando falta algo.
            if (
                estado != "ERROR"
                and conv.get("incompleta")
                and terminar_antes_de - time.monotonic() > 300
            ):
                try:
                    campos = completar_datos(conv["id"])
                except Exception as error:  # noqa: BLE001
                    print("No se pudo completar la convocatoria", conv["id"], error)

                resultado["completadas"] += bool(campos)

            resultado["resultados"].append({"id": conv["id"], "estado": estado, "campos_completados": campos})
            print(f"  [{estado}] {conv['id']} {conv['titulo'][:80]}" + (f" (+{', '.join(campos)})" if campos else ""))
    finally:
        conexion.close()

    return resultado
