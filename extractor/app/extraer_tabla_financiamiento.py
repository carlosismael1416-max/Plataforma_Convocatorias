
import json
import re
import unicodedata
from decimal import Decimal
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent.parent

ENTRADA = (
    BASE_DIR
    / "data/resultados/ciencia_basica_2025_finanzas.json"
)

SALIDA = (
    BASE_DIR
    / "data/resultados/ciencia_basica_2025_tabla.json"
)


def normalizar(texto):
    texto = unicodedata.normalize(
        "NFKD",
        texto.lower(),
    )

    texto = "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )

    return re.sub(r"\s+", " ", texto).strip()


def convertir_importe(texto):
    return int(
        Decimal(texto.replace(",", ""))
    )


def extraer_tabla(documento):
    # Los fragmentos conservan el contexto
    # de las cantidades encontradas en el PDF.
    fragmentos = [
        monto["fuente"]
        for monto in documento["montos_clasificados"]
    ]

    for fuente in fragmentos:
        original = fuente["fragmento"]
        texto = normalizar(original)

        # Solo interpretar una tabla que identifique
        # expresamente la moneda y sus tres etapas.
        if "(pesos mexicanos)" not in texto:
            continue

        etapas = re.findall(
            r"etapa\s+([123])\s*\((\d{4})\)",
            texto,
        )

        if len(etapas) != 3:
            continue

        patron_fila = re.compile(
            r"(?P<modalidad>"
            r"1\.\s*individual"
            r"|2\.\s*grupo de investigacion"
            r")\s+"
            r"(?P<importes>"
            r"(?:\$\s*[\d,.]+\s*){4}"
            r")"
        )

        filas = {}

        for coincidencia in patron_fila.finditer(texto):
            modalidad = coincidencia.group("modalidad")

            importes = re.findall(
                r"\$\s*([\d,.]+)",
                coincidencia.group("importes"),
            )

            if len(importes) != 4:
                continue

            valores = [
                convertir_importe(importe)
                for importe in importes
            ]

            nombre = (
                "INDIVIDUAL"
                if "individual" in modalidad
                else "GRUPO_INVESTIGACION"
            )

            filas[nombre] = {
                "etapas": valores[:3],
                "total": valores[3],
            }

        if set(filas) != {
            "INDIVIDUAL",
            "GRUPO_INVESTIGACION",
        }:
            continue

        for nombre, fila in filas.items():
            if sum(fila["etapas"]) != fila["total"]:
                raise ValueError(
                    "Los importes no coinciden "
                    f"en la modalidad {nombre}."
                )

        return {
            "etapas": [
                {
                    "numero": int(numero),
                    "anio": int(anio),
                }
                for numero, anio in etapas
            ],
            "modalidades": filas,
            "fuente": {
                "documento": documento["documento"],
                "pagina": fuente["pagina"],
                "fragmento": original,
            },
        }

    raise ValueError(
        "No se pudo identificar la tabla de "
        f"{documento['documento']}."
    )


def main():
    datos = json.loads(
        ENTRADA.read_text(encoding="utf-8")
    )

    documentos = datos["documentos_pdf"]

    if len(documentos) != 2:
        raise ValueError(
            "Esta prueba necesita la convocatoria "
            "principal y los TDR."
        )

    tablas = [
        extraer_tabla(documento)
        for documento in documentos
    ]

    primera, segunda = tablas

    # No fusionar información contradictoria.
    if (
        primera["etapas"] != segunda["etapas"]
        or primera["modalidades"]
        != segunda["modalidades"]
    ):
        raise ValueError(
            "Las tablas de los dos PDF son distintas. "
            "Se requiere revisión manual."
        )

    resultado = {
        "convocatoria": (
            "Ciencia Básica y de Frontera 2025"
        ),
        "moneda": "MXN",
        "estado": "EXTRACCION_PRELIMINAR",
        "etapas": primera["etapas"],
        "modalidades": primera["modalidades"],
        "validaciones": {
            "sumas_correctas": True,
            "documentos_coinciden": True,
        },
        "fuentes": [
            tabla["fuente"]
            for tabla in tablas
        ],
    }

    SALIDA.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("=== TABLA DE FINANCIAMIENTO ===")
    print("Moneda:", resultado["moneda"])

    for nombre, fila in (
        resultado["modalidades"].items()
    ):
        print("\nModalidad:", nombre)

        for etapa, importe in zip(
            resultado["etapas"],
            fila["etapas"],
        ):
            print(
                f"Etapa {etapa['numero']} "
                f"({etapa['anio']}):",
                importe,
            )

        print("Máximo total:", fila["total"])

    print("\nSUMAS CORRECTAS: Sí")
    print("AMBOS DOCUMENTOS COINCIDEN: Sí")
    print("\nJSON generado:", SALIDA)


if __name__ == "__main__":
    main()
