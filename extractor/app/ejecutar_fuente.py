import argparse
import json
import os
import subprocess
import sys
import time
import traceback
from pathlib import Path

import psycopg
from dotenv import load_dotenv


BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")


def conectar():
    return psycopg.connect(
        host=os.getenv(
            "DB_HOST",
            "127.0.0.1",
        ),
        port=int(
            os.getenv(
                "DB_PORT",
                "5432",
            )
        ),
        dbname=os.environ[
            "DB_DATABASE"
        ],
        user=os.environ[
            "DB_USERNAME"
        ],
        password=os.environ[
            "DB_PASSWORD"
        ],
    )


def obtener_fuente(fuente_id):
    with conectar() as conexion:
        with conexion.cursor() as cursor:
            cursor.execute(
                """
                SELECT
                    id,
                    nombre,
                    url_base,
                    tipo_fuente,
                    requiere_javascript,
                    activa
                FROM fuentes
                WHERE id = %s
                LIMIT 1
                """,
                (fuente_id,),
            )

            fila = cursor.fetchone()

    if not fila:
        raise ValueError(
            f"No existe la fuente {fuente_id}."
        )

    return {
        "id": fila[0],
        "nombre": fila[1],
        "url_base": fila[2],
        "tipo_fuente": fila[3],
        "requiere_javascript": fila[4],
        "activa": fila[5],
    }


def crear_ejecucion(fuente_id):
    with conectar() as conexion:
        with conexion.cursor() as cursor:

            cursor.execute(
                """
                INSERT INTO ejecuciones_scraping (
                    fecha_inicio,
                    estado,
                    total_fuentes,
                    fuentes_exitosas,
                    fuentes_fallidas,
                    total_encontradas,
                    nuevas_convocatorias,
                    convocatorias_actualizadas,
                    duplicados_detectados,
                    total_errores,
                    created_at,
                    updated_at
                )
                VALUES (
                    NOW(),
                    'EJECUTANDO',
                    1,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    0,
                    NOW(),
                    NOW()
                )
                RETURNING id
                """
            )

            ejecucion_id = (
                cursor.fetchone()[0]
            )

            cursor.execute(
                """
                INSERT INTO ejecucion_fuentes (
                    ejecucion_scraping_id,
                    fuente_id,
                    fecha_inicio,
                    estado,
                    registros_encontrados,
                    registros_nuevos,
                    registros_actualizados,
                    duplicados,
                    errores,
                    created_at,
                    updated_at
                )
                VALUES (
                    %s,
                    %s,
                    NOW(),
                    'EJECUTANDO',
                    0,
                    0,
                    0,
                    0,
                    0,
                    NOW(),
                    NOW()
                )
                RETURNING id
                """,
                (
                    ejecucion_id,
                    fuente_id,
                ),
            )

            ejecucion_fuente_id = (
                cursor.fetchone()[0]
            )

    return (
        ejecucion_id,
        ejecucion_fuente_id,
    )


def preparar_ejecucion_reservada(
    ejecucion_id,
    ejecucion_fuente_id,
    fuente_id,
):
    """
    Valida una ejecución creada previamente por FastAPI
    y la cambia a EJECUTANDO.

    Esto evita crear una segunda ejecución desde
    ejecutar_fuente.py.
    """

    with conectar() as conexion:
        with conexion.cursor() as cursor:

            cursor.execute(
                """
                SELECT
                    e.id,
                    e.estado,
                    ef.id,
                    ef.fuente_id,
                    ef.ejecucion_scraping_id
                FROM ejecuciones_scraping e
                JOIN ejecucion_fuentes ef
                  ON ef.ejecucion_scraping_id = e.id
                WHERE e.id = %s
                  AND ef.id = %s
                LIMIT 1
                """,
                (
                    ejecucion_id,
                    ejecucion_fuente_id,
                ),
            )

            fila = cursor.fetchone()

            if not fila:
                raise ValueError(
                    "La ejecución reservada no existe."
                )

            if int(fila[3]) != int(fuente_id):
                raise ValueError(
                    "La ejecución reservada pertenece "
                    "a otra fuente."
                )

            if fila[1] not in {
                "INICIADO",
                "EJECUTANDO",
            }:
                raise ValueError(
                    "La ejecución reservada ya no está activa."
                )

            cursor.execute(
                """
                UPDATE ejecuciones_scraping
                SET
                    estado = 'EJECUTANDO',
                    updated_at = NOW()
                WHERE id = %s
                """,
                (ejecucion_id,),
            )

            cursor.execute(
                """
                UPDATE ejecucion_fuentes
                SET
                    estado = 'EJECUTANDO',
                    updated_at = NOW()
                WHERE id = %s
                """,
                (ejecucion_fuente_id,),
            )



def analizar_resultados(
    resultados,
    guardar,
):
    total = len(resultados)

    nuevas = 0
    actualizadas = 0
    duplicados = 0
    errores = 0

    if not guardar:
        return {
            "total": total,
            "nuevas": 0,
            "actualizadas": 0,
            "duplicados": 0,
            "errores": 0,
        }

    for item in resultados:
        guardado = (
            item.get("guardado_db")
            or {}
        )

        estado = guardado.get(
            "estado"
        )

        if estado == "CREADA":
            nuevas += 1

        elif estado == "YA_EXISTE":
            duplicados += 1

        elif estado in {
            "ACTUALIZADA",
            "ACTUALIZADO",
        }:
            actualizadas += 1

        else:
            errores += 1

    return {
        "total": total,
        "nuevas": nuevas,
        "actualizadas": actualizadas,
        "duplicados": duplicados,
        "errores": errores,
    }


def completar_ejecucion(
    ejecucion_id,
    ejecucion_fuente_id,
    fuente_id,
    estadisticas,
    duracion,
):
    errores = int(
        estadisticas["errores"]
    )

    estado = (
        "COMPLETADO"
        if errores == 0
        else "COMPLETADO_CON_ERRORES"
    )

    with conectar() as conexion:
        with conexion.cursor() as cursor:

            cursor.execute(
                """
                UPDATE ejecucion_fuentes
                SET
                    fecha_fin = NOW(),
                    estado = %s,
                    registros_encontrados = %s,
                    registros_nuevos = %s,
                    registros_actualizados = %s,
                    duplicados = %s,
                    errores = %s,
                    duracion_segundos = %s,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (
                    (
                        "COMPLETADO"
                        if errores == 0
                        else "ERROR"
                    ),
                    estadisticas["total"],
                    estadisticas["nuevas"],
                    estadisticas[
                        "actualizadas"
                    ],
                    estadisticas[
                        "duplicados"
                    ],
                    errores,
                    duracion,
                    ejecucion_fuente_id,
                ),
            )

            cursor.execute(
                """
                UPDATE ejecuciones_scraping
                SET
                    fecha_fin = NOW(),
                    estado = %s,
                    fuentes_exitosas = %s,
                    fuentes_fallidas = %s,
                    total_encontradas = %s,
                    nuevas_convocatorias = %s,
                    convocatorias_actualizadas = %s,
                    duplicados_detectados = %s,
                    total_errores = %s,
                    duracion_segundos = %s,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (
                    estado,
                    (
                        1
                        if errores == 0
                        else 0
                    ),
                    (
                        0
                        if errores == 0
                        else 1
                    ),
                    estadisticas["total"],
                    estadisticas["nuevas"],
                    estadisticas[
                        "actualizadas"
                    ],
                    estadisticas[
                        "duplicados"
                    ],
                    errores,
                    duracion,
                    ejecucion_id,
                ),
            )

            cursor.execute(
                """
                UPDATE fuentes
                SET
                    ultima_ejecucion = NOW(),
                    updated_at = NOW()
                WHERE id = %s
                """,
                (fuente_id,),
            )

    return estado


def registrar_fallo(
    ejecucion_id,
    ejecucion_fuente_id,
    fuente,
    mensaje,
    detalle,
    stack_trace,
    codigo_error,
    duracion,
):
    with conectar() as conexion:
        with conexion.cursor() as cursor:

            cursor.execute(
                """
                INSERT INTO bitacora_errores (
                    ejecucion_scraping_id,
                    ejecucion_fuente_id,
                    fuente_id,
                    tipo_error,
                    codigo_error,
                    mensaje,
                    detalle,
                    stack_trace,
                    url,
                    resuelto,
                    created_at,
                    updated_at
                )
                VALUES (
                    %s,
                    %s,
                    %s,
                    'SCRAPING',
                    %s,
                    %s,
                    %s,
                    %s,
                    %s,
                    false,
                    NOW(),
                    NOW()
                )
                """,
                (
                    ejecucion_id,
                    ejecucion_fuente_id,
                    fuente["id"],
                    codigo_error,
                    mensaje,
                    detalle,
                    stack_trace,
                    fuente["url_base"],
                ),
            )

            cursor.execute(
                """
                UPDATE ejecucion_fuentes
                SET
                    fecha_fin = NOW(),
                    estado = 'ERROR',
                    errores = 1,
                    duracion_segundos = %s,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (
                    duracion,
                    ejecucion_fuente_id,
                ),
            )

            cursor.execute(
                """
                UPDATE ejecuciones_scraping
                SET
                    fecha_fin = NOW(),
                    estado = 'FALLIDO',
                    fuentes_exitosas = 0,
                    fuentes_fallidas = 1,
                    total_errores = 1,
                    duracion_segundos = %s,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (
                    duracion,
                    ejecucion_id,
                ),
            )

            cursor.execute(
                """
                UPDATE fuentes
                SET
                    ultima_ejecucion = NOW(),
                    updated_at = NOW()
                WHERE id = %s
                """,
                (fuente["id"],),
            )


def ejecutar_fuente(
    fuente_id,
    limite,
    guardar,
    ejecucion_id=None,
    ejecucion_fuente_id=None,
):
    fuente = obtener_fuente(
        fuente_id
    )

    if not fuente["activa"]:
        raise ValueError(
            "La fuente seleccionada está inactiva."
        )

    if (
        fuente[
            "requiere_javascript"
        ]
    ):
        raise ValueError(
            "La fuente requiere JavaScript "
            "y todavía no tiene un motor "
            "compatible configurado."
        )

    print(
        "========================================"
    )
    print(
        "=== EJECUCIÓN DE FUENTE ITSVA ==="
    )
    print(
        "========================================"
    )

    print(
        "Fuente ID:",
        fuente["id"],
    )

    print(
        "Nombre:",
        fuente["nombre"],
    )

    print(
        "URL:",
        fuente["url_base"],
    )

    print(
        "Modo:",
        (
            "GUARDADO"
            if guardar
            else "VISTA PREVIA"
        ),
    )

    print(
        "Límite:",
        limite,
    )

    if (
        (ejecucion_id is None)
        != (ejecucion_fuente_id is None)
    ):
        raise ValueError(
            "Debes proporcionar ambos IDs de "
            "ejecución o ninguno."
        )

    if (
        ejecucion_id is not None
        and ejecucion_fuente_id is not None
    ):
        preparar_ejecucion_reservada(
            ejecucion_id=ejecucion_id,
            ejecucion_fuente_id=ejecucion_fuente_id,
            fuente_id=fuente["id"],
        )

    else:
        (
            ejecucion_id,
            ejecucion_fuente_id,
        ) = crear_ejecucion(
            fuente["id"]
        )

    print(
        "Ejecución:",
        ejecucion_id,
    )

    print(
        "Ejecución fuente:",
        ejecucion_fuente_id,
    )

    inicio = time.monotonic()

    archivo_salida = (
        BASE_DIR
        / (
            "scraping_guardado.json"
            if guardar
            else
            "scraping_vista_previa.json"
        )
    )

    comando = [
        sys.executable,
        "-m",
        "app.ejecutar_scraping",
        "--fuente-id",
        str(
            fuente["id"]
        ),
        "--url",
        fuente["url_base"],
        "--limite",
        str(limite),
    ]

    if guardar:
        comando.append(
            "--guardar"
        )

    try:
        resultado = subprocess.run(
            comando,
            cwd=BASE_DIR,
            text=True,
            capture_output=True,
            check=False,
        )

        duracion = round(
            time.monotonic()
            - inicio,
            3,
        )

        if resultado.stdout:
            print()
            print(
                "=== SALIDA SCRAPER ==="
            )
            print(
                resultado.stdout.rstrip()
            )

        if resultado.returncode != 0:
            raise RuntimeError(
                "El scraper terminó con "
                f"código {resultado.returncode}."
            )

        if not archivo_salida.exists():
            raise RuntimeError(
                "El scraper terminó sin "
                "generar el archivo JSON."
            )

        resultados = json.loads(
            archivo_salida.read_text(
                encoding="utf-8"
            )
        )

        if not isinstance(
            resultados,
            list,
        ):
            raise ValueError(
                "El JSON generado por el "
                "scraper no contiene una lista."
            )

        estadisticas = (
            analizar_resultados(
                resultados,
                guardar,
            )
        )

        estado = completar_ejecucion(
            ejecucion_id=
                ejecucion_id,

            ejecucion_fuente_id=
                ejecucion_fuente_id,

            fuente_id=
                fuente["id"],

            estadisticas=
                estadisticas,

            duracion=
                duracion,
        )

        print()
        print(
            "=== CONTROL DE EJECUCIÓN ==="
        )

        print(
            "Estado:",
            estado,
        )

        print(
            "Encontradas:",
            estadisticas["total"],
        )

        print(
            "Nuevas:",
            estadisticas["nuevas"],
        )

        print(
            "Actualizadas:",
            estadisticas[
                "actualizadas"
            ],
        )

        print(
            "Duplicadas:",
            estadisticas[
                "duplicados"
            ],
        )

        print(
            "Errores:",
            estadisticas["errores"],
        )

        print(
            "Duración:",
            f"{duracion}s",
        )

        return 0

    except Exception as error:
        duracion = round(
            time.monotonic()
            - inicio,
            3,
        )

        detalle = None

        if "resultado" in locals():
            partes = []

            if resultado.stdout:
                partes.append(
                    "STDOUT:\n"
                    + resultado.stdout
                )

            if resultado.stderr:
                partes.append(
                    "STDERR:\n"
                    + resultado.stderr
                )

            detalle = "\n\n".join(
                partes
            ) or None

            codigo_error = (
                "SUBPROCESS_"
                + str(
                    resultado.returncode
                )
            )

        else:
            codigo_error = (
                error.__class__.__name__
            )

        stack = traceback.format_exc()

        registrar_fallo(
            ejecucion_id=
                ejecucion_id,

            ejecucion_fuente_id=
                ejecucion_fuente_id,

            fuente=fuente,

            mensaje=str(error),

            detalle=detalle,

            stack_trace=stack,

            codigo_error=
                codigo_error,

            duracion=duracion,
        )

        print()
        print(
            "=== EJECUCIÓN FALLIDA ==="
        )

        print(
            "Error:",
            error,
        )

        if detalle:
            print()
            print(detalle)

        return 1


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Ejecuta una fuente y registra "
            "su seguimiento en PostgreSQL."
        )
    )

    parser.add_argument(
        "--fuente-id",
        type=int,
        required=True,
        help=(
            "ID de fuentes.id."
        ),
    )

    parser.add_argument(
        "--ejecucion-id",
        type=int,
        default=None,
        help=(
            "ID de una ejecución previamente "
            "reservada por FastAPI."
        ),
    )

    parser.add_argument(
        "--ejecucion-fuente-id",
        type=int,
        default=None,
        help=(
            "ID del detalle de fuente previamente "
            "reservado por FastAPI."
        ),
    )

    parser.add_argument(
        "--limite",
        type=int,
        default=5,
        help=(
            "Número máximo de "
            "convocatorias."
        ),
    )

    parser.add_argument(
        "--guardar",
        action="store_true",
        help=(
            "Permite guardar las "
            "convocatorias en PostgreSQL."
        ),
    )

    argumentos = (
        parser.parse_args()
    )

    if argumentos.limite < 1:
        parser.error(
            "--limite debe ser "
            "mayor que cero."
        )

    try:
        codigo = ejecutar_fuente(
            fuente_id=
                argumentos.fuente_id,

            limite=
                argumentos.limite,

            guardar=
                argumentos.guardar,

            ejecucion_id=
                argumentos.ejecucion_id,

            ejecucion_fuente_id=
                argumentos.ejecucion_fuente_id,
        )

    except Exception as error:
        print(
            "ERROR:",
            error,
            file=sys.stderr,
        )

        codigo = 1

    raise SystemExit(
        codigo
    )


if __name__ == "__main__":
    main()
