from __future__ import annotations

import hashlib
import json
import os

from pathlib import Path

from dotenv import load_dotenv


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parents[2]
)

load_dotenv(
    BASE_DIR / ".env"
)


def estado_inicial(
    decision: str | None,
) -> str:
    decision = (
        decision
        or ""
    ).upper()

    if decision == "ACEPTAR":
        return "NUEVO"

    if decision == "REVISION":
        return "REVISION"

    if decision == "DESCARTAR":
        return "DESCARTADO"

    return "REVISION"


def generar_hash(
    url_normalizada: str,
) -> str:
    return hashlib.sha256(
        url_normalizada.encode(
            "utf-8"
        )
    ).hexdigest()


def conectar():
    host = os.getenv(
        "DB_HOST",
        "127.0.0.1",
    )

    port = os.getenv(
        "DB_PORT",
        "5432",
    )

    database = (
        os.getenv("DB_DATABASE")
        or os.getenv("DB_NAME")
    )

    user = (
        os.getenv("DB_USERNAME")
        or os.getenv("DB_USER")
    )

    password = (
        os.getenv("DB_PASSWORD")
        or os.getenv("DB_PASS")
    )

    if not database or not user:
        raise RuntimeError(
            "Faltan variables de conexión "
            "a PostgreSQL."
        )

    try:
        import psycopg

        return psycopg.connect(
            host=host,
            port=port,
            dbname=database,
            user=user,
            password=password,
        )

    except ImportError:
        try:
            import psycopg2

            return psycopg2.connect(
                host=host,
                port=port,
                dbname=database,
                user=user,
                password=password,
            )

        except ImportError as error:
            raise RuntimeError(
                "No está instalado psycopg "
                "ni psycopg2."
            ) from error


def json_db(
    valor,
):
    if valor is None:
        return None

    return json.dumps(
        valor,
        ensure_ascii=False,
    )


def preparar_registro(
    candidato: dict,
) -> dict:
    url = (
        candidato.get(
            "url_normalizada"
        )
        or candidato.get(
            "url"
        )
        or ""
    ).strip()

    if not url:
        raise ValueError(
            "El candidato no contiene URL."
        )

    decision = candidato.get(
        "decision_objetivo"
    )

    return {
        "titulo":
            candidato.get("titulo"),

        "url_original":
            candidato.get("url"),

        "url_normalizada":
            url,

        "url_hash":
            generar_hash(url),

        "dominio":
            candidato.get("dominio"),

        "metodo_descubrimiento":
            candidato.get(
                "metodo_descubrimiento"
            ),

        "pagina_origen":
            candidato.get(
                "pagina_origen"
            ),

        "tipo":
            candidato.get("tipo"),

        "puntaje":
            int(
                candidato.get(
                    "puntaje",
                    0,
                )
                or 0
            ),

        "relevancia_tematica":
            candidato.get(
                "relevancia_tematica"
            ),

        "estado_temporal":
            candidato.get(
                "estado_temporal"
            ),

        "anios_detectados":
            candidato.get(
                "anios_detectados"
            ),

        "origen_anio":
            candidato.get(
                "origen_anio"
            ),

        "razones":
            candidato.get(
                "razones"
            ),

        "categoria_objetivo":
            candidato.get(
                "categoria_objetivo"
            ),

        "decision_objetivo":
            decision,

        "puntaje_objetivo":
            int(
                candidato.get(
                    "puntaje_objetivo",
                    0,
                )
                or 0
            ),

        "razones_objetivo":
            candidato.get(
                "razones_objetivo"
            ),

        "estado":
            estado_inicial(
                decision
            ),

        "estado_fuente":
            candidato.get(
                "estado_fuente"
            ),

        "evidencia":
            candidato.get(
                "evidencia_google"
            ),
    }


SQL_UPSERT = """
INSERT INTO descubrimientos_web (
    titulo,
    url_original,
    url_normalizada,
    url_hash,
    dominio,
    metodo_descubrimiento,
    pagina_origen,
    tipo,
    puntaje,
    relevancia_tematica,
    estado_temporal,
    anios_detectados,
    origen_anio,
    razones,
    categoria_objetivo,
    decision_objetivo,
    puntaje_objetivo,
    razones_objetivo,
    estado,
    estado_fuente,
    evidencia,
    descubierto_en,
    ultimo_visto_en,
    created_at,
    updated_at
)
VALUES (
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s,
    %s::jsonb,
    %s,
    %s::jsonb,
    %s,
    %s,
    %s,
    %s::jsonb,
    %s,
    %s,
    %s::jsonb,
    NOW(),
    NOW(),
    NOW(),
    NOW()
)
ON CONFLICT (url_hash)
DO UPDATE SET
    titulo = EXCLUDED.titulo,
    url_original = EXCLUDED.url_original,
    url_normalizada = EXCLUDED.url_normalizada,
    dominio = EXCLUDED.dominio,
    metodo_descubrimiento =
        EXCLUDED.metodo_descubrimiento,
    pagina_origen =
        EXCLUDED.pagina_origen,
    tipo = EXCLUDED.tipo,
    puntaje = EXCLUDED.puntaje,
    relevancia_tematica =
        EXCLUDED.relevancia_tematica,
    estado_temporal =
        EXCLUDED.estado_temporal,
    anios_detectados =
        EXCLUDED.anios_detectados,
    origen_anio =
        EXCLUDED.origen_anio,
    razones =
        EXCLUDED.razones,
    categoria_objetivo =
        EXCLUDED.categoria_objetivo,
    decision_objetivo =
        EXCLUDED.decision_objetivo,
    puntaje_objetivo =
        EXCLUDED.puntaje_objetivo,
    razones_objetivo =
        EXCLUDED.razones_objetivo,

    /*
     * Solo los estados automáticos pueden
     * cambiar durante un nuevo descubrimiento.
     *
     * No reactivamos automáticamente un
     * registro que ya fue procesado,
     * descartado manualmente, está en proceso
     * o terminó con error.
     */
    estado = CASE
        WHEN descubrimientos_web.estado IN (
            'EN_PROCESO',
            'PROCESADO',
            'DESCARTADO',
            'ERROR',
            'ERROR_TEMPORAL',
            'REVISION_FINAL'
        )
        THEN descubrimientos_web.estado

        ELSE EXCLUDED.estado
    END,

    estado_fuente =
        EXCLUDED.estado_fuente,

    evidencia =
        EXCLUDED.evidencia,

    ultimo_visto_en =
        NOW(),

    updated_at =
        NOW()

RETURNING
    id,
    estado,
    (xmax = 0) AS insertado
"""


def guardar_descubrimientos(
    candidatos: list[dict],
) -> dict:
    resumen = {
        "recibidos": len(candidatos),
        "insertados": 0,
        "actualizados": 0,
        "errores": [],
        "registros": [],
    }

    if not candidatos:
        return resumen

    conexion = conectar()

    try:
        cursor = conexion.cursor()

        for candidato in candidatos:
            try:
                registro = preparar_registro(
                    candidato
                )

                valores = (
                    registro["titulo"],
                    registro["url_original"],
                    registro["url_normalizada"],
                    registro["url_hash"],
                    registro["dominio"],
                    registro[
                        "metodo_descubrimiento"
                    ],
                    registro["pagina_origen"],
                    registro["tipo"],
                    registro["puntaje"],
                    registro[
                        "relevancia_tematica"
                    ],
                    registro[
                        "estado_temporal"
                    ],
                    json_db(
                        registro[
                            "anios_detectados"
                        ]
                    ),
                    registro["origen_anio"],
                    json_db(
                        registro["razones"]
                    ),
                    registro[
                        "categoria_objetivo"
                    ],
                    registro[
                        "decision_objetivo"
                    ],
                    registro[
                        "puntaje_objetivo"
                    ],
                    json_db(
                        registro[
                            "razones_objetivo"
                        ]
                    ),
                    registro["estado"],
                    registro["estado_fuente"],
                    json_db(
                        registro["evidencia"]
                    ),
                )

                cursor.execute(
                    SQL_UPSERT,
                    valores,
                )

                fila = cursor.fetchone()

                registro_id = fila[0]
                estado_final = fila[1]
                insertado = bool(
                    fila[2]
                )

                if insertado:
                    resumen[
                        "insertados"
                    ] += 1
                else:
                    resumen[
                        "actualizados"
                    ] += 1

                resumen[
                    "registros"
                ].append({
                    "id":
                        registro_id,

                    "url":
                        registro[
                            "url_original"
                        ],

                    "estado":
                        estado_final,

                    "accion":
                        (
                            "INSERTADO"
                            if insertado
                            else "ACTUALIZADO"
                        ),
                })

            except Exception as error:
                resumen[
                    "errores"
                ].append({
                    "url":
                        candidato.get(
                            "url"
                        ),

                    "error":
                        (
                            f"{type(error).__name__}: "
                            f"{error}"
                        ),
                })

                raise

        conexion.commit()

        cursor.close()
        conexion.close()

        return resumen

    except Exception:
        try:
            conexion.rollback()
            conexion.close()
        except Exception:
            pass

        raise
