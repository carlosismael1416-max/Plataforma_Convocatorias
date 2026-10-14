from __future__ import annotations

import re
import unicodedata
from datetime import datetime, timezone
from email.utils import parsedate_to_datetime


def normalizar_texto(
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
        r"\s+",
        " ",
        valor,
    )

    return valor.strip()


def contiene(
    texto: str,
    *terminos: str,
) -> bool:
    for termino in terminos:
        termino = normalizar_texto(
            termino
        )

        patron = (
            r"(?<!\w)"
            + re.escape(termino)
            + r"(?!\w)"
        )

        if re.search(
            patron,
            texto,
        ):
            return True

    return False


def dias_desde_publicacion(
    fecha: str | None,
) -> int | None:
    if not fecha:
        return None

    try:
        fecha_dt = (
            parsedate_to_datetime(
                fecha
            )
        )

        if fecha_dt.tzinfo is None:
            fecha_dt = fecha_dt.replace(
                tzinfo=timezone.utc
            )

        ahora = datetime.now(
            timezone.utc
        )

        return max(
            0,
            (
                ahora
                - fecha_dt.astimezone(
                    timezone.utc
                )
            ).days,
        )

    except Exception:
        return None


def clasificar_google_news(
    titulo: str,
    fuente_nombre: str | None,
    dominio: str | None,
    fecha_publicacion: str | None = None,
) -> dict:
    titulo_norm = normalizar_texto(
        titulo
    )

    fuente_norm = normalizar_texto(
        fuente_nombre
    )

    dominio_norm = normalizar_texto(
        dominio
    )

    puntaje = 0
    razones = []

    # ---------------------------------
    # Conceptos positivos
    # ---------------------------------

    tiene_convocatoria = contiene(
        titulo_norm,
        "convocatoria",
        "convocatorias",
    )

    tiene_investigacion = contiene(
        titulo_norm,
        "investigacion",
        "investigaciones",
        "investigador",
        "investigadores",
        "cientifica",
        "cientifico",
    )

    tiene_financiamiento = contiene(
        titulo_norm,
        "financiamiento",
        "financiacion",
        "fondo",
        "fondos",
    )

    tiene_proyecto = contiene(
        titulo_norm,
        "proyecto",
        "proyectos",
    )

    tiene_beca = contiene(
        titulo_norm,
        "beca",
        "becas",
    )

    tiene_apoyo = contiene(
        titulo_norm,
        "apoyo",
        "apoyos",
    )

    if tiene_convocatoria:
        puntaje += 4
        razones.append(
            "+4 convocatoria"
        )

    if tiene_investigacion:
        puntaje += 4
        razones.append(
            "+4 investigación"
        )

    if tiene_financiamiento:
        puntaje += 5
        razones.append(
            "+5 financiamiento/fondo"
        )

    if tiene_proyecto:
        puntaje += 2
        razones.append(
            "+2 proyectos"
        )

    if contiene(
        titulo_norm,
        "innovacion",
    ):
        puntaje += 2
        razones.append(
            "+2 innovación"
        )

    if contiene(
        titulo_norm,
        "ciencia",
    ):
        puntaje += 2
        razones.append(
            "+2 ciencia"
        )

    if contiene(
        titulo_norm,
        "tecnologia",
    ):
        puntaje += 2
        razones.append(
            "+2 tecnología"
        )

    if tiene_apoyo:
        puntaje += 2
        razones.append(
            "+2 apoyo"
        )

    # Una beca por sí sola no significa
    # convocatoria de investigación.
    if (
        tiene_beca
        and tiene_investigacion
    ):
        puntaje += 3
        razones.append(
            "+3 beca de investigación"
        )

    elif tiene_beca:
        puntaje += 1
        razones.append(
            "+1 beca genérica"
        )

    # ---------------------------------
    # Combinaciones fuertes
    # ---------------------------------

    if (
        tiene_convocatoria
        and tiene_investigacion
    ):
        puntaje += 3
        razones.append(
            "+3 convocatoria + investigación"
        )

    if (
        tiene_convocatoria
        and (
            tiene_financiamiento
            or tiene_apoyo
        )
    ):
        puntaje += 3
        razones.append(
            "+3 convocatoria + financiamiento"
        )

    # ---------------------------------
    # Tipo de fuente
    # ---------------------------------

    if dominio_norm.endswith(
        ".gob.mx"
    ):
        puntaje += 3
        razones.append(
            "+3 dominio gubernamental"
        )

    elif dominio_norm.endswith(
        ".edu.mx"
    ):
        puntaje += 3
        razones.append(
            "+3 dominio educativo MX"
        )

    if contiene(
        fuente_norm,
        "universidad",
    ):
        puntaje += 2
        razones.append(
            "+2 universidad"
        )

    if contiene(
        fuente_norm,
        "instituto",
    ):
        puntaje += 1
        razones.append(
            "+1 instituto"
        )

    if contiene(
        titulo_norm,
        "mexico",
    ):
        puntaje += 2
        razones.append(
            "+2 referencia México"
        )

    # ---------------------------------
    # Ruido / temas no relevantes
    # ---------------------------------

    descarte_directo = False

    if contiene(
        titulo_norm,
        "policia",
        "vacante",
        "vacantes",
        "contratacion",
    ):
        puntaje -= 12
        razones.append(
            "-12 empleo/seguridad"
        )
        descarte_directo = True

    if contiene(
        titulo_norm,
        "escuelas particulares",
        "preescolar",
        "primaria",
        "secundaria",
    ):
        puntaje -= 10
        razones.append(
            "-10 beca escolar"
        )
        descarte_directo = True

    if contiene(
        titulo_norm,
        "premio",
        "certamen",
        "coloquio",
        "congreso",
    ):
        puntaje -= 6
        razones.append(
            "-6 premio/evento"
        )

    if contiene(
        titulo_norm,
        "licitacion",
        "licitaciones",
        "proveedor",
        "proveedores",
    ):
        puntaje -= 10
        razones.append(
            "-10 contratación pública"
        )
        descarte_directo = True

    # ---------------------------------
    # Convocatorias probablemente cerradas
    # ---------------------------------

    convocatoria_finalizada = False

    if contiene(
        titulo_norm,
        "ganadores",
        "seleccionados",
        "resultados",
    ):
        puntaje -= 10
        razones.append(
            "-10 resultados publicados"
        )
        convocatoria_finalizada = True

    if (
        contiene(
            titulo_norm,
            "reciben",
            "recibieron",
        )
        and tiene_proyecto
    ):
        puntaje -= 8
        razones.append(
            "-8 recepción ya realizada"
        )
        convocatoria_finalizada = True

    if contiene(
        titulo_norm,
        "cerro",
        "cerrada",
        "concluyo",
        "finalizo",
    ):
        puntaje -= 10
        razones.append(
            "-10 convocatoria cerrada"
        )
        convocatoria_finalizada = True

    # ---------------------------------
    # Antigüedad
    # ---------------------------------

    dias = dias_desde_publicacion(
        fecha_publicacion
    )

    if dias is not None:
        if dias > 365:
            puntaje -= 8
            razones.append(
                "-8 publicación > 1 año"
            )

        elif dias > 180:
            puntaje -= 4
            razones.append(
                "-4 publicación > 180 días"
            )

        elif dias > 120:
            puntaje -= 2
            razones.append(
                "-2 publicación > 120 días"
            )

    # ---------------------------------
    # Clasificación final
    # ---------------------------------

    if (
        descarte_directo
        or convocatoria_finalizada
    ):
        estado = "DESCARTAR"

    elif puntaje >= 10:
        estado = (
            "POSIBLE_CONVOCATORIA"
        )

    elif puntaje >= 6:
        estado = (
            "REVISION_MANUAL"
        )

    else:
        estado = "DESCARTAR"

    return {
        "puntaje": puntaje,
        "estado": estado,
        "razones": razones,
        "dias_desde_publicacion":
            dias,
    }
