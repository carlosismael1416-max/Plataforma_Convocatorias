import os
import subprocess
import sys
from pathlib import Path

import psycopg
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException
from pydantic import BaseModel, Field


BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")


app = FastAPI(
    title="Motor de Extracción ITSVA",
    version="0.3.0",
    description=(
        "Servicio de extracción y procesamiento "
        "de convocatorias"
    ),
)


class EjecutarFuenteRequest(BaseModel):
    fuente_id: int = Field(
        gt=0,
        description="ID de fuentes.id",
    )

    limite: int = Field(
        default=5,
        ge=1,
        le=50,
    )

    guardar: bool = True


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
        connect_timeout=5,
    )


def obtener_fuente(fuente_id: int):
    with conectar() as conexion:
        with conexion.cursor() as cursor:
            cursor.execute(
                """
                SELECT
                    id,
                    nombre,
                    url_base,
                    activa,
                    requiere_javascript
                FROM fuentes
                WHERE id = %s
                LIMIT 1
                """,
                (fuente_id,),
            )

            fila = cursor.fetchone()

    if not fila:
        return None

    return {
        "id": fila[0],
        "nombre": fila[1],
        "url_base": fila[2],
        "activa": fila[3],
        "requiere_javascript": fila[4],
    }


def reservar_ejecucion(fuente_id: int):
    """
    Reserva una ejecución dentro de una transacción.

    pg_advisory_xact_lock serializa los intentos de
    arranque. Si llegan dos peticiones al mismo tiempo,
    la segunda esperará y después encontrará la primera
    ejecución en INICIADO.
    """

    with conectar() as conexion:
        with conexion.transaction():
            with conexion.cursor() as cursor:

                cursor.execute(
                    """
                    SELECT pg_advisory_xact_lock(
                        9262026
                    )
                    """
                )

                cursor.execute(
                    """
                    SELECT id
                    FROM ejecuciones_scraping
                    WHERE estado IN (
                        'INICIADO',
                        'EJECUTANDO'
                    )
                    ORDER BY id DESC
                    LIMIT 1
                    """
                )

                activa = cursor.fetchone()

                if activa:
                    return None

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
                        'INICIADO',
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
                        'PENDIENTE',
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


def marcar_fallo_arranque(
    ejecucion_id: int,
    ejecucion_fuente_id: int,
    fuente: dict,
    mensaje: str,
):
    with conectar() as conexion:
        with conexion.cursor() as cursor:

            cursor.execute(
                """
                UPDATE ejecuciones_scraping
                SET
                    fecha_fin = NOW(),
                    estado = 'FALLIDO',
                    fuentes_fallidas = 1,
                    total_errores = 1,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (ejecucion_id,),
            )

            cursor.execute(
                """
                UPDATE ejecucion_fuentes
                SET
                    fecha_fin = NOW(),
                    estado = 'ERROR',
                    errores = 1,
                    updated_at = NOW()
                WHERE id = %s
                """,
                (ejecucion_fuente_id,),
            )

            cursor.execute(
                """
                INSERT INTO bitacora_errores (
                    ejecucion_scraping_id,
                    ejecucion_fuente_id,
                    fuente_id,
                    tipo_error,
                    codigo_error,
                    mensaje,
                    url,
                    resuelto,
                    created_at,
                    updated_at
                )
                VALUES (
                    %s,
                    %s,
                    %s,
                    'ARRANQUE',
                    'FASTAPI_POPEN',
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
                    mensaje,
                    fuente["url_base"],
                ),
            )


@app.get("/health")
def health():
    return {
        "status": "ok",
        "service": "motor-extraccion-itsva",
        "version": "0.3.0",
    }


@app.get("/db-check")
def db_check():
    try:
        with conectar() as conexion:
            with conexion.cursor() as cursor:
                cursor.execute("SELECT 1")
                cursor.fetchone()

        return {
            "status": "ok",
            "postgresql": "connected",
        }

    except (
        psycopg.Error,
        KeyError,
        ValueError,
    ):
        raise HTTPException(
            status_code=503,
            detail=(
                "No se pudo establecer la "
                "conexión con PostgreSQL"
            ),
        )


@app.post(
    "/scraping/ejecutar",
    status_code=202,
)
def ejecutar_scraping(
    datos: EjecutarFuenteRequest,
):
    try:
        fuente = obtener_fuente(
            datos.fuente_id
        )

    except psycopg.Error:
        raise HTTPException(
            status_code=503,
            detail=(
                "No se pudo consultar PostgreSQL."
            ),
        )

    if not fuente:
        raise HTTPException(
            status_code=404,
            detail="La fuente no existe.",
        )

    if not fuente["activa"]:
        raise HTTPException(
            status_code=409,
            detail=(
                "La fuente seleccionada "
                "está inactiva."
            ),
        )

    if fuente["requiere_javascript"]:
        raise HTTPException(
            status_code=409,
            detail=(
                "La fuente requiere JavaScript "
                "y todavía no tiene un motor "
                "compatible configurado."
            ),
        )

    try:
        reserva = reservar_ejecucion(
            fuente["id"]
        )

    except psycopg.Error:
        raise HTTPException(
            status_code=503,
            detail=(
                "No se pudo reservar la ejecución."
            ),
        )

    if reserva is None:
        raise HTTPException(
            status_code=409,
            detail=(
                "Ya existe una ejecución "
                "de scraping activa."
            ),
        )

    (
        ejecucion_id,
        ejecucion_fuente_id,
    ) = reserva

    comando = [
        sys.executable,
        "-m",
        "app.ejecutar_fuente",

        "--fuente-id",
        str(fuente["id"]),

        "--ejecucion-id",
        str(ejecucion_id),

        "--ejecucion-fuente-id",
        str(ejecucion_fuente_id),

        "--limite",
        str(datos.limite),
    ]

    if datos.guardar:
        comando.append(
            "--guardar"
        )

    try:
        proceso = subprocess.Popen(
            comando,
            cwd=BASE_DIR,
            stdout=None,
            stderr=None,
            start_new_session=True,
        )

    except OSError as error:
        mensaje = (
            "No se pudo iniciar el extractor: "
            f"{error}"
        )

        marcar_fallo_arranque(
            ejecucion_id=
                ejecucion_id,

            ejecucion_fuente_id=
                ejecucion_fuente_id,

            fuente=fuente,

            mensaje=mensaje,
        )

        raise HTTPException(
            status_code=500,
            detail=mensaje,
        )

    return {
        "status": "accepted",
        "message": (
            "La ejecución del extractor "
            "fue iniciada."
        ),
        "ejecucion_id":
            ejecucion_id,

        "ejecucion_fuente_id":
            ejecucion_fuente_id,

        "fuente": {
            "id": fuente["id"],
            "nombre": fuente["nombre"],
        },
        "limite": datos.limite,
        "guardar": datos.guardar,
        "process_id": proceso.pid,
    }
