from __future__ import annotations

import argparse

from datetime import date, datetime

import requests

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.enriquecer_convocatoria import (
    aplicar_enriquecimiento,
    cargar_contexto,
    construir_enriquecimiento,
)

from app.validar_convocatoria import (
    validar_convocatoria,
)


def normalizar_fecha(
    valor,
) -> date | None:
    if valor is None:
        return None

    if isinstance(
        valor,
        datetime,
    ):
        return valor.date()

    if isinstance(
        valor,
        date,
    ):
        return valor

    return date.fromisoformat(
        str(valor)
    )


def obtener_hijas(
    convocatoria_padre_id: int,
) -> list[dict]:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            convocatoria_padre_id,
            titulo,
            fecha_cierre,
            estado,
            fuente_id,
            url_original
        FROM convocatorias
        WHERE convocatoria_padre_id = %s
        ORDER BY id
        """,
        (
            convocatoria_padre_id,
        ),
    )

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    resultados = [
        dict(
            zip(
                columnas,
                fila,
            )
        )
        for fila in cursor.fetchall()
    ]

    cursor.close()
    conexion.rollback()
    conexion.close()

    return resultados


def actualizar_estado(
    convocatoria_id: int,
    estado: str,
) -> None:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        UPDATE convocatorias
        SET
            estado = %s,
            updated_at = NOW()
        WHERE id = %s
        """,
        (
            estado,
            convocatoria_id,
        ),
    )

    conexion.commit()
    cursor.close()
    conexion.close()


def es_error_temporal(
    error: Exception,
) -> bool:
    if isinstance(
        error,
        (
            requests.exceptions.Timeout,
            requests.exceptions.ConnectionError,
        ),
    ):
        return True

    texto = (
        f"{type(error).__name__}: {error}"
    ).casefold()

    indicadores = (
        "timeout",
        "timed out",
        "connection reset",
        "connection refused",
        "temporary failure",
        "temporarily unavailable",
        "502 bad gateway",
        "503 service unavailable",
        "504 gateway timeout",
    )

    return any(
        indicador in texto
        for indicador in indicadores
    )


def procesar_hija(
    hija: dict,
    *,
    fecha_referencia: date | None = None,
) -> dict:
    if fecha_referencia is None:
        fecha_referencia = date.today()

    convocatoria_id = int(
        hija["id"]
    )

    fecha_cierre = normalizar_fecha(
        hija.get(
            "fecha_cierre"
        )
    )

    if fecha_cierre is None:
        actualizar_estado(
            convocatoria_id,
            "PENDIENTE_REVISION",
        )

        return {
            "convocatoria_id":
                convocatoria_id,

            "estado":
                "REVISION_FINAL",

            "motivo":
                "SIN_FECHA_VERIFICABLE",
        }

    # --------------------------------------------------------
    # VENCIDA
    # --------------------------------------------------------

    if fecha_cierre < fecha_referencia:
        if hija.get(
            "estado"
        ) != "CERRADA":
            actualizar_estado(
                convocatoria_id,
                "CERRADA",
            )

        return {
            "convocatoria_id":
                convocatoria_id,

            "estado":
                "CERRADA",

            "fecha_cierre":
                fecha_cierre,

            "motivo":
                "FECHA_VENCIDA",
        }

    # --------------------------------------------------------
    # VIGENTE / FUTURA
    # --------------------------------------------------------

    try:
        contexto = cargar_contexto(
            convocatoria_id,
            None,
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

        # validar_convocatoria vuelve a cargar
        # la fila desde PostgreSQL, por lo que
        # verá inmediatamente el enriquecimiento
        # que acabamos de guardar.
        validacion = validar_convocatoria(
            convocatoria_id,
            None,
            publicar=True,
        )

        if validacion[
            "publicable"
        ]:
            return {
                "convocatoria_id":
                    convocatoria_id,

                "estado":
                    "PUBLICADA",

                "fecha_cierre":
                    fecha_cierre,

                "campos_actualizados":
                    campos,

                "validacion":
                    validacion,
            }

        actualizar_estado(
            convocatoria_id,
            "PENDIENTE_REVISION",
        )

        return {
            "convocatoria_id":
                convocatoria_id,

            "estado":
                "REVISION_FINAL",

            "fecha_cierre":
                fecha_cierre,

            "campos_actualizados":
                campos,

            "validacion":
                validacion,

            "motivo":
                "NO_SUPERA_VALIDACION_FINAL",
        }

    except Exception as error:
        temporal = es_error_temporal(
            error
        )

        # La convocatoria sigue pendiente.
        # No se publica ante un fallo parcial.
        actualizar_estado(
            convocatoria_id,
            "PENDIENTE_REVISION",
        )

        return {
            "convocatoria_id":
                convocatoria_id,

            "estado":
                (
                    "ERROR_TEMPORAL"
                    if temporal
                    else "REVISION_FINAL"
                ),

            "error":
                (
                    f"{type(error).__name__}: "
                    f"{error}"
                ),
        }


def procesar_hijas_marco(
    convocatoria_padre_id: int,
    *,
    fecha_referencia: date | None = None,
) -> dict:
    if fecha_referencia is None:
        fecha_referencia = date.today()

    hijas = obtener_hijas(
        convocatoria_padre_id
    )

    resumen = {
        "convocatoria_padre_id":
            convocatoria_padre_id,

        "total":
            len(
                hijas
            ),

        "publicadas": 0,
        "cerradas": 0,
        "revision_final": 0,
        "temporales": 0,
        "resultados": [],
    }

    for hija in hijas:
        print()
        print(
            "-" * 90
        )
        print(
            "Procesando hija:",
            hija["id"],
        )
        print(
            hija["titulo"]
        )
        print(
            "Cierre:",
            hija[
                "fecha_cierre"
            ],
        )
        print(
            "Estado previo:",
            hija[
                "estado"
            ],
        )

        resultado = procesar_hija(
            hija,
            fecha_referencia=
                fecha_referencia,
        )

        estado = resultado[
            "estado"
        ]

        if estado == "PUBLICADA":
            resumen[
                "publicadas"
            ] += 1

        elif estado == "CERRADA":
            resumen[
                "cerradas"
            ] += 1

        elif estado == "ERROR_TEMPORAL":
            resumen[
                "temporales"
            ] += 1

        else:
            resumen[
                "revision_final"
            ] += 1

        resumen[
            "resultados"
        ].append(
            resultado
        )

        print(
            "Resultado:",
            estado,
        )

        if resultado.get(
            "motivo"
        ):
            print(
                "Motivo:",
                resultado[
                    "motivo"
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

    return resumen


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Procesa automáticamente las "
            "convocatorias hijas de una "
            "convocatoria marco."
        )
    )

    parser.add_argument(
        "--convocatoria-padre-id",
        type=int,
        required=True,
    )

    parser.add_argument(
        "--fecha",
        type=date.fromisoformat,
        default=date.today(),
        help=(
            "Fecha de referencia AAAA-MM-DD."
        ),
    )

    args = parser.parse_args()

    print()
    print(
        "=" * 90
    )
    print(
        "PROCESAMIENTO AUTOMÁTICO "
        "DE CONVOCATORIAS HIJAS"
    )
    print(
        "=" * 90
    )

    resumen = procesar_hijas_marco(
        args.convocatoria_padre_id,
        fecha_referencia=args.fecha,
    )

    print()
    print(
        "=" * 90
    )
    print(
        "RESUMEN HIJAS"
    )
    print(
        "=" * 90
    )

    print(
        "Total:",
        resumen[
            "total"
        ],
    )

    print(
        "Publicadas:",
        resumen[
            "publicadas"
        ],
    )

    print(
        "Cerradas:",
        resumen[
            "cerradas"
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


if __name__ == "__main__":
    main()
