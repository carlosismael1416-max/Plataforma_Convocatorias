
import json
import re
from decimal import Decimal
from pathlib import Path

import pymupdf as fitz

from app.extraer_apoyo_frances_ecos import extraer_apoyo_frances


BASE_DIR = Path(__file__).resolve().parent.parent

PDF_CONVOCATORIA = (
    BASE_DIR
    / "data/pdfs/ecos_nord_2026_convocatoria.pdf"
)

PDF_TDR = (
    BASE_DIR
    / "data/pdfs/ecos_nord_2026_tdr.pdf"
)

SALIDA = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_estructurado.json"
)


def leer_pdf(ruta):
    if not ruta.exists():
        raise FileNotFoundError(
            f"No se encontró el PDF: {ruta}"
        )

    paginas = {}

    with fitz.open(ruta) as documento:
        for numero, pagina in enumerate(
            documento,
            start=1,
        ):
            paginas[numero] = pagina.get_text(
                "text",
                sort=True,
            )

    return paginas


def compactar(texto):
    return re.sub(r"\s+", " ", texto).strip()


def buscar_dato(
    texto,
    patron,
    documento,
    pagina,
    convertir=str,
):
    texto = compactar(texto)

    coincidencia = re.search(
        patron,
        texto,
        re.IGNORECASE,
    )

    if not coincidencia:
        return None

    inicio = max(
        0,
        coincidencia.start() - 75,
    )

    fin = min(
        len(texto),
        coincidencia.end() + 140,
    )

    return {
        "valor": convertir(
            coincidencia.group(1)
        ),
        "fuente": {
            "documento": documento,
            "pagina": pagina,
            "fragmento": texto[inicio:fin],
        },
    }


def convertir_monto(texto):
    numero = Decimal(
        texto.replace(",", "")
    )

    if numero == numero.to_integral_value():
        return int(numero)

    return float(numero)


def extraer_requisitos(texto, documento, pagina):
    # Limitar las búsquedas a la sección V.
    texto = compactar(texto)

    seccion = re.search(
        r"V\.\s*Requisitos de participaci[oó]n"
        r"(.*?)"
        r"VI\.\s*Proceso de recepci[oó]n",
        texto,
        re.IGNORECASE | re.DOTALL,
    )

    if not seccion:
        return []

    contenido = seccion.group(1)

    reglas = [
        (
            "Perfil Único de Rizoma actualizado",
            r"(Contar con Perfil [ÚU]nico"
            r" en Rizoma actualizado)",
        ),
        (
            "Máximo una propuesta por persona",
            r"(Una misma persona no podr[aá]"
            r" presentar m[aá]s de una propuesta"
            r" en esta Convocatoria)",
        ),
        (
            "Carta de postulación institucional",
            r"(La Instituci[oó]n beneficiaria"
            r" deber[aá] manifestar.*?"
            r"firmada por la o el Representante Legal)",
        ),
        (
            "Cumplir los componentes de los TDR",
            r"(El proyecto deber[aá] considerar"
            r" cada uno de los componentes.*?"
            r"T[eé]rminos de Referencia)",
        ),
    ]

    requisitos = []

    for nombre, patron in reglas:
        coincidencia = re.search(
            patron,
            contenido,
            re.IGNORECASE | re.DOTALL,
        )

        if not coincidencia:
            continue

        requisitos.append({
            "requisito": nombre,
            "fuente": {
                "documento": documento,
                "pagina": pagina,
                "fragmento": coincidencia.group(1),
            },
        })

    return requisitos


def extraer_rubros(texto, documento, pagina):
    # Primera versión: rubros identificados
    # en la página 8 de los TDR.
    rubros_buscados = [
        "Pasajes nacionales e internacionales terrestres",
        "Pasajes nacionales e internacionales aéreos",
        "Viáticos",
        "Servicios de auditoría",
    ]

    rubros = []

    for nombre in rubros_buscados:
        patron = (
            r"(?m)^\s*"
            + re.escape(nombre)
            + r"\s*$"
        )

        coincidencia = re.search(
            patron,
            texto,
            re.IGNORECASE,
        )

        if not coincidencia:
            continue

        # Conservar el encabezado y el texto
        # que aparece inmediatamente después.
        fragmento = texto[
            coincidencia.start():
            coincidencia.end() + 450
        ].strip()

        rubros.append({
            "nombre": nombre,
            "restricciones_verificadas": False,
            "fuente": {
                "documento": documento,
                "pagina": pagina,
                "fragmento": fragmento,
            },
        })

    return rubros


def extraer_restricciones_auditoria(
    pagina_8,
    pagina_9,
    documento,
):
    texto_8 = compactar(pagina_8)
    texto_9 = compactar(pagina_9)

    # El párrafo sobre auditoría continúa
    # desde la página 8 hasta la página 9.
    inicio = texto_8.casefold().find(
        "servicios de auditoría"
    )

    if inicio == -1:
        raise ValueError(
            "No se encontró el rubro de auditoría."
        )

    fragmento_8 = texto_8[inicio:inicio + 650]

    fragmento_9 = re.split(
        r"[-–]\s*Por la parte francesa",
        texto_9,
        maxsplit=1,
        flags=re.IGNORECASE,
    )[0]

    condiciones = {
        "ultima_etapa": (
            "última etapa" in fragmento_8.casefold()
        ),
        "porcentaje": bool(
            re.search(
                r"1[.,]5\s*%",
                fragmento_9,
            )
        ),
        "tope_uma": bool(
            re.search(
                r"500\s+UMAS?\b",
                fragmento_9,
                re.IGNORECASE,
            )
        ),
        "cotizaciones": bool(
            re.search(
                r"3\s+cotizaciones",
                fragmento_9,
                re.IGNORECASE,
            )
        ),
        "reintegro": (
            "reintegrarlo"
            in fragmento_9.casefold()
        ),
    }

    faltantes = [
        nombre
        for nombre, encontrada in condiciones.items()
        if not encontrada
    ]

    if faltantes:
        raise ValueError(
            "No se pudieron comprobar estas "
            "restricciones de auditoría: "
            + ", ".join(faltantes)
        )

    return {
        "rubro": "Servicios de auditoría",
        "porcentaje_maximo": 1.5,
        "base_porcentaje": (
            "Monto total ministrado al proyecto"
        ),
        "tope_adicional": {
            "cantidad": 500,
            "unidad": "UMA",
            "referencia": "Valor diario",
        },
        "etapa_permitida": "ULTIMA_ETAPA",
        "cotizaciones_minimas": 3,
        "requiere_despachos_aprobados": True,
        "requiere_reintegrar_saldo": True,
        "fuentes": [
            {
                "documento": documento,
                "pagina": 8,
                "fragmento": fragmento_8,
            },
            {
                "documento": documento,
                "pagina": 9,
                "fragmento": fragmento_9,
            },
        ],
    }


def main():
    convocatoria = leer_pdf(
        PDF_CONVOCATORIA
    )

    tdr = leer_pdf(
        PDF_TDR
    )

    pagina_montos = convocatoria.get(2, "")
    pagina_requisitos = tdr.get(6, "")
    pagina_rubros = tdr.get(8, "")
    pagina_restricciones = tdr.get(9, "")

    resultado = {
        "convocatoria": "ECOS Nord 2026",
        "moneda": "MXN",
        "financiamiento": {
            "monto_maximo_total": buscar_dato(
                pagina_montos,
                r"apoyo total de hasta"
                r"\s*\$\s*([\d,.]+)",
                PDF_CONVOCATORIA.name,
                2,
                convertir_monto,
            ),
            "monto_maximo_por_etapa": buscar_dato(
                pagina_montos,
                r"etapa por a[nñ]o de hasta"
                r"\s*\$\s*([\d,.]+)",
                PDF_CONVOCATORIA.name,
                2,
                convertir_monto,
            ),
            "numero_etapas": buscar_dato(
                pagina_montos,
                r"distribuidos en\s+(\d+)\s+etapas",
                PDF_CONVOCATORIA.name,
                2,
                int,
            ),
            "meses_por_etapa": buscar_dato(
                pagina_requisitos,
                r"etapas anuales de\s+(\d+)\s+meses",
                PDF_TDR.name,
                6,
                int,
            ),
        },
        "requisitos": extraer_requisitos(
            pagina_requisitos,
            PDF_TDR.name,
            6,
        ),
        "rubros_detectados": extraer_rubros(
            pagina_rubros,
            PDF_TDR.name,
            8,
        ),
        "observaciones": [
            (
                "Los montos están sujetos a "
                "suficiencia presupuestaria."
            ),
            (
                "Los rubros detectados son parciales. "
                "Falta revisar las restricciones "
                "y los rubros de las páginas siguientes."
            ),
            (
                "Los requisitos extraídos no "
                "constituyen una evaluación de "
                "elegibilidad institucional."
            ),
        ],
    }

    resultado["restricciones_auditoria"] = (
        extraer_restricciones_auditoria(
            pagina_rubros,
            pagina_restricciones,
            PDF_TDR.name,
        )
    )

    for rubro in resultado["rubros_detectados"]:
        if rubro["nombre"] == "Servicios de auditoría":
            rubro["restricciones_verificadas"] = True

    # Extraer y conservar por separado
    # el financiamiento de la parte francesa.
    datos_franceses = extraer_apoyo_frances()

    resultado["apoyo_parte_francesa"] = (
        datos_franceses["apoyo_parte_francesa"]
    )

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

    print("\n=== ECOS NORD 2026 ===")

    for campo, dato in (
        resultado["financiamiento"].items()
    ):
        valor = (
            dato["valor"]
            if dato is not None
            else "NO DETECTADO"
        )

        print(f"{campo}: {valor}")

    print(
        "\nRequisitos detectados:",
        len(resultado["requisitos"]),
    )

    for requisito in resultado["requisitos"]:
        print("-", requisito["requisito"])

    print(
        "\nRubros detectados:",
        len(resultado["rubros_detectados"]),
    )

    for rubro in resultado["rubros_detectados"]:
        print("-", rubro["nombre"])

    print("\nJSON generado:", SALIDA)


if __name__ == "__main__":
    main()
