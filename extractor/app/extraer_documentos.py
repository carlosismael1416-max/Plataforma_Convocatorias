import json
import re
from pathlib import Path


ARCHIVO_TEXTO = Path(
    "data/textos/terminos_referencia_2025.txt"
)

ARCHIVO_SALIDA = Path(
    "data/extraidos/documentos_convocatoria_2025.json"
)


def limpiar_texto(texto):
    lineas = []

    for linea in texto.splitlines():
        linea = linea.strip()

        if not linea:
            continue

        # Eliminar separadores y números de página.
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


def extraer_documentos(texto):
    # Localizar la sección 8: Documentos Anexos.
    inicio = re.search(
        r"^\s*8\.\s*Documentos\s+Anexos:",
        texto,
        re.IGNORECASE | re.MULTILINE,
    )

    if not inicio:
        raise ValueError(
            "No se encontró la sección Documentos Anexos."
        )

    contenido = texto[inicio.end():]

    # La sección termina antes del apartado IV.
    fin = re.search(
        r"^[ \t]*IV\.[ \t]*Proceso de recepción",
        contenido,
        re.IGNORECASE | re.MULTILINE,
    )

    if not fin:
        raise ValueError(
            "No se encontró el final de Documentos Anexos."
        )

    seccion = contenido[:fin.start()]

    # Identificar los incisos a. y b.
    patron = re.compile(
        r"^[ \t]*([ab])\.[ \t]+",
        re.MULTILINE,
    )

    coincidencias = list(patron.finditer(seccion))

    if len(coincidencias) != 2:
        raise ValueError(
            "Se esperaban dos documentos anexos; "
            f"se encontraron {len(coincidencias)}."
        )

    nombres = {
        "a": "Protocolo de investigación",
        "b": (
            "Documento probatorio del último grado "
            "de estudios de la persona Responsable Técnica"
        ),
    }

    documentos = []

    for indice, coincidencia in enumerate(coincidencias):
        inciso = coincidencia.group(1)

        inicio_texto = coincidencia.end()

        if indice + 1 < len(coincidencias):
            fin_texto = coincidencias[
                indice + 1
            ].start()
        else:
            fin_texto = len(seccion)

        descripcion = limpiar_texto(
            seccion[inicio_texto:fin_texto]
        )

        documento = {
            "inciso": inciso,
            "nombre": nombres[inciso],
            "descripcion_original": descripcion,
            "formato": "PDF",
            "tamano_maximo_indicado": "4Mb",
            "obligatorio": inciso == "a",
            "condicion": (
                "Si la persona Responsable Técnica "
                "no pertenece al SNII"
                if inciso == "b"
                else None
            ),
        }

        documentos.append(documento)

    return documentos


def main():
    texto = ARCHIVO_TEXTO.read_text(
        encoding="utf-8"
    )

    documentos = extraer_documentos(texto)

    resultado = {
        "documentos_requeridos": documentos,
        "total": len(documentos),
        "fuente": str(ARCHIVO_TEXTO),
        "metodo_extraccion": "reglas",
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

    print("=== DOCUMENTOS IDENTIFICADOS ===\n")

    for documento in documentos:
        print("Documento:", documento["nombre"])
        print("Inciso:", documento["inciso"])
        print("Formato:", documento["formato"])
        print(
            "Obligatorio:",
            documento["obligatorio"],
        )
        print(
            "Condición:",
            documento["condicion"],
        )
        print(
            "Descripción:",
            documento["descripcion_original"],
        )
        print()

    print("Total:", len(documentos))
    print("\nArchivo generado:", ARCHIVO_SALIDA)


if __name__ == "__main__":
    main()
