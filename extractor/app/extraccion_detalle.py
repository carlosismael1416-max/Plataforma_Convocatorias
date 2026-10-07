import json
from pathlib import Path
from urllib.parse import urljoin, urlparse

from scrapy import Selector

from app.conciliar_fechas import extraer_fecha_cierre_html


def extraer_detalle(html, url_pagina):
    pagina = Selector(text=html)

    # Obtener el título de la convocatoria.
    titulo = pagina.css("h1").xpath(
        "normalize-space(.)"
    ).get()

    # Buscar los documentos PDF.
    documentos = []
    urls_encontradas = set()

    for enlace in pagina.css("a[href]"):
        href = enlace.attrib.get(
            "href",
            "",
        ).strip()

        if not href:
            continue

        url_documento = urljoin(
            url_pagina,
            href,
        )

        # Comprobar que el enlace apunta a un PDF.
        ruta = urlparse(
            url_documento
        ).path.lower()

        if not ruta.endswith(".pdf"):
            continue

        # Evitar documentos duplicados.
        if url_documento in urls_encontradas:
            continue

        urls_encontradas.add(
            url_documento
        )

        nombre = enlace.xpath(
            "normalize-space(.)"
        ).get()

        documentos.append({
            "nombre": nombre or "Documento PDF",
            "url": url_documento,
        })

    # Buscar una ampliación de fecha de cierre
    # directamente en el HTML recibido por Scrapy.
    fecha_cierre = extraer_fecha_cierre_html(
    html
    )

    fecha_cierre_web = fecha_cierre["fecha"]
    hora_cierre_web = fecha_cierre["hora"]
    tipo_fecha_cierre = fecha_cierre["tipo"]
    etiqueta_fecha_cierre = fecha_cierre["etiqueta"]

    return {
    "titulo": titulo,
    "url_original": url_pagina,
    "descripcion": None,
    "fecha_cierre_web": fecha_cierre_web,
    "hora_cierre_web": hora_cierre_web,
    "tipo_fecha_cierre": tipo_fecha_cierre,
    "etiqueta_fecha_cierre": etiqueta_fecha_cierre,
    "documentos": documentos,
}

if __name__ == "__main__":
    # Probar con el HTML que ya descargamos.
    html = Path(
        "detalle_proyecto_actual.html"
    ).read_text(
        encoding="utf-8",
        errors="replace",
    )

    url = (
        "https://www.secihti.mx/convocatoria/"
        "ciencias-y-humanidades/"
        "proyectos-de-investigacion/"
        "convocatoria-proyectos-de-investigacion-"
        "cientifica-y-humanistica-en-ejes-estrategicos-2025/"
    )

    resultado = extraer_detalle(
        html,
        url,
    )

    # Guardar todos los datos en un archivo JSON.
    Path("detalle_resultado.json").write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print(
        "Título:",
        resultado["titulo"],
    )

    print(
        "Fecha cierre web:",
        resultado["fecha_cierre_web"],
    )

    print(
        "Hora cierre web:",
        resultado["hora_cierre_web"],
    )

    print(
        "Documentos PDF:",
        len(resultado["documentos"]),
    )

    print("\nPRIMEROS TRES DOCUMENTOS:")

    for documento in resultado[
        "documentos"
    ][:3]:
        print(
            "-",
            documento["nombre"],
        )
        print(
            " ",
            documento["url"],
        )

    print(
        "\nArchivo generado: "
        "detalle_resultado.json"
    )