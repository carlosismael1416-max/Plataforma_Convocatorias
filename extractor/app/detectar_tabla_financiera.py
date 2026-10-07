
import re
import unicodedata

from app.extraer_tabla_financiamiento import (
    extraer_tabla,
)


def normalizar(texto):
    texto = unicodedata.normalize(
        "NFKD",
        (texto or "").lower(),
    )

    texto = "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )

    return re.sub(r"\s+", " ", texto).strip()


def contiene_tabla_modalidades(documento):
    """
    Comprueba si los fragmentos contienen
    el formato de dos modalidades y
    tres etapas que ya sabemos procesar.
    """

    fragmentos = [
        (monto.get("fuente") or {}).get(
            "fragmento", ""
        )
        for monto in documento.get(
            "montos_clasificados", []
        )
    ]

    texto = normalizar(
        " ".join(fragmentos)
    )

    elementos = [
        "1. individual",
        "2. grupo de investigacion",
        "etapa 1",
        "etapa 2",
        "etapa 3",
        "(pesos mexicanos)",
    ]

    return all(
        elemento in texto
        for elemento in elementos
    )


def detectar_tabla_financiera(documentos):
    tablas = []

    for documento in documentos:
        if not contiene_tabla_modalidades(
            documento
        ):
            continue

        # Reutilizar las reglas ya probadas.
        # Si una tabla parece compatible pero
        # sus importes son inconsistentes,
        # conservar el error para revisarlo.
        tabla = extraer_tabla(
            documento
        )

        tablas.append(tabla)

    if not tablas:
        return None

    primera = tablas[0]

    # No fusionar tablas contradictorias.
    for tabla in tablas[1:]:
        if (
            tabla["etapas"] != primera["etapas"]
            or tabla["modalidades"]
            != primera["modalidades"]
        ):
            return {
                "tipo": "MODALIDADES_TRES_ETAPAS",
                "estado": "DISCREPANCIA_DOCUMENTAL",
                "tablas_por_documento": tablas,
            }

    estado = (
        "COINCIDENCIA_DOCUMENTAL"
        if len(tablas) >= 2
        else "UNA_FUENTE_PENDIENTE_CORROBORACION"
    )

    return {
        "tipo": "MODALIDADES_TRES_ETAPAS",
        "estado": estado,
        "moneda": "MXN",
        "etapas": primera["etapas"],
        "modalidades": primera["modalidades"],
        "documentos_coincidentes": len(tablas),
        "fuentes": [
            tabla["fuente"]
            for tabla in tablas
        ],
    }
