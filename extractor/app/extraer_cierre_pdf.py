import re
from pathlib import Path

from app.extraer_fechas import (
    PATRON_FECHA,
    convertir_fecha,
    buscar_hora,
)


def buscar_fecha_por_etiquetas(
    texto,
    etiquetas,
    longitud=300,
):
    """
    Busca una fecha inmediatamente después de cualquiera
    de las etiquetas proporcionadas.
    """

    for etiqueta in etiquetas:
        posicion = re.search(
            etiqueta,
            texto,
            re.IGNORECASE,
        )

        if not posicion:
            continue

        fragmento = texto[
            posicion.end():
            posicion.end() + longitud
        ]

        coincidencia = PATRON_FECHA.search(
            fragmento
        )

        if not coincidencia:
            continue

        fecha = convertir_fecha(
            coincidencia
        )

        posterior = fragmento[
            coincidencia.end():
        ]

        hora = buscar_hora(
            posterior
        )

        return {
            "fecha": fecha,
            "hora": hora,
            "etiqueta": posicion.group(0),
        }

    return None


def extraer_cierre_pdf(texto):
    """
    Clasifica las posibles fechas de cierre encontradas
    dentro del texto de un PDF.

    Una fecha de cierre de postulación/recepción tiene
    prioridad sobre un simple cierre administrativo
    del proceso.
    """

    # Alta confianza: fecha límite para participar.
    cierre_postulacion = buscar_fecha_por_etiquetas(
        texto,
        [
            r"fecha\s+l[ií]mite\s+de\s+postulaci[oó]n",
            r"cierre\s+de\s+postulaci[oó]n",
            r"cierre\s+de\s+recepci[oó]n\s+de\s+solicitudes",
            r"conclusi[oó]n\s+de\s+recepci[oó]n\s+de\s+solicitudes",
            r"fecha\s+l[ií]mite\s+de\s+recepci[oó]n",
            r"cierre\s+del\s+sistema\s+para\s+captura",
        ],
    )

    if cierre_postulacion:
        return {
            "fecha_cierre_postulacion": (
                cierre_postulacion["fecha"]
            ),
            "hora_cierre_postulacion": (
                cierre_postulacion["hora"]
            ),
            "fecha_cierre_proceso": None,
            "tipo": "CIERRE_POSTULACION",
            "etiqueta": (
                cierre_postulacion["etiqueta"]
            ),
            "usar_como_fecha_cierre": True,
        }

    # Menor prioridad: final administrativo del proceso.
    cierre_proceso = buscar_fecha_por_etiquetas(
        texto,
        [
            r"fecha\s+de\s+cierre\s+del\s+proceso",
            r"cierre\s+del\s+proceso",
        ],
    )

    if cierre_proceso:
        return {
            "fecha_cierre_postulacion": None,
            "hora_cierre_postulacion": None,
            "fecha_cierre_proceso": (
                cierre_proceso["fecha"]
            ),
            "tipo": "CIERRE_PROCESO",
            "etiqueta": (
                cierre_proceso["etiqueta"]
            ),
            "usar_como_fecha_cierre": False,
        }

    return {
        "fecha_cierre_postulacion": None,
        "hora_cierre_postulacion": None,
        "fecha_cierre_proceso": None,
        "tipo": "SIN_FECHA_CIERRE",
        "etiqueta": None,
        "usar_como_fecha_cierre": False,
    }


if __name__ == "__main__":
    archivo = Path(
        "data/textos/secihti_oea_2026.txt"
    )

    texto = archivo.read_text(
        encoding="utf-8",
        errors="replace",
    )

    print(
        extraer_cierre_pdf(texto)
    )
