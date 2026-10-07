import json
from pathlib import Path


BASE = Path(__file__).resolve().parent.parent

CARPETA_EXTRAIDOS = BASE / "data" / "extraidos"

ARCHIVO_SALIDA = (
    CARPETA_EXTRAIDOS / "convocatoria_completa_2025.json"
)


def leer_json(ruta):
    if not ruta.exists():
        raise FileNotFoundError(
            f"No se encontró el archivo: {ruta}"
        )

    with ruta.open(encoding="utf-8") as archivo:
        return json.load(archivo)


def main():
    # Leer los resultados de las pruebas anteriores.
    detalle = leer_json(
        BASE / "detalle_resultado.json"
    )

    fechas = leer_json(
        CARPETA_EXTRAIDOS
        / "fechas_convocatoria_2025.json"
    )

    objetivo = leer_json(
        CARPETA_EXTRAIDOS
        / "objetivo_convocatoria_2025.json"
    )

    montos = leer_json(
        CARPETA_EXTRAIDOS
        / "montos_convocatoria_2025.json"
    )

    requisitos = leer_json(
        CARPETA_EXTRAIDOS
        / "requisitos_convocatoria_2025.json"
    )

    documentos = leer_json(
        CARPETA_EXTRAIDOS
        / "documentos_convocatoria_2025.json"
    )

    # Construir el registro de la convocatoria.
    convocatoria = {
        "titulo": detalle["titulo"],
        "url_original": detalle["url_original"],
        "descripcion": detalle.get("descripcion"),
        "objetivo": objetivo["objetivo"],

        "fechas": {
            "publicacion": fechas["fecha_publicacion"],
            "apertura": fechas["fecha_apertura"],
            "cierre_original_pdf": (
                fechas["fecha_cierre_pdf_original"]
            ),
            "cierre_ampliado_web": (
                fechas["fecha_cierre_ampliada_web"]
            ),
            "cierre_preferente_provisional": (
                fechas["fecha_cierre_preferente"]
            ),
            "hora_cierre": (
                fechas["hora_cierre_ampliada_web"]
            ),
            "zona_horaria": fechas["zona_horaria"],
            "requiere_verificar_cambios_posteriores": (
                fechas[
                    "requiere_verificar_cambios_posteriores"
                ]
            ),
        },

        "financiamiento": {
            "tipos_apoyo": montos["tipos_apoyo"],
            "numero_maximo_etapas": (
                montos["numero_maximo_etapas"]
            ),
        },

        "disposiciones": requisitos["disposiciones"],

        "documentos_requeridos": (
            documentos["documentos_requeridos"]
        ),

        "archivos_pdf": detalle["documentos"],

        "estado_revision": "PENDIENTE_REVISION",

        "fuentes_extraccion": {
            "objetivo": objetivo["fuente"],
            "montos": montos["fuente"],
            "disposiciones": requisitos["fuente"],
            "documentos_requeridos": documentos["fuente"],
            "fechas_pdf": fechas["fuente_fecha_original"],
            "fechas_web": fechas["fuente_fecha_ampliada"],
        },
    }

    # Crear la carpeta de salida si hace falta.
    CARPETA_EXTRAIDOS.mkdir(
        parents=True,
        exist_ok=True,
    )

    # Guardar la convocatoria completa.
    ARCHIVO_SALIDA.write_text(
        json.dumps(
            convocatoria,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    # Mostrar solamente un resumen para no
    # imprimir todas las disposiciones y PDF.
    print("=== CONVOCATORIA UNIFICADA ===\n")

    print("Título:", convocatoria["titulo"])

    print(
        "Fecha de cierre provisional:",
        convocatoria["fechas"][
            "cierre_preferente_provisional"
        ],
    )

    print(
        "Grupos de financiamiento:",
        len(
            convocatoria["financiamiento"][
                "tipos_apoyo"
            ]
        ),
    )

    print(
        "Disposiciones:",
        len(convocatoria["disposiciones"]),
    )

    print(
        "Documentos requeridos:",
        len(convocatoria["documentos_requeridos"]),
    )

    print(
        "Archivos PDF:",
        len(convocatoria["archivos_pdf"]),
    )

    print(
        "Estado:",
        convocatoria["estado_revision"],
    )

    print("\nArchivo generado:", ARCHIVO_SALIDA)


if __name__ == "__main__":
    main()
