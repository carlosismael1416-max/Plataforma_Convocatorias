
import argparse
import json
import re
from pathlib import Path

import pymupdf


# Detecta cantidades que indiquen explícitamente
# un símbolo o código de moneda.
PATRON_MONTO = re.compile(
    r"(?<!\w)"
    r"(?P<moneda>MXN|USD|EUR|MX\$|US\$|\$|€)"
    r"\s*"
    r"(?P<importe>\d[\d,.]*)",
    re.IGNORECASE,
)


PATRONES_FRAGMENTOS = {
    "FINANCIAMIENTO": re.compile(
        r"\bmonto\b"
        r"|\bmontos\b"
        r"|\bfinanciamiento\b"
        r"|\bpresupuesto\b"
        r"|\bministraci[oó]n\b",
        re.IGNORECASE,
    ),

    "REQUISITOS": re.compile(
        r"\brequisitos?\b"
        r"|\belegibilidad\b"
        r"|\bpodr[aá]n participar\b"
        r"|\bcarta de postulaci[oó]n\b",
        re.IGNORECASE,
    ),

    "RUBROS": re.compile(
        r"\brubros?\b"
        r"|\bgastos? financiables\b"
        r"|\bvi[aá]ticos\b"
        r"|\bpasajes\b"
        r"|\bauditor[ií]a\b",
        re.IGNORECASE,
    ),
}


def compactar(texto):
    return re.sub(
        r"\s+",
        " ",
        texto,
    ).strip()


def identificar_moneda(simbolo):
    simbolo = simbolo.upper()

    if simbolo in ("MXN", "MX$"):
        return "MXN"

    if simbolo in ("USD", "US$"):
        return "USD"

    if simbolo in ("EUR", "€"):
        return "EUR"

    # No asumir que todos los documentos
    # utilizan pesos mexicanos.
    return "NO_DETERMINADA"


def extraer_fragmentos(texto, numero_pagina):
    resultados = {
        categoria: []
        for categoria in PATRONES_FRAGMENTOS
    }

    lineas = texto.splitlines()

    for indice, linea in enumerate(lineas):
        for categoria, patron in (
            PATRONES_FRAGMENTOS.items()
        ):
            if not patron.search(linea):
                continue

            inicio = max(0, indice - 1)
            fin = min(
                len(lineas),
                indice + 3,
            )

            fragmento = compactar(
                " ".join(lineas[inicio:fin])
            )

            registro = {
                "pagina": numero_pagina,
                "fragmento": fragmento,
                "estado": "PENDIENTE_REVISION",
            }

            if registro not in resultados[categoria]:
                resultados[categoria].append(
                    registro
                )

    return resultados


def analizar_pdf(ruta_pdf, url_fuente=None):
    if not ruta_pdf.is_file():
        raise FileNotFoundError(
            f"No se encontró el PDF: {ruta_pdf}"
        )

    resultado = {
        "archivo": ruta_pdf.name,
        "url_fuente": url_fuente,
        "estado_extraccion": "COMPLETADA",
        "revision": "PENDIENTE_REVISION",
        "total_paginas": 0,
        "caracteres_extraidos": 0,
        "montos_candidatos": [],
        "fragmentos": {
            categoria: []
            for categoria in PATRONES_FRAGMENTOS
        },
        "advertencias": [],
    }

    montos_encontrados = set()

    with pymupdf.open(ruta_pdf) as documento:
        if documento.needs_pass:
            raise ValueError(
                "El PDF está protegido con contraseña."
            )

        resultado["total_paginas"] = len(
            documento
        )

        for numero, pagina in enumerate(
            documento,
            start=1,
        ):
            texto = pagina.get_text(
                "text",
                sort=True,
            )

            resultado["caracteres_extraidos"] += (
                len(texto)
            )

            texto_compacto = compactar(texto)

            # Detectar montos sin atribuirles
            # todavía un significado financiero.
            for coincidencia in (
                PATRON_MONTO.finditer(texto_compacto)
            ):
                simbolo = coincidencia.group(
                    "moneda"
                )

                importe = (
                    coincidencia.group("importe")
                    .rstrip(".,")
                )

                clave = (
                    numero,
                    simbolo,
                    importe,
                )

                if clave in montos_encontrados:
                    continue

                montos_encontrados.add(clave)

                inicio = max(
                    0,
                    coincidencia.start() - 120,
                )

                fin = min(
                    len(texto_compacto),
                    coincidencia.end() + 160,
                )

                resultado[
                    "montos_candidatos"
                ].append({
                    "importe_original": importe,
                    "moneda": identificar_moneda(
                        simbolo
                    ),
                    "simbolo_original": simbolo,
                    "pagina": numero,
                    "fragmento": (
                        texto_compacto[inicio:fin]
                    ),
                    "estado": (
                        "PENDIENTE_REVISION"
                    ),
                })

            # Detectar fragmentos relacionados
            # con financiamiento, requisitos y rubros.
            fragmentos = extraer_fragmentos(
                texto,
                numero,
            )

            for categoria, encontrados in (
                fragmentos.items()
            ):
                resultado["fragmentos"][
                    categoria
                ].extend(encontrados)

    if resultado["caracteres_extraidos"] < 100:
        resultado["advertencias"].append(
            "Se extrajo muy poco texto. "
            "El PDF podría estar escaneado."
        )

    if not resultado["montos_candidatos"]:
        resultado["advertencias"].append(
            "No se detectaron cantidades "
            "con símbolos de moneda."
        )

    return resultado


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Extraer información preliminar "
            "de cualquier PDF de convocatoria."
        )
    )

    parser.add_argument(
        "--pdf",
        required=True,
        type=Path,
        help="Ruta del PDF que se analizará.",
    )

    parser.add_argument(
        "--salida",
        required=True,
        type=Path,
        help="Ruta del JSON resultante.",
    )

    parser.add_argument(
        "--url-fuente",
        default=None,
        help="URL original del documento.",
    )

    argumentos = parser.parse_args()

    resultado = analizar_pdf(
        argumentos.pdf,
        argumentos.url_fuente,
    )

    argumentos.salida.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    argumentos.salida.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("\n=== EXTRACCIÓN PDF GENÉRICA ===")
    print("Archivo:", resultado["archivo"])
    print(
        "Páginas:",
        resultado["total_paginas"],
    )
    print(
        "Caracteres:",
        resultado["caracteres_extraidos"],
    )
    print(
        "Montos candidatos:",
        len(resultado["montos_candidatos"]),
    )

    for categoria, fragmentos in (
        resultado["fragmentos"].items()
    ):
        print(
            f"{categoria}:",
            len(fragmentos),
            "fragmentos",
        )

    print("\nPrimeros montos encontrados:")

    for monto in (
        resultado["montos_candidatos"][:5]
    ):
        print(
            monto["simbolo_original"],
            monto["importe_original"],
            "- Página",
            monto["pagina"],
        )

    for advertencia in resultado["advertencias"]:
        print("ADVERTENCIA:", advertencia)

    print("\nJSON generado:", argumentos.salida)


if __name__ == "__main__":
    main()
