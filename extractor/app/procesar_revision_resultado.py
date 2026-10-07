import os

from datetime import date
from pathlib import Path
from urllib.parse import urldefrag

import psycopg
from dotenv import load_dotenv

from app.registrar_revision_fecha import registrar_cambio_fecha


BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")


def conectar():
    return psycopg.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "5432")),
        dbname=os.environ["DB_DATABASE"],
        user=os.environ["DB_USERNAME"],
        password=os.environ["DB_PASSWORD"],
    )


def normalizar_url(url):
    if not url:
        return None

    url, _ = urldefrag(url)

    return url.strip().rstrip("/")


def buscar_convocatoria_por_url(
    conexion,
    url_original,
):
    """
    Busca una convocatoria almacenada usando su URL.

    Devuelve:
        (convocatoria_id, fuente_id)

    o None si no existe.
    """

    url_normalizada = normalizar_url(
        url_original
    )

    if not url_normalizada:
        return None

    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT
                id,
                fuente_id
            FROM convocatorias
            WHERE RTRIM(url_original, '/') = %s
            ORDER BY id
            LIMIT 1
            """,
            (url_normalizada,),
        )

        return cursor.fetchone()


def procesar_revision_resultado(resultado):
    """
    Procesa la fecha encontrada por Scrapy.

    Las fechas normales NO se guardan como revisiones.

    Solo una AMPLIACION puede pasar al mecanismo
    de historial de convocatoria_revisions.
    """

    url_original = resultado.get(
        "url_original"
    )

    fecha_cierre_web = resultado.get(
        "fecha_cierre_web"
    )

    hora_cierre_web = resultado.get(
        "hora_cierre_web"
    )

    tipo_fecha = resultado.get(
        "tipo_fecha_cierre"
    )

    if not url_original:
        return {
            "estado": "SIN_URL",
            "revision_id": None,
        }

    if (
        tipo_fecha == "SIN_FECHA"
        or not fecha_cierre_web
    ):
        return {
            "estado": "SIN_FECHA_WEB",
            "revision_id": None,
        }

    # Una fecha normal forma parte de los datos
    # de la convocatoria, pero NO es una revisión.
    if tipo_fecha == "CIERRE_NORMAL":
        return {
            "estado": "CIERRE_NORMAL_DETECTADO",
            "fecha": fecha_cierre_web,
            "hora": hora_cierre_web,
            "revision_id": None,
        }

    # Por seguridad, solamente las ampliaciones
    # entran al historial automático.
    if tipo_fecha != "AMPLIACION":
        return {
            "estado": "TIPO_FECHA_NO_PROCESADO",
            "tipo_fecha": tipo_fecha,
            "revision_id": None,
        }

    if not hora_cierre_web:
        return {
            "estado": "AMPLIACION_SIN_HORA",
            "fecha": fecha_cierre_web,
            "revision_id": None,
        }

    fecha_cierre_web = date.fromisoformat(
        fecha_cierre_web
    )

    with conectar() as conexion:
        convocatoria = buscar_convocatoria_por_url(
            conexion,
            url_original,
        )

        if convocatoria is None:
            return {
                "estado": "CONVOCATORIA_NO_REGISTRADA",
                "fecha": fecha_cierre_web.isoformat(),
                "hora": hora_cierre_web,
                "revision_id": None,
            }

        convocatoria_id = convocatoria[0]
        fuente_id = convocatoria[1]

        revision_id = registrar_cambio_fecha(
            conexion=conexion,
            convocatoria_id=convocatoria_id,
            fuente_id=fuente_id,
            fecha_nueva=fecha_cierre_web,
            hora_nueva=hora_cierre_web,
            fuente_url=url_original,
        )

    if revision_id is None:
        return {
            "estado": "SIN_CAMBIOS",
            "convocatoria_id": convocatoria_id,
            "fuente_id": fuente_id,
            "revision_id": None,
        }

    return {
        "estado": "REVISION_REGISTRADA",
        "convocatoria_id": convocatoria_id,
        "fuente_id": fuente_id,
        "revision_id": revision_id,
    }