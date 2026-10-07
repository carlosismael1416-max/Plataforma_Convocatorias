
import json
import re
from pathlib import Path

import pymupdf


BASE_DIR = Path(__file__).resolve().parent.parent

PDF_TDR = (
    BASE_DIR / "data/pdfs/ecos_nord_2026_tdr.pdf"
)

SALIDA = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_apoyo_frances.json"
)


def compactar(texto):
    return re.sub(r"\s+", " ", texto).strip()


def comprobar(patron, texto, descripcion):
    if not re.search(
        patron,
        texto,
        re.IGNORECASE,
    ):
        raise ValueError(
            f"No se pudo comprobar: {descripcion}"
        )


def extraer_apoyo_frances():
    if not PDF_TDR.exists():
        raise FileNotFoundError(
            f"No se encontró: {PDF_TDR}"
        )

    with pymupdf.open(PDF_TDR) as pdf:
        texto_pagina = pdf[8].get_text(
            "text",
            sort=True,
        )

    texto = compactar(texto_pagina)

    # Aislar la sección correspondiente
    # exclusivamente a la parte francesa.
    coincidencia = re.search(
        r"Por la parte francesa"
        r"(.*?)"
        r"VIII\.\s*Proceso de revisión",
        texto,
        re.IGNORECASE | re.DOTALL,
    )

    if not coincidencia:
        raise ValueError(
            "No se encontró la sección "
            "de financiamiento francés."
        )

    seccion = coincidencia.group(1)

    bloque_pasajes = re.search(
        r"Pasajes\s*:"
        r"(.*?)"
        r"Viáticos\s*:",
        seccion,
        re.IGNORECASE | re.DOTALL,
    )

    bloque_viaticos = re.search(
        r"Viáticos\s*:"
        r"(.*?)"
        r"Los gastos no señalados",
        seccion,
        re.IGNORECASE | re.DOTALL,
    )

    if not bloque_pasajes or not bloque_viaticos:
        raise ValueError(
            "No se pudieron separar los "
            "apartados de pasajes y viáticos."
        )

    pasajes = compactar(
        bloque_pasajes.group(1)
    )

    viaticos = compactar(
        bloque_viaticos.group(1)
    )

    # Comprobar los importes antes
    # de asignarlos a los campos JSON.
    comprobar(
        r"€\s*1,300\.00",
        pasajes,
        "Pasajes de 1,300 euros",
    )

    comprobar(
        r"€\s*90\.00",
        viaticos,
        "Viáticos de 90 euros",
    )

    comprobar(
        r"€\s*65\.00",
        viaticos,
        "Viáticos de 65 euros",
    )

    comprobar(
        r"15\s+días\s+por\s+año",
        viaticos,
        "Límite de 15 días",
    )

    comprobar(
        r"duración\s+de\s+30\s+días",
        viaticos,
        "Duración de 30 días",
    )

    comprobar(
        r"45\s+días\s+por\s+año",
        viaticos,
        "Límite de 45 días",
    )

    fuente_pasajes = {
        "documento": PDF_TDR.name,
        "pagina": 9,
        "fragmento": pasajes,
    }

    fuente_viaticos = {
        "documento": PDF_TDR.name,
        "pagina": 9,
        "fragmento": viaticos,
    }

    return {
        "convocatoria": "ECOS Nord 2026",
        "apoyo_parte_francesa": {
            "moneda": "EUR",
            "pasajes_internacionales": {
                "maximo_por_persona_anual": 1300,
                "beneficiarios": (
                    "Una persona investigadora y "
                    "una estudiante francesa"
                ),
                "tipo_tarifa": "ECONOMICA",
                "fuente": fuente_pasajes,
            },
            "viaticos_investigadores": {
                "maximo_diario": 90,
                "maximo_dias_por_anio": 15,
                "fuente": fuente_viaticos,
            },
            "viaticos_estudiantes": {
                "maximo_diario": 65,
                "duracion_indicada_dias": 30,
                "maximo_dias_por_anio": 45,
                "fuente": fuente_viaticos,
            },
            "separado_del_apoyo_mexicano": True,
        },
    }


def main():
    resultado = extraer_apoyo_frances()

    SALIDA.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    SALIDA.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    apoyo = resultado["apoyo_parte_francesa"]

    print("=== APOYO DE LA PARTE FRANCESA ===")
    print("Moneda:", apoyo["moneda"])

    print(
        "Pasajes por persona y año:",
        apoyo["pasajes_internacionales"][
            "maximo_por_persona_anual"
        ],
    )

    print(
        "Viáticos investigadores:",
        apoyo["viaticos_investigadores"][
            "maximo_diario"
        ],
        "EUR diarios",
    )

    print(
        "Viáticos estudiantes:",
        apoyo["viaticos_estudiantes"][
            "maximo_diario"
        ],
        "EUR diarios",
    )

    print(
        "Presupuesto separado del mexicano:",
        apoyo["separado_del_apoyo_mexicano"],
    )

    print("\nJSON generado:", SALIDA)


if __name__ == "__main__":
    main()
