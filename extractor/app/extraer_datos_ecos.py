import json
import re
import unicodedata
from pathlib import Path

import fitz
import scrapy
from scrapy.crawler import CrawlerProcess


BASE_DIR = Path(__file__).resolve().parent.parent

CARPETA_PDF = BASE_DIR / "data" / "pdfs"
CARPETA_TEXTO = BASE_DIR / "data" / "textos"
CARPETA_RESULTADOS = BASE_DIR / "data" / "resultados"


DOCUMENTOS = {
    "convocatoria": {
        "nombre": "Convocatoria ECOS Nord 2026",
        "url": (
            "https://www.secihti.mx/wp-content/uploads/"
            "2026/04/1.-Convocatoria-Ecos-Nord-"
            "2026-20260413T195309.pdf"
        ),
    },
    "tdr": {
        "nombre": "TDR ECOS Nord 2026",
        "url": (
            "https://www.secihti.mx/wp-content/uploads/"
            "2026/04/2.-TDR-Convocatoria-Ecos-"
            "Nord-2026-20260413T195444.pdf"
        ),
    },
}


def normalizar(texto):
    texto = unicodedata.normalize(
        "NFKD",
        texto.lower(),
    )

    return "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )


PATRONES = {
    "FINANCIAMIENTO": re.compile(
        r"\bmontos?\b"
        r"|\bfinanciamiento\b"
        r"|\bpresupuesto\b"
        r"|\bpesos\b"
        r"|\$\s*[\d,.]+"
    ),

    "REQUISITOS": re.compile(
        r"\brequisitos?\b"
        r"|\belegibilidad\b"
        r"|\bpodran participar\b"
        r"|\binstituciones participantes\b"
        r"|\bsujetos de apoyo\b"
    ),

    "GASTOS_Y_RUBROS": re.compile(
        r"\bgastos?\s+elegibles\b"
        r"|\brubros?\b"
        r"|\bconceptos?\s+de\s+apoyo\b"
    ),
}


def buscar_fragmentos(paginas):
    resultados = {
        categoria: []
        for categoria in PATRONES
    }

    for numero_pagina, texto in paginas:
        lineas = texto.splitlines()

        for posicion, linea in enumerate(lineas):
            linea_normalizada = normalizar(linea)

            for categoria, patron in PATRONES.items():
                if not patron.search(linea_normalizada):
                    continue

                # Conservar dos líneas anteriores
                # y dos posteriores como contexto.
                inicio = max(0, posicion - 2)
                fin = min(
                    len(lineas),
                    posicion + 3,
                )

                fragmento = "\n".join(
                    lineas[inicio:fin]
                ).strip()

                registro = {
                    "pagina": numero_pagina,
                    "texto": fragmento,
                }

                if registro not in resultados[categoria]:
                    resultados[categoria].append(
                        registro
                    )

    return resultados


class ExtraerDatosEcosSpider(scrapy.Spider):
    name = "extraer_datos_ecos"

    custom_settings = {
        "ROBOTSTXT_OBEY": True,
        "DOWNLOAD_DELAY": 2,
        "CONCURRENT_REQUESTS_PER_DOMAIN": 1,
        "TELNETCONSOLE_ENABLED": False,
        "LOG_LEVEL": "WARNING",
    }

    async def start(self):
        for clave, documento in DOCUMENTOS.items():
            yield scrapy.Request(
                url=documento["url"],
                callback=self.procesar_pdf,
                errback=self.error_descarga,
                cb_kwargs={"clave": clave},
            )

    def procesar_pdf(self, response, clave):
        documento = DOCUMENTOS[clave]

        if b"%PDF-" not in response.body[:1024]:
            print(
                "La respuesta no parece ser un PDF:",
                response.url,
            )
            return

        CARPETA_PDF.mkdir(
            parents=True,
            exist_ok=True,
        )

        CARPETA_TEXTO.mkdir(
            parents=True,
            exist_ok=True,
        )

        CARPETA_RESULTADOS.mkdir(
            parents=True,
            exist_ok=True,
        )

        nombre_base = f"ecos_nord_2026_{clave}"

        ruta_pdf = (
            CARPETA_PDF / f"{nombre_base}.pdf"
        )

        ruta_texto = (
            CARPETA_TEXTO / f"{nombre_base}.txt"
        )

        ruta_resultado = (
            CARPETA_RESULTADOS
            / f"{nombre_base}.json"
        )

        ruta_pdf.write_bytes(response.body)

        paginas = []

        with fitz.open(
            stream=response.body,
            filetype="pdf",
        ) as pdf:

            total_paginas = len(pdf)

            for numero, pagina in enumerate(
                pdf,
                start=1,
            ):
                texto = pagina.get_text(
                    "text",
                    sort=True,
                )

                paginas.append(
                    (numero, texto)
                )

        texto_completo = "\n\n".join(
            f"===== PÁGINA {numero} =====\n{texto}"
            for numero, texto in paginas
        )

        ruta_texto.write_text(
            texto_completo,
            encoding="utf-8",
        )

        fragmentos = buscar_fragmentos(
            paginas
        )

        resultado = {
            "documento": documento["nombre"],
            "url_fuente": response.url,
            "total_paginas": total_paginas,
            "caracteres_extraidos": sum(
                len(texto)
                for _, texto in paginas
            ),
            "fragmentos": fragmentos,
        }

        ruta_resultado.write_text(
            json.dumps(
                resultado,
                ensure_ascii=False,
                indent=2,
            ),
            encoding="utf-8",
        )

        print("\n" + "=" * 55)
        print("DOCUMENTO:", documento["nombre"])
        print("Páginas:", total_paginas)

        print(
            "Caracteres extraídos:",
            resultado["caracteres_extraidos"],
        )

        for categoria, encontrados in (
            fragmentos.items()
        ):
            print(f"\n--- {categoria} ---")
            print(
                "Coincidencias:",
                len(encontrados),
            )

            # Mostrar únicamente los primeros
            # tres fragmentos de cada categoría.
            for fragmento in encontrados[:3]:
                print(
                    "\nPágina:",
                    fragmento["pagina"],
                )
                print(
                    fragmento["texto"][:450]
                )

        print("\nPDF:", ruta_pdf)
        print("Texto:", ruta_texto)
        print("Fragmentos:", ruta_resultado)

        if resultado["caracteres_extraidos"] < 100:
            print(
                "ADVERTENCIA: se extrajo muy poco "
                "texto. El documento podría "
                "contener páginas escaneadas."
            )

    def error_descarga(self, failure):
        print("\nERROR AL DESCARGAR PDF")
        print("URL:", failure.request.url)
        print("Detalle:", failure.value)


if __name__ == "__main__":
    proceso = CrawlerProcess(
        settings={
            "LOG_LEVEL": "WARNING",
            "TELNETCONSOLE_ENABLED": False,
        }
    )

    proceso.crawl(ExtraerDatosEcosSpider)
    proceso.start()
