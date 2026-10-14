from __future__ import annotations

import re
import unicodedata

from urllib.parse import (
    unquote,
    urlparse,
)


def normalizar(
    valor: str | None,
) -> str:
    valor = valor or ""

    valor = unicodedata.normalize(
        "NFKD",
        valor,
    )

    valor = "".join(
        caracter
        for caracter in valor
        if not unicodedata.combining(
            caracter
        )
    )

    valor = valor.casefold()

    valor = re.sub(
        r"[^a-z0-9]+",
        " ",
        valor,
    )

    valor = re.sub(
        r"\s+",
        " ",
        valor,
    )

    return valor.strip()


def ultimo_segmento(
    url: str,
) -> str:
    try:
        ruta = unquote(
            urlparse(url).path
        )

        segmentos = [
            x
            for x in ruta.split("/")
            if x
        ]

        if not segmentos:
            return ""

        return segmentos[-1]

    except Exception:
        return ""


def contiene_palabra(
    texto: str,
    palabra: str,
) -> bool:
    # Palabra completa con plural opcional (-s/-es), para que
    # "curso" no coincida dentro de "recursos" ni "foro" en "aforo".
    return re.search(
        rf"\b{re.escape(palabra)}(?:s|es)?\b",
        texto,
    ) is not None


def contiene(
    texto: str,
    palabras: tuple[str, ...],
) -> bool:
    return any(
        contiene_palabra(
            texto,
            palabra,
        )
        for palabra in palabras
    )


PALABRAS_INVESTIGACION = (
    "investigacion",
    "investigaciones",
    "investigador",
    "investigadores",
    "investigadora",
    "cientifica",
    "cientifico",
    "cientificas",
    "cientificos",
    "humanistica",
    "humanistico",
    "investigacion aplicada",
)


PALABRAS_PROYECTO = (
    "proyecto",
    "proyectos",
)


PALABRAS_FINANCIAMIENTO = (
    "financiamiento",
    "financiacion",
    "financiar",
    "financia",
    "financiado",
    "financiada",
    "fondo",
    "fondos",
    "subvencion",
    "subvenciones",
    "grant",
    "funding",
    "recursos",
    "apoyo economico",
    "apoyos economicos",
    "millon de pesos",
    "millones de pesos",
)


PALABRAS_BECA = (
    "beca",
    "becas",
    "becario",
    "becaria",
)


PALABRAS_EVENTO = (
    "congreso",
    "concurso",
    "premio",
    "reconocimiento",
    "seminario",
    "diplomado",
    "curso",
    "taller",
    "encuentro",
    "foro",
    "feria",
    "simposio",
)


def clasificar_objetivo_convocatoria(
    titulo: str,
    url: str,
    contexto: str = "",
) -> dict:
    """
    Clasifica una URL según el objetivo
    real del proyecto ITSVA.

    ACEPTAR:
        Investigación/proyecto/financiamiento.

    REVISION:
        Becas o convocatorias científicas
        demasiado generales.

    DESCARTAR:
        Congresos, concursos, premios,
        cursos y otros eventos.
    """

    texto = normalizar(
        f"{titulo} "
        f"{ultimo_segmento(url)}"
    )

    convocatoria = contiene(
        texto,
        (
            "convocatoria",
            "convocatorias",
        ),
    )

    investigacion = contiene(
        texto,
        PALABRAS_INVESTIGACION,
    )

    proyecto = contiene(
        texto,
        PALABRAS_PROYECTO,
    )

    financiamiento = contiene(
        texto,
        PALABRAS_FINANCIAMIENTO,
    )

    ciencia_tecnologia = contiene(
        texto,
        (
            "ciencia",
            "tecnologia",
            "innovacion",
        ),
    )

    fomento = contiene(
        texto,
        (
            "fomento",
            "impulso",
            "impulsar",
        ),
    )

    beca = contiene(
        texto,
        PALABRAS_BECA,
    )

    evento = contiene(
        texto,
        PALABRAS_EVENTO,
    )

    razones = []
    puntaje = 0

    if convocatoria:
        puntaje += 4
        razones.append(
            "+4 convocatoria"
        )

    if investigacion:
        puntaje += 5
        razones.append(
            "+5 investigación"
        )

    if proyecto:
        puntaje += 5
        razones.append(
            "+5 proyecto"
        )

    if financiamiento:
        puntaje += 6
        razones.append(
            "+6 financiamiento"
        )

    if ciencia_tecnologia:
        puntaje += 2
        razones.append(
            "+2 ciencia/tecnología"
        )

    if fomento:
        puntaje += 2
        razones.append(
            "+2 fomento/impulso"
        )

    if beca:
        puntaje -= 2
        razones.append(
            "-2 beca"
        )

    if evento:
        puntaje -= 12
        razones.append(
            "-12 evento"
        )

    # ==================================
    # Eventos
    # ==================================

    if evento:
        return {
            "categoria_objetivo":
                "EVENTO",

            "decision_objetivo":
                "DESCARTAR",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Becas
    # ==================================

    if beca:
        categoria = (
            "BECA_INVESTIGACION"
            if investigacion
            else "BECA"
        )

        return {
            "categoria_objetivo":
                categoria,

            "decision_objetivo":
                "REVISION",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Convocatorias claramente dirigidas
    # a proyectos/investigación
    # ==================================

    if (
        convocatoria
        and (
            investigacion
            or proyecto
        )
    ):
        return {
            "categoria_objetivo":
                "PROYECTO_INVESTIGACION",

            "decision_objetivo":
                "ACEPTAR",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Financiamiento temático
    # ==================================

    if (
        financiamiento
        and (
            investigacion
            or proyecto
            or ciencia_tecnologia
        )
    ):
        return {
            "categoria_objetivo":
                "FINANCIAMIENTO_INVESTIGACION",

            "decision_objetivo":
                "ACEPTAR",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Fomento + investigación
    # ==================================

    if (
        fomento
        and investigacion
    ):
        return {
            "categoria_objetivo":
                "PROYECTO_INVESTIGACION",

            "decision_objetivo":
                "ACEPTAR",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Contexto de la nota
    # ==================================
    #
    # Un título general ("Abre FECTI 2026 convocatorias
    # para impulsar la ciencia") no dice qué se financia;
    # si el texto de la nota habla de financiar
    # investigación o proyectos, se acepta. Solo sube
    # desde REVISION: eventos y becas ya se resolvieron.

    if convocatoria and contexto:
        texto_contexto = normalizar(
            contexto
        )

        # Una nota sobre becas o eventos no sube: el título
        # puede no decirlo (p. ej. el nombre de un PDF).
        if (
            not contiene(
                texto_contexto,
                PALABRAS_BECA + PALABRAS_EVENTO,
            )
            and contiene(
                texto_contexto,
                PALABRAS_FINANCIAMIENTO,
            )
            and (
                contiene(
                    texto_contexto,
                    PALABRAS_INVESTIGACION,
                )
                or contiene(
                    texto_contexto,
                    PALABRAS_PROYECTO,
                )
            )
        ):
            razones.append(
                "+contexto: la nota describe "
                "financiamiento de investigación "
                "o proyectos"
            )

            return {
                "categoria_objetivo":
                    "FINANCIAMIENTO_INVESTIGACION",

                "decision_objetivo":
                    "ACEPTAR",

                "puntaje_objetivo":
                    puntaje,

                "razones_objetivo":
                    razones,
            }

    # ==================================
    # Ciencia/tecnología demasiado general
    # ==================================

    if (
        convocatoria
        and ciencia_tecnologia
    ):
        return {
            "categoria_objetivo":
                "CIENCIA_TECNOLOGIA_GENERAL",

            "decision_objetivo":
                "REVISION",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    # ==================================
    # Convocatoria genérica
    # ==================================

    if convocatoria:
        return {
            "categoria_objetivo":
                "CONVOCATORIA_GENERAL",

            "decision_objetivo":
                "REVISION",

            "puntaje_objetivo":
                puntaje,

            "razones_objetivo":
                razones,
        }

    return {
        "categoria_objetivo":
            "NO_OBJETIVO",

        "decision_objetivo":
            "DESCARTAR",

        "puntaje_objetivo":
            puntaje,

        "razones_objetivo":
            razones,
    }
