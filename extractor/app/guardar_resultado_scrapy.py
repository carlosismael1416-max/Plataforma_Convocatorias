import os

from pathlib import Path
from urllib.parse import unquote, urldefrag, urlparse

import psycopg
from dotenv import load_dotenv

from app.duplicados import buscar_misma
from app.urls import hash_url



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
              AND url_archivo IN (%s, %s)
            LIMIT 1
            """,
            (
                convocatoria_id,
                url_archivo,
                # «Convocatoria%20X.pdf» y «Convocatoria X.pdf» son el mismo.
                unquote(url_archivo),
            ),
        )

        if cursor.fetchone():
            existentes += 1
            continue

        es_pdf = urlparse(url_archivo).path.lower().endswith(".pdf")

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
                %s,
                %s,
                %s,
                NOW(),
                NOW()
            )
            """,
            (
                convocatoria_id,
                nombre,
                # Un enlace a otra página de la misma convocatoria.
                "PDF" if es_pdf else "ENLACE",
                "application/pdf" if es_pdf else "text/html",
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

                misma = None
                if not existente:
                    # El mismo enlace no está, pero puede ser la misma
                    # convocatoria publicada en otro sitio (micrositio,
                    # PDF de bases, página de la secretaría).
                    misma = buscar_misma(cursor, titulo, fecha_cierre)

                if misma:
                    convocatoria_id = misma[0]

                    archivos = guardar_documentos(
                        cursor,
                        convocatoria_id,
                        [
                            *documentos,
                            {"nombre": "Otra página de la convocatoria", "url": url_original},
                        ],
                    )

                    # Completar lo que falte; lo que ya tiene no se cambia.
                    cursor.execute(
                        """
                        UPDATE convocatorias
                        SET descripcion = COALESCE(NULLIF(descripcion, ''), %s),
                            fecha_cierre = COALESCE(fecha_cierre, %s),
                            updated_at = NOW()
                        WHERE id = %s
                        """,
                        (resultado.get("descripcion"), fecha_cierre, convocatoria_id),
                    )

                    return {
                        "estado": "MISMA_CONVOCATORIA",
                        "convocatoria_id": convocatoria_id,
                        "fecha_cierre_actual": misma[2] or fecha_cierre,
                        "archivos": archivos,
                    }

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
