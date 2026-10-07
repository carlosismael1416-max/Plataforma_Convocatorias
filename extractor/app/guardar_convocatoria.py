import hashlib
import json
import os
from pathlib import Path

import psycopg
from dotenv import load_dotenv

BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")

ARCHIVO_JSON = (
    BASE_DIR
    / "data"
    / "extraidos"
    / "convocatoria_completa_2025.json"
)

FUENTE_ID = 2


def conectar():
    return psycopg.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "5432")),
        dbname=os.environ["DB_DATABASE"],
        user=os.environ["DB_USERNAME"],
        password=os.environ["DB_PASSWORD"],
    )


def hash_url(url):
    return hashlib.sha256(
        url.encode("utf-8")
    ).hexdigest()


def cargar_json():
    if not ARCHIVO_JSON.exists():
        raise FileNotFoundError(
            f"No se encontró: {ARCHIVO_JSON}"
        )

    with ARCHIVO_JSON.open(
        encoding="utf-8"
    ) as archivo:
        return json.load(archivo)


def main():
    datos = cargar_json()

    titulo = datos["titulo"]
    url = datos["url_original"]
    objetivo = datos.get("objetivo")

    fechas = datos["fechas"]

    fecha_publicacion = fechas.get(
        "publicacion"
    )

    fecha_inicio = fechas.get(
        "apertura"
    )

    fecha_cierre = fechas.get(
        "cierre_preferente_provisional"
    )

    grupos = datos[
        "financiamiento"
    ]["tipos_apoyo"]

    montos_totales = [
        grupo["monto_maximo_total"]
        for grupo in grupos
        if grupo.get("monto_maximo_total") is not None
    ]

    monto_maximo = (
        max(montos_totales)
        if montos_totales
        else None
    )

    moneda = "MXN"

    url_hash = hash_url(url)

    with conectar() as conexion:
        with conexion.cursor() as cursor:

            # Verificar nuevamente que no exista.
            cursor.execute(
                """
                SELECT id
                FROM convocatorias
                WHERE url_original = %s
                   OR url_hash = %s
                LIMIT 1
                """,
                (
                    url,
                    url_hash,
                ),
            )

            existente = cursor.fetchone()

            if existente:
                print(
                    "La convocatoria ya existe."
                )
                print(
                    "ID:",
                    existente[0],
                )
                return

            cursor.execute(
                """
                INSERT INTO convocatorias (
                    titulo,
                    objetivo,
                    fecha_publicacion,
                    fecha_inicio,
                    fecha_cierre,
                    monto_maximo,
                    moneda,
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
                    objetivo,
                    fecha_publicacion,
                    fecha_inicio,
                    fecha_cierre,
                    monto_maximo,
                    moneda,
                    FUENTE_ID,
                    url,
                    url_hash,
                ),
            )

            convocatoria_id = cursor.fetchone()[0]

            # Guardar documentos PDF encontrados.
            for archivo in datos["archivos_pdf"]:
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
                        archivo["nombre"],
                        archivo["url"],
                    ),
                )

            # Guardar disposiciones.
            orden = 1

            for requisito in datos["disposiciones"]:
                cursor.execute(
                    """
                    INSERT INTO convocatoria_requisitos (
                        convocatoria_id,
                        titulo,
                        descripcion,
                        obligatorio,
                        tipo_requisito,
                        orden,
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
                        NOW(),
                        NOW()
                    )
                    """,
                    (
                        convocatoria_id,
                        f"Inciso {requisito['inciso']}",
                        requisito["texto"],
                        requisito["clasificacion"] in (
  				"requisito",
    				"restriccion",
				"causa_no_elegibilidad",
),
                        requisito["clasificacion"],
                        orden,
                    ),
                )

                orden += 1

            # Guardar documentos requeridos.
            for documento in datos[
                "documentos_requeridos"
            ]:
                descripcion = (
                    documento[
                        "descripcion_original"
                    ]
                )

                if documento.get("condicion"):
                    descripcion += (
                        " Condición: "
                        + documento["condicion"]
                    )

                cursor.execute(
                    """
                    INSERT INTO convocatoria_requisitos (
                        convocatoria_id,
                        titulo,
                        descripcion,
                        obligatorio,
                        tipo_requisito,
                        orden,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        %s,
                        %s,
                        %s,
                        %s,
                        'documento',
                        %s,
                        NOW(),
                        NOW()
                    )
                    """,
                    (
                        convocatoria_id,
                        documento["nombre"],
                        descripcion,
                        documento["obligatorio"],
                        orden,
                    ),
                )

                orden += 1

        conexion.commit()

    print(
        "Convocatoria guardada correctamente."
    )

    print(
        "ID:",
        convocatoria_id,
    )

    print(
        "PDF guardados:",
        len(datos["archivos_pdf"]),
    )

    print(
        "Requisitos/disposiciones guardados:",
        orden - 1,
    )


if __name__ == "__main__":
    main()
