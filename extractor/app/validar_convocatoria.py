from __future__ import annotations

import json
import re
import unicodedata

from datetime import date
from urllib.parse import urlparse

from app.descubrimiento.elegibilidad import (
    evaluar_elegibilidad,
)

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.tipo_convocatoria import (
    clasificar_tipo,
    excluye_mexicanos,
)


UMBRAL_PUBLICACION = 70


# Desde el 18-oct-2026 becas, premios, concursos, eventos y formación se
# publican marcados con su tipo y a quién van dirigidos
# (app/tipo_convocatoria.py). Solo se bloquean los anuncios de resultados,
# las vacantes/plazas, lo no elegible para ITSVA y los programas que
# excluyen a mexicanos.


def bloqueo_por_objetivo(objetivo: str, descripcion: str) -> str | None:
    """Bloqueo duro que solo se ve en el objetivo o la descripción."""
    if excluye_mexicanos(objetivo, descripcion):
        return "excluye a participantes mexicanos"

    return None


def normalizar_texto(
    valor: str | None,
) -> str:
    valor = valor or ""

    valor = unicodedata.normalize(
        "NFKD",
        valor,
    )

    valor = "".join(
        caracter
        for caracter in valor
        if not unicodedata.combining(
            caracter
        )
    )

    valor = valor.casefold()

    valor = re.sub(
        r"\s+",
        " ",
        valor,
    )

    return valor.strip()


def url_valida(
    url: str | None,
) -> bool:
    if not url:
        return False

    try:
        parsed = urlparse(
            url
        )

        return (
            parsed.scheme
            in {
                "http",
                "https",
            }
            and bool(
                parsed.hostname
            )
        )

    except Exception:
        return False


def cargar_convocatoria(
    conexion,
    convocatoria_id: int,
) -> dict | None:
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            titulo,
            descripcion,
            objetivo,
            fecha_publicacion,
            fecha_inicio,
            fecha_cierre,
            monto_minimo,
            monto_maximo,
            moneda,
            modalidad,
            ubicacion,
            categoria_id,
            organismo_id,
            fuente_id,
            url_original,
            url_hash,
            contenido_hash,
            origen,
            estado,
            fecha_extraccion
        FROM convocatorias
        WHERE id = %s
        """,
        (
            convocatoria_id,
        ),
    )

    fila = cursor.fetchone()

    if fila is None:
        cursor.close()
        return None

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    resultado = dict(
        zip(
            columnas,
            fila,
        )
    )

    cursor.close()

    return resultado


def cargar_descubrimiento(
    conexion,
    descubrimiento_id: int | None,
) -> dict | None:
    if descubrimiento_id is None:
        return None

    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            evaluacion_score,
            decision_objetivo,
            categoria_objetivo,
            estado_temporal,
            dominio,
            url_original
        FROM descubrimientos_web
        WHERE id = %s
        """,
        (
            descubrimiento_id,
        ),
    )

    fila = cursor.fetchone()

    if fila is None:
        cursor.close()
        return None

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    resultado = dict(
        zip(
            columnas,
            fila,
        )
    )

    cursor.close()

    return resultado


def contar_url_duplicada(
    conexion,
    convocatoria: dict,
) -> int:
    url_hash = convocatoria.get(
        "url_hash"
    )

    if not url_hash:
        return 0

    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT COUNT(*)
        FROM convocatorias
        WHERE url_hash = %s
          AND id <> %s
        """,
        (
            url_hash,
            convocatoria["id"],
        ),
    )

    total = int(
        cursor.fetchone()[0]
        or 0
    )

    cursor.close()

    return total


def evaluar_convocatoria(
    convocatoria: dict,
    descubrimiento: dict | None = None,
    *,
    duplicados: int = 0,
) -> dict:
    score = 0
    razones = []
    bloqueos = []

    titulo = (
        convocatoria.get(
            "titulo"
        )
        or ""
    ).strip()

    descripcion = (
        convocatoria.get(
            "descripcion"
        )
        or ""
    ).strip()

    objetivo = (
        convocatoria.get(
            "objetivo"
        )
        or ""
    ).strip()

    url = (
        convocatoria.get(
            "url_original"
        )
        or ""
    ).strip()

    # =========================================================
    # TÍTULO
    # =========================================================

    if (
        len(titulo) >= 15
        and not titulo.lower().startswith(
            (
                "http://",
                "https://",
            )
        )
    ):
        score += 15
        razones.append(
            "+15 título válido"
        )

    else:
        bloqueos.append(
            "Título insuficiente o inválido."
        )

    clasificacion = clasificar_tipo(
        titulo,
        url,
        objetivo,
        descripcion,
    )

    if clasificacion["tipo"] == "NO_APLICA":
        bloqueos.append(
            "No es una convocatoria para participar: "
            f"{clasificacion['motivo']}."
        )

    motivo_objetivo = bloqueo_por_objetivo(
        objetivo,
        descripcion,
    )

    if motivo_objetivo:
        bloqueos.append(
            "No elegible para ITSVA: "
            f"{motivo_objetivo}."
        )

    elegibilidad = evaluar_elegibilidad(
        titulo,
        url,
    )

    if not elegibilidad["elegible"]:
        bloqueos.append(
            "No elegible para ITSVA: "
            + elegibilidad["motivo"]
        )

    # =========================================================
    # URL
    # =========================================================

    if url_valida(
        url
    ):
        score += 15
        razones.append(
            "+15 URL válida"
        )

    else:
        bloqueos.append(
            "No existe una URL oficial válida."
        )

    # =========================================================
    # FUENTE
    # =========================================================

    if convocatoria.get(
        "fuente_id"
    ):
        score += 15
        razones.append(
            "+15 fuente identificada"
        )

    else:
        bloqueos.append(
            "No existe fuente asociada."
        )

    # =========================================================
    # INFORMACIÓN EXTRAÍDA
    # =========================================================

    if len(
        descripcion
    ) >= 80:
        score += 10
        razones.append(
            "+10 descripción suficiente"
        )

    if len(
        objetivo
    ) >= 40:
        score += 10
        razones.append(
            "+10 objetivo identificado"
        )

    if convocatoria.get(
        "organismo_id"
    ):
        score += 10
        razones.append(
            "+10 organismo identificado"
        )

    if convocatoria.get(
        "categoria_id"
    ):
        score += 5
        razones.append(
            "+5 categoría identificada"
        )

    fecha_cierre = convocatoria.get(
        "fecha_cierre"
    )

    if fecha_cierre:
        if fecha_cierre < date.today():
            bloqueos.append(
                "La fecha de cierre ya pasó."
            )

        else:
            score += 15
            razones.append(
                "+15 fecha de cierre vigente"
            )

    else:
        bloqueos.append(
            "No existe una fecha de cierre "
            "verificable."
        )

    if (
        convocatoria.get(
            "monto_minimo"
        )
        is not None
        or convocatoria.get(
            "monto_maximo"
        )
        is not None
    ):
        score += 5
        razones.append(
            "+5 financiamiento identificado"
        )

    if (
        convocatoria.get(
            "modalidad"
        )
        or convocatoria.get(
            "ubicacion"
        )
    ):
        score += 5
        razones.append(
            "+5 modalidad/ubicación identificada"
        )

    # =========================================================
    # RESPALDO DEL DESCUBRIDOR
    # =========================================================

    if descubrimiento:
        if (
            descubrimiento.get(
                "decision_objetivo"
            )
            == "ACEPTAR"
            and (
                descubrimiento.get(
                    "evaluacion_score"
                )
                or 0
            ) >= 80
        ):
            score += 15
            razones.append(
                "+15 descubrimiento aprobado"
            )

        estado_temporal = (
            descubrimiento.get(
                "estado_temporal"
            )
            or ""
        )

        if estado_temporal == "HISTORICA":
            bloqueos.append(
                "El descubrimiento fue "
                "clasificado como histórico."
            )

        elif estado_temporal in {
            "ACTUAL",
            "FUTURA",
        }:
            score += 5
            razones.append(
                "+5 temporalidad válida"
            )

    # =========================================================
    # CONTENIDO MÍNIMO
    # =========================================================

    tiene_contenido_util = any([
        len(
            descripcion
        ) >= 80,

        len(
            objetivo
        ) >= 40,

        fecha_cierre is not None,

        convocatoria.get(
            "organismo_id"
        )
        is not None,

        convocatoria.get(
            "monto_minimo"
        )
        is not None,

        convocatoria.get(
            "monto_maximo"
        )
        is not None,
    ])

    if not tiene_contenido_util:
        bloqueos.append(
            "La extracción no obtuvo "
            "información suficiente."
        )

    # =========================================================
    # DUPLICADOS
    # =========================================================

    if duplicados > 0:
        bloqueos.append(
            "Existe otra convocatoria "
            "con el mismo url_hash."
        )

    score = max(
        0,
        min(
            score,
            100,
        ),
    )

    publicable = (
        score >= UMBRAL_PUBLICACION
        and not bloqueos
    )

    return {
        "publicable":
            publicable,

        # Lo que nunca debe mostrarse (resultados, vacantes, no elegible)
        # queda NO_APLICA; así tampoco entra al historial.
        "tipo":
            "NO_APLICA"
            if any(
                bloqueo.startswith(("No elegible", "No es una convocatoria"))
                for bloqueo in bloqueos
            )
            else clasificacion["tipo"],

        "dirigido_a":
            clasificacion["dirigido_a"],

        "score":
            score,

        "umbral":
            UMBRAL_PUBLICACION,

        "razones":
            razones,

        "bloqueos":
            bloqueos,
    }


_COLUMNAS_TIPO: bool | None = None


def columnas_tipo_disponibles(conexion) -> bool:
    """¿Ya existe convocatorias.tipo? (migración 2026_10_18_220000)."""
    global _COLUMNAS_TIPO

    if _COLUMNAS_TIPO is None:
        cursor = conexion.cursor()
        cursor.execute(
            """
            SELECT COUNT(*)
            FROM information_schema.columns
            WHERE table_name = 'convocatorias'
              AND column_name IN ('tipo', 'dirigido_a')
            """
        )
        _COLUMNAS_TIPO = int(cursor.fetchone()[0]) == 2
        cursor.close()

    return _COLUMNAS_TIPO


def guardar_clasificacion(
    conexion,
    convocatoria_id: int,
    tipo: str,
    dirigido_a: list[str],
) -> None:
    if not columnas_tipo_disponibles(conexion):
        return

    cursor = conexion.cursor()
    cursor.execute(
        """
        UPDATE convocatorias
        SET tipo = %s,
            dirigido_a = %s::jsonb
        WHERE id = %s
          AND (tipo IS DISTINCT FROM %s OR dirigido_a IS DISTINCT FROM %s::jsonb)
        """,
        (
            tipo,
            json.dumps(dirigido_a),
            convocatoria_id,
            tipo,
            json.dumps(dirigido_a),
        ),
    )
    cursor.close()


def validar_convocatoria(
    convocatoria_id: int,
    descubrimiento_id: int | None = None,
    *,
    publicar: bool = False,
) -> dict:
    conexion = conectar()

    try:
        convocatoria = (
            cargar_convocatoria(
                conexion,
                convocatoria_id,
            )
        )

        if convocatoria is None:
            conexion.rollback()
            conexion.close()

            return {
                "publicable": False,
                "score": 0,
                "razones": [],
                "bloqueos": [
                    "La convocatoria no existe."
                ],
                "estado_final":
                    "NO_ENCONTRADA",
            }

        descubrimiento = (
            cargar_descubrimiento(
                conexion,
                descubrimiento_id,
            )
        )

        duplicados = (
            contar_url_duplicada(
                conexion,
                convocatoria,
            )
        )

        resultado = evaluar_convocatoria(
            convocatoria,
            descubrimiento,
            duplicados=duplicados,
        )

        # Las subidas a mano conservan el tipo que eligió su autor.
        if convocatoria.get("origen") != "MANUAL":
            guardar_clasificacion(
                conexion,
                convocatoria_id,
                resultado["tipo"],
                resultado["dirigido_a"],
            )

        # Un administrador la descartó a mano: el motor nunca la publica.
        if (
            publicar
            and resultado[
                "publicable"
            ]
            and convocatoria.get("estado") != "DESCARTADA"
        ):
            cursor = conexion.cursor()

            cursor.execute(
                """
                UPDATE convocatorias
                SET
                    estado = 'PUBLICADA',
                    updated_at = NOW()
                WHERE id = %s
                  AND estado <> 'DESCARTADA'
                """,
                (
                    convocatoria_id,
                ),
            )

            cursor.close()

            resultado[
                "estado_final"
            ] = "PUBLICADA"

            conexion.commit()

        else:
            resultado[
                "estado_final"
            ] = (
                convocatoria.get(
                    "estado"
                )
            )

            # Solo se guarda la clasificación (si hubo).
            conexion.commit()

        conexion.close()

        return resultado

    except Exception:
        try:
            conexion.rollback()
            conexion.close()
        except Exception:
            pass

        raise
