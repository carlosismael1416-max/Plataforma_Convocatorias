
import json
import re
from pathlib import Path


ARCHIVO_TEXTO = Path(
    "data/textos/convocatoria_investigacion_2025.txt"
)

ARCHIVO_SALIDA = Path(
    "data/extraidos/objetivo_convocatoria_2025.json"
)


def extraer_objetivo(texto):
    # Localizar el encabezado I. Objetivo.
    inicio = re.search(
        r"^[ \t]*I\.[ \t]*Objetivo[ \t]*$",
        texto,
        re.IGNORECASE | re.MULTILINE,
    )

    if not inicio:
        raise ValueError(
            "No se encontró la sección I. Objetivo."
        )

    # Localizar el siguiente encabezado:
    # II. Población objetivo.
    fin = re.search(
        r"^[ \t]*II\.[ \t]*Poblaci[oó]n[ \t]+objetivo[ \t]*$",
        texto[inicio.end():],
        re.IGNORECASE | re.MULTILINE,
    )

    if not fin:
        raise ValueError(
            "No se encontró el final de la sección Objetivo."
        )

    # Obtener solamente el contenido de la sección.
    contenido = texto[
        inicio.end():inicio.end() + fin.start()
    ]

    # Eliminar saltos de línea y espacios sobrantes.
    objetivo = " ".join(contenido.split())

    if not objetivo:
        raise ValueError(
            "La sección Objetivo está vacía."
        )

    return objetivo


def main():
    texto = ARCHIVO_TEXTO.read_text(
        encoding="utf-8"
    )

    objetivo = extraer_objetivo(texto)

    resultado = {
        "objetivo": objetivo,
        "fuente": str(ARCHIVO_TEXTO),
        "metodo_extraccion": "reglas",
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

    print("=== OBJETIVO EXTRAÍDO ===\n")
    print(objetivo)
    print("\nArchivo generado:", ARCHIVO_SALIDA)


if __name__ == "__main__":
    main()
