import os
import subprocess
import sys

from datetime import date
from pathlib import Path

import psycopg
from dotenv import load_dotenv

from app.conciliar_fechas import extraer_ampliacion_web
from app.registrar_revision_fecha import registrar_cambio_fecha


BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")

CONVOCATORIA_ID = 2
FUENTE_ID = 2

URL_CONVOCATORIA = (
    "https://www.secihti.mx/convocatoria/"
    "ciencias-y-humanidades/proyectos-de-investigacion/"
    "convocatoria-proyectos-de-investigacion-cientifica-"
    "y-humanistica-en-ejes-estrategicos-2025/"
)

HTML_ACTUAL = BASE_DIR / "detalle_proyecto_actual.html"
HTML_TEMPORAL = BASE_DIR / "detalle_proyecto_actual.tmp.html"


def conectar():
    return psycopg.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "5432")),
        dbname=os.environ["DB_DATABASE"],
        user=os.environ["DB_USERNAME"],
        password=os.environ["DB_PASSWORD"],
    )


def descargar_y_extraer_fecha():
    print("=== DESCARGANDO PÁGINA ACTUAL ===")

    comando = [
        sys.executable,
        "-m",
        "scrapy",
        "fetch",
        "--nolog",
        "-s",
        "ROBOTSTXT_OBEY=True",
        URL_CONVOCATORIA,
    ]

    resultado = subprocess.run(
        comando,
        capture_output=True,
        text=True,
        timeout=120,
    )

    if resultado.returncode != 0:
        raise RuntimeError(
            "No se pudo descargar la página:\n"
            + resultado.stderr[-1500:]
        )

    html = resultado.stdout

    if "<html" not in html.lower():
        raise ValueError(
            "La descarga no parece contener una página HTML válida."
        )

    HTML_TEMPORAL.write_text(
        html,
        encoding="utf-8",
    )

    try:
        fecha_web, hora_web = extraer_ampliacion_web(
            HTML_TEMPORAL
        )

        if not fecha_web or not hora_web:
            raise ValueError(
                "No se pudo identificar una fecha y hora de cierre."
            )

        HTML_TEMPORAL.replace(HTML_ACTUAL)

    finally:
        HTML_TEMPORAL.unlink(missing_ok=True)

    print("Descarga y extracción correctas.")
    print("HTML guardado en:", HTML_ACTUAL)

    return fecha_web, hora_web


def main():
    # 1. Descargar y analizar la página actual.
    fecha_web, hora_web = descargar_y_extraer_fecha()

    fecha_web = date.fromisoformat(fecha_web)
    hora_web = hora_web[:5]

    print("\n=== FECHA DETECTADA EN LA WEB ===")
    print("Fecha:", fecha_web)
    print("Hora:", hora_web)

    # 2. Consultar PostgreSQL.
    with conectar() as conexion:
        with conexion.cursor() as cursor:
            cursor.execute(
                """
                SELECT fecha_cierre
                FROM convocatorias
                WHERE id = %s
                  AND fuente_id = %s
                """,
                (
                    CONVOCATORIA_ID,
                    FUENTE_ID,
                ),
            )

            convocatoria = cursor.fetchone()

            if convocatoria is None:
                raise ValueError(
                    "No se encontró la convocatoria "
                    "con la fuente esperada."
                )

            fecha_registrada = convocatoria[0]

            cursor.execute(
                """
                SELECT
                    fecha_cierre_nueva,
                    hora_cierre
                FROM convocatoria_revisions
                WHERE convocatoria_id = %s
                  AND tipo_revision = 'FECHA_CIERRE'
                  AND fuente_tipo = 'WEB'
                ORDER BY fecha_revision DESC, id DESC
                LIMIT 1
                """,
                (CONVOCATORIA_ID,),
            )

            revision = cursor.fetchone()

    print("\n=== FECHAS REGISTRADAS ===")
    print(
        "Fecha actual en convocatorias:",
        fecha_registrada,
    )

    if revision is None:
        print("No existe una revisión web anterior.")
        print("Se requiere revisar manualmente la fecha detectada.")
        return

    ultima_fecha_web = revision[0]

    ultima_hora_web = (
        revision[1].strftime("%H:%M")
        if revision[1] is not None
        else None
    )

    print(
        "Última fecha del historial web:",
        ultima_fecha_web,
    )
    print(
        "Última hora del historial web:",
        ultima_hora_web,
    )

    # 3. Comparar fecha y hora.
    print("\n=== RESULTADO DE LA COMPARACIÓN ===")

    if (
        fecha_web == ultima_fecha_web
        and hora_web == ultima_hora_web
    ):
        print("SIN CAMBIOS")
        print("La fecha y la hora ya están registradas.")

    else:
        print("POSIBLE CAMBIO DETECTADO")
        print("Fecha anterior:", ultima_fecha_web)
        print("Hora anterior:", ultima_hora_web)
        print("Fecha encontrada:", fecha_web)
        print("Hora encontrada:", hora_web)

        # 4. Registrar el posible cambio.
        with conectar() as conexion:
            revision_id = registrar_cambio_fecha(
                conexion=conexion,
                convocatoria_id=CONVOCATORIA_ID,
                fuente_id=FUENTE_ID,
                fecha_nueva=fecha_web,
                hora_nueva=hora_web,
                fuente_url=URL_CONVOCATORIA,
            )

        if revision_id is None:
            print("El cambio ya estaba registrado.")
        else:
            print(
                "Nueva revisión registrada:",
                revision_id,
            )
            print(
                "Estado: PENDIENTE DE VERIFICACIÓN"
            )
            print(
                "La fecha principal de la convocatoria "
                "no fue modificada."
            )


if __name__ == "__main__":
    main()
