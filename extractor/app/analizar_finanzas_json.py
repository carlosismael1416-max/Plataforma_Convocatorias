
import argparse
import json
from pathlib import Path

from scrapy.crawler import CrawlerProcess

from app.probar_scrapy_finanzas import FinanzasSpider
from app.detectar_tabla_financiera import detectar_tabla_financiera
from app.conciliar_montos_tabla import conciliar_montos_tabla


BASE_DIR = Path(__file__).resolve().parent.parent


def seleccionar_documentos(convocatoria):
    """
    Selecciona la convocatoria principal y los TDR.

    Reconoce tanto 'TDR' como
    'Términos de Referencia'.
    """

    import unicodedata

    def normalizar(texto):
        texto = unicodedata.normalize(
            "NFKD",
            (texto or "").lower(),
        )

        return "".join(
            caracter
            for caracter in texto
            if not unicodedata.combining(caracter)
        ).strip()

    principales = []
    terminos = []

    urls_vistas = set()

    for documento in convocatoria.get("documentos", []):
        nombre = normalizar(
            documento.get("nombre")
        )

        url = documento.get("url") or ""

        if not url.lower().split("?")[0].endswith(".pdf"):
            continue

        clave = url.rstrip("/")

        if clave in urls_vistas:
            continue

        urls_vistas.add(clave)

        # Los TDR se comprueban primero, pues
        # su título puede contener "convocatoria".
        if (
            "terminos de referencia" in nombre
            or nombre.startswith("tdr ")
            or nombre == "tdr"
        ):
            terminos.append(documento)

        elif (
            nombre.startswith("descargar convocatoria")
            or nombre == "convocatoria"
            or nombre.startswith("convocatoria ")
        ):
            principales.append(documento)

    # Evitar elegir arbitrariamente documentos
    # cuando existen varios candidatos.
    if len(principales) > 1:
        raise ValueError(
            "Se detectaron varias posibles "
            "convocatorias principales. "
            "Es necesario revisarlas."
        )

    if len(terminos) > 1:
        raise ValueError(
            "Se detectaron varios posibles TDR. "
            "Es necesario revisarlos."
        )

    return principales + terminos




def agregar_tabla_financiera(resultado):
    """
    Incorpora las tablas identificadas sin eliminar
    los montos candidatos de los documentos.
    """

    tabla = detectar_tabla_financiera(
        resultado["documentos_pdf"]
    )

    resultado["tabla_financiera"] = tabla

    resumen = resultado["resumen"]

    if tabla is None:
        resumen["estado_tabla_financiera"] = (
            "NO_DETECTADA"
        )
        resumen["modalidades_estructuradas"] = 0
        resumen["celdas_monetarias_tabla"] = 0
        return resultado

    resumen["estado_tabla_financiera"] = (
        tabla["estado"]
    )

    modalidades = tabla.get("modalidades") or {}

    resumen["modalidades_estructuradas"] = len(
        modalidades
    )

    if tabla["estado"] == "COINCIDENCIA_DOCUMENTAL":
        resumen["celdas_monetarias_tabla"] = sum(
            len(datos["etapas"]) + 1
            for datos in modalidades.values()
        )
    else:
        resumen["celdas_monetarias_tabla"] = 0

    return resultado


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Analizar los PDF de una convocatoria "
            "encontrada por el scraper."
        )
    )

    parser.add_argument(
        "--buscar",
        required=True,
        help="Parte del título de la convocatoria.",
    )

    parser.add_argument(
        "--entrada",
        type=Path,
        default=BASE_DIR / "scraping_vista_previa.json",
    )

    parser.add_argument(
        "--salida",
        type=Path,
        default=BASE_DIR / "vista_previa_financiera.json",
    )

    argumentos = parser.parse_args()

    convocatorias = json.loads(
        argumentos.entrada.read_text(encoding="utf-8")
    )

    coincidencias = [
        item
        for item in convocatorias
        if argumentos.buscar.casefold()
        in (item.get("titulo") or "").casefold()
    ]

    if len(coincidencias) != 1:
        raise ValueError(
            "Se esperaba una única convocatoria. "
            f"Se encontraron: {len(coincidencias)}"
        )

    convocatoria = coincidencias[0]

    documentos = seleccionar_documentos(
        convocatoria
    )

    if not documentos:
        raise ValueError(
            "No se encontraron PDF principales o TDR."
        )

    # Archivo intermedio con los resultados
    # generados por Scrapy.
    temporal = argumentos.salida.with_name(
        argumentos.salida.stem + "_documentos.json"
    )

    temporal.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    temporal.unlink(missing_ok=True)

    proceso = CrawlerProcess(
        settings={
            "ITEM_PIPELINES": {},
            "TELNETCONSOLE_ENABLED": False,
            "LOG_LEVEL": "WARNING",
            "FEEDS": {
                str(temporal): {
                    "format": "json",
                    "encoding": "utf-8",
                    "indent": 2,
                    "overwrite": True,
                },
            },
        }
    )

    proceso.crawl(
        FinanzasSpider,
        documentos=documentos,
    )

    proceso.start()

    if not temporal.exists():
        raise RuntimeError(
            "Scrapy no generó los resultados financieros."
        )

    analisis = json.loads(
        temporal.read_text(encoding="utf-8")
    )

    montos = [
        monto
        for documento in analisis
        for monto in documento.get(
            "montos_clasificados", []
        )
    ]

    resultado = {
        "convocatoria": {
            "titulo": convocatoria.get("titulo"),
            "url_original": convocatoria.get("url_original"),
            "fecha_cierre_candidata": convocatoria.get(
                "fecha_cierre_candidata"
            ),
        },
        "estado": "ANALISIS_FINANCIERO_PRELIMINAR",
        "documentos_pdf": analisis,
        "resumen": {
            "documentos_seleccionados": len(documentos),
            "documentos_procesados": len(analisis),
            "montos_encontrados": len(montos),
            "reglas_coincidentes": sum(
                monto.get("estado") == "REGLA_COINCIDENTE"
                for monto in montos
            ),
            "pendientes_revision": sum(
                monto.get("estado") != "REGLA_COINCIDENTE"
                for monto in montos
            ),
        },
    }

    resultado = agregar_tabla_financiera(resultado)
    resultado = conciliar_montos_tabla(resultado)

    argumentos.salida.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("\n=== VISTA PREVIA FINANCIERA ===")
    print("Convocatoria:", convocatoria["titulo"])

    for nombre, cantidad in resultado["resumen"].items():
        print(f"{nombre}: {cantidad}")

    print("\nMONTOS DETECTADOS")

    for documento in resultado["documentos_pdf"]:
        print("\nDOCUMENTO:", documento["documento"])

        for monto in documento["montos_clasificados"]:
            conciliacion = (
                monto.get("conciliacion_tabla") or {}
            )

            fuente = monto.get("fuente") or {}
            pagina = fuente.get("pagina", "?")

            if (
                conciliacion.get("estado")
                == "CUBIERTO_POR_TABLA"
            ):
                moneda = conciliacion["moneda"]

                print(
                    f"{monto['valor']} {moneda}"
                    " - CUBIERTO_POR_TABLA"
                    f" (página {pagina})"
                )
            else:
                print(
                    f"{monto['valor']} "
                    f"{monto['moneda']}"
                    f" - {monto['concepto']}"
                    f" [{monto['estado']}]"
                    f" (página {pagina})"
                )

    print("\nJSON generado:", argumentos.salida)


if __name__ == "__main__":
    main()
