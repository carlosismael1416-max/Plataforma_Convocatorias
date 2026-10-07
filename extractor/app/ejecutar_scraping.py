
import argparse
import json
import subprocess
import sys
from pathlib import Path
from datetime import date
from urllib.parse import urlparse

from scrapy.crawler import CrawlerProcess

from app.spiders.convocatorias import ConvocatoriasSpider
from app.filtrar_resultados import obtener_clasificacion
from app.filtrar_vigencia import clasificar_vigencia


BASE_DIR = Path(__file__).resolve().parent.parent

DEFAULT_URL_INICIAL = "https://www.secihti.mx/convocatorias/"

DEFAULT_FUENTE_ID = 2


def ejecutar(
    limite,
    guardar,
    modo,
    vigencia,
    fecha_referencia,
    fuente_id,
    url_inicial,
):
    if limite < 1:
        raise ValueError(
            "El límite debe ser mayor que cero."
        )

    if fuente_id < 1:
        raise ValueError(
            "El ID de la fuente debe ser mayor que cero."
        )

    url_inicial = url_inicial.strip()

    url_parseada = urlparse(url_inicial)

    if (
        url_parseada.scheme not in {"http", "https"}
        or not url_parseada.netloc
    ):
        raise ValueError(
            "La URL de la fuente debe ser HTTP o HTTPS."
        )

    if guardar and (
        modo != "todos" or vigencia != "todas"
    ):
        raise ValueError(
            "Los filtros solo están disponibles "
            "en Vista previa. No los combines "
            "con --guardar."
        )

    if guardar:
        archivo_salida = (
            BASE_DIR / "scraping_guardado.json"
        )
    else:
        archivo_salida = (
            BASE_DIR / "scraping_vista_previa.json"
        )

    configuracion = {
        "FUENTE_ID": fuente_id,
        "SOLO_LECTURA": not guardar,
        "TELNETCONSOLE_ENABLED": False,
        "LOG_LEVEL": "WARNING",
        "FEEDS": {
            str(archivo_salida): {
                "format": "json",
                "encoding": "utf-8",
                "indent": 2,
                "overwrite": True,
            },
        },
    }

    # Activar la escritura en PostgreSQL
    # solamente si se solicita --guardar.
    if guardar:
        configuracion["ITEM_PIPELINES"] = {
            "app.pipelines.GuardarConvocatoriasPipeline": 300,
        }

    print("=== MOTOR DE EXTRACCIÓN ITSVA ===")
    print(
        "Modo:",
        "GUARDADO" if guardar else "VISTA PREVIA",
    )
    print("Fuente ID:", fuente_id)
    print("Fuente:", url_inicial)
    print("Límite:", limite)
    print("Filtro temático:", modo)
    print("Filtro de vigencia:", vigencia)
    print("Fecha de referencia:", fecha_referencia)

    archivo_salida.unlink(
        missing_ok=True
    )

    proceso = CrawlerProcess(
        settings=configuracion
    )

    proceso.crawl(
        ConvocatoriasSpider,
        url=url_inicial,
        limite=limite,
    )

    proceso.start()

    if not archivo_salida.exists():
        raise RuntimeError(
            "El scraper no generó el JSON."
        )

    resultados = json.loads(
        archivo_salida.read_text(
            encoding="utf-8"
        )
    )

    categorias = {
        "todos": {
            "POSIBLE_PROYECTO",
            "REVISION_MANUAL",
            "OTRO_TIPO",
        },
        "proyectos": {
            "POSIBLE_PROYECTO",
        },
        "proyectos_revision": {
            "POSIBLE_PROYECTO",
            "REVISION_MANUAL",
        },
        "otros": {
            "OTRO_TIPO",
        },
    }

    permitidas = categorias[modo]

    seleccionadas = [
        item
        for item in resultados
        if obtener_clasificacion(item) in permitidas
    ]

    total_por_tema = len(seleccionadas)

    clasificados = []

    for item in seleccionadas:
        registro = dict(item)

        registro["vigencia"] = {
            "estado": clasificar_vigencia(
                item, fecha_referencia
            ),
            "fecha_referencia": fecha_referencia.isoformat(),
        }

        clasificados.append(registro)

    estados_permitidos = {
        "futuras": {"FECHA_FUTURA"},
        "futuras_revision": {
            "FECHA_FUTURA",
            "CIERRA_HOY",
            "SIN_FECHA_VERIFICABLE",
            "FECHA_INVALIDA",
        },
        "vencidas": {"VENCIDA"},
    }

    if vigencia == "todas":
        seleccionadas = clasificados
    else:
        seleccionadas = [
            item for item in clasificados
            if item["vigencia"]["estado"]
            in estados_permitidos[vigencia]
        ]

    if modo != "todos" or vigencia != "todas":
        archivo_filtrado = (
            BASE_DIR / "scraping_filtrado.json"
        )

        archivo_filtrado.write_text(
            json.dumps(
                seleccionadas,
                ensure_ascii=False,
                indent=2,
            ),
            encoding="utf-8",
        )

        print(
            "Archivo filtrado:",
            archivo_filtrado,
        )

    print("\n=== RESUMEN ===")
    print("Coinciden con el tema:", total_por_tema)
    print(
        "Convocatorias obtenidas:",
        len(resultados),
    )
    print(
        "Seleccionadas por el filtro:",
        len(seleccionadas),
    )

    if not resultados:
        print(
            "No se obtuvieron convocatorias. "
            "Revisa los mensajes de Scrapy."
        )
        return

    for numero, item in enumerate(
        seleccionadas,
        start=1,
    ):
        candidato = item.get(
            "fecha_cierre_candidata"
        ) or {}

        print(
            f"\n--- CONVOCATORIA {numero} ---"
        )

        print(
            "Título:",
            item.get("titulo"),
        )

        print(
            "Fecha candidata:",
            candidato.get("fecha"),
        )

        print(
            "Tipo:",
            candidato.get("tipo"),
        )

        print(
            "Vigencia:",
            item["vigencia"]["estado"],
        )
        print(
            "PDF:",
            len(item.get("documentos", [])),
        )

        print(
            "Revisión:",
            item.get("revision_db", {}).get(
                "estado"
            ),
        )

        if guardar:
            print(
                "Guardado:",
                item.get("guardado_db"),
            )

    print(
        "\nArchivo generado:",
        archivo_salida,
    )



def analizar_pdf_seleccionado(modo, vigencia, buscar):
    if modo == "todos" and vigencia == "todas":
        archivo_entrada = (
            BASE_DIR / "scraping_vista_previa.json"
        )
    else:
        archivo_entrada = (
            BASE_DIR / "scraping_filtrado.json"
        )

    convocatorias = json.loads(
        archivo_entrada.read_text(encoding="utf-8")
    )

    coincidencias = [
        item
        for item in convocatorias
        if buscar.casefold()
        in (item.get("titulo") or "").casefold()
    ]

    if len(coincidencias) != 1:
        raise ValueError(
            "El análisis financiero necesita una "
            "única convocatoria seleccionada. "
            f"Coincidencias: {len(coincidencias)}. "
            "Prueba otro título o amplía el límite."
        )

    carpeta = BASE_DIR / "data" / "resultados"
    carpeta.mkdir(parents=True, exist_ok=True)

    # Entregar solamente la convocatoria elegida
    # al programa de análisis financiero.
    seleccion = (
        carpeta / "seleccion_analisis_pdf.json"
    )

    seleccion.write_text(
        json.dumps(
            coincidencias,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    salida = (
        carpeta / "analisis_financiero_integrado.json"
    )

    print("\n=== ANÁLISIS FINANCIERO ===")
    print(
        "Convocatoria:",
        coincidencias[0]["titulo"],
    )

    # Scrapy ya terminó su primera ejecución.
    # El segundo proceso utiliza un reactor nuevo.
    subprocess.run(
        [
            sys.executable,
            "-m",
            "app.analizar_finanzas_json",
            "--buscar",
            coincidencias[0]["titulo"],
            "--entrada",
            str(seleccion),
            "--salida",
            str(salida),
        ],
        cwd=BASE_DIR,
        check=True,
    )


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Motor de extracción de convocatorias ITSVA"
        )
    )

    parser.add_argument(
        "--fuente-id",
        type=int,
        default=DEFAULT_FUENTE_ID,
        help=(
            "ID de la fuente registrada en PostgreSQL."
        ),
    )

    parser.add_argument(
        "--url",
        default=DEFAULT_URL_INICIAL,
        help=(
            "URL inicial que utilizará el spider."
        ),
    )

    parser.add_argument(
        "--limite",
        type=int,
        default=5,
        help="Número máximo de convocatorias.",
    )

    parser.add_argument(
        "--guardar",
        action="store_true",
        help="Activar el guardado en PostgreSQL.",
    )

    parser.add_argument(
        "--modo",
        choices=[
            "todos",
            "proyectos",
            "proyectos_revision",
            "otros",
        ],
        default="todos",
        help="Filtro temático de Vista previa.",
    )

    parser.add_argument(
        "--vigencia",
        choices=[
            "todas",
            "futuras",
            "futuras_revision",
            "vencidas",
        ],
        default="todas",
    )

    parser.add_argument(
        "--fecha",
        type=date.fromisoformat,
        default=date.today(),
        help="Fecha de referencia AAAA-MM-DD.",
    )

    parser.add_argument(
        "--analizar-pdf",
        action="store_true",
        help="Analizar los PDF de una convocatoria.",
    )

    parser.add_argument(
        "--buscar-pdf",
        help="Parte del título que se desea analizar.",
    )

    argumentos = parser.parse_args()

    if argumentos.analizar_pdf and argumentos.guardar:
        parser.error(
            "--analizar-pdf solo está disponible "
            "en Vista previa."
        )

    if argumentos.analizar_pdf and not argumentos.buscar_pdf:
        parser.error(
            "Debes indicar --buscar-pdf."
        )


    ejecutar(
        limite=argumentos.limite,
        guardar=argumentos.guardar,
        modo=argumentos.modo,
        vigencia=argumentos.vigencia,
        fecha_referencia=argumentos.fecha,
        fuente_id=argumentos.fuente_id,
        url_inicial=argumentos.url,
    )

    if argumentos.analizar_pdf:
        analizar_pdf_seleccionado(
            modo=argumentos.modo,
            vigencia=argumentos.vigencia,
            buscar=argumentos.buscar_pdf,
        )


if __name__ == "__main__":
    main()
