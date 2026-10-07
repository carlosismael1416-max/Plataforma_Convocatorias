import json
import re
from datetime import date
from pathlib import Path


ARCHIVO = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)

MESES = {
    "enero": 1,
    "febrero": 2,
    "marzo": 3,
    "abril": 4,
    "mayo": 5,
    "junio": 6,
    "julio": 7,
    "agosto": 8,
    "septiembre": 9,
    "setiembre": 9,
    "octubre": 10,
    "noviembre": 11,
    "diciembre": 12,
}


# Reconoce, por ejemplo:
#
# 12 de mayo de 2025
# 12 mayo 2025
# 23 octubre 2026
#
PATRON_FECHA = re.compile(
    r"\b(\d{1,2})\s+"
    r"(?:de\s+)?"
    r"(enero|febrero|marzo|abril|mayo|junio|"
    r"julio|agosto|septiembre|setiembre|octubre|"
    r"noviembre|diciembre)"
    r"\s+(?:de\s+)?(\d{4})\b",
    re.IGNORECASE,
)


# Reconoce, por ejemplo:
#
# 23:59
# 23:59 horas
# 11:59 PM
# 11:59 p.m.
#
PATRON_HORA = re.compile(
    r"\b(\d{1,2}):(\d{2})"
    r"\s*"
    r"(a\.?\s*m\.?|p\.?\s*m\.?|am|pm)?"
    r"(?:\s+horas)?\b",
    re.IGNORECASE,
)


def convertir_fecha(coincidencia):
    dia = int(
        coincidencia.group(1)
    )

    mes_texto = (
        coincidencia.group(2)
        .lower()
    )

    mes = MESES[mes_texto]

    anio = int(
        coincidencia.group(3)
    )

    return date(
        anio,
        mes,
        dia,
    ).isoformat()


def convertir_hora(
    hora,
    minuto,
    periodo=None,
):
    hora = int(hora)
    minuto = int(minuto)

    if minuto < 0 or minuto > 59:
        return None

    if periodo:
        periodo = (
            periodo.lower()
            .replace(".", "")
            .replace(" ", "")
        )

        if hora < 1 or hora > 12:
            return None

        if periodo == "pm" and hora != 12:
            hora += 12

        if periodo == "am" and hora == 12:
            hora = 0

    else:
        if hora < 0 or hora > 23:
            return None

    return f"{hora:02d}:{minuto:02d}"


def buscar_hora(texto):
    coincidencia = PATRON_HORA.search(
        texto
    )

    if not coincidencia:
        return None

    return convertir_hora(
        hora=coincidencia.group(1),
        minuto=coincidencia.group(2),
        periodo=coincidencia.group(3),
    )


def obtener_calendario(texto):
    inicio = re.search(
        r"\bIV\.\s*Calendario\b",
        texto,
        re.IGNORECASE,
    )

    if not inicio:
        raise ValueError(
            "No se encontró la sección Calendario."
        )

    contenido = texto[
        inicio.end():
    ]

    fin = re.search(
        r"\bV\.\s*Disposiciones",
        contenido,
        re.IGNORECASE,
    )

    if fin:
        contenido = contenido[
            :fin.start()
        ]

    return contenido


def buscar_fecha(
    calendario,
    etiqueta,
):
    posicion = re.search(
        etiqueta,
        calendario,
        re.IGNORECASE,
    )

    if not posicion:
        return None, None

    # Revisar el texto que aparece inmediatamente
    # después de la etiqueta encontrada.
    fragmento = calendario[
        posicion.end():
        posicion.end() + 220
    ]

    coincidencia = PATRON_FECHA.search(
        fragmento
    )

    if not coincidencia:
        return None, None

    fecha = convertir_fecha(
        coincidencia
    )

    # Buscar una hora después de la fecha.
    texto_posterior = fragmento[
        coincidencia.end():
    ]

    hora = buscar_hora(
        texto_posterior
    )

    return fecha, hora


def main():
    texto = ARCHIVO.read_text(
        encoding="utf-8"
    )

    calendario = obtener_calendario(
        texto
    )

    publicacion, _ = buscar_fecha(
        calendario,
        r"Publicación\s+de\s+la\s+Convocatoria",
    )

    apertura, _ = buscar_fecha(
        calendario,
        r"Apertura\s+del\s+sistema\s+para\s+captura\s+de",
    )

    cierre, hora_cierre = buscar_fecha(
        calendario,
        r"Cierre\s+del\s+sistema\s+para\s+captura\s+de",
    )

    resultado = {
        "fecha_publicacion": publicacion,
        "fecha_apertura": apertura,
        "fecha_cierre_pdf_original": cierre,
        "hora_cierre": hora_cierre,
        "zona_horaria": "America/Mexico_City",
        "fecha_cierre_definitiva_verificada": False,
    }

    print(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        )
    )


if __name__ == "__main__":
    main()