from __future__ import annotations

import argparse
import json

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)


CATEGORIAS_AUTO = {
    "PROYECTO_INVESTIGACION",
    "FINANCIAMIENTO_INVESTIGACION",
}

TEMPORALES_AUTO = {
    "ACTUAL",
    "FUTURA",
}

TIPOS_NO_PUBLICABLES = {
    "PAGINA_INDICE",
    "PORTAL_INSTITUCIONAL",
}


def dominio_institucional(
    dominio: str | None,
) -> bool:
    dominio = (
        dominio
        or ""
    ).strip().lower()

    dominio = dominio.removeprefix(
        "www."
    )

    if not dominio:
        return False

    # Gobierno mexicano
    if (
        dominio.endswith(".gob.mx")
        or dominio == "gob.mx"
    ):
        return True

    # Instituciones educativas
    if (
        dominio.endswith(".edu.mx")
        or dominio.endswith(".edu.ar")
        or dominio.endswith(".edu.co")
    ):
        return True

    # Organismos conocidos de ciencia
    dominios_conocidos = {
        "secihti.mx",
        "conahcyt.mx",
    }

    for conocido in dominios_conocidos:
        if (
            dominio == conocido
            or dominio.endswith(
                "." + conocido
            )
        ):
            return True

    return False


def evaluar_fila(
    fila: dict,
) -> dict:
    score = 0
    razones = []

    decision = (
        fila.get(
            "decision_objetivo"
        )
        or ""
    ).upper()

    categoria = (
        fila.get(
            "categoria_objetivo"
        )
        or ""
    ).upper()

    temporal = (
        fila.get(
            "estado_temporal"
        )
        or ""
    ).upper()

    relevancia = (
        fila.get(
            "relevancia_tematica"
        )
        or ""
    ).upper()

    tipo = (
        fila.get(
            "tipo"
        )
        or ""
    ).upper()

    dominio = (
        fila.get(
            "dominio"
        )
        or ""
    ).lower()

    estado_fuente = (
        fila.get(
            "estado_fuente"
        )
        or ""
    ).upper()

    # =========================================================
    # PUNTAJE
    # =========================================================

    if decision == "ACEPTAR":
        score += 35
        razones.append(
            "+35 decisión objetivo ACEPTAR"
        )

    if categoria in CATEGORIAS_AUTO:
        score += 25
        razones.append(
            "+25 categoría compatible con ITSVA"
        )

    if temporal == "ACTUAL":
        score += 15
        razones.append(
            "+15 convocatoria actual"
        )

    elif temporal == "FUTURA":
        score += 12
        razones.append(
            "+12 convocatoria futura"
        )

    if relevancia == "ALTA":
        score += 10
        razones.append(
            "+10 relevancia temática alta"
        )

    institucional = (
        dominio_institucional(
            dominio
        )
    )

    if institucional:
        score += 10
        razones.append(
            "+10 dominio institucional"
        )

    if estado_fuente in {
        "INSTITUCIONAL_DIRECTA",
        "INSTITUCIONAL_DESCUBIERTA_DESDE_NOTICIA",
    }:
        score += 5
        razones.append(
            "+5 fuente institucional confirmada"
        )

    elif (
        estado_fuente
        == "INSTITUCIONAL_PENDIENTE_DESCARGA"
    ):
        score += 3
        razones.append(
            "+3 fuente institucional pendiente"
        )

    if tipo in {
        "PAGINA",
        "PDF",
    }:
        score += 5
        razones.append(
            "+5 URL procesable"
        )

    # =========================================================
    # PENALIZACIONES
    # =========================================================

    if temporal == "HISTORICA":
        score -= 50
        razones.append(
            "-50 convocatoria histórica"
        )

    if tipo in TIPOS_NO_PUBLICABLES:
        score -= 30
        razones.append(
            "-30 índice/portal, no convocatoria individual"
        )

    if categoria in {
        "EVENTO",
        "BECA",
        "BECA_INVESTIGACION",
        "CONVOCATORIA_GENERAL",
        "CIENCIA_TECNOLOGIA_GENERAL",
        "PORTAL_CONVOCATORIAS",
        "ENLACE_INSTITUCIONAL_GENERICO",
        "NO_OBJETIVO",
    }:
        score -= 20
        razones.append(
            "-20 categoría no publicable automáticamente"
        )

    score = max(
        0,
        min(
            score,
            100,
        ),
    )

    # =========================================================
    # CONDICIONES OBLIGATORIAS
    # =========================================================

    condiciones = {
        "decision_aceptar":
            decision == "ACEPTAR",

        "categoria_valida":
            categoria
            in CATEGORIAS_AUTO,

        "vigente":
            temporal
            in TEMPORALES_AUTO,

        "institucional":
            institucional,

        "tipo_publicable":
            tipo
            not in TIPOS_NO_PUBLICABLES,

        "no_historica":
            temporal
            != "HISTORICA",
    }

    cumple_obligatorias = all(
        condiciones.values()
    )

    # =========================================================
    # DECISIÓN FINAL
    # =========================================================

    if (
        decision == "DESCARTAR"
        or categoria
        in {
            "EVENTO",
            "ENLACE_INSTITUCIONAL_GENERICO",
            "NO_OBJETIVO",
        }
    ):
        estado = "DESCARTADO"

    elif (
        cumple_obligatorias
        and score >= 80
    ):
        estado = "APROBADO"

    else:
        estado = "REVISION"

    return {
        "score": score,
        "estado": estado,
        "condiciones": condiciones,
        "razones": razones,
    }


def cargar_pendientes(
    conexion,
) -> list[dict]:
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            titulo,
            url_original,
            dominio,
            tipo,
            relevancia_tematica,
            estado_temporal,
            categoria_objetivo,
            decision_objetivo,
            estado,
            estado_fuente
        FROM descubrimientos_web
        WHERE convocatoria_id IS NULL
          AND estado IN (
              'NUEVO',
              'REVISION',
              'APROBADO'
          )
        ORDER BY id
        """
    )

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    filas = []

    for fila in cursor.fetchall():
        filas.append(
            dict(
                zip(
                    columnas,
                    fila,
                )
            )
        )

    cursor.close()

    return filas


def evaluar_descubrimientos(
    aplicar: bool = True,
) -> dict:
    conexion = conectar()

    resumen = {
        "evaluados": 0,
        "aprobados": 0,
        "revision": 0,
        "descartados": 0,
        "resultados": [],
    }

    try:
        filas = cargar_pendientes(
            conexion
        )

        cursor = conexion.cursor()

        for fila in filas:
            resultado = evaluar_fila(
                fila
            )

            resumen[
                "evaluados"
            ] += 1

            if (
                resultado["estado"]
                == "APROBADO"
            ):
                resumen[
                    "aprobados"
                ] += 1

            elif (
                resultado["estado"]
                == "REVISION"
            ):
                resumen[
                    "revision"
                ] += 1

            elif (
                resultado["estado"]
                == "DESCARTADO"
            ):
                resumen[
                    "descartados"
                ] += 1

            resumen[
                "resultados"
            ].append({
                "id":
                    fila["id"],

                "titulo":
                    fila["titulo"],

                "url":
                    fila[
                        "url_original"
                    ],

                **resultado,
            })

            if aplicar:
                detalle = {
                    "condiciones":
                        resultado[
                            "condiciones"
                        ],

                    "razones":
                        resultado[
                            "razones"
                        ],
                }

                cursor.execute(
                    """
                    UPDATE descubrimientos_web
                    SET
                        estado = %s,
                        evaluacion_score = %s,
                        evaluacion_detalle = %s::jsonb,
                        evaluado_en = NOW(),
                        updated_at = NOW()
                    WHERE id = %s
                    """,
                    (
                        resultado[
                            "estado"
                        ],
                        resultado[
                            "score"
                        ],
                        json.dumps(
                            detalle,
                            ensure_ascii=False,
                        ),
                        fila["id"],
                    ),
                )

        if aplicar:
            conexion.commit()
        else:
            conexion.rollback()

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


def imprimir_resumen(
    resumen: dict,
    vista_previa: bool,
) -> None:
    print()
    print(
        "=" * 70
    )
    print(
        "EVALUADOR AUTOMÁTICO ITSVA"
    )

    print(
        (
            "MODO: VISTA PREVIA"
            if vista_previa
            else
            "MODO: APLICAR EVALUACIÓN"
        )
    )

    print(
        "=" * 70
    )

    print(
        "Evaluados:",
        resumen[
            "evaluados"
        ],
    )

    print(
        "Aprobados:",
        resumen[
            "aprobados"
        ],
    )

    print(
        "Revisión:",
        resumen[
            "revision"
        ],
    )

    print(
        "Descartados:",
        resumen[
            "descartados"
        ],
    )

    print()
    print(
        "=" * 70
    )
    print(
        "RESULTADOS"
    )
    print(
        "=" * 70
    )

    for item in resumen[
        "resultados"
    ]:
        print()
        print(
            f"ID: {item['id']}"
        )

        print(
            f"[{item['estado']}] "
            f"[{item['score']}/100]"
        )

        print(
            item[
                "titulo"
            ]
        )

        print(
            item[
                "url"
            ]
        )

        print(
            "Condiciones:"
        )

        for clave, valor in (
            item[
                "condiciones"
            ].items()
        ):
            print(
                " ",
                "✓"
                if valor
                else "✗",
                clave,
            )

        print(
            "Razones:"
        )

        for razon in item[
            "razones"
        ]:
            print(
                " -",
                razon,
            )


def main() -> None:
    parser = (
        argparse.ArgumentParser(
            description=(
                "Evalúa automáticamente "
                "descubrimientos web ITSVA."
            )
        )
    )

    parser.add_argument(
        "--vista-previa",
        action="store_true",
        help=(
            "Evalúa pero no modifica "
            "PostgreSQL."
        ),
    )

    argumentos = (
        parser.parse_args()
    )

    resumen = (
        evaluar_descubrimientos(
            aplicar=
                not argumentos.vista_previa
        )
    )

    imprimir_resumen(
        resumen,
        argumentos.vista_previa,
    )


if __name__ == "__main__":
    main()
