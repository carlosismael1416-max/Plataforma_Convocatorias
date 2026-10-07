import hashlib
import os

from pathlib import Path
from urllib.parse import urldefrag

import psycopg
from dotenv import load_dotenv


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


def hash_url(url):
    return hashlib.sha256(
        url.encode("utf-8")
    ).hexdigest()


def obtener_fecha_cierre(resultado):
    """
    Obtiene únicamente una fecha de cierre que el
    resolvedor haya marcado como utilizable.

    Nunca convierte CIERRE_PROCESO en fecha_cierre.
    """

    candidato = resultado.get(
        "fecha_cierre_candidata"
    )

    if not candidato:
        return None

    if not candidato.get("utilizable"):
        return None

    return candidato.get("fecha")


def buscar_convocatoria(
    cursor,
    url_original,
    url_hash,
):
    cursor.execute(
        """
        SELECT
            id,
            titulo,
            fecha_cierre,
            fuente_id
        FROM convocatorias
        WHERE url_hash = %s
           OR RTRIM(url_original, '/') = %s
        ORDER BY id
        LIMIT 1
        """,
        (
            url_hash,
            url_original,
        ),
    )

    return cursor.fetchone()


def guardar_documentos(
    cursor,
    convocatoria_id,
    documentos,
):
    insertados = 0
    existentes = 0

    for documento in documentos:
        url_archivo = documento.get("url")

        if not url_archivo:
            continue

        nombre = (
            documento.get("nombre")
            or "Documento PDF"
        )

        cursor.execute(
            """
            SELECT id
            FROM convocatoria_archivos
            WHERE convocatoria_id = %s
              AND url_archivo = %s
            LIMIT 1
            """,
            (
                convocatoria_id,
                url_archivo,
            ),
        )

        if cursor.fetchone():
            existentes += 1
            continue

        cursor.execute(
            """
            INSERT INTO convocatoria_archivos (
                convocatoria_id,
                nombre,
                tipo_archivo,
                mime_type,
                url_archivo,
                created_at,
                updated_at
            )
            VALUES (
                %s,
                %s,
                'PDF',
                'application/pdf',
                %s,
                NOW(),
                NOW()
            )
            """,
            (
                convocatoria_id,
                nombre,
                url_archivo,
            ),
        )

        insertados += 1

    return {
        "insertados": insertados,
        "existentes": existentes,
    }


def guardar_resultado_scrapy(
    resultado,
    fuente_id,
):
    """
    Guarda una convocatoria producida por Scrapy.

    Si ya existe:
        - no crea otra convocatoria;
        - comprueba y agrega PDFs faltantes.

    Si no existe:
        - crea la convocatoria;
        - usa fecha_cierre_candidata únicamente
          cuando utilizable=True;
        - guarda sus documentos PDF.

    Devuelve un resumen de la operación.
    """

    titulo = (
        resultado.get("titulo")
        or ""
    ).strip()

    if not titulo:
        return {
            "estado": "SIN_TITULO",
            "convocatoria_id": None,
        }

    url_original = normalizar_url(
        resultado.get("url_original")
    )

    if not url_original:
        return {
            "estado": "SIN_URL",
            "convocatoria_id": None,
        }

    url_hash = hash_url(
        url_original
    )

    fecha_cierre = obtener_fecha_cierre(
        resultado
    )

    documentos = resultado.get(
        "documentos",
        [],
    )

    with conectar() as conexion:
        with conexion.transaction():
            with conexion.cursor() as cursor:

                existente = buscar_convocatoria(
                    cursor,
                    url_original,
                    url_hash,
                )

                if existente:
                    convocatoria_id = existente[0]

                    archivos = guardar_documentos(
                        cursor,
                        convocatoria_id,
                        documentos,
                    )

                    return {
                        "estado": "YA_EXISTE",
                        "convocatoria_id": (
                            convocatoria_id
                        ),
                        "fecha_cierre_actual": (
                            existente[2]
                        ),
                        "archivos": archivos,
                    }

                cursor.execute(
                    """
                    INSERT INTO convocatorias (
                        titulo,
                        descripcion,
                        fecha_cierre,
                        fuente_id,
                        url_original,
                        url_hash,
                        origen,
                        estado,
                        fecha_extraccion,
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
                        'SCRAPING',
                        'PENDIENTE_REVISION',
                        NOW(),
                        NOW(),
                        NOW()
                    )
                    RETURNING id
                    """,
                    (
                        titulo,
                        resultado.get(
                            "descripcion"
                        ),
                        fecha_cierre,
                        fuente_id,
                        url_original,
                        url_hash,
                    ),
                )

                convocatoria_id = (
                    cursor.fetchone()[0]
                )

                archivos = guardar_documentos(
                    cursor,
                    convocatoria_id,
                    documentos,
                )

                return {
                    "estado": "CREADA",
                    "convocatoria_id": (
                        convocatoria_id
                    ),
                    "fecha_cierre": fecha_cierre,
                    "archivos": archivos,
                }
