import json
import re
from pathlib import Path

from scrapy import Selector

from app.extraer_fechas import (
    PATRON_FECHA,
    buscar_fecha,
    buscar_hora,
    convertir_fecha,
    obtener_calendario,
)


ARCHIVO_PDF = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)

ARCHIVO_HTML = Path("detalle_proyecto.html")

ARCHIVO_SALIDA = Path(
    "data/extraidos/fechas_convocatoria_2025.json"
)


def extraer_fechas_pdf():
    texto = ARCHIVO_PDF.read_text(
        encoding="utf-8"
    )

    calendario = obtener_calendario(texto)

    publicacion, _ = buscar_fecha(
        calendario,
        r"Publicación\s+de\s+la\s+Convocatoria",
    )

    apertura, _ = buscar_fecha(
        calendario,
        r"Apertura\s+del\s+sistema\s+para\s+captura\s+de",
    )

    cierre, hora = buscar_fecha(
        calendario,
        r"Cierre\s+del\s+sistema\s+para\s+captura\s+de",
    )

    return publicacion, apertura, cierre, hora


def extraer_ampliacion_html(html):
    """
    Extrae una fecha ampliada de cierre directamente
    desde una cadena HTML.

    Devuelve:
        (fecha, hora)

    Ejemplo:
        ("2025-05-12", "23:59")

    Si la página no contiene un aviso de ampliación,
    devuelve:
        (None, None)
    """

    if not html:
        return None, None

    pagina = Selector(text=html)

    textos = [
        " ".join(texto.split())
        for texto in pagina.xpath(
            "//body//text()["
            "not(ancestor::script) and "
            "not(ancestor::style)]"
        ).getall()
    ]

    textos = [
        texto
        for texto in textos
        if texto
    ]

    for indice, texto in enumerate(textos):
        if (
            "fecha ampliada de cierre del sistema"
            not in texto.lower()
        ):
            continue

        # Examinar el aviso y algunos textos
        # inmediatamente posteriores.
        fragmento = " ".join(
            textos[indice:indice + 7]
        )

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

        hora = re.search(
            r"a\s+las\s+(\d{1,2}:\d{2})\s+horas",
            posterior,
            re.IGNORECASE,
        )

        hora_detectada = (
            hora.group(1)
            if hora
            else None
        )

        return fecha, hora_detectada

    return None, None

def extraer_fecha_cierre_html(html):
    """
    Busca una fecha de cierre en el HTML.

    Prioridad:
    1. Ampliación de fecha de cierre.
    2. Fecha de cierre normal.

    Devuelve un diccionario con:
        fecha
        hora
        tipo
        etiqueta
    """

    if not html:
        return {
            "fecha": None,
            "hora": None,
            "tipo": "SIN_FECHA",
            "etiqueta": None,
        }

    pagina = Selector(text=html)

    textos = [
        " ".join(texto.split())
        for texto in pagina.xpath(
            "//body//text()["
            "not(ancestor::script) and "
            "not(ancestor::style)]"
        ).getall()
    ]

    textos = [
        texto
        for texto in textos
        if texto
    ]

    criterios = [
        (
            "AMPLIACION",
            [
                "fecha ampliada de cierre del sistema",
                "ampliación de fecha de cierre",
                "ampliacion de fecha de cierre",
                "fecha ampliada de cierre",
            ],
        ),
        (
            "CIERRE_NORMAL",
            [
                "conclusión de recepción de solicitudes",
                "conclusion de recepción de solicitudes",
                "conclusion de recepcion de solicitudes",
                "cierre de recepción de solicitudes",
                "cierre de recepcion de solicitudes",
                "cierre del sistema",
                "fecha de cierre",
            ],
        ),
    ]

    for tipo, etiquetas in criterios:

        for indice, texto in enumerate(textos):
            texto_minusculas = texto.lower()

            etiqueta_encontrada = None

            for etiqueta in etiquetas:
                if etiqueta in texto_minusculas:
                    etiqueta_encontrada = etiqueta
                    break

            if etiqueta_encontrada is None:
                continue

            # La fecha puede estar en el mismo nodo HTML
            # o en alguno de los inmediatamente posteriores.
            fragmento = " ".join(
                textos[indice:indice + 8]
            )

            coincidencia = PATRON_FECHA.search(
                fragmento
            )

            if not coincidencia:
                continue

            fecha = convertir_fecha(
                coincidencia
            )

            texto_posterior = fragmento[
                coincidencia.end():
            ]

            hora = buscar_hora(
                texto_posterior
            )

            return {
                "fecha": fecha,
                "hora": hora,
                "tipo": tipo,
                "etiqueta": etiqueta_encontrada,
            }

    return {
        "fecha": None,
        "hora": None,
        "tipo": "SIN_FECHA",
        "etiqueta": None,
    }

def extraer_ampliacion_web(
    ruta_html=ARCHIVO_HTML,
):
    """
    Compatibilidad con los scripts anteriores.

    Lee un archivo HTML y utiliza el mismo extractor
    que podrá utilizar Scrapy directamente.
    """

    html = Path(ruta_html).read_text(
        encoding="utf-8",
        errors="replace",
    )

    fecha, hora = extraer_ampliacion_html(
        html
    )

    if fecha is None:
        raise ValueError(
            "No se encontró la fecha ampliada "
            "en el HTML."
        )

    return fecha, hora


def main():
    publicacion, apertura, cierre_pdf, hora_pdf = (
        extraer_fechas_pdf()
    )

    cierre_web, hora_web = (
        extraer_ampliacion_web()
    )

    resultado = {
        "fecha_publicacion": publicacion,
        "fecha_apertura": apertura,
        "fecha_cierre_pdf_original": cierre_pdf,
        "hora_cierre_pdf_original": hora_pdf,
        "fecha_cierre_ampliada_web": cierre_web,
        "hora_cierre_ampliada_web": hora_web,
        "fecha_cierre_preferente": cierre_web,
        "zona_horaria": "America/Mexico_City",
        "fuente_fecha_original": str(
            ARCHIVO_PDF
        ),
        "fuente_fecha_ampliada": str(
            ARCHIVO_HTML
        ),
        "requiere_verificar_cambios_posteriores": True,
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

    print(contenido)

    print(
        "\nArchivo generado:",
        ARCHIVO_SALIDA,
    )


if __name__ == "__main__":
    main()
