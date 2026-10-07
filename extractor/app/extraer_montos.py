import json
import re
from decimal import Decimal
from pathlib import Path


ARCHIVO_TEXTO = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)

ARCHIVO_SALIDA = Path(
    "data/extraidos/montos_convocatoria_2025.json"
)

# Identifica los grupos de ejes al inicio de cada fila.
PATRON_EJES = re.compile(
    r"^\s*(\d+(?:\s*,\s*\d+)*\s+y\s+\d+)\s+(.*)$",
    re.IGNORECASE,
)

# Identifica importes como $500,000.00.
PATRON_MONTO = re.compile(
    r"\$\s*[\d,]+\.\d{2}"
)


def convertir_monto(valor):
    numero = (
        valor.replace("$", "")
        .replace(",", "")
        .strip()
    )

    monto = Decimal(numero)

    # Mantener los centavos si el monto los tiene.
    if monto == monto.to_integral_value():
        return int(monto)

    return float(monto)


def obtener_seccion_montos(texto):
    inicio = re.search(
        r"^\s*III\.\s*Ejes estratégicos",
        texto,
        re.IGNORECASE | re.MULTILINE,
    )

    if not inicio:
        raise ValueError(
            "No se encontró la sección III."
        )

    contenido = texto[inicio.end():]

    fin = re.search(
        r"^\s*IV\.\s*Calendario",
        contenido,
        re.IGNORECASE | re.MULTILINE,
    )

    if not fin:
        raise ValueError(
            "No se encontró el final de la sección III."
        )

    return contenido[:fin.start()]


def extraer_montos(texto):
    seccion = obtener_seccion_montos(texto)

    # Identificar los ejercicios fiscales de cada etapa.
    etapas = re.search(
        r"Etapa\s*1\s*\((\d{4})\)\s*"
        r"Etapa\s*2\s*\((\d{4})\)",
        seccion,
        re.IGNORECASE,
    )

    if not etapas:
        raise ValueError(
            "No se identificaron los años de las etapas."
        )

    anio_etapa_1 = int(etapas.group(1))
    anio_etapa_2 = int(etapas.group(2))

    resultados = []

    for linea in seccion.splitlines():
        coincidencia = PATRON_EJES.match(linea)

        if not coincidencia:
            continue

        grupo_ejes = coincidencia.group(1)
        contenido_fila = coincidencia.group(2)

        importes = PATRON_MONTO.findall(
            contenido_fila
        )

        # Cada fila debe tener tres importes:
        # etapa 1, etapa 2 y total.
        if len(importes) != 3:
            continue

        numeros_ejes = [
            int(numero)
            for numero in re.findall(
                r"\d+",
                grupo_ejes,
            )
        ]

        monto_etapa_1 = convertir_monto(
            importes[0]
        )

        monto_etapa_2 = convertir_monto(
            importes[1]
        )

        monto_total = convertir_monto(
            importes[2]
        )

        suma_correcta = (
            Decimal(str(monto_etapa_1))
            + Decimal(str(monto_etapa_2))
            == Decimal(str(monto_total))
        )

        resultados.append({
            "ejes_estrategicos": numeros_ejes,
            "etapa_1": {
                "anio": anio_etapa_1,
                "monto_maximo": monto_etapa_1,
            },
            "etapa_2": {
                "anio": anio_etapa_2,
                "monto_maximo": monto_etapa_2,
            },
            "monto_maximo_total": monto_total,
            "moneda": "MXN",
            "suma_verificada": suma_correcta,
        })

    if not resultados:
        raise ValueError(
            "No se encontraron filas válidas de montos."
        )

    return resultados


def main():
    texto = ARCHIVO_TEXTO.read_text(
        encoding="utf-8"
    )

    montos = extraer_montos(texto)

    resultado = {
        "tipos_apoyo": montos,
        "numero_maximo_etapas": 2,
        "fuente": str(ARCHIVO_TEXTO),
        "metodo_extraccion": "reglas",
    }

    ARCHIVO_SALIDA.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    contenido = json.dumps(
        resultado,
        ensure_ascii=False,
        indent=2,
    )

    ARCHIVO_SALIDA.write_text(
        contenido,
        encoding="utf-8",
    )

    print("=== MONTOS EXTRAÍDOS ===\n")
    print(contenido)

    print(
        "\nArchivo generado:",
        ARCHIVO_SALIDA,
    )


if __name__ == "__main__":
    main()
