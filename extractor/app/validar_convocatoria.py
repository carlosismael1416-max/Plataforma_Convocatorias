from __future__ import annotations

import re
import unicodedata

from datetime import date
from urllib.parse import urlparse

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)


UMBRAL_PUBLICACION = 70


PALABRAS_BLOQUEO = (
    "premio",
    "premios",
    "ganadores",
    "ganadoras",
    "resultado",
    "resultados",
    "lista de aceptados",
    "lista de seleccionados",
    "congreso",
    "seminario",
    "simposio",
    "curso",
    "taller",
    "diplomado",
    "beca",
    "becas",
)


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

    titulo_normalizado = (
        normalizar_texto(
            titulo
        )
    )

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

    for palabra in PALABRAS_BLOQUEO:
        if palabra in titulo_normalizado:
            bloqueos.append(
                "El título corresponde a una "
                "categoría no publicable "
                f"automáticamente: {palabra}."
            )
            break

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

        "score":
            score,

        "umbral":
            UMBRAL_PUBLICACION,

        "razones":
            razones,

        "bloqueos":
            bloqueos,
    }


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

        if (
            publicar
            and resultado[
                "publicable"
            ]
        ):
            cursor = conexion.cursor()

            cursor.execute(
                """
                UPDATE convocatorias
                SET
                    estado = 'PUBLICADA',
                    updated_at = NOW()
                WHERE id = %s
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

            conexion.rollback()

        conexion.close()

        return resultado

    except Exception:
        try:
            conexion.rollback()
            conexion.close()
        except Exception:
            pass

        raise
