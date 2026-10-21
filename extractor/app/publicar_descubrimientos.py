from __future__ import annotations

import argparse
import json
import subprocess
import sys
import time

from difflib import SequenceMatcher
from urllib.parse import urlparse

from app.descubrimiento.elegibilidad import (
    evaluar_elegibilidad,
)

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.seguridad_red import (
    validar_url_publica,
)

from app.urls import (
    hash_url,
    normalizar_url,
)

from app.validar_convocatoria import (
    validar_convocatoria,
)

from app.enriquecer_convocatoria import (
    aplicar_enriquecimiento,
    cargar_contexto,
    construir_enriquecimiento,
    construir_item_pagina_directa,
    descargar_html_pagina_segura,
    descargar_pdf_seguro,
    es_url_pdf,
)
from app.guardar_resultado_scrapy import guardar_resultado_scrapy
from app.importar_desde_url import titulo_de_pagina



def dominio_url(
    url: str | None,
) -> str:
    try:
        return (
            urlparse(
                url or ""
            ).hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

    except Exception:
        return ""



def es_error_temporal(
    salida: str | None,
) -> bool:
    texto = (
        salida
        or ""
    ).casefold()

    indicadores = (
        "downloadtimeouterror",
        "user timeout caused connection failure",
        "timeout caused connection failure",
        "timed out",
        "connection timed out",
        "temporary failure",
        "temporarily unavailable",
        "connection reset",
        "connection refused",
        "502 bad gateway",
        "503 service unavailable",
        "504 gateway timeout",
    )

    return any(
        indicador in texto
        for indicador in indicadores
    )


MAX_INTENTOS = 3


def cerrar_temporales_agotados(
    cursor,
) -> int:
    # Un ERROR_TEMPORAL sin intentos restantes ya no se
    # reintenta; se marca la fuente como inaccesible para
    # buscar un canal oficial alterno (no evadir bloqueos).
    cursor.execute(
        """
        UPDATE descubrimientos_web
        SET
            estado = 'FUENTE_INACCESIBLE',

            error_ultimo =
                'FUENTE_INACCESIBLE: se agotaron '
                || intentos_procesamiento
                || ' intentos. Buscar un canal '
                || 'oficial alterno. Último error: '
                || COALESCE(
                    error_ultimo,
                    ''
                ),

            updated_at = NOW()

        WHERE estado = 'ERROR_TEMPORAL'
          AND intentos_procesamiento >= %s
        """,
        (
            MAX_INTENTOS,
        ),
    )

    return cursor.rowcount


def cerrar_publicadas_vencidas(
    cursor,
) -> int:
    # Una convocatoria PUBLICADA no cambiaba de estado al vencer.
    # Se cierran las que ya pasaron su fecha de cierre y los
    # padres cuyas hijas están todas cerradas.
    cursor.execute(
        """
        UPDATE convocatorias padre
        SET
            estado = 'CERRADA',
            updated_at = NOW()
        WHERE padre.estado = 'PUBLICADA'
          AND (
                padre.fecha_cierre < CURRENT_DATE
                OR (
                    EXISTS (
                        SELECT 1
                        FROM convocatorias hija
                        WHERE hija.convocatoria_padre_id
                              = padre.id
                    )
                    AND NOT EXISTS (
                        SELECT 1
                        FROM convocatorias hija
                        WHERE hija.convocatoria_padre_id
                              = padre.id
                          AND hija.estado <> 'CERRADA'
                    )
                )
              )
        """
    )

    return cursor.rowcount


HORAS_MAX_EN_PROCESO = 2


def recuperar_en_proceso_abandonados(
    cursor,
) -> int:
    # Si systemd corta el ciclo (TimeoutStartSec) o WSL se apaga,
    # el descubrimiento queda en EN_PROCESO y nada lo vuelve a
    # tomar. Un intento tarda como máximo ~25 min (extracción,
    # marco y enriquecimiento); pasadas 2 h se considera
    # abandonado. El intento ya se contó al pasar a EN_PROCESO.
    cursor.execute(
        """
        UPDATE descubrimientos_web
        SET
            estado = 'ERROR_TEMPORAL',
            error_ultimo =
                'ERROR_TEMPORAL: el procesamiento '
                || 'se interrumpió (EN_PROCESO '
                || 'abandonado).',
            updated_at = NOW()
        WHERE estado = 'EN_PROCESO'
          AND updated_at < NOW()
              - make_interval(hours => %s)
        """,
        (
            HORAS_MAX_EN_PROCESO,
        ),
    )

    return cursor.rowcount


def descartar_temporales_no_elegibles(
    cursor,
) -> int:
    # El evaluador no revisa los ERROR_TEMPORAL; sin esto se
    # reintentaría una convocatoria en la que ITSVA no participa.
    cursor.execute(
        """
        SELECT id, titulo, url_original
        FROM descubrimientos_web
        WHERE estado = 'ERROR_TEMPORAL'
        """
    )

    descartados = 0

    for (
        descubrimiento_id,
        titulo,
        url,
    ) in cursor.fetchall():
        elegibilidad = evaluar_elegibilidad(
            titulo,
            url,
        )

        if elegibilidad["elegible"]:
            continue

        cursor.execute(
            """
            UPDATE descubrimientos_web
            SET
                estado = 'DESCARTADO',
                error_ultimo = %s,
                updated_at = NOW()
            WHERE id = %s
            """,
            (
                "No elegible para ITSVA: "
                + elegibilidad["motivo"],
                descubrimiento_id,
            ),
        )

        descartados += 1

    return descartados


def obtener_aprobados(
    limite: int,
) -> list[dict]:
    conexion = conectar()
    cursor = conexion.cursor()

    vencidas = cerrar_publicadas_vencidas(
        cursor
    )

    recuperados = recuperar_en_proceso_abandonados(
        cursor
    )

    no_elegibles = descartar_temporales_no_elegibles(
        cursor
    )

    cerrados = cerrar_temporales_agotados(
        cursor
    )

    conexion.commit()

    if vencidas:
        print(
            "Convocatorias publicadas cerradas "
            f"por vencimiento: {vencidas}"
        )

    if recuperados:
        print(
            "Recuperados de EN_PROCESO "
            f"abandonado: {recuperados}"
        )

    if no_elegibles:
        print(
            "Descartados por no ser elegibles "
            f"para ITSVA: {no_elegibles}"
        )

    if cerrados:
        print(
            "Marcados como FUENTE_INACCESIBLE "
            f"(intentos agotados): {cerrados}"
        )

    cursor.execute(
        """
        SELECT
            id,
            titulo,
            url_original,
            dominio,
            evaluacion_score,
            fuente_id
        FROM descubrimientos_web
        WHERE (
                (
                    estado = 'APROBADO'
                    AND convocatoria_id IS NULL
                )

                OR (
                    estado = 'ERROR_TEMPORAL'
                    AND evaluacion_score >= 80
                    AND intentos_procesamiento < %s
                    -- El timer corre cada 6 h y updated_at se
                    -- guarda al final del ciclo; con 6 h exactas
                    -- se saltaría un ciclo. 5 h deja margen.
                    AND updated_at <= (
                        NOW()
                        - INTERVAL '5 hours'
                    )
                )
              )
        ORDER BY
            evaluacion_score DESC,
            id ASC
        LIMIT %s
        """,
        (
            MAX_INTENTOS,
            limite,
        ),
    )

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    filas = [
        dict(
            zip(
                columnas,
                fila,
            )
        )
        for fila in cursor.fetchall()
    ]

    conexion.rollback()
    cursor.close()
    conexion.close()

    return filas


def buscar_fuente(
    conexion,
    url: str,
) -> int | None:
    dominio = dominio_url(
        url
    )

    cursor = conexion.cursor()

    cursor.execute("""
        SELECT
            id,
            url_base
        FROM fuentes
        ORDER BY id
    """)

    for fuente_id, url_base in cursor.fetchall():
        host = dominio_url(
            url_base
        )

        if not host:
            continue

        if (
            dominio == host
            or dominio.endswith(
                "." + host
            )
            or host.endswith(
                "." + dominio
            )
        ):
            cursor.close()
            return int(
                fuente_id
            )

    cursor.close()

    return None


def crear_fuente(
    conexion,
    url: str,
) -> int:
    dominio = dominio_url(
        url
    )

    if not dominio:
        raise RuntimeError(
            "La URL no contiene dominio."
        )

    selector_config = {
        "creada_automaticamente":
            True,

        "dominio":
            dominio,
    }

    cursor = conexion.cursor()

    cursor.execute(
        """
        INSERT INTO fuentes (
            nombre,
            url_base,
            tipo_fuente,
            selector_config,
            requiere_javascript,
            activa,
            frecuencia_scraping,
            created_at,
            updated_at
        )
        VALUES (
            %s,
            %s,
            'WEB',
            %s::jsonb,
            FALSE,
            TRUE,
            NULL,
            NOW(),
            NOW()
        )
        RETURNING id
        """,
        (
            (
                "Descubrimiento automático - "
                + dominio
            ),
            (
                "https://"
                + dominio
                + "/"
            ),
            json.dumps(
                selector_config,
                ensure_ascii=False,
            ),
        ),
    )

    fuente_id = int(
        cursor.fetchone()[0]
    )

    cursor.close()

    return fuente_id


def obtener_o_crear_fuente(
    url: str,
) -> int:
    validar_url_publica(
        url
    )

    conexion = conectar()

    try:
        fuente_id = buscar_fuente(
            conexion,
            url,
        )

        if fuente_id is not None:
            conexion.rollback()
            conexion.close()

            return fuente_id

        fuente_id = crear_fuente(
            conexion,
            url,
        )

        conexion.commit()
        conexion.close()

        return fuente_id

    except Exception:
        try:
            conexion.rollback()
            conexion.close()
        except Exception:
            pass

        raise


def actualizar_descubrimiento(
    descubrimiento_id: int,
    *,
    estado: str,
    fuente_id: int | None = None,
    convocatoria_id: int | None = None,
    error: str | None = None,
    salida: str | None = None,
    incrementar_intento: bool = False,
    procesado: bool = False,
) -> None:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        UPDATE descubrimientos_web
        SET
            estado = %s,

            fuente_id = COALESCE(
                %s,
                fuente_id
            ),

            convocatoria_id = COALESCE(
                %s,
                convocatoria_id
            ),

            error_ultimo = %s,

            ultima_salida_extractor =
                COALESCE(
                    %s,
                    ultima_salida_extractor
                ),

            intentos_procesamiento =
                intentos_procesamiento
                + CASE
                    WHEN %s
                    THEN 1
                    ELSE 0
                  END,

            procesado_en =
                CASE
                    WHEN %s
                    THEN NOW()
                    ELSE procesado_en
                END,

            updated_at = NOW()

        WHERE id = %s
        """,
        (
            estado,
            fuente_id,
            convocatoria_id,
            error,
            (
                salida[-20000:]
                if salida
                else None
            ),
            incrementar_intento,
            procesado,
            descubrimiento_id,
        ),
    )

    conexion.commit()
    cursor.close()
    conexion.close()


def max_convocatoria_id(
    conexion,
) -> int:
    cursor = conexion.cursor()

    cursor.execute("""
        SELECT COALESCE(
            MAX(id),
            0
        )
        FROM convocatorias
    """)

    valor = int(
        cursor.fetchone()[0]
        or 0
    )

    cursor.close()

    return valor


def buscar_convocatoria_exacta(
    conexion,
    url: str,
) -> dict | None:
    cursor = conexion.cursor()

    # url_hash es único e indexado y se calcula con la misma
    # normalización en todo el sistema (app/urls.py).
    cursor.execute(
        """
        SELECT
            id,
            titulo,
            url_original,
            fuente_id,
            estado
        FROM convocatorias
        WHERE url_hash = %s
        LIMIT 1
        """,
        (
            hash_url(
                url
            ),
        ),
    )

    fila = cursor.fetchone()
    cursor.close()

    if fila is None:
        return None

    return {
        "id":
            fila[0],
        "titulo":
            fila[1],
        "url":
            fila[2],
        "fuente_id":
            fila[3],
        "estado":
            fila[4],
    }


def buscar_nuevas(
    conexion,
    despues_de: int,
    fuente_id: int,
) -> list[dict]:
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            titulo,
            url_original,
            fuente_id,
            estado
        FROM convocatorias
        WHERE id > %s
        ORDER BY id
        """,
        (
            despues_de,
        ),
    )

    resultados = []

    for fila in cursor.fetchall():
        resultados.append({
            "id":
                fila[0],

            "titulo":
                fila[1],

            "url":
                fila[2],

            "fuente_id":
                fila[3],

            "estado":
                fila[4],
        })

    cursor.close()

    misma_fuente = [
        item
        for item in resultados
        if item.get(
            "fuente_id"
        ) == fuente_id
    ]

    return (
        misma_fuente
        if misma_fuente
        else resultados
    )


def similitud_titulo(
    a: str | None,
    b: str | None,
) -> float:
    return SequenceMatcher(
        None,
        (
            a
            or ""
        ).casefold(),

        (
            b
            or ""
        ).casefold(),
    ).ratio()


def elegir_convocatoria(
    descubrimiento: dict,
    candidatas: list[dict],
) -> dict | None:
    if not candidatas:
        return None

    url_objetivo = normalizar_url(
        descubrimiento[
            "url_original"
        ]
    )

    for candidata in candidatas:
        if normalizar_url(
            candidata.get(
                "url"
            )
        ) == url_objetivo:
            return candidata

    puntuadas = sorted(
        [
            (
                similitud_titulo(
                    descubrimiento.get(
                        "titulo"
                    ),
                    candidata.get(
                        "titulo"
                    ),
                ),
                candidata,
            )
            for candidata in candidatas
        ],
        key=lambda item:
            item[0],
        reverse=True,
    )

    mejor_score, mejor = (
        puntuadas[0]
    )

    if mejor_score < 0.55:
        return None

    if (
        len(
            puntuadas
        ) > 1
        and (
            mejor_score
            - puntuadas[1][0]
        ) < 0.05
    ):
        return None

    return mejor



def obtener_url_convocatoria(
    convocatoria_id: int,
) -> str:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT url_original
        FROM convocatorias
        WHERE id = %s
        """,
        (
            convocatoria_id,
        ),
    )

    fila = cursor.fetchone()

    cursor.close()
    conexion.rollback()
    conexion.close()

    if (
        fila is None
        or not fila[0]
    ):
        raise RuntimeError(
            "La convocatoria no tiene "
            "URL oficial."
        )

    return str(
        fila[0]
    )


def resumen_hijas_marco(
    convocatoria_padre_id: int,
) -> dict:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            COUNT(*) AS total,

            COUNT(*) FILTER (
                WHERE estado = 'PUBLICADA'
            ) AS publicadas,

            COUNT(*) FILTER (
                WHERE estado = 'CERRADA'
            ) AS cerradas,

            COUNT(*) FILTER (
                WHERE estado = 'PENDIENTE_REVISION'
            ) AS pendientes

        FROM convocatorias
        WHERE convocatoria_padre_id = %s
        """,
        (
            convocatoria_padre_id,
        ),
    )

    fila = cursor.fetchone()

    cursor.execute(
        """
        SELECT estado
        FROM convocatorias
        WHERE id = %s
        """,
        (
            convocatoria_padre_id,
        ),
    )

    padre = cursor.fetchone()

    cursor.close()
    conexion.rollback()
    conexion.close()

    return {
        "total":
            int(
                fila[0]
                or 0
            ),

        "publicadas":
            int(
                fila[1]
                or 0
            ),

        "cerradas":
            int(
                fila[2]
                or 0
            ),

        "pendientes":
            int(
                fila[3]
                or 0
            ),

        "estado_padre":
            (
                padre[0]
                if padre
                else None
            ),
    }


def validacion_sugiere_marco(
    validacion: dict,
) -> bool:
    bloqueos = [
        str(
            bloqueo
        ).casefold()
        for bloqueo
        in validacion.get(
            "bloqueos",
            []
        )
    ]

    return any(
        (
            "no existe una fecha de cierre"
            in bloqueo
            or
            "fecha de cierre verificable"
            in bloqueo
        )
        for bloqueo in bloqueos
    )


def ejecutar_resolvedor_marco(
    *,
    convocatoria_id: int,
    descubrimiento_id: int,
) -> dict:
    url = obtener_url_convocatoria(
        convocatoria_id
    )

    comando = [
        sys.executable,
        "-m",
        "app.resolver_convocatoria_marco",

        "--url",
        url,

        "--convocatoria-id",
        str(
            convocatoria_id
        ),

        "--descubrimiento-id",
        str(
            descubrimiento_id
        ),

        "--crear-hijas",
        "--aplicar-padre",
    ]

    print()
    print(
        "Intentando resolver "
        "convocatoria marco:"
    )
    print(
        " ".join(
            comando
        )
    )

    try:
        proceso = subprocess.run(
            comando,
            capture_output=True,
            text=True,
            timeout=900,
        )

    except subprocess.TimeoutExpired as error:
        return {
            "estado":
                "ERROR_TEMPORAL",

            "error":
                (
                    "El resolvedor de "
                    "convocatorias marco "
                    "superó 900 segundos."
                ),

            "salida":
                str(
                    error
                ),
        }

    salida = (
        (proceso.stdout or "")
        + "\n"
        + (proceso.stderr or "")
    )

    if proceso.returncode != 0:
        temporal = es_error_temporal(
            salida
        )

        return {
            "estado":
                (
                    "ERROR_TEMPORAL"
                    if temporal
                    else "ERROR"
                ),

            "error":
                (
                    "RESOLVEDOR_MARCO_"
                    + str(
                        proceso.returncode
                    )
                ),

            "salida":
                salida,
        }

    resumen = resumen_hijas_marco(
        convocatoria_id
    )

    if resumen[
        "total"
    ] <= 0:
        return {
            "estado":
                "NO_ES_MARCO",

            "salida":
                salida,

            "hijas":
                resumen,
        }

    return {
        "estado":
            "MARCO_PROCESADO",

        "salida":
            salida,

        "hijas":
            resumen,
    }


def finalizar_convocatoria_automatica(
    *,
    descubrimiento: dict,
    convocatoria: dict,
    fuente_id: int,
    salida: str | None = None,
    intento_registrado: bool = False,
) -> dict:
    """
    Fase final del ciclo automático:

    1. Enriquece.
    2. Valida.
    3. Publica si es válida.
    4. Si falta fecha, intenta resolver
       estructura de convocatoria marco.
    5. Crea/procesa hijas automáticamente.
    """

    convocatoria_id = int(
        convocatoria[
            "id"
        ]
    )

    descubrimiento_id = int(
        descubrimiento[
            "id"
        ]
    )

    error_enriquecimiento = None
    enriquecimiento_temporal = False

    # ========================================================
    # ENRIQUECIMIENTO
    # ========================================================

    try:
        contexto = cargar_contexto(
            convocatoria_id,
            descubrimiento_id,
        )

        enriquecimiento = (
            construir_enriquecimiento(
                contexto
            )
        )

        campos = aplicar_enriquecimiento(
            contexto,
            enriquecimiento,
        )

        if campos:
            print(
                "Campos enriquecidos:",
                ", ".join(
                    campos
                ),
            )

    except Exception as error:
        error_enriquecimiento = (
            f"{type(error).__name__}: "
            f"{error}"
        )

        enriquecimiento_temporal = (
            es_error_temporal(
                error_enriquecimiento
            )
        )

        print()
        print(
            "Aviso de enriquecimiento:",
            error_enriquecimiento,
        )

    # ========================================================
    # VALIDACIÓN NORMAL
    # ========================================================

    validacion = validar_convocatoria(
        convocatoria_id,
        descubrimiento_id,
        publicar=True,
    )

    if validacion[
        "publicable"
    ]:
        actualizar_descubrimiento(
            descubrimiento_id,
            estado="PROCESADO",
            fuente_id=fuente_id,
            convocatoria_id=
                convocatoria_id,
            error=None,
            salida=salida,
            procesado=True,
        )

        return {
            "estado":
                "PUBLICADA",

            "convocatoria_id":
                convocatoria_id,

            "validacion":
                validacion,
        }

    # ========================================================
    # POSIBLE CONVOCATORIA MARCO
    # ========================================================

    if validacion_sugiere_marco(
        validacion
    ):
        resultado_marco = (
            ejecutar_resolvedor_marco(
                convocatoria_id=
                    convocatoria_id,

                descubrimiento_id=
                    descubrimiento_id,
            )
        )

        if (
            resultado_marco[
                "estado"
            ]
            == "MARCO_PROCESADO"
        ):
            resumen = resultado_marco[
                "hijas"
            ]

            actualizar_descubrimiento(
                descubrimiento_id,
                estado="PROCESADO",
                fuente_id=fuente_id,
                convocatoria_id=
                    convocatoria_id,
                error=None,
                salida=
                    resultado_marco.get(
                        "salida"
                    ),
                procesado=True,
            )

            return {
                "estado":
                    "MARCO_PROCESADO",

                "convocatoria_id":
                    convocatoria_id,

                "hijas":
                    resumen,

                "validacion":
                    validacion,
            }

        if (
            resultado_marco[
                "estado"
            ]
            == "ERROR_TEMPORAL"
        ):
            actualizar_descubrimiento(
                descubrimiento_id,
                estado="ERROR_TEMPORAL",
                fuente_id=fuente_id,
                convocatoria_id=
                    convocatoria_id,
                error=(
                    "ERROR_TEMPORAL: "
                    + str(
                        resultado_marco.get(
                            "error"
                        )
                    )
                ),
                salida=
                    resultado_marco.get(
                        "salida"
                    ),
                # Si la extracción ya contó este intento, no sumar otro.
                incrementar_intento=not intento_registrado,
            )

            return {
                "estado":
                    "ERROR_TEMPORAL",

                "convocatoria_id":
                    convocatoria_id,

                "error":
                    resultado_marco.get(
                        "error"
                    ),
            }

        if (
            resultado_marco[
                "estado"
            ]
            == "ERROR"
        ):
            actualizar_descubrimiento(
                descubrimiento_id,
                estado="ERROR",
                fuente_id=fuente_id,
                convocatoria_id=
                    convocatoria_id,
                error=(
                    "ERROR_RESOLVEDOR_MARCO: "
                    + str(
                        resultado_marco.get(
                            "error"
                        )
                    )
                ),
                salida=
                    resultado_marco.get(
                        "salida"
                    ),
            )

            return {
                "estado":
                    "ERROR",

                "convocatoria_id":
                    convocatoria_id,

                "error":
                    resultado_marco.get(
                        "error"
                    ),
            }

    # ========================================================
    # SI EL ENRIQUECIMIENTO FALLÓ POR RED, REINTENTAR LUEGO
    # ========================================================

    if enriquecimiento_temporal:
        actualizar_descubrimiento(
            descubrimiento_id,
            estado="ERROR_TEMPORAL",
            fuente_id=fuente_id,
            convocatoria_id=
                convocatoria_id,
            error=(
                "ERROR_TEMPORAL_ENRIQUECIMIENTO: "
                + str(
                    error_enriquecimiento
                )
            ),
            salida=salida,
            # Si la extracción ya contó este intento, no sumar otro.
            incrementar_intento=not intento_registrado,
        )

        return {
            "estado":
                "ERROR_TEMPORAL",

            "convocatoria_id":
                convocatoria_id,

            "error":
                error_enriquecimiento,
        }

    # ========================================================
    # REVISIÓN FINAL REAL
    # ========================================================

    mensaje = (
        "VALIDACION_FINAL: "
        + " | ".join(
            validacion[
                "bloqueos"
            ]
        )
    )

    if error_enriquecimiento:
        mensaje += (
            " | ENRIQUECIMIENTO: "
            + error_enriquecimiento
        )

    actualizar_descubrimiento(
        descubrimiento_id,
        estado="REVISION_FINAL",
        fuente_id=fuente_id,
        convocatoria_id=
            convocatoria_id,
        error=mensaje,
        salida=salida,
    )

    return {
        "estado":
            "REVISION_FINAL",

        "convocatoria_id":
            convocatoria_id,

        "validacion":
            validacion,
    }


def procesar_descubrimiento(
    descubrimiento: dict,
) -> dict:
    validar_url_publica(
        descubrimiento[
            "url_original"
        ]
    )

    fuente_id = (
        descubrimiento.get(
            "fuente_id"
        )
    )

    if fuente_id is None:
        fuente_id = (
            obtener_o_crear_fuente(
                descubrimiento[
                    "url_original"
                ]
            )
        )

    actualizar_descubrimiento(
        descubrimiento["id"],
        estado="APROBADO",
        fuente_id=fuente_id,
    )

    # =========================================================
    # ¿YA EXISTE?
    # =========================================================

    conexion = conectar()

    existente = (
        buscar_convocatoria_exacta(
            conexion,
            descubrimiento[
                "url_original"
            ],
        )
    )

    if existente:
        conexion.rollback()
        conexion.close()

        return finalizar_convocatoria_automatica(
            descubrimiento=
                descubrimiento,

            convocatoria=
                existente,

            fuente_id=
                fuente_id,

            salida=(
                "La convocatoria ya existía "
                "en PostgreSQL. Se ejecutó "
                "enriquecimiento y validación "
                "automática."
            ),
        )

    antes_id = max_convocatoria_id(
        conexion
    )

    conexion.rollback()
    conexion.close()

    # =========================================================
    # EXTRACCIÓN
    # =========================================================

    actualizar_descubrimiento(
        descubrimiento["id"],
        estado="EN_PROCESO",
        fuente_id=fuente_id,
        incrementar_intento=True,
        error=None,
    )

    comando = [
        sys.executable,
        "-m",
        "app.ejecutar_scraping",

        "--fuente-id",
        str(
            fuente_id
        ),

        "--url",
        descubrimiento[
            "url_original"
        ],

        "--guardar",

        "--limite",
        "5",
    ]

    print()
    print(
        "Ejecutando extractor:"
    )
    print(
        " ".join(
            comando
        )
    )

    try:
        proceso = subprocess.run(
            comando,
            capture_output=True,
            text=True,
            timeout=300,
        )

    except subprocess.TimeoutExpired:
        actualizar_descubrimiento(
            descubrimiento["id"],
            estado="ERROR_TEMPORAL",
            fuente_id=fuente_id,
            error=(
                "ERROR_TEMPORAL: "
                "el extractor superó "
                "300 segundos."
            ),
        )

        return {
            "estado":
                "ERROR_TEMPORAL",

            "error":
                "TIMEOUT",
        }

    salida = (
        (proceso.stdout or "")
        + "\n"
        + (proceso.stderr or "")
    )

    if proceso.returncode != 0:
        print()
        print(
            "SALIDA DEL EXTRACTOR:"
        )
        print(
            salida[-5000:]
        )

        temporal = es_error_temporal(
            salida
        )

        estado_error = (
            "ERROR_TEMPORAL"
            if temporal
            else "ERROR"
        )

        mensaje = (
            "ERROR_TEMPORAL: "
            if temporal
            else "ERROR: "
        )

        mensaje += (
            "Extractor terminó "
            f"con código "
            f"{proceso.returncode}."
        )

        actualizar_descubrimiento(
            descubrimiento["id"],
            estado=estado_error,
            fuente_id=fuente_id,
            error=mensaje,
            salida=salida,
        )

        return {
            "estado":
                estado_error,

            "error":
                (
                    "RETURN_CODE_"
                    + str(
                        proceso.returncode
                    )
                ),
        }

    # =========================================================
    # ENCONTRAR LA FILA QUE CREÓ SCRAPY
    # =========================================================

    conexion = conectar()

    exacta = (
        buscar_convocatoria_exacta(
            conexion,
            descubrimiento[
                "url_original"
            ],
        )
    )

    if exacta:
        convocatoria = exacta

    else:
        candidatas = buscar_nuevas(
            conexion,
            antes_id,
            fuente_id,
        )

        convocatoria = (
            elegir_convocatoria(
                descubrimiento,
                candidatas,
            )
        )

    conexion.rollback()
    conexion.close()

    if convocatoria is None:
        temporal = es_error_temporal(
            salida
        )

        if temporal:
            actualizar_descubrimiento(
                descubrimiento["id"],
                estado="ERROR_TEMPORAL",
                fuente_id=fuente_id,
                error=(
                    "ERROR_TEMPORAL: "
                    "la fuente oficial no "
                    "respondió correctamente. "
                    "Se reintentará después."
                ),
                salida=salida,
            )

            return {
                "estado":
                    "ERROR_TEMPORAL",

                "error":
                    "FUENTE_NO_DISPONIBLE",
            }

        # El spider lee la URL como un listado; si el descubrimiento es la
        # página de la convocatoria (o su PDF), se toma directamente.
        try:
            convocatoria = guardar_desde_pagina_directa(
                descubrimiento,
                fuente_id,
            )
        except Exception as error:
            convocatoria = None
            salida += (
                "\nRespaldo de página directa: "
                f"{type(error).__name__}: {error}"
            )

        if convocatoria is not None:
            print("Guardada desde la página directa (el spider no la obtuvo).")

            return finalizar_convocatoria_automatica(
                descubrimiento=descubrimiento,
                convocatoria=convocatoria,
                fuente_id=fuente_id,
                salida=salida + "\nGuardada desde la página directa.",
                intento_registrado=True,
            )

        actualizar_descubrimiento(
            descubrimiento["id"],
            estado="ERROR",
            fuente_id=fuente_id,
            error=(
                "El extractor terminó, "
                "pero no se pudo vincular "
                "con seguridad una "
                "convocatoria."
            ),
            salida=salida,
        )

        return {
            "estado":
                "ERROR",

            "error":
                "SIN_VINCULO_SEGURO",
        }

    # =========================================================
    # ENRIQUECIMIENTO + VALIDACIÓN + MARCO
    # =========================================================

    return finalizar_convocatoria_automatica(
        descubrimiento=
            descubrimiento,

        convocatoria=
            convocatoria,

        fuente_id=
            fuente_id,

        salida=
            salida,

        intento_registrado=True,
    )


# Peor caso de un descubrimiento: extracción (300 s) + resolvedor
# marco (900 s) + enriquecimiento (300 s).
SEGUNDOS_MAX_POR_DESCUBRIMIENTO = 1500


def guardar_desde_pagina_directa(
    descubrimiento: dict,
    fuente_id: int,
) -> dict | None:
    """
    Respaldo cuando el spider no devuelve la convocatoria: la página del
    descubrimiento (o el PDF, si el enlace es un PDF) se guarda como
    convocatoria PENDIENTE_REVISION con sus documentos; después pasa por el
    enriquecimiento y el validador de siempre. Mismas defensas de red
    (solo direcciones públicas y redirecciones del mismo dominio).
    """
    url = descubrimiento["url_original"]
    partes = urlparse(url)
    url_base = f"{partes.scheme}://{partes.netloc}/"

    if es_url_pdf(url):
        # Comprueba que de verdad sea un PDF accesible.
        descargar_pdf_seguro(url, url_base)
        resultado = {
            "titulo": (descubrimiento.get("titulo") or "").strip()
            or partes.path.rsplit("/", 1)[-1].rsplit(".", 1)[0].replace("_", " ").replace("-", " "),
            "url_original": url,
            "descripcion": None,
            "documentos": [{"nombre": "Convocatoria (PDF)", "url": url}],
        }
    else:
        url_final, html = descargar_html_pagina_segura(url, url_base)
        titulo = titulo_de_pagina(html) or (descubrimiento.get("titulo") or "").strip()
        item = construir_item_pagina_directa(
            {"titulo": titulo, "url_original": url_final, "fuente_url_base": url_base},
            descarga=(url_final, html),
        )
        resultado = {
            "titulo": titulo,
            "url_original": url_final,
            "descripcion": None,
            "documentos": item.get("documentos") or [],
        }

    guardado = guardar_resultado_scrapy(resultado, fuente_id)

    if not guardado.get("convocatoria_id"):
        return None

    conexion = conectar()

    try:
        return buscar_convocatoria_exacta(conexion, resultado["url_original"]) or {
            "id": guardado["convocatoria_id"],
            "titulo": resultado["titulo"],
            "url": resultado["url_original"],
            "fuente_id": fuente_id,
            "estado": "PENDIENTE_REVISION",
        }
    finally:
        conexion.rollback()
        conexion.close()


def procesar_con_registro(
    descubrimiento: dict,
) -> dict:
    """
    Procesa un descubrimiento y, si falla de forma inesperada, lo deja en
    ERROR con el motivo. Lo usan el ciclo y «Extraer ahora» del panel.
    """

    try:
        return procesar_descubrimiento(
            descubrimiento
        )

    except Exception as error:
        mensaje = f"{type(error).__name__}: {error}"

        actualizar_descubrimiento(
            descubrimiento["id"],
            estado="ERROR",
            error=mensaje,
        )

        return {
            "estado": "ERROR",
            "error": mensaje,
        }


def procesar_aprobados(
    limite: int = 5,
    *,
    terminar_antes_de: float | None = None,
) -> dict:
    """
    terminar_antes_de: instante (time.monotonic()) en que el ciclo
    debe haber terminado. No se empieza un descubrimiento que, en el
    peor caso, no alcance a terminar; queda para el siguiente ciclo.
    """

    aprobados = obtener_aprobados(
        limite
    )

    resumen = {
        "recibidos":
            len(
                aprobados
            ),

        "publicados": 0,
        "marcos": 0,
        "revision_final": 0,
        "temporales": 0,
        "errores": 0,
        "pospuestos": 0,
        "resultados": [],
    }

    for posicion, descubrimiento in enumerate(
        aprobados
    ):
        if (
            terminar_antes_de is not None
            and time.monotonic()
            + SEGUNDOS_MAX_POR_DESCUBRIMIENTO
            > terminar_antes_de
        ):
            resumen["pospuestos"] = (
                len(aprobados)
                - posicion
            )

            print()
            print(
                "Tiempo del ciclo agotado: "
                f"{resumen['pospuestos']} "
                "descubrimiento(s) quedan para "
                "el siguiente ciclo."
            )
            break

        print()
        print(
            "=" * 70
        )
        print(
            "DESCUBRIMIENTO",
            descubrimiento[
                "id"
            ],
        )
        print(
            descubrimiento[
                "titulo"
            ]
        )
        print(
            descubrimiento[
                "url_original"
            ]
        )
        print(
            "Evaluación:",
            descubrimiento[
                "evaluacion_score"
            ],
        )

        resultado = procesar_con_registro(
            descubrimiento
        )

        if (
            resultado[
                "estado"
            ]
            == "PUBLICADA"
        ):
            resumen[
                "publicados"
            ] += 1

        elif (
            resultado[
                "estado"
            ]
            == "MARCO_PROCESADO"
        ):
            resumen[
                "marcos"
            ] += 1

        elif (
            resultado[
                "estado"
            ]
            == "REVISION_FINAL"
        ):
            resumen[
                "revision_final"
            ] += 1

        elif (
            resultado[
                "estado"
            ]
            == "ERROR_TEMPORAL"
        ):
            resumen[
                "temporales"
            ] += 1

        else:
            resumen[
                "errores"
            ] += 1

        resumen[
            "resultados"
        ].append({
            "descubrimiento_id":
                descubrimiento[
                    "id"
                ],

            **resultado,
        })

        print(
            "Resultado:",
            resultado[
                "estado"
            ],
        )

        if resultado.get(
            "error"
        ):
            print(
                "Error:",
                resultado[
                    "error"
                ],
            )

        if (
            "validacion"
            in resultado
        ):
            print(
                "Score final:",
                resultado[
                    "validacion"
                ][
                    "score"
                ],
            )

            if resultado[
                "validacion"
            ][
                "bloqueos"
            ]:
                print(
                    "Bloqueos:"
                )

                for bloqueo in (
                    resultado[
                        "validacion"
                    ][
                        "bloqueos"
                    ]
                ):
                    print(
                        " -",
                        bloqueo,
                    )

    return resumen


# Revalidación de convocatorias pendientes que llegaron por scraping
# (no por un descubrimiento). Sin este paso nadie las volvía a
# enriquecer ni validar. Las MANUAL las revisa un directivo.
MAX_REVALIDACIONES = 5
HORAS_ENTRE_REVALIDACIONES = 20
# Extracción en vista previa (300 s) + PDF del enriquecimiento.
SEGUNDOS_MAX_POR_REVALIDACION = 600


def cerrar_pendientes_vencidas(
    cursor,
) -> int:
    cursor.execute(
        """
        UPDATE convocatorias
        SET
            estado = 'CERRADA',
            updated_at = NOW()
        WHERE estado = 'PENDIENTE_REVISION'
          AND origen = 'SCRAPING'
          AND fecha_cierre < CURRENT_DATE
          -- Recién guardada y nunca revisada: primero se enriquece (para
          -- que llegue completa al historial) y se cierra en el siguiente
          -- ciclo.
          AND updated_at > created_at + INTERVAL '1 minute'
        """
    )

    return cursor.rowcount


def marcar_revisada(
    convocatoria_id: int,
) -> None:
    # updated_at ordena la rotación: la siguiente revisión de esta
    # convocatoria será en HORAS_ENTRE_REVALIDACIONES.
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        UPDATE convocatorias
        SET updated_at = NOW()
        WHERE id = %s
        """,
        (
            convocatoria_id,
        ),
    )

    conexion.commit()
    conexion.close()


def revalidar_pendientes(
    limite: int = MAX_REVALIDACIONES,
    *,
    terminar_antes_de: float | None = None,
) -> dict:
    conexion = conectar()
    cursor = conexion.cursor()

    cerradas = cerrar_pendientes_vencidas(
        cursor
    )

    cursor.execute(
        """
        SELECT id, titulo
        FROM convocatorias
        WHERE estado = 'PENDIENTE_REVISION'
          AND origen = 'SCRAPING'
          AND (
                (
                    (
                        fecha_cierre IS NULL
                        OR fecha_cierre >= CURRENT_DATE
                    )
                    AND updated_at < NOW()
                        - make_interval(hours => %s)
                )
                -- Recién guardada y nunca revisada (p. ej. por la fase de
                -- fuentes oficiales): se enriquece y valida en el siguiente
                -- ciclo, aunque ya haya vencido, para que quede completa.
                OR updated_at <= created_at + INTERVAL '1 minute'
              )
        ORDER BY (updated_at <= created_at + INTERVAL '1 minute') DESC, updated_at, id
        LIMIT %s
        """,
        (
            HORAS_ENTRE_REVALIDACIONES,
            limite,
        ),
    )

    pendientes = cursor.fetchall()

    conexion.commit()
    conexion.close()

    resumen = {
        "cerradas_vencidas": cerradas,
        "revisadas": 0,
        "publicadas": 0,
        "siguen_pendientes": 0,
        "errores": 0,
        "pospuestas": 0,
        "resultados": [],
    }

    for posicion, (convocatoria_id, titulo) in enumerate(
        pendientes
    ):
        if (
            terminar_antes_de is not None
            and time.monotonic()
            + SEGUNDOS_MAX_POR_REVALIDACION
            > terminar_antes_de
        ):
            resumen["pospuestas"] = (
                len(pendientes)
                - posicion
            )
            break

        print()
        print(
            "REVALIDANDO CONVOCATORIA",
            convocatoria_id,
        )
        print(
            titulo
        )

        resumen["revisadas"] += 1

        try:
            contexto = cargar_contexto(
                convocatoria_id,
                None,
            )

            aplicar_enriquecimiento(
                contexto,
                construir_enriquecimiento(
                    contexto
                ),
            )

            validacion = validar_convocatoria(
                convocatoria_id,
                None,
                publicar=True,
            )

        except Exception as error:
            resumen["errores"] += 1
            resumen["resultados"].append({
                "convocatoria_id": convocatoria_id,
                "estado": "ERROR",
                "error": f"{type(error).__name__}: {error}",
            })
            marcar_revisada(
                convocatoria_id
            )
            continue

        if validacion["publicable"]:
            resumen["publicadas"] += 1
            estado = "PUBLICADA"

        else:
            resumen["siguen_pendientes"] += 1
            estado = "PENDIENTE_REVISION"
            marcar_revisada(
                convocatoria_id
            )

        print(
            "Resultado:",
            estado,
            f"({validacion['score']}/{validacion['umbral']})",
        )

        resumen["resultados"].append({
            "convocatoria_id": convocatoria_id,
            "estado": estado,
            "score": validacion["score"],
            "bloqueos": validacion["bloqueos"],
        })

    return resumen


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Extrae, valida y publica "
            "descubrimientos aprobados."
        )
    )

    parser.add_argument(
        "--limite",
        type=int,
        default=5,
    )

    argumentos = (
        parser.parse_args()
    )

    limite = max(
        1,
        min(
            argumentos.limite,
            20,
        ),
    )

    resumen = procesar_aprobados(
        limite
    )

    print()
    print(
        "=" * 70
    )
    print(
        "RESUMEN PUBLICACIÓN AUTOMÁTICA"
    )
    print(
        "=" * 70
    )

    print(
        "Recibidos:",
        resumen[
            "recibidos"
        ],
    )

    print(
        "Publicados:",
        resumen[
            "publicados"
        ],
    )

    print(
        "Revisión final:",
        resumen[
            "revision_final"
        ],
    )

    print(
        "Errores temporales:",
        resumen[
            "temporales"
        ],
    )

    print(
        "Errores:",
        resumen[
            "errores"
        ],
    )


if __name__ == "__main__":
    main()
