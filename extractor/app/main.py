import contextlib
import io
import os
import subprocess
import sys
import threading
from pathlib import Path

import psycopg
from dotenv import load_dotenv
from fastapi import FastAPI, HTTPException

from app.interruptor_motor import motor_activo
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


MINUTOS_MAX_EJECUCION = 60


def liberar_ejecuciones_vencidas(cursor) -> int:
    """
    Si el extractor muere (reinicio de WSL, de la API o
    del equipo), su ejecución queda en INICIADO/EJECUTANDO
    y bloquearía todas las siguientes con 409. Las que
    superan MINUTOS_MAX_EJECUCION se marcan FALLIDO y se
    registran en bitacora_errores.
    """

    cursor.execute(
        """
        UPDATE ejecuciones_scraping
        SET
            fecha_fin = NOW(),
            estado = 'FALLIDO',
            total_errores = total_errores + 1,
            updated_at = NOW()
        WHERE estado IN (
            'INICIADO',
            'EJECUTANDO'
        )
          AND fecha_inicio < NOW()
              - make_interval(mins => %s)
        RETURNING id
        """,
        (MINUTOS_MAX_EJECUCION,),
    )

    vencidas = [
        fila[0]
        for fila in cursor.fetchall()
    ]

    if not vencidas:
        return 0

    cursor.execute(
        """
        UPDATE ejecucion_fuentes
        SET
            fecha_fin = NOW(),
            estado = 'ERROR',
            errores = errores + 1,
            updated_at = NOW()
        WHERE ejecucion_scraping_id = ANY(%s)
          AND estado IN (
              'PENDIENTE',
              'EJECUTANDO'
          )
        """,
        (vencidas,),
    )

    cursor.execute(
        """
        INSERT INTO bitacora_errores (
            ejecucion_scraping_id,
            fuente_id,
            tipo_error,
            codigo_error,
            mensaje,
            resuelto,
            created_at,
            updated_at
        )
        SELECT
            es.id,
            ef.fuente_id,
            'EJECUCION',
            'EJECUCION_ABANDONADA',
            %s,
            false,
            NOW() AT TIME ZONE 'UTC',
            NOW() AT TIME ZONE 'UTC'
        FROM ejecuciones_scraping es
        LEFT JOIN ejecucion_fuentes ef
            ON ef.ejecucion_scraping_id = es.id
        WHERE es.id = ANY(%s)
        """,
        (
            "La ejecución no terminó en "
            f"{MINUTOS_MAX_EJECUCION} minutos; "
            "se marcó FALLIDO para liberar "
            "la reserva.",
            vencidas,
        ),
    )

    return len(vencidas)


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

                liberar_ejecuciones_vencidas(
                    cursor
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
                    NOW() AT TIME ZONE 'UTC',
                    NOW() AT TIME ZONE 'UTC'
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


class AnalizarUrlRequest(BaseModel):
    url: str = Field(
        min_length=10,
        max_length=2048,
        description="Enlace de la convocatoria a analizar",
    )


# Un análisis a la vez: descarga la página y sus PDF (bajo consumo).
_ANALISIS_EN_CURSO = threading.Lock()


def _registrar_en_bitacora(tipo: str, mensaje: str, url: str | None = None) -> None:
    """La bitácora nunca debe convertir un error en otro."""
    try:
        from app.bitacora import registrar_incidencia

        with conectar() as conexion:
            registrar_incidencia(conexion, tipo=tipo, mensaje=mensaje, url=url)
            conexion.commit()
    except Exception:  # noqa: BLE001
        pass


class AnalizarArchivoRequest(BaseModel):
    nombre: str = Field(default="convocatoria", max_length=255)
    # PDF o imagen en base64 (hasta 15 MB del archivo original).
    contenido_base64: str = Field(min_length=8, max_length=21_000_000)


@app.post("/convocatorias/analizar-archivo")
def analizar_archivo_endpoint(datos: AnalizarArchivoRequest):
    """
    «Subir convocatoria desde un archivo» (PDF o imagen): lee el texto,
    lo analiza como el motor y aplica el validador, sin guardar nada.
    """

    import base64
    import binascii

    from app.importar_desde_archivo import ArchivoInvalido, analizar_archivo

    if not motor_activo():
        raise HTTPException(
            status_code=409,
            detail="El motor de extracción está apagado. Usa la subida manual o pide a un administrador que lo encienda.",
        )

    try:
        contenido = base64.b64decode(datos.contenido_base64, validate=True)
    except (binascii.Error, ValueError):
        raise HTTPException(status_code=422, detail="El archivo no llegó completo.")

    if not _ANALISIS_EN_CURSO.acquire(blocking=False):
        raise HTTPException(status_code=429, detail="Hay otro análisis en curso. Intenta de nuevo en un momento.")

    try:
        with contextlib.redirect_stdout(io.StringIO()):
            return analizar_archivo(contenido, datos.nombre)

    except ArchivoInvalido as error:
        raise HTTPException(status_code=422, detail=str(error))

    except psycopg.Error:
        raise HTTPException(status_code=503, detail="No se pudo consultar PostgreSQL.")

    except Exception as error:
        _registrar_en_bitacora("API", f"Analizar archivo «{datos.nombre}»: {type(error).__name__}: {error}")
        raise HTTPException(status_code=502, detail=f"No se pudo leer la convocatoria del archivo: {type(error).__name__}.")

    finally:
        _ANALISIS_EN_CURSO.release()


@app.post("/convocatorias/analizar-url")
def analizar_url_endpoint(
    datos: AnalizarUrlRequest,
):
    """
    «Subir convocatoria desde un enlace»: extrae y valida sin guardar
    nada; la web muestra el resultado y el usuario decide si publica.
    """

    from app.importar_desde_url import EnlaceInvalido, analizar_url

    if not motor_activo():
        raise HTTPException(
            status_code=409,
            detail=(
                "El motor de extracción está apagado. "
                "Usa la subida manual o pide a un administrador que lo encienda."
            ),
        )

    if not _ANALISIS_EN_CURSO.acquire(blocking=False):
        raise HTTPException(
            status_code=429,
            detail="Hay otro enlace en análisis. Intenta de nuevo en un momento.",
        )

    try:
        # El enriquecimiento imprime su avance; aquí no hace falta.
        with contextlib.redirect_stdout(io.StringIO()):
            return analizar_url(datos.url)

    except EnlaceInvalido as error:
        raise HTTPException(status_code=422, detail=str(error))

    except psycopg.Error:
        raise HTTPException(status_code=503, detail="No se pudo consultar PostgreSQL.")

    except Exception as error:
        _registrar_en_bitacora("API", f"Analizar enlace: {type(error).__name__}: {error}", url=datos.url)
        raise HTTPException(
            status_code=502,
            detail=f"No se pudo extraer la convocatoria: {type(error).__name__}.",
        )

    finally:
        _ANALISIS_EN_CURSO.release()


@app.post(
    "/descubrimientos/{descubrimiento_id}/procesar",
    status_code=202,
)
def procesar_descubrimiento_ahora(descubrimiento_id: int):
    """
    «Extraer ahora» del panel: procesa un descubrimiento sin esperar al
    ciclo (mismo proceso y umbrales). Corre en segundo plano; el panel ve
    el avance en el estado del descubrimiento.
    """

    from app.procesar_descubrimiento_ahora import SALIDA_OK, SALIDA_NO_PROCESABLE, comprobar

    try:
        conexion = conectar()

        try:
            codigo, motivo = comprobar(descubrimiento_id, conexion)
        finally:
            conexion.close()

    except psycopg.Error:
        raise HTTPException(status_code=503, detail="No se pudo consultar PostgreSQL.")

    if codigo != SALIDA_OK:
        raise HTTPException(
            status_code=404 if codigo == SALIDA_NO_PROCESABLE and "no existe" in (motivo or "") else 409,
            detail=motivo,
        )

    try:
        proceso = subprocess.Popen(
            [sys.executable, "-m", "app.procesar_descubrimiento_ahora", "--id", str(descubrimiento_id)],
            cwd=BASE_DIR,
            stdout=None,
            stderr=None,
            start_new_session=True,
        )

    except OSError as error:
        _registrar_en_bitacora("API", f"Extraer ahora (descubrimiento {descubrimiento_id}): {error}")
        raise HTTPException(status_code=500, detail="No se pudo iniciar el extractor.")

    return {
        "status": "accepted",
        "message": "La extracción empezó; puede tardar unos minutos.",
        "descubrimiento_id": descubrimiento_id,
        "process_id": proceso.pid,
    }


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
    if not motor_activo():
        raise HTTPException(
            status_code=409,
            detail=(
                "El motor de extracción está apagado. "
                "Enciéndelo desde el panel de administración."
            ),
        )

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
