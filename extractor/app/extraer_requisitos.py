import json
import re
from pathlib import Path


ARCHIVO_TEXTO = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)

ARCHIVO_SALIDA = Path(
    "data/extraidos/requisitos_convocatoria_2025.json"
)


# Clasificación específica de esta convocatoria.
CLASIFICACIONES = {
    "a": "requisito",
    "b": "requisito",
    "c": "requisito",
    "d": "criterio_prioridad",
    "e": "causa_no_elegibilidad",
    "f": "condicion_financiamiento",
    "g": "condicion_financiamiento",
    "h": "restriccion",
}


def obtener_seccion(texto):
    # Encontrar el inicio de la sección V.
    inicio = re.search(
        r"^[ \t]*V\.[ \t]*Disposiciones "
        r"generales y restricciones",
        texto,
        re.IGNORECASE | re.MULTILINE,
    )

    if not inicio:
        raise ValueError(
            "No se encontró la sección V."
        )

    contenido = texto[inicio.end():]

    # La sección termina cuando comienza la VI.
    fin = re.search(
        r"^[ \t]*VI\.[ \t]*Solicitud y resultados",
        contenido,
        re.IGNORECASE | re.MULTILINE,
    )

    if not fin:
        raise ValueError(
            "No se encontró el final de la sección V."
        )

    return contenido[:fin.start()]


def limpiar_texto(contenido):
    lineas = []

    for linea in contenido.splitlines():
        linea = linea.strip()

        if not linea:
            continue

        # Ignorar los separadores de página
        # que agregamos al extraer el PDF.
        if re.fullmatch(r"={5,}", linea):
            continue

        if re.fullmatch(
            r"PÁGINA\s+\d+",
            linea,
            re.IGNORECASE,
        ):
            continue

        if re.fullmatch(
            r"\d+\s+de\s+\d+",
            linea,
        ):
            continue

        lineas.append(linea)

    return " ".join(lineas)


def extraer_disposiciones(texto):
    seccion = obtener_seccion(texto)

    # Localizar los incisos a), b), c), etc.,
    # únicamente al comienzo de una línea.
    patron = re.compile(
        r"^[ \t]*([a-h])\)[ \t]*",
        re.MULTILINE,
    )

    coincidencias = list(
        patron.finditer(seccion)
    )

    disposiciones = []

    for indice, coincidencia in enumerate(
        coincidencias
    ):
        inciso = coincidencia.group(1)

        inicio_texto = coincidencia.end()

        if indice + 1 < len(coincidencias):
            fin_texto = coincidencias[
                indice + 1
            ].start()
        else:
            fin_texto = len(seccion)

        contenido = seccion[
            inicio_texto:fin_texto
        ]

        contenido = limpiar_texto(contenido)

        if not contenido:
            raise ValueError(
                f"El inciso {inciso} está vacío."
            )

        disposiciones.append({
            "inciso": inciso,
            "clasificacion": CLASIFICACIONES[inciso],
            "texto": contenido,
        })

    # Verificar que los ocho incisos de
    # este documento se hayan recuperado.
    encontrados = {
        item["inciso"]
        for item in disposiciones
    }

    esperados = set("abcdefgh")

    if encontrados != esperados:
        faltantes = esperados - encontrados

        raise ValueError(
            "No se pudieron identificar todos "
            f"los incisos. Faltantes: {faltantes}"
        )

    return disposiciones


def main():
    texto = ARCHIVO_TEXTO.read_text(
        encoding="utf-8",
    )

    disposiciones = extraer_disposiciones(
        texto
    )

    resultado = {
        "disposiciones": disposiciones,
        "total": len(disposiciones),
        "fuente": str(ARCHIVO_TEXTO),
        "metodo_extraccion": "reglas",
        "requiere_revisar_terminos_referencia": True,
    }

    ARCHIVO_SALIDA.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    ARCHIVO_SALIDA.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("=== DISPOSICIONES EXTRAÍDAS ===\n")

    for item in disposiciones:
        print(
            f"{item['inciso']}) "
            f"[{item['clasificacion']}]"
        )

        print(item["texto"])
        print()

    print(
        "Total de disposiciones:",
        len(disposiciones),
    )

    print(
        "\nArchivo generado:",
        ARCHIVO_SALIDA,
    )


if __name__ == "__main__":
    main()
