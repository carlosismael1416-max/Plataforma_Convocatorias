from __future__ import annotations

import argparse
import json
import time
from datetime import datetime
from pathlib import Path

from app.descubrimiento.buscar_google_news import (
    GoogleNewsError,
    buscar_google_news,
)

from app.descubrimiento.clasificar_google_news import (
    clasificar_google_news,
    normalizar_texto,
)


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parent
    .parent
)


CONSULTAS = [
    (
        '"convocatoria" '
        '"proyectos de investigación" '
        'México'
    ),
    (
        '"convocatoria" '
        'financiamiento investigación '
        'México'
    ),
    (
        '"convocatoria" '
        'ciencia tecnología innovación '
        'México'
    ),
    (
        '"fondo" investigación '
        'México convocatoria'
    ),
    (
        '"beca de investigación" '
        'México convocatoria'
    ),
]


def clave_candidato(
    titulo: str,
    dominio: str,
) -> str:
    return (
        normalizar_texto(dominio)
        + "|"
        + normalizar_texto(titulo)
    )


def ejecutar(
    cantidad: int = 10,
) -> None:
    candidatos = {}

    errores = []

    print(
        "=" * 70
    )

    print(
        "BOT DESCUBRIDOR ITSVA"
    )

    print(
        "Google News RSS"
    )

    print(
        "=" * 70
    )

    for numero, consulta in enumerate(
        CONSULTAS,
        start=1,
    ):
        print()
        print(
            f"[{numero}/"
            f"{len(CONSULTAS)}]"
        )

        print(
            "Consulta:",
            consulta,
        )

        try:
            resultados = (
                buscar_google_news(
                    consulta=consulta,
                    cantidad=cantidad,
                )
            )

        except GoogleNewsError as error:
            print(
                "ERROR:",
                error,
            )

            errores.append({
                "consulta": consulta,
                "error": str(error),
            })

            continue

        print(
            "Resultados:",
            len(resultados),
        )

        for resultado in resultados:
            clasificacion = (
                clasificar_google_news(
                    titulo=resultado[
                        "titulo"
                    ],
                    fuente_nombre=resultado[
                        "fuente_nombre"
                    ],
                    dominio=resultado[
                        "dominio"
                    ],
                )
            )

            candidato = {
                **resultado,

                "consulta":
                    consulta,

                "puntaje":
                    clasificacion[
                        "puntaje"
                    ],

                "estado":
                    clasificacion[
                        "estado"
                    ],

                "razones":
                    clasificacion[
                        "razones"
                    ],
            }

            clave = clave_candidato(
                candidato[
                    "titulo"
                ],
                candidato[
                    "dominio"
                ],
            )

            existente = (
                candidatos.get(
                    clave
                )
            )

            if (
                existente is None
                or candidato["puntaje"]
                > existente["puntaje"]
            ):
                candidatos[
                    clave
                ] = candidato

        # Pequeña pausa entre
        # consultas para no bombardear
        # el servicio.
        time.sleep(2)

    lista = list(
        candidatos.values()
    )

    lista.sort(
        key=lambda item: (
            item["puntaje"],
            item["fecha_publicacion"]
            or "",
        ),
        reverse=True,
    )

    posibles = [
        item
        for item in lista
        if item["estado"]
        == "POSIBLE_CONVOCATORIA"
    ]

    revision = [
        item
        for item in lista
        if item["estado"]
        == "REVISION_MANUAL"
    ]

    descartados = [
        item
        for item in lista
        if item["estado"]
        == "DESCARTAR"
    ]

    salida = {
        "generado_en":
            datetime.now()
            .astimezone()
            .isoformat(),

        "motor":
            "GOOGLE_NEWS_RSS",

        "consultas":
            len(CONSULTAS),

        "total_unicos":
            len(lista),

        "posibles_convocatorias":
            len(posibles),

        "revision_manual":
            len(revision),

        "descartados":
            len(descartados),

        "errores":
            errores,

        "resultados":
            lista,
    }

    archivo = (
        BASE_DIR
        / "descubrimiento_google_vista_previa.json"
    )

    archivo.write_text(
        json.dumps(
            salida,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print()
    print(
        "=" * 70
    )

    print(
        "RESUMEN"
    )

    print(
        "=" * 70
    )

    print(
        "URLs/candidatos únicos:",
        len(lista),
    )

    print(
        "Posibles convocatorias:",
        len(posibles),
    )

    print(
        "Revisión manual:",
        len(revision),
    )

    print(
        "Descartados:",
        len(descartados),
    )

    print(
        "Errores:",
        len(errores),
    )

    print(
        "Archivo:",
        archivo,
    )

    print()
    print(
        "=" * 70
    )

    print(
        "MEJORES CANDIDATOS"
    )

    print(
        "=" * 70
    )

    mejores = [
        item
        for item in lista
        if item["estado"]
        != "DESCARTAR"
    ]

    for numero, item in enumerate(
        mejores[:20],
        start=1,
    ):
        print()

        print(
            f"{numero}. "
            f"[{item['puntaje']}] "
            f"{item['estado']}"
        )

        print(
            "Título:",
            item["titulo"],
        )

        print(
            "Fuente:",
            item[
                "fuente_nombre"
            ],
        )

        print(
            "Dominio:",
            item["dominio"],
        )

        print(
            "Fecha:",
            item[
                "fecha_publicacion"
            ],
        )

        print(
            "Razones:",
            ", ".join(
                item["razones"]
            ),
        )


def main() -> None:
    parser = (
        argparse.ArgumentParser(
            description=(
                "Descubrimiento ligero "
                "de convocatorias mediante "
                "Google News RSS."
            )
        )
    )

    parser.add_argument(
        "--cantidad",
        type=int,
        default=10,
        help=(
            "Resultados máximos "
            "por consulta."
        ),
    )

    argumentos = (
        parser.parse_args()
    )

    if not (
        1
        <= argumentos.cantidad
        <= 50
    ):
        parser.error(
            "--cantidad debe estar "
            "entre 1 y 50."
        )

    ejecutar(
        cantidad=
            argumentos.cantidad
    )


if __name__ == "__main__":
    main()
