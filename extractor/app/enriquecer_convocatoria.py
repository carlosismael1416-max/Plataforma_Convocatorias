from __future__ import annotations

import argparse
import hashlib
import json
import re
import time
import subprocess
import sys
import unicodedata

from datetime import date, datetime
from pathlib import Path
from urllib.parse import urljoin, urlparse

import requests

from selectolax.parser import HTMLParser

from app.descubrimiento.clasificar_objetivo import (
    clasificar_objetivo_convocatoria,
)
from app.enriquecer_datos_extra import (
    descripcion_de_pagina,
    extraer_requisitos,
    extraer_fechas_publicacion_inicio,
    extraer_montos,
)
from app.analizar_pdf import (
    extraer_texto_pdf_bytes,
)

from app.extraer_cierre_pdf import (
    extraer_cierre_pdf,
)

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.seguridad_red import (
    descargar_mismo_dominio,
    mismo_dominio,
)


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parent
    .parent
)

MAX_PDF_BYTES = (
    20 * 1024 * 1024
)


MESES = {
    "enero": 1,
    "febrero": 2,
    "marzo": 3,
    "abril": 4,
    "mayo": 5,
    "junio": 6,
    "julio": 7,
    "agosto": 8,
    "septiembre": 9,
    "setiembre": 9,
    "octubre": 10,
    "noviembre": 11,
    "diciembre": 12,
}


PALABRAS_CIERRE = (
    "fecha de cierre",
    "fecha limite",
    "fecha límite",
    "cierre de convocatoria",
    "cierre de la convocatoria",
    "cierre de postulacion",
    "cierre de postulación",
    "cierre de postulaciones",
    "cierre de solicitudes",
    "recepcion de solicitudes",
    "recepción de solicitudes",
    "recepcion de propuestas",
    "recepción de propuestas",
    "registro de propuestas",
    "hasta el",
)


CABECERAS_OBJETIVO = (
    "objetivo",
    "objetivo general",
    "objetivos",
    "proposito",
    "propósito",
    "finalidad",
    "objeto de la convocatoria",
)


CABECERAS_DESCRIPCION = (
    "presentacion",
    "presentación",
    "introduccion",
    "introducción",
    "antecedentes",
    "descripcion",
    "descripción",
)


CABECERAS_CORTE = (
    "requisitos",
    "bases",
    "participantes",
    "elegibilidad",
    "calendario",
    "cronograma",
    "fechas",
    "procedimiento",
    "documentacion",
    "documentación",
    "evaluacion",
    "evaluación",
    "resultados",
    "contacto",
)


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

    return re.sub(
        r"\s+",
        " ",
        valor.casefold(),
    ).strip()


def normalizar_url(
    valor: str | None,
) -> str:
    if not valor:
        return ""

    try:
        parsed = urlparse(
            valor.strip()
        )

        host = (
            parsed.hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

        ruta = (
            parsed.path
            or "/"
        )

        if ruta != "/":
            ruta = ruta.rstrip("/")

        return (
            f"{parsed.scheme.lower() or 'https'}"
            f"://{host}{ruta}"
        )

    except Exception:
        return valor.strip().rstrip("/")


def host_url(
    valor: str | None,
) -> str:
    try:
        return (
            urlparse(
                valor or ""
            ).hostname
            or ""
        ).lower().removeprefix(
            "www."
        )

    except Exception:
        return ""


def descargar_pdf_seguro(
    url: str,
    url_base: str,
) -> bytes:
    _, contenido, _ = descargar_mismo_dominio(
        url,
        url_base,
        max_bytes=MAX_PDF_BYTES,
    )

    if not contenido.startswith(
        b"%PDF"
    ):
        raise ValueError(
            "La respuesta no es un PDF."
        )

    return contenido


def cargar_contexto(
    convocatoria_id: int,
    descubrimiento_id: int | None = None,
) -> dict:
    """
    Carga una convocatoria para enriquecimiento.

    El descubrimiento es opcional porque una
    convocatoria hija creada a partir de una
    convocatoria marco puede no tener un
    descubrimiento_web independiente.
    """

    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            c.id AS convocatoria_id,
            c.titulo,
            c.descripcion,
            c.objetivo,
            c.fecha_cierre,
            c.categoria_id,
            c.organismo_id,
            c.fuente_id,
            c.url_original,
            c.contenido_hash,
            c.monto_minimo,
            c.monto_maximo,
            c.moneda,
            c.fecha_publicacion,
            c.fecha_inicio,

            d.id AS descubrimiento_id,
            d.categoria_objetivo,
            d.estado_temporal,

            f.nombre AS fuente_nombre,
            f.url_base AS fuente_url_base

        FROM convocatorias c

        LEFT JOIN descubrimientos_web d
            ON d.id = %s

        INNER JOIN fuentes f
            ON f.id = c.fuente_id

        WHERE c.id = %s
        """,
        (
            descubrimiento_id,
            convocatoria_id,
        ),
    )

    fila = cursor.fetchone()

    if fila is None:
        cursor.close()
        conexion.rollback()
        conexion.close()

        raise RuntimeError(
            "No se encontró la convocatoria "
            "o su fuente."
        )

    columnas = [
        descripcion[0]
        for descripcion
        in cursor.description
    ]

    resultado = dict(
        zip(
            columnas,
            fila,
        )
    )

    cursor.close()
    conexion.rollback()
    conexion.close()

    return resultado

def ejecutar_preview(
    contexto: dict,
) -> list[dict]:
    comando = [
        sys.executable,
        "-m",
        "app.ejecutar_scraping",

        "--fuente-id",
        str(
            contexto[
                "fuente_id"
            ]
        ),

        "--url",
        contexto[
            "url_original"
        ],

        "--limite",
        "5",
    ]

    print()
    print(
        "Ejecutando vista previa:"
    )
    print(
        " ".join(
            comando
        )
    )

    proceso = subprocess.run(
        comando,
        cwd=str(
            BASE_DIR
        ),
        capture_output=True,
        text=True,
        timeout=300,
    )

    if proceso.returncode != 0:
        raise RuntimeError(
            "Vista previa falló:\n"
            + (
                proceso.stdout
                or ""
            )[-3000:]
            + "\n"
            + (
                proceso.stderr
                or ""
            )[-3000:]
        )

    ruta = (
        BASE_DIR
        / "scraping_vista_previa.json"
    )

    if not ruta.exists():
        raise RuntimeError(
            "No se generó "
            "scraping_vista_previa.json."
        )

    datos = json.loads(
        ruta.read_text(
            encoding="utf-8"
        )
    )

    if not isinstance(
        datos,
        list,
    ):
        raise RuntimeError(
            "Formato inesperado en "
            "scraping_vista_previa.json."
        )

    return datos


def elegir_resultado(
    resultados: list[dict],
    contexto: dict,
) -> dict:
    objetivo_url = normalizar_url(
        contexto[
            "url_original"
        ]
    )

    for item in resultados:
        if normalizar_url(
            item.get(
                "url_original"
            )
        ) == objetivo_url:
            return item

    objetivo_titulo = normalizar_texto(
        contexto[
            "titulo"
        ]
    )

    for item in resultados:
        if normalizar_texto(
            item.get(
                "titulo"
            )
        ) == objetivo_titulo:
            return item

    raise RuntimeError(
        "No se encontró la convocatoria "
        "en la vista previa."
    )


def limpiar_lineas(
    texto: str,
) -> list[str]:
    resultado = []

    for linea in texto.splitlines():
        limpia = re.sub(
            r"\s+",
            " ",
            linea,
        ).strip()

        if not limpia:
            continue

        if limpia.startswith(
            "=" * 10
        ):
            continue

        if re.fullmatch(
            r"PÁGINA\s+\d+",
            limpia,
            flags=re.IGNORECASE,
        ):
            continue

        resultado.append(
            limpia
        )

    return resultado


def quitar_numeracion_cabecera(
    texto: str,
) -> str:
    texto = normalizar_texto(
        texto
    )

    return re.sub(
        r"^(?:[ivxlcdm]+|\d+)"
        r"(?:\.|\)|-|:)?\s+",
        "",
        texto,
        flags=re.IGNORECASE,
    ).strip()


def parece_cabecera(
    linea: str,
) -> bool:
    normalizada = normalizar_texto(
        linea
    )

    sin_numeracion = (
        quitar_numeracion_cabecera(
            linea
        )
    )

    if len(
        linea
    ) > 90:
        return False

    conocidas = (
        CABECERAS_OBJETIVO
        + CABECERAS_DESCRIPCION
        + CABECERAS_CORTE
    )

    conocidas_norm = {
        normalizar_texto(
            x
        )
        for x in conocidas
    }

    if (
        normalizada in conocidas_norm
        or sin_numeracion in conocidas_norm
    ):
        return True

    if re.match(
        r"^(?:[ivxlcdm]+|\d+)"
        r"(?:\.|\)|-|:)\s+.+",
        normalizada,
        flags=re.IGNORECASE,
    ):
        return True

    if (
        len(linea) <= 70
        and linea.upper() == linea
        and any(
            caracter.isalpha()
            for caracter in linea
        )
    ):
        return True

    return False


def extraer_seccion(
    lineas: list[str],
    cabeceras: tuple[str, ...],
    max_caracteres: int = 2500,
) -> str | None:
    objetivos = {
        normalizar_texto(
            x
        )
        for x in cabeceras
    }

    for indice, linea in enumerate(
        lineas
    ):
        normalizada = normalizar_texto(
            linea
        )

        sin_numeracion = (
            quitar_numeracion_cabecera(
                linea
            )
        )

        encontrada = False

        if (
            normalizada in objetivos
            or sin_numeracion in objetivos
        ):
            encontrada = True

        else:
            for cabecera in objetivos:
                if (
                    normalizada.startswith(
                        cabecera + ":"
                    )
                    or sin_numeracion.startswith(
                        cabecera + ":"
                    )
                ):
                    resto = linea.split(
                        ":",
                        1,
                    )[1].strip()

                    if len(
                        resto
                    ) >= 40:
                        return resto[
                            :max_caracteres
                        ]

                    encontrada = True
                    break

        if not encontrada:
            continue

        partes = []

        for siguiente in lineas[
            indice + 1:
            indice + 15
        ]:
            if parece_cabecera(
                siguiente
            ):
                break

            partes.append(
                siguiente
            )

            unido = " ".join(
                partes
            )

            if len(
                unido
            ) >= max_caracteres:
                break

        contenido = " ".join(
            partes
        ).strip()

        if len(
            contenido
        ) >= 40:
            return contenido[
                :max_caracteres
            ]

    return None


def descripcion_literal(
    lineas: list[str],
    titulo: str,
) -> str | None:
    titulo_normalizado = normalizar_texto(
        titulo
    )

    seleccion = []

    for linea in lineas:
        normalizada = normalizar_texto(
            linea
        )

        if normalizada == titulo_normalizado:
            continue

        if len(
            linea
        ) < 45:
            continue

        if parece_cabecera(
            linea
        ):
            continue

        if linea.lower().startswith(
            (
                "http://",
                "https://",
            )
        ):
            continue

        seleccion.append(
            linea
        )

        if len(
            " ".join(
                seleccion
            )
        ) >= 900:
            break

    texto = " ".join(
        seleccion
    ).strip()

    if len(
        texto
    ) < 100:
        return None

    return texto[:1200]


def convertir_fecha(
    valor,
) -> date | None:
    if valor is None:
        return None

    if isinstance(
        valor,
        datetime,
    ):
        return valor.date()

    if isinstance(
        valor,
        date,
    ):
        return valor

    texto = str(
        valor
    ).strip()

    try:
        return date.fromisoformat(
            texto[:10]
        )
    except Exception:
        return None


def fechas_en_linea(
    linea: str,
) -> list[date]:
    resultados = []

    normalizada = normalizar_texto(
        linea
    )

    patron_textual = re.compile(
        r"\b("
        r"\d{1,2}"
        r")\s+de\s+("
        + "|".join(
            MESES.keys()
        )
        + r")\s+de\s+("
        r"20\d{2}"
        r")\b",
        flags=re.IGNORECASE,
    )

    for match in patron_textual.finditer(
        normalizada
    ):
        try:
            resultados.append(
                date(
                    int(
                        match.group(3)
                    ),
                    MESES[
                        match.group(2)
                    ],
                    int(
                        match.group(1)
                    ),
                )
            )
        except ValueError:
            pass

    patron_numerico = re.compile(
        r"\b("
        r"\d{1,2}"
        r")[/-]("
        r"\d{1,2}"
        r")[/-]("
        r"20\d{2}"
        r")\b"
    )

    for match in patron_numerico.finditer(
        normalizada
    ):
        try:
            resultados.append(
                date(
                    int(
                        match.group(3)
                    ),
                    int(
                        match.group(2)
                    ),
                    int(
                        match.group(1)
                    ),
                )
            )
        except ValueError:
            pass

    return resultados


def extraer_fecha_cierre(
    texto: str,
    lineas: list[str],
) -> tuple[
    date | None,
    str | None,
]:
    try:
        existente = extraer_cierre_pdf(
            texto
        )

        for clave in (
            "fecha_cierre_postulacion",
            "fecha_cierre_proceso",
            "fecha",
        ):
            fecha = convertir_fecha(
                existente.get(
                    clave
                )
            )

            if fecha:
                return (
                    fecha,
                    (
                        "extraer_cierre_pdf:"
                        + clave
                    ),
                )

    except Exception:
        pass

    candidatas = []

    for linea in lineas:
        normalizada = normalizar_texto(
            linea
        )

        coincidencias = [
            palabra
            for palabra in PALABRAS_CIERRE
            if normalizar_texto(
                palabra
            ) in normalizada
        ]

        if not coincidencias:
            continue

        fechas = fechas_en_linea(
            linea
        )

        for fecha in fechas:
            prioridad = 1

            if (
                "cierre"
                in normalizada
                or "fecha limite"
                in normalizada
            ):
                prioridad = 3

            elif (
                "hasta el"
                in normalizada
                or "recepcion"
                in normalizada
            ):
                prioridad = 2

            candidatas.append(
                (
                    prioridad,
                    fecha,
                    linea,
                )
            )

    if not candidatas:
        return (
            None,
            None,
        )

    candidatas.sort(
        key=lambda item: (
            item[0],
            item[1],
        ),
        reverse=True,
    )

    mejor = candidatas[0]

    return (
        mejor[1],
        mejor[2],
    )


def lineas_interes_fecha(
    lineas: list[str],
) -> list[str]:
    resultado = []

    for linea in lineas:
        normalizada = normalizar_texto(
            linea
        )

        if any(
            normalizar_texto(
                palabra
            ) in normalizada
            for palabra in PALABRAS_CIERRE
        ):
            resultado.append(
                linea
            )

    return resultado[:20]


def buscar_categoria(
    categoria_objetivo: str | None,
) -> int | None:
    if categoria_objetivo not in {
        "PROYECTO_INVESTIGACION",
        "FINANCIAMIENTO_INVESTIGACION",
    }:
        return None

    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute("""
        SELECT
            id,
            nombre
        FROM categorias
        WHERE estado = TRUE
        ORDER BY id
    """)

    categoria_id = None

    for fila in cursor.fetchall():
        nombre = normalizar_texto(
            fila[1]
        )

        if "investig" in nombre:
            categoria_id = int(
                fila[0]
            )
            break

    cursor.close()
    conexion.rollback()
    conexion.close()

    return categoria_id


def buscar_organismo_existente(
    fuente_url: str,
    fuente_nombre: str,
) -> int | None:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute("""
        SELECT
            id,
            nombre,
            sitio_web
        FROM organismos
        WHERE estado = TRUE
        ORDER BY id
    """)

    fuente_host = host_url(
        fuente_url
    )

    tokens_fuente = {
        token
        for token in re.findall(
            r"[a-z0-9]+",
            normalizar_texto(
                fuente_nombre
            ),
        )
        if len(
            token
        ) >= 5
    }

    encontrado = None

    for organismo_id, nombre, sitio_web in cursor.fetchall():
        if (
            sitio_web
            and fuente_host
            and mismo_dominio(
                fuente_url,
                sitio_web,
            )
        ):
            encontrado = int(
                organismo_id
            )
            break

        tokens_nombre = {
            token
            for token in re.findall(
                r"[a-z0-9]+",
                normalizar_texto(
                    nombre
                ),
            )
            if len(
                token
            ) >= 5
        }

        if (
            tokens_fuente
            and tokens_nombre
            and (
                tokens_fuente
                & tokens_nombre
            )
        ):
            encontrado = int(
                organismo_id
            )
            break

    cursor.close()
    conexion.rollback()
    conexion.close()

    return encontrado



def puntuar_documento_pagina(
    nombre: str,
    url: str,
) -> int:
    """
    Prioriza documentos útiles para enriquecer
    convocatorias sin depender de nombres,
    años o una fuente concreta.
    """

    combinado = normalizar_texto(
        (
            nombre
            or ""
        )
        + " "
        + (
            url
            or ""
        )
    )

    score = 0

    positivos = (
        (
            (
                "terminos de referencia",
                "termino de referencia",
                "tdr",
            ),
            120,
        ),
        (
            (
                "bases especificas",
                "bases especifica",
            ),
            110,
        ),
        (
            (
                "bases",
                "convocatoria",
                "capitulo",
            ),
            80,
        ),
        (
            (
                "anexo",
                "protocolo",
                "formato",
            ),
            30,
        ),
        (
            (
                "guia",
            ),
            10,
        ),
    )

    for palabras, puntos in positivos:
        if any(
            palabra
            in combinado
            for palabra in palabras
        ):
            score += puntos

    negativos = (
        (
            (
                "resultado",
                "resultados",
                "proyectos aprobados",
                "lista de proyectos",
            ),
            300,
        ),
        (
            (
                "preguntas frecuentes",
            ),
            120,
        ),
        (
            (
                "comunicado",
            ),
            60,
        ),
    )

    for palabras, puntos in negativos:
        if any(
            palabra
            in combinado
            for palabra in palabras
        ):
            score -= puntos

    return score


def descargar_html_pagina_segura(
    url: str,
    url_base: str,
) -> tuple[str, str]:
    """
    Descarga HTML institucional siguiendo
    únicamente redirecciones seguras y del
    mismo dominio.
    """

    url_final, contenido, tipo = (
        descargar_mismo_dominio(
            url,
            url_base,
            max_bytes=5 * 1024 * 1024,
        )
    )

    codificacion = (
        requests.utils.get_encoding_from_headers(
            {
                "content-type": tipo,
            }
        )
        or "utf-8"
    )

    return (
        url_final,
        contenido.decode(
            codificacion,
            errors="replace",
        ),
    )


def construir_item_pagina_directa(
    contexto: dict,
    descarga: tuple[str, str] | None = None,
) -> dict:
    """
    Fallback cuando el spider general no
    devuelve la URL exacta.

    Sirve especialmente para subconvocatorias,
    capítulos o páginas específicas enlazadas
    desde una convocatoria marco.
    """

    # descarga: (url_final, html) si quien llama ya bajó la página.
    url_final, html = descarga or (
        descargar_html_pagina_segura(
            contexto[
                "url_original"
            ],
            contexto[
                "fuente_url_base"
            ],
        )
    )

    arbol = HTMLParser(
        html
    )

    documentos = []
    vistos = set()

    for nodo in arbol.css(
        "a"
    ):
        href = (
            nodo.attributes.get(
                "href"
            )
            or ""
        ).strip()

        if not href:
            continue

        destino = urljoin(
            url_final,
            href,
        )

        # Solamente documentos del dominio
        # institucional correspondiente.
        if not mismo_dominio(
            destino,
            contexto[
                "fuente_url_base"
            ],
        ):
            continue

        ruta_url = (
            destino.split(
                "?",
                1,
            )[0]
            .split(
                "#",
                1,
            )[0]
            .lower()
        )

        if not ruta_url.endswith(
            ".pdf"
        ):
            continue

        if destino in vistos:
            continue

        vistos.add(
            destino
        )

        nombre = (
            nodo.text(
                strip=True
            )
            or "Documento PDF"
        ).strip()

        documentos.append({
            "nombre":
                nombre,

            "url":
                destino,

            "_score":
                puntuar_documento_pagina(
                    nombre,
                    destino,
                ),
        })

    documentos.sort(
        key=lambda item: (
            item.get(
                "_score",
                0,
            ),
            len(
                item.get(
                    "nombre",
                    "",
                )
            ),
        ),
        reverse=True,
    )

    # Quitar metadato interno.
    for documento in documentos:
        documento.pop(
            "_score",
            None,
        )

    texto_pagina = (
        arbol.text(
            separator="\n",
            strip=True,
        )
        or ""
    )

    return {
        "titulo":
            contexto[
                "titulo"
            ],

        "url_original":
            url_final,

        "descripcion":
            None,

        "documentos":
            documentos,

        "_texto_pagina":
            texto_pagina,

        "_origen_item":
            "PAGINA_DIRECTA",
    }



def descargar_pdf_con_reintentos(
    url: str,
    url_base: str,
    *,
    intentos: int = 3,
) -> bytes:
    ultimo_error = None

    for numero in range(
        1,
        intentos + 1,
    ):
        try:
            return descargar_pdf_seguro(
                url,
                url_base,
            )

        except (
            requests.exceptions.Timeout,
            requests.exceptions.ConnectionError,
        ) as error:
            ultimo_error = error

            if numero >= intentos:
                raise

            espera = numero * 2

            print(
                "PDF temporalmente no disponible. "
                f"Reintento {numero + 1}/{intentos} "
                f"en {espera}s..."
            )

            time.sleep(
                espera
            )

    if ultimo_error:
        raise ultimo_error

    raise RuntimeError(
        "No se pudo descargar el PDF."
    )


def extraer_prorroga_pagina(
    texto_pagina: str,
):
    """
    Busca una fecha explícitamente publicada
    como ampliación/prórroga del cierre.

    Tiene prioridad sobre fechas originales
    presentes en Bases o TDR.
    """

    if not texto_pagina:
        return (
            None,
            None,
        )

    lineas = limpiar_lineas(
        texto_pagina
    )

    indicadores = (
        "se extiende la fecha",
        "se amplia la fecha",
        "se amplía la fecha",
        "prorroga",
        "prórroga",
        "nuevo cierre",
        "nueva fecha de cierre",
    )

    for indice, linea in enumerate(
        lineas
    ):
        normalizada = normalizar_texto(
            linea
        )

        if not any(
            normalizar_texto(
                indicador
            )
            in normalizada
            for indicador
            in indicadores
        ):
            continue

        ventana = " ".join(
            lineas[
                max(
                    0,
                    indice - 1,
                ):
                min(
                    len(lineas),
                    indice + 3,
                )
            ]
        )

        fechas = fechas_en_linea(
            ventana
        )

        if not fechas:
            continue

        # Si hay varias fechas en el aviso,
        # tomamos la más tardía.
        fecha = max(
            fechas
        )

        return (
            fecha,
            (
                "PAGINA_PRORROGA: "
                + ventana
            ),
        )

    return (
        None,
        None,
    )



def objetivo_extraido_valido(
    objetivo: str | None,
) -> bool:
    """
    Evita guardar preguntas de formularios,
    instrucciones o encabezados como si fueran
    el objetivo real de una convocatoria.
    """

    texto = (
        objetivo
        or ""
    ).strip()

    if len(texto) < 70:
        return False

    normalizado = normalizar_texto(
        texto
    )

    inicios_invalidos = (
        "que se busca lograr",
        "que busca lograr",
        "describa",
        "indique",
        "especifique",
        "capture",
        "ingrese",
        "senale",
        "señale",
    )

    if normalizado.startswith(
        inicios_invalidos
    ):
        return False

    if (
        "?"
        in texto
        and len(texto) < 300
    ):
        return False

    frases_formulario = (
        "que se busca lograr con",
        "describa el objetivo",
        "indique el objetivo",
        "especifique el objetivo",
        "pregunta de investigacion",
    )

    if any(
        frase in normalizado
        for frase in frases_formulario
    ):
        return False

    return True


def puntuar_objetivo_extraido(
    objetivo: str | None,
) -> int:
    if not objetivo_extraido_valido(
        objetivo
    ):
        return -1000

    texto = (
        objetivo
        or ""
    ).strip()

    normalizado = normalizar_texto(
        texto
    )

    score = min(
        len(texto),
        800,
    ) // 20

    indicadores = (
        "finalidad",
        "objetivo",
        "proposito",
        "impulsar",
        "fortalecer",
        "promover",
        "apoyar",
        "contribuir",
        "fomentar",
        "generar conocimiento",
    )

    for indicador in indicadores:
        if indicador in normalizado:
            score += 10

    if len(texto) > 1800:
        score -= 20

    return score


def seleccionar_objetivo_documental(
    textos: list[str],
) -> str | None:
    """
    Busca el objetivo por documento en vez de
    concatenar todos los PDFs y aceptar la
    primera coincidencia.

    Esto permite descartar preguntas de
    formularios/TDR y conservar una sección
    objetiva encontrada en otro documento
    oficial.
    """

    candidatos = []

    for indice, texto_documento in enumerate(
        textos
    ):
        if not (
            texto_documento
            or ""
        ).strip():
            continue

        lineas_documento = limpiar_lineas(
            texto_documento
        )

        candidato = extraer_seccion(
            lineas_documento,
            CABECERAS_OBJETIVO,
            max_caracteres=2200,
        )

        if not objetivo_extraido_valido(
            candidato
        ):
            continue

        candidatos.append(
            (
                puntuar_objetivo_extraido(
                    candidato
                ),
                -indice,
                candidato,
            )
        )

    if not candidatos:
        return None

    candidatos.sort(
        reverse=True
    )

    return candidatos[0][2]


def construir_enriquecimiento(
    contexto: dict,
    item: dict | None = None,
) -> dict:
    # item: página ya armada (importación desde un enlace, sin fuente
    # registrada y sin spider).
    try:
        if item is None:
            item = elegir_resultado(
                ejecutar_preview(
                    contexto
                ),
                contexto,
            )

    except RuntimeError as error:
        if (
            "No se encontró la convocatoria "
            "en la vista previa"
            not in str(
                error
            )
        ):
            raise

        print()
        print(
            "La vista previa de Scrapy no "
            "devolvió la URL exacta."
        )
        print(
            "Usando fallback seguro sobre "
            "la página oficial."
        )

        item = construir_item_pagina_directa(
            contexto
        )

    documentos = item.get(
        "documentos"
    ) or []

    textos = []

    # El HTML de la página se conserva por
    # separado. Sirve para avisos/prórrogas,
    # pero no debe contaminar la descripción
    # ni el objetivo documental.
    texto_pagina = (
        item.get(
            "_texto_pagina"
        )
        or ""
    ).strip()

    documentos_procesados = []
    errores_documentos = []

    for documento in documentos[:3]:
        url = (
            documento.get(
                "url"
            )
            or ""
        ).strip()

        if not url:
            continue

        try:
            contenido = descargar_pdf_con_reintentos(
                url,
                contexto[
                    "fuente_url_base"
                ],
            )

            texto = (
                extraer_texto_pdf_bytes(
                    contenido
                )
            )

            textos.append(
                texto
            )

            documentos_procesados.append({
                "nombre":
                    documento.get(
                        "nombre"
                    ),

                "url":
                    url,

                "caracteres":
                    len(
                        texto
                    ),
            })

        except Exception as error:
            errores_documentos.append({
                "url":
                    url,

                "error":
                    (
                        f"{type(error).__name__}: "
                        f"{error}"
                    ),
            })

    texto_total = "\n".join(
        textos
    )

    lineas = limpiar_lineas(
        texto_total
    )

    objetivo = seleccionar_objetivo_documental(
        textos
    )

    if objetivo is None:
        candidato_general = extraer_seccion(
            lineas,
            CABECERAS_OBJETIVO,
            max_caracteres=2200,
        )

        if objetivo_extraido_valido(
            candidato_general
        ):
            objetivo = candidato_general

    descripcion = extraer_seccion(
        lineas,
        CABECERAS_DESCRIPCION,
        max_caracteres=2200,
    )

    if not descripcion:
        descripcion = descripcion_literal(
            lineas,
            contexto[
                "titulo"
            ],
        )

    # Sin PDF (o sin texto útil en él): la descripción sale de la
    # propia página oficial.
    if not descripcion:
        descripcion = descripcion_de_pagina(
            texto_pagina,
            contexto["titulo"],
        )

    montos = extraer_montos(
        texto_total + "\n" + texto_pagina
    )

    fechas_extra = extraer_fechas_publicacion_inicio(
        lineas + limpiar_lineas(texto_pagina),
        fechas_en_linea,
    )

    fecha_pdf, evidencia_pdf = (
        extraer_fecha_cierre(
            texto_total,
            lineas,
        )
    )

    (
        fecha_prorroga,
        evidencia_prorroga,
    ) = extraer_prorroga_pagina(
        texto_pagina
    )

    if fecha_prorroga is not None:
        fecha_cierre = fecha_prorroga
        evidencia_fecha = (
            evidencia_prorroga
        )

    else:
        fecha_cierre = fecha_pdf
        evidencia_fecha = (
            evidencia_pdf
        )

    categoria_objetivo = contexto.get("categoria_objetivo")

    # Lo que entra por una fuente oficial no tiene descubrimiento: la
    # categoría se deduce del título, la URL y el texto de la convocatoria.
    if not categoria_objetivo:
        categoria_objetivo = clasificar_objetivo_convocatoria(
            titulo=contexto.get("titulo") or "",
            url=contexto.get("url_original") or "",
            contexto=(objetivo or descripcion or texto_total)[:3000],
        ).get("categoria_objetivo")

    categoria_id = buscar_categoria(
        categoria_objetivo
    )

    requisitos = extraer_requisitos(
        lineas
    )

    organismo_id = (
        buscar_organismo_existente(
            contexto[
                "fuente_url_base"
            ],
            contexto[
                "fuente_nombre"
            ],
        )
    )

    contenido_hash = None

    if texto_total.strip():
        contenido_hash = hashlib.sha256(
            texto_total.encode(
                "utf-8",
                errors="ignore",
            )
        ).hexdigest()

    return {
        "descripcion":
            descripcion,

        "objetivo":
            objetivo,

        "fecha_cierre":
            fecha_cierre,

        "evidencia_fecha":
            evidencia_fecha,

        "categoria_id":
            categoria_id,

        "organismo_id":
            organismo_id,

        "contenido_hash":
            contenido_hash,

        "monto_minimo":
            montos["monto_minimo"],

        "monto_maximo":
            montos["monto_maximo"],

        "moneda":
            montos["moneda"],

        "fecha_publicacion":
            fechas_extra["fecha_publicacion"],

        "fecha_inicio":
            fechas_extra["fecha_inicio"],

        "requisitos":
            requisitos,

        "documentos_procesados":
            documentos_procesados,

        "errores_documentos":
            errores_documentos,

        "lineas_fecha":
            lineas_interes_fecha(
                lineas
            ),
    }


def guardar_requisitos(
    convocatoria_id: int,
    requisitos: list[dict],
) -> int:
    """Inserta los requisitos extraídos solo si la convocatoria no tiene."""

    if not requisitos:
        return 0

    conexion = conectar()

    try:
        with conexion.cursor() as cursor:
            cursor.execute(
                "SELECT COUNT(*) FROM convocatoria_requisitos WHERE convocatoria_id = %s",
                (convocatoria_id,),
            )

            if cursor.fetchone()[0] > 0:
                conexion.rollback()
                return 0

            for requisito in requisitos:
                cursor.execute(
                    """
                    INSERT INTO convocatoria_requisitos (
                        convocatoria_id, titulo, descripcion, obligatorio,
                        tipo_requisito, orden, created_at, updated_at
                    )
                    VALUES (%s, %s, %s, %s, %s, %s, NOW(), NOW())
                    """,
                    (
                        convocatoria_id,
                        requisito["titulo"],
                        requisito["descripcion"],
                        requisito["obligatorio"],
                        requisito["tipo_requisito"],
                        requisito["orden"],
                    ),
                )

        conexion.commit()
        return len(requisitos)
    finally:
        conexion.close()


def campos_extra(
    contexto: dict,
    resultado: dict,
    campos: dict,
) -> dict:
    """
    Montos y fechas de publicación/inicio: solo llenan campos vacíos y
    respetan las restricciones de la tabla (cierre >= inicio y
    máximo >= mínimo).
    """

    extra = {}

    if contexto.get("monto_maximo") is None and resultado.get("monto_maximo") is not None:
        minimo = resultado.get("monto_minimo")
        minimo_actual = contexto.get("monto_minimo")

        if minimo_actual is None or resultado["monto_maximo"] >= minimo_actual:
            extra["monto_maximo"] = resultado["monto_maximo"]

            if minimo_actual is None and minimo is not None and minimo <= resultado["monto_maximo"]:
                extra["monto_minimo"] = minimo

            if resultado.get("moneda") and resultado["moneda"] != (contexto.get("moneda") or "MXN"):
                extra["moneda"] = resultado["moneda"]

    if contexto.get("fecha_publicacion") is None and resultado.get("fecha_publicacion"):
        extra["fecha_publicacion"] = resultado["fecha_publicacion"]

    cierre = campos.get("fecha_cierre") or contexto.get("fecha_cierre")

    if (
        contexto.get("fecha_inicio") is None
        and resultado.get("fecha_inicio")
        and (cierre is None or resultado["fecha_inicio"] <= cierre)
    ):
        extra["fecha_inicio"] = resultado["fecha_inicio"]

    return extra


def aplicar_enriquecimiento(
    contexto: dict,
    resultado: dict,
) -> list[str]:
    campos = {}

    if (
        not contexto.get(
            "descripcion"
        )
        and resultado.get(
            "descripcion"
        )
    ):
        campos[
            "descripcion"
        ] = resultado[
            "descripcion"
        ]

    objetivo_actual = contexto.get(
        "objetivo"
    )

    objetivo_nuevo = resultado.get(
        "objetivo"
    )

    if (
        (
            not objetivo_actual
            or not objetivo_extraido_valido(
                objetivo_actual
            )
        )
        and objetivo_extraido_valido(
            objetivo_nuevo
        )
    ):
        campos[
            "objetivo"
        ] = objetivo_nuevo

    if (
        contexto.get(
            "fecha_cierre"
        )
        is None
        and resultado.get(
            "fecha_cierre"
        )
        is not None
    ):
        campos[
            "fecha_cierre"
        ] = resultado[
            "fecha_cierre"
        ]

    if (
        contexto.get(
            "categoria_id"
        )
        is None
        and resultado.get(
            "categoria_id"
        )
        is not None
    ):
        campos[
            "categoria_id"
        ] = resultado[
            "categoria_id"
        ]

    if (
        contexto.get(
            "organismo_id"
        )
        is None
        and resultado.get(
            "organismo_id"
        )
        is not None
    ):
        campos[
            "organismo_id"
        ] = resultado[
            "organismo_id"
        ]

    if (
        not contexto.get(
            "contenido_hash"
        )
        and resultado.get(
            "contenido_hash"
        )
    ):
        campos[
            "contenido_hash"
        ] = resultado[
            "contenido_hash"
        ]

    campos.update(
        campos_extra(contexto, resultado, campos)
    )

    requisitos_insertados = guardar_requisitos(
        contexto["convocatoria_id"],
        resultado.get("requisitos") or [],
    )

    if requisitos_insertados and not campos:
        return ["requisitos"]

    if not campos:
        return []

    conexion = conectar()
    cursor = conexion.cursor()

    asignaciones = [
        f'"{campo}" = %s'
        for campo in campos
    ]

    valores = list(
        campos.values()
    )

    asignaciones.append(
        "updated_at = NOW()"
    )

    sql = f"""
        UPDATE convocatorias
        SET
            {", ".join(asignaciones)}
        WHERE id = %s
    """

    valores.append(
        contexto[
            "convocatoria_id"
        ]
    )

    cursor.execute(
        sql,
        valores,
    )

    conexion.commit()
    cursor.close()
    conexion.close()

    return list(
        campos.keys()
    )


def mostrar(
    contexto: dict,
    resultado: dict,
) -> None:
    print()
    print(
        "=" * 80
    )
    print(
        "ENRIQUECIMIENTO AUTOMÁTICO"
    )
    print(
        "=" * 80
    )

    print(
        "Convocatoria:",
        contexto[
            "convocatoria_id"
        ],
    )

    print(
        "Título:",
        contexto[
            "titulo"
        ],
    )

    print(
        "Fuente:",
        contexto[
            "fuente_nombre"
        ],
    )

    print()
    print(
        "Descripción:",
        (
            resultado[
                "descripcion"
            ][:800]
            if resultado.get(
                "descripcion"
            )
            else "NO DETECTADA"
        ),
    )

    print()
    print(
        "Objetivo:",
        (
            resultado[
                "objetivo"
            ][:800]
            if resultado.get(
                "objetivo"
            )
            else "NO DETECTADO"
        ),
    )

    print()
    print(
        "Fecha cierre:",
        resultado.get(
            "fecha_cierre"
        ),
    )

    print(
        "Evidencia fecha:",
        resultado.get(
            "evidencia_fecha"
        ),
    )

    print(
        "Categoría ID:",
        resultado.get(
            "categoria_id"
        ),
    )

    print(
        "Organismo ID:",
        resultado.get(
            "organismo_id"
        ),
    )

    print()
    print(
        "PDF procesados:",
        len(
            resultado[
                "documentos_procesados"
            ]
        ),
    )

    for documento in resultado[
        "documentos_procesados"
    ]:
        print(
            " -",
            documento,
        )

    if resultado[
        "errores_documentos"
    ]:
        print()
        print(
            "Errores PDF:"
        )

        for error in resultado[
            "errores_documentos"
        ]:
            print(
                " -",
                error,
            )

    print()
    print(
        "Líneas relacionadas "
        "con cierre:"
    )

    if resultado[
        "lineas_fecha"
    ]:
        for linea in resultado[
            "lineas_fecha"
        ]:
            print(
                " -",
                linea,
            )
    else:
        print(
            " - Ninguna"
        )


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Enriquece una convocatoria "
            "usando sus documentos oficiales."
        )
    )

    parser.add_argument(
        "--convocatoria-id",
        type=int,
        required=True,
    )

    parser.add_argument(
        "--descubrimiento-id",
        type=int,
        default=None,
        help=(
            "ID del descubrimiento asociado. "
            "Es opcional para convocatorias hijas "
            "de una convocatoria marco."
        ),
    )

    parser.add_argument(
        "--aplicar",
        action="store_true",
    )

    args = parser.parse_args()

    contexto = cargar_contexto(
        args.convocatoria_id,
        args.descubrimiento_id,
    )

    resultado = (
        construir_enriquecimiento(
            contexto
        )
    )

    mostrar(
        contexto,
        resultado,
    )

    if not args.aplicar:
        print()
        print(
            "MODO VISTA PREVIA: "
            "PostgreSQL no fue modificado."
        )

        return

    campos = aplicar_enriquecimiento(
        contexto,
        resultado,
    )

    print()
    print(
        "Campos actualizados:",
        (
            ", ".join(
                campos
            )
            if campos
            else "NINGUNO"
        ),
    )


if __name__ == "__main__":
    main()
