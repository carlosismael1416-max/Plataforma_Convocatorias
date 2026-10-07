
import json
from decimal import Decimal
from pathlib import Path


BASE_DIR = Path(__file__).resolve().parent.parent

ARCHIVO_FINANCIERO = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_finanzas_final.json"
)

ARCHIVO_SCRAPING = (
    BASE_DIR / "scraping_vista_previa.json"
)

ARCHIVO_SALIDA = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_apoyos_preparados.json"
)


# Configuración específica de ECOS Nord 2026.
# Los importes deben coincidir con los extraídos
# de los PDF; no se sustituyen automáticamente.

CONCEPTOS = {
    "APOYO_MAXIMO_PROYECTO": {
        "concepto": "Apoyo máximo del proyecto",
        "valor_esperado": "600000",
        "moneda": "MXN",
        "componente": "MEXICANO",
        "unidad": "PROYECTO_TOTAL",
        "incluido_en_clave": None,
        "condiciones": {
            "numero_etapas": 4,
            "sujeto_a_disponibilidad_presupuestal": True,
        },
    },
    "MAXIMO_ETAPA_ANUAL": {
        "concepto": "Máximo por etapa anual",
        "valor_esperado": "150000",
        "moneda": "MXN",
        "componente": "MEXICANO",
        "unidad": "ETAPA_ANUAL",
        "incluido_en_clave": "APOYO_MAXIMO_PROYECTO",
        "condiciones": {
            "numero_etapas": 4,
        },
    },
    "PASAJE_INTERNACIONAL": {
        "concepto": "Pasaje internacional",
        "valor_esperado": "1300",
        "moneda": "EUR",
        "componente": "FRANCES",
        "unidad": "PASAJE",
        "incluido_en_clave": None,
        "condiciones": {
            "beneficiarios": {
                "investigadores_franceses": 1,
                "estudiantes_franceses": 1,
            },
            "tope_por_persona": True,
            "periodicidad": "ANUAL",
            "trayecto": "FRANCIA_MEXICO_IDA_Y_VUELTA",
            "tarifa": "ECONOMICA",
        },
    },
    "VIATICOS_INVESTIGADORES": {
        "concepto": "Viáticos de investigadores",
        "valor_esperado": "90",
        "moneda": "EUR",
        "componente": "FRANCES",
        "unidad": "DIA",
        "incluido_en_clave": None,
        "condiciones": {
            "beneficiarios": "INVESTIGADORES_FRANCESES_EN_MEXICO",
            "gastos_cubiertos": [
                "ALIMENTACION",
                "HOSPEDAJE",
                "TRASLADO_INTERNO",
            ],
            "maximo_dias_por_anio": 15,
        },
    },
    "VIATICOS_ESTUDIANTES": {
        "concepto": "Viáticos de estudiantes",
        "valor_esperado": "65",
        "moneda": "EUR",
        "componente": "FRANCES",
        "unidad": "DIA",
        "incluido_en_clave": None,
        "condiciones": {
            "beneficiarios": "ESTUDIANTES_FRANCESES_EN_MEXICO",
            "gastos_cubiertos": [
                "ALIMENTACION",
                "HOSPEDAJE",
                "TRASLADO_INTERNO",
            ],
            "duracion_indicada_dias": 30,
            "maximo_dias_por_anio": 45,
        },
    },
}


def cargar_json(ruta):
    return json.loads(
        ruta.read_text(encoding="utf-8")
    )


def obtener_convocatoria(financiero, resultados):
    identidad = financiero["convocatoria"]

    if identidad["titulo"] != "Convocatoria Ecos Nord 2026":
        raise ValueError(
            "Este módulo está preparado únicamente "
            "para ECOS Nord 2026."
        )

    url = identidad["url_original"].rstrip("/")

    coincidencias = [
        item
        for item in resultados
        if (item.get("url_original") or "").rstrip("/")
        == url
    ]

    if len(coincidencias) != 1:
        raise ValueError(
            "No se encontró exactamente una "
            "convocatoria con la URL indicada."
        )

    convocatoria = coincidencias[0]

    if convocatoria["titulo"] != identidad["titulo"]:
        raise ValueError(
            "Los títulos de los dos JSON no coinciden."
        )

    return convocatoria


def preparar_apoyos(financiero, convocatoria):
    documentos = {
        documento["documento"]: documento
        for documento in financiero["documentos_pdf"]
    }

    urls_scraping = {
        archivo["url"]
        for archivo in convocatoria["documentos"]
    }

    apoyos = {}

    for documento in financiero["documentos_pdf"]:
        for monto in documento["montos_clasificados"]:
            clave = monto["concepto"]

            if clave not in CONCEPTOS:
                raise ValueError(
                    f"Concepto no reconocido: {clave}"
                )

            if clave in apoyos:
                raise ValueError(
                    f"Concepto duplicado: {clave}. "
                    "Se requiere revisión."
                )

            especificacion = CONCEPTOS[clave]
            valor = Decimal(str(monto["valor"]))

            if valor != Decimal(
                especificacion["valor_esperado"]
            ):
                raise ValueError(
                    f"El importe de {clave} no coincide "
                    "con el esperado."
                )

            if monto["moneda"] != especificacion["moneda"]:
                raise ValueError(
                    f"Moneda inesperada en {clave}."
                )

            if monto.get("estado") != "REGLA_COINCIDENTE":
                raise ValueError(
                    f"El concepto {clave} no está clasificado."
                )

            fuente = monto["fuente"]
            nombre_documento = fuente["documento"]

            if nombre_documento not in documentos:
                raise ValueError(
                    f"Documento no encontrado: {nombre_documento}"
                )

            url_pdf = documentos[nombre_documento]["url_fuente"]

            if url_pdf not in urls_scraping:
                raise ValueError(
                    f"El PDF no pertenece a la convocatoria: "
                    f"{url_pdf}"
                )

            apoyos[clave] = {
                "clave": clave,
                "concepto": especificacion["concepto"],
                "componente": especificacion["componente"],
                "monto_maximo": str(valor),
                "moneda": monto["moneda"],
                "unidad": especificacion["unidad"],
                "incluido_en_clave": (
                    especificacion["incluido_en_clave"]
                ),
                "condiciones": especificacion["condiciones"],
                "estado_documental": "PENDIENTE_REVISION",
                "fuentes_documentales": [
                    {
                        "documento": nombre_documento,
                        "url_pdf": url_pdf,
                        "pagina": fuente["pagina"],
                        "fragmento": fuente["fragmento"],
                    }
                ],
            }

    if set(apoyos) != set(CONCEPTOS):
        faltantes = set(CONCEPTOS) - set(apoyos)

        raise ValueError(
            f"Faltan conceptos: {sorted(faltantes)}"
        )

    total = Decimal(
        apoyos["APOYO_MAXIMO_PROYECTO"]["monto_maximo"]
    )

    anual = Decimal(
        apoyos["MAXIMO_ETAPA_ANUAL"]["monto_maximo"]
    )

    if anual * 4 != total:
        raise ValueError(
            "Las cuatro etapas anuales no coinciden "
            "con el presupuesto total."
        )

    return list(apoyos.values())


def main():
    financiero = cargar_json(ARCHIVO_FINANCIERO)
    resultados = cargar_json(ARCHIVO_SCRAPING)

    convocatoria = obtener_convocatoria(
        financiero,
        resultados,
    )

    apoyos = preparar_apoyos(
        financiero,
        convocatoria,
    )

    preparado = {
        "convocatoria": convocatoria,
        "fuente_id": 2,
        "apoyos": apoyos,
    }

    ARCHIVO_SALIDA.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    ARCHIVO_SALIDA.write_text(
        json.dumps(
            preparado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("=== APOYOS DE ECOS NORD 2026 ===")
    print("Convocatoria:", convocatoria["titulo"])
    print("PDF asociados:", len(convocatoria["documentos"]))
    print("Apoyos preparados:", len(apoyos))

    for apoyo in apoyos:
        print()
        print("Concepto:", apoyo["clave"])
        print(
            "Importe:",
            apoyo["monto_maximo"],
            apoyo["moneda"],
        )
        print("Componente:", apoyo["componente"])
        print("Unidad:", apoyo["unidad"])
        print(
            "Incluido en:",
            apoyo["incluido_en_clave"] or "NO",
        )
        print(
            "Página:",
            apoyo["fuentes_documentales"][0]["pagina"],
        )

    print("\nJSON generado:", ARCHIVO_SALIDA)
    print("VISTA PREVIA: PostgreSQL no fue modificado.")


if __name__ == "__main__":
    main()
