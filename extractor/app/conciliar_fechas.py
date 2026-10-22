from scrapy import Selector

from app.extraer_fechas import (
    PATRON_FECHA,
    buscar_hora,
    convertir_fecha,
)


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

