from __future__ import annotations

import argparse
import json
import subprocess
import sys

from difflib import SequenceMatcher
from urllib.parse import urlparse

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.seguridad_red import (
    validar_url_publica,
)

from app.validar_convocatoria import (
    validar_convocatoria,
)

from app.enriquecer_convocatoria import (
    aplicar_enriquecimiento,
    cargar_contexto,
    construir_enriquecimiento,
)



def normalizar_url(
    url: str | None,
) -> str:
    if not url:
        return ""

    try:
        parsed = urlparse(
            url.strip()
        )

        host = (
            parsed.hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

        path = (
            parsed.path
            or "/"
        )

        if path != "/":
            path = path.rstrip("/")

        resultado = (
            f"{parsed.scheme.lower() or 'https'}"
            f"://{host}{path}"
        )

        if parsed.query:
            resultado += (
                "?"
                + parsed.query
            )

        return resultado

    except Exception:
        return url.strip().rstrip("/")


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


def obtener_aprobados(
    limite: int,
) -> list[dict]:
    conexion = conectar()
    cursor = conexion.cursor()

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
                    AND intentos_procesamiento < 3
                    AND updated_at <= (
                        NOW()
                        - INTERVAL '6 hours'
                    )
                )
              )
        ORDER BY
            evaluacion_score DESC,
            id ASC
        LIMIT %s
        """,
        (
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
    objetivo = normalizar_url(
        url
    )

    cursor = conexion.cursor()

    cursor.execute("""
        SELECT
            id,
            titulo,
            url_original,
            fuente_id,
            estado
        FROM convocatorias
        WHERE url_original IS NOT NULL
        ORDER BY id DESC
    """)

    for fila in cursor.fetchall():
        if normalizar_url(
            fila[2]
        ) == objetivo:
            cursor.close()

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

    cursor.close()

    return None


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
                incrementar_intento=True,
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
            incrementar_intento=True,
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
    )


def procesar_aprobados(
    limite: int = 5,
) -> dict:
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
        "resultados": [],
    }

    for descubrimiento in aprobados:
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

        try:
            resultado = (
                procesar_descubrimiento(
                    descubrimiento
                )
            )

        except Exception as error:
            actualizar_descubrimiento(
                descubrimiento["id"],
                estado="ERROR",
                error=(
                    f"{type(error).__name__}: "
                    f"{error}"
                ),
            )

            resultado = {
                "estado":
                    "ERROR",

                "error":
                    (
                        f"{type(error).__name__}: "
                        f"{error}"
                    ),
            }

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
