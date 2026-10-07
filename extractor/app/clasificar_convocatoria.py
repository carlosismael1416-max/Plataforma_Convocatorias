
import re
import unicodedata


def normalizar_texto(texto):
    """
    Convierte el texto a minúsculas y elimina
    diferencias entre letras con y sin acentos.
    """

    texto = texto or ""

    texto = unicodedata.normalize(
        "NFKD",
        texto,
    )

    texto = "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )

    return " ".join(
        texto.lower().split()
    )


PATRONES_PROYECTOS = [
    (
        r"\bproyectos?\s+de\s+investigacion\b",
        "Proyectos de investigación",
    ),
    (
        r"\bciencia\s+basica\s+y\s+de\s+frontera\b",
        "Ciencia básica y de frontera",
    ),
    (
        r"\bproyectos?\s+de\s+desarrollo\s+tecnologico\b",
        "Proyectos de desarrollo tecnológico",
    ),
    (
        r"\bproyectos?\s+de\s+innovacion\b",
        "Proyectos de innovación",
    ),
    (
        r"\bfortalecimiento\s+de\s+infraestructura\b",
        "Fortalecimiento de infraestructura",
    ),
]


PATRONES_OTROS = [
    (
        r"\bpremios?\b",
        "Premio",
    ),
    (
        r"\bcopas?\b",
        "Competencia",
    ),
    (
        r"\bconcursos?\b",
        "Concurso",
    ),
    (
        r"\bbecas?\b",
        "Beca",
    ),
    (
        r"\bproceso\s+para\s+ocupar\s+el\s+cargo\b",
        "Selección de personal",
    ),
]


def buscar_coincidencias(texto, patrones):
    encontradas = []

    for patron, descripcion in patrones:
        if re.search(patron, texto):
            encontradas.append(descripcion)

    return encontradas


def clasificar_convocatoria(resultado):
    """
    Clasificación preliminar basada en el título
    y la descripción disponibles.

    No determina la elegibilidad definitiva
    ni confirma que exista financiamiento.
    """

    titulo = resultado.get("titulo") or ""
    descripcion = resultado.get("descripcion") or ""

    texto = normalizar_texto(
        titulo + " " + descripcion
    )

    proyectos = buscar_coincidencias(
        texto,
        PATRONES_PROYECTOS,
    )

    otros = buscar_coincidencias(
        texto,
        PATRONES_OTROS,
    )

    if proyectos and otros:
        return {
            "clasificacion": "REVISION_MANUAL",
            "motivo": (
                "Contiene términos de proyectos, "
                "pero también corresponde a otro "
                "tipo de convocatoria."
            ),
            "coincidencias": proyectos + otros,
        }

    if otros:
        return {
            "clasificacion": "OTRO_TIPO",
            "motivo": (
                "Aparentemente corresponde a un "
                "premio, concurso, beca u otro proceso. "
                "Revisar antes de descartarlo."
            ),
            "coincidencias": otros,
        }

    if proyectos:
        return {
            "clasificacion": "POSIBLE_PROYECTO",
            "motivo": (
                "El título o la descripción contiene "
                "términos relacionados con proyectos "
                "de investigación o desarrollo."
            ),
            "coincidencias": proyectos,
        }

    return {
        "clasificacion": "REVISION_MANUAL",
        "motivo": (
            "No se encontró información suficiente "
            "para determinar su tipo."
        ),
        "coincidencias": [],
    }
