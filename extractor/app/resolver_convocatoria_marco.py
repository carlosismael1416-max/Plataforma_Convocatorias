from __future__ import annotations

import argparse
import io
import re
import unicodedata

from datetime import date
from urllib.parse import urljoin, urlparse

import pytesseract

from PIL import Image, ImageOps
from selectolax.parser import HTMLParser

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.procesar_hijas_marco import (
    procesar_hijas_marco,
)

from app.seguridad_red import (
    descargar_mismo_dominio,
    mismo_dominio,
)

from app.urls import (
    hash_url,
    normalizar_url,
)


MAX_BYTES = 10 * 1024 * 1024


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


def normalizar(
    texto: str | None,
) -> str:
    texto = texto or ""

    texto = unicodedata.normalize(
        "NFKD",
        texto,
    )

    texto = "".join(
        c
        for c in texto
        if not unicodedata.combining(c)
    )

    return re.sub(
        r"\s+",
        " ",
        texto.casefold(),
    ).strip()


def fechas_texto(
    texto: str,
) -> list[date]:
    texto = normalizar(
        texto
    )

    patron = re.compile(
        r"\b(\d{1,2})"
        r"\s+de\s+("
        + "|".join(MESES.keys())
        + r")"
        r"\s+de\s+(20\d{2})\b",
        flags=re.IGNORECASE,
    )

    resultado = []

    for match in patron.finditer(
        texto
    ):
        try:
            resultado.append(
                date(
                    int(match.group(3)),
                    MESES[match.group(2)],
                    int(match.group(1)),
                )
            )
        except ValueError:
            pass

    return resultado


def descargar_seguro(
    url: str,
    base: str,
    *,
    max_bytes: int = MAX_BYTES,
) -> tuple[
    str,
    bytes,
    str,
]:
    return descargar_mismo_dominio(
        url,
        base,
        max_bytes=max_bytes,
        reintentos=3,
    )


def descargar_html(
    url: str,
) -> tuple[
    str,
    HTMLParser,
]:
    final, contenido, _ = (
        descargar_seguro(
            url,
            url,
            max_bytes=5 * 1024 * 1024,
        )
    )

    texto = contenido.decode(
        "utf-8",
        errors="replace",
    )

    return (
        final,
        HTMLParser(texto),
    )


def obtener_lineas_html(
    arbol: HTMLParser,
) -> list[str]:
    texto = arbol.text(
        separator="\n",
        strip=True,
    )

    return [
        re.sub(
            r"\s+",
            " ",
            linea,
        ).strip()
        for linea in texto.splitlines()
        if linea.strip()
    ]


def buscar_cierre_textual(
    lineas: list[str],
) -> dict | None:
    candidatos = []

    for indice in range(
        len(lineas)
    ):
        inicio = max(
            0,
            indice - 2,
        )

        fin = min(
            len(lineas),
            indice + 4,
        )

        ventana = " ".join(
            lineas[
                inicio:fin
            ]
        )

        norm = normalizar(
            ventana
        )

        score = 0

        if (
            "se extiende la fecha"
            in norm
            and (
                "recepcion de propuestas"
                in norm
                or "captura de solicitudes"
                in norm
            )
        ):
            score = 100

        elif (
            "cierre del sistema"
            in norm
            and "solicitudes"
            in norm
        ):
            score = 95

        elif (
            "cierre"
            in norm
            and (
                "recepcion"
                in norm
                or "postulacion"
                in norm
                or "solicitudes"
                in norm
            )
        ):
            score = 85

        elif (
            "recepcion de propuestas"
            in norm
            or "recepcion de solicitudes"
            in norm
        ):
            score = 70

        if score == 0:
            continue

        fechas = fechas_texto(
            ventana
        )

        if not fechas:
            continue

        for fecha in fechas:
            candidatos.append({
                "score":
                    score,

                "fecha":
                    fecha,

                "evidencia":
                    ventana,
            })

    if not candidatos:
        return None

    candidatos.sort(
        key=lambda x: (
            x["score"],
            x["fecha"],
        ),
        reverse=True,
    )

    return candidatos[0]


def imagenes_calendario(
    arbol: HTMLParser,
    url_pagina: str,
) -> list[str]:
    resultado = []

    for nodo in arbol.css("img"):
        posibles = [
            nodo.attributes.get(
                "src"
            ),
            nodo.attributes.get(
                "data-src"
            ),
            nodo.attributes.get(
                "data-lazy-src"
            ),
        ]

        descripcion = " ".join([
            nodo.attributes.get(
                "alt"
            )
            or "",
            nodo.attributes.get(
                "title"
            )
            or "",
        ])

        for valor in posibles:
            if not valor:
                continue

            url = urljoin(
                url_pagina,
                valor,
            )

            combinado = normalizar(
                descripcion
                + " "
                + url
            )

            if "calendario" not in combinado:
                continue

            if url not in resultado:
                resultado.append(
                    url
                )

    return resultado


def preparar_imagen_ocr(
    contenido: bytes,
):
    imagen = Image.open(
        io.BytesIO(
            contenido
        )
    )

    imagen = ImageOps.exif_transpose(
        imagen
    )

    imagen = ImageOps.grayscale(
        imagen
    )

    imagen = ImageOps.autocontrast(
        imagen
    )

    ancho, alto = imagen.size

    # Aumentar tamaño mejora OCR de
    # tablas pequeñas.
    if ancho < 1800:
        imagen = imagen.resize(
            (
                ancho * 2,
                alto * 2,
            )
        )

    return imagen


def buscar_cierre_por_coordenadas(
    imagen,
    psm: int,
) -> dict | None:
    datos = pytesseract.image_to_data(
        imagen,
        lang="spa",
        config=f"--psm {psm}",
        output_type=(
            pytesseract.Output.DICT
        ),
    )

    total = len(
        datos.get(
            "text",
            []
        )
    )

    candidatos = []

    for i in range(total):
        palabra = (
            datos["text"][i]
            or ""
        ).strip()

        if not palabra:
            continue

        if "cierre" not in normalizar(
            palabra
        ):
            continue

        top = int(
            datos["top"][i]
        )

        altura = max(
            1,
            int(
                datos["height"][i]
            ),
        )

        # La fecha está en la misma fila
        # visual, normalmente a la derecha.
        margen_superior = max(
            10,
            int(
                imagen.height * 0.015
            ),
        )

        altura_fila = max(
            altura * 5,
            int(
                imagen.height * 0.12
            ),
        )

        y_min = max(
            0,
            top - margen_superior,
        )

        y_max = min(
            imagen.height,
            top + altura_fila,
        )

        palabras_fila = []

        for j in range(total):
            palabra_j = (
                datos["text"][j]
                or ""
            ).strip()

            if not palabra_j:
                continue

            top_j = int(
                datos["top"][j]
            )

            alto_j = max(
                1,
                int(
                    datos["height"][j]
                ),
            )

            centro_j = (
                top_j
                + alto_j / 2
            )

            if not (
                y_min
                <= centro_j
                <= y_max
            ):
                continue

            palabras_fila.append(
                (
                    top_j,
                    int(
                        datos["left"][j]
                    ),
                    palabra_j,
                )
            )

        palabras_fila.sort(
            key=lambda item: (
                item[0],
                item[1],
            )
        )

        evidencia = " ".join(
            item[2]
            for item in palabras_fila
        )

        fechas = fechas_texto(
            evidencia
        )

        if not fechas:
            continue

        norm = normalizar(
            evidencia
        )

        score = 80

        if (
            "cierre del sistema"
            in norm
        ):
            score += 20

        if (
            "captura"
            in norm
            and "solicitudes"
            in norm
        ):
            score += 10

        if (
            "recepcion"
            in norm
            or "postulacion"
            in norm
        ):
            score += 5

        candidatos.append({
            "score":
                score,

            # La primera fecha encontrada
            # dentro de la fila de cierre.
            "fecha":
                fechas[0],

            "evidencia":
                evidencia,

            "psm":
                psm,
        })

    if not candidatos:
        return None

    candidatos.sort(
        key=lambda item: (
            item["score"],
            item["fecha"],
        ),
        reverse=True,
    )

    return candidatos[0]


def buscar_cierre_ocr_secuencial(
    texto_ocr: str,
) -> dict | None:
    """
    Busca específicamente el cierre en texto
    producido por OCR.

    Tesseract puede leer:
        Cierre -> ierre
        Cierre -> ¡erre
        Cierre -> erre

    Evitamos usar solamente la palabra
    'sistema' porque también existe:
        Apertura del sistema...
    """

    lineas = [
        re.sub(
            r"\s+",
            " ",
            linea,
        ).strip()
        for linea in texto_ocr.splitlines()
        if linea.strip()
    ]

    for indice, linea in enumerate(
        lineas
    ):
        norm = normalizar(
            linea
        )

        # "erre del sistema" funciona para:
        #
        # cierre del sistema
        # ierre del sistema
        # ¡erre del sistema
        #
        # y NO coincide con:
        # apertura del sistema
        es_cierre = (
            "erre del sistema"
            in norm
        )

        if not es_cierre:
            continue

        if not (
            "captura"
            in norm
            or "recepcion"
            in norm
            or "postulacion"
            in norm
        ):
            continue

        # Primero buscamos la fecha en la
        # misma línea. Es la evidencia
        # más segura para estos calendarios.
        fechas = fechas_texto(
            linea
        )

        if fechas:
            return {
                "score":
                    120,

                "fecha":
                    fechas[0],

                "evidencia":
                    linea,

                "metodo":
                    "OCR_CIERRE_MISMA_LINEA",
            }

        # Si Tesseract separó la celda,
        # admitimos como máximo las dos
        # líneas inmediatamente posteriores.
        ventana = " ".join(
            lineas[
                indice:
                min(
                    len(lineas),
                    indice + 3,
                )
            ]
        )

        fechas = fechas_texto(
            ventana
        )

        if fechas:
            return {
                "score":
                    110,

                "fecha":
                    fechas[0],

                "evidencia":
                    ventana,

                "metodo":
                    "OCR_CIERRE_VENTANA",
            }

    return None


def buscar_cierre_imagen(
    url: str,
    url_pagina: str,
) -> dict | None:
    _, contenido, _ = (
        descargar_seguro(
            url,
            url_pagina,
        )
    )

    imagen = preparar_imagen_ocr(
        contenido
    )

    # Primero: búsqueda espacial.
    # Probamos distintos modos porque
    # Tesseract interpreta de manera
    # diferente las tablas.
    for psm in (
        6,
        4,
        3,
        11,
    ):
        try:
            resultado = (
                buscar_cierre_por_coordenadas(
                    imagen,
                    psm,
                )
            )

            if resultado:
                resultado[
                    "metodo"
                ] = (
                    "OCR_COORDENADAS"
                )

                return resultado

        except Exception:
            continue

    # Segundo fallback:
    # OCR lineal tradicional.
    for psm in (
        6,
        4,
        3,
        11,
    ):
        try:
            texto_ocr = (
                pytesseract.image_to_string(
                    imagen,
                    lang="spa",
                    config=f"--psm {psm}",
                )
            )

            # Primero usamos una búsqueda
            # especializada y tolerante a
            # errores OCR sobre "Cierre".
            resultado = (
                buscar_cierre_ocr_secuencial(
                    texto_ocr
                )
            )

            if resultado:
                resultado[
                    "psm"
                ] = psm

                return resultado

            # Fallback genérico.
            lineas_ocr = [
                re.sub(
                    r"\s+",
                    " ",
                    linea,
                ).strip()
                for linea
                in texto_ocr.splitlines()
                if linea.strip()
            ]

            resultado = (
                buscar_cierre_textual(
                    lineas_ocr
                )
            )

            if resultado:
                resultado[
                    "metodo"
                ] = (
                    "OCR_TEXTO"
                )

                resultado[
                    "psm"
                ] = psm

                return resultado

        except Exception:
            continue

    return None


def descubrir_capitulos(
    url_padre: str,
) -> list[dict]:
    url_final, arbol = (
        descargar_html(
            url_padre
        )
    )

    resultados = []
    vistos = set()

    for nodo in arbol.css("a"):
        href = (
            nodo.attributes.get(
                "href"
            )
            or ""
        ).strip()

        if not href:
            continue

        texto = (
            nodo.text(
                strip=True
            )
            or ""
        ).strip()

        url = urljoin(
            url_final,
            href,
        )

        combinado = normalizar(
            texto
            + " "
            + url
        )

        texto_norm = normalizar(
            texto
        )

        ruta_norm = normalizar(
            urlparse(
                url
            ).path
        )

        es_capitulo = (
            "capitulo"
            in texto_norm
            or "/capitulo"
            in ruta_norm
        )

        if not es_capitulo:
            continue

        if urlparse(
            url
        ).path.lower().endswith(
            (
                ".pdf",
                ".png",
                ".jpg",
                ".jpeg",
                ".webp",
            )
        ):
            continue

        if not mismo_dominio(
            url,
            url_padre,
        ):
            continue

        if url in vistos:
            continue

        vistos.add(
            url
        )

        titulo = re.sub(
            r"^[⇒→\-\s]+",
            "",
            texto,
        ).strip()

        resultados.append({
            "titulo":
                titulo
                or url,

            "url":
                url,
        })

    return resultados


def resolver_capitulo(
    capitulo: dict,
) -> dict:
    url_final, arbol = (
        descargar_html(
            capitulo[
                "url"
            ]
        )
    )

    lineas = obtener_lineas_html(
        arbol
    )

    directo = buscar_cierre_textual(
        lineas
    )

    if directo:
        fecha = directo[
            "fecha"
        ]

        return {
            **capitulo,

            "fecha_cierre":
                fecha.isoformat(),

            "origen":
                "PAGINA_TEXTO",

            "evidencia":
                directo[
                    "evidencia"
                ],

            "estado_temporal":
                (
                    "VENCIDA"
                    if fecha < date.today()
                    else "ACTUAL"
                ),

            "imagen_calendario":
                None,
        }

    imagenes = imagenes_calendario(
        arbol,
        url_final,
    )

    errores = []

    for imagen_url in imagenes:
        try:
            cierre = buscar_cierre_imagen(
                imagen_url,
                url_final,
            )

            if not cierre:
                continue

            fecha = cierre[
                "fecha"
            ]

            return {
                **capitulo,

                "fecha_cierre":
                    fecha.isoformat(),

                "origen":
                    "CALENDARIO_IMAGEN",

                "metodo_ocr":
                    cierre.get(
                        "metodo"
                    ),

                "psm_ocr":
                    cierre.get(
                        "psm"
                    ),

                "evidencia":
                    cierre[
                        "evidencia"
                    ],

                "estado_temporal":
                    (
                        "VENCIDA"
                        if fecha < date.today()
                        else "ACTUAL"
                    ),

                "imagen_calendario":
                    imagen_url,
            }

        except Exception as error:
            errores.append(
                (
                    f"{type(error).__name__}: "
                    f"{error}"
                )
            )

    return {
        **capitulo,

        "fecha_cierre":
            None,

        "origen":
            None,

        "evidencia":
            None,

        "estado_temporal":
            "SIN_FECHA_VERIFICABLE",

        "imagen_calendario":
            (
                imagenes[0]
                if imagenes
                else None
            ),

        "errores":
            errores,
    }


def obtener_datos_padre(
    convocatoria_padre_id: int,
) -> dict:
    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT
            id,
            titulo,
            categoria_id,
            organismo_id,
            fuente_id,
            moneda
        FROM convocatorias
        WHERE id = %s
        """,
        (
            convocatoria_padre_id,
        ),
    )

    fila = cursor.fetchone()

    if fila is None:
        cursor.close()
        conexion.rollback()
        conexion.close()

        raise RuntimeError(
            "No existe la convocatoria padre."
        )

    resultado = {
        "id":
            fila[0],

        "titulo":
            fila[1],

        "categoria_id":
            fila[2],

        "organismo_id":
            fila[3],

        "fuente_id":
            fila[4],

        "moneda":
            fila[5]
            or "MXN",
    }

    cursor.close()
    conexion.rollback()
    conexion.close()

    return resultado


def crear_o_actualizar_capitulos(
    convocatoria_padre_id: int,
    resultados: list[dict],
) -> list[dict]:
    """
    Persiste capítulos resueltos como
    convocatorias hijas.

    No utiliza nombres, años ni fechas
    hardcodeados.

    La URL oficial es la identidad del
    capítulo para evitar duplicados.
    """

    padre = obtener_datos_padre(
        convocatoria_padre_id
    )

    conexion = conectar()
    cursor = conexion.cursor()

    guardados = []

    try:
        for capitulo in resultados:
            fecha_cierre = (
                capitulo.get(
                    "fecha_cierre"
                )
            )

            estado_temporal = (
                capitulo.get(
                    "estado_temporal"
                )
            )

            url = (
                capitulo.get(
                    "url"
                )
                or ""
            ).strip()

            titulo = (
                capitulo.get(
                    "titulo"
                )
                or ""
            ).strip()

            # No persistir como convocatoria
            # algo que todavía no podemos
            # verificar suficientemente.
            if (
                not url
                or not titulo
                or not fecha_cierre
                or estado_temporal
                not in {
                    "ACTUAL",
                    "VENCIDA",
                }
            ):
                guardados.append({
                    "titulo":
                        titulo,

                    "url":
                        url,

                    "accion":
                        "OMITIDO",

                    "motivo":
                        (
                            "SIN_FECHA_VERIFICABLE"
                            if not fecha_cierre
                            else "ESTADO_NO_VERIFICADO"
                        ),
                })

                continue

            url_normalizada = (
                normalizar_url(
                    url
                )
            )

            url_hash = (
                hash_url(
                    url
                )
            )

            cursor.execute(
                """
                SELECT
                    id,
                    estado
                FROM convocatorias
                WHERE
                    url_hash = %s
                    OR RTRIM(
                        url_original,
                        '/'
                    ) = RTRIM(
                        %s,
                        '/'
                    )
                ORDER BY id
                LIMIT 1
                """,
                (
                    url_hash,
                    url_normalizada,
                ),
            )

            existente = cursor.fetchone()

            if (
                estado_temporal
                == "VENCIDA"
            ):
                nuevo_estado = (
                    "CERRADA"
                )
            else:
                nuevo_estado = (
                    "PENDIENTE_REVISION"
                )

            if existente:
                convocatoria_id = int(
                    existente[0]
                )

                estado_actual = (
                    existente[1]
                )

                # Si ya estaba publicada y
                # sigue vigente, no degradarla.
                if (
                    estado_temporal
                    == "ACTUAL"
                    and estado_actual
                    == "PUBLICADA"
                ):
                    nuevo_estado = (
                        "PUBLICADA"
                    )

                # Descartada a mano por un administrador: se queda así.
                if estado_actual == "DESCARTADA":
                    nuevo_estado = "DESCARTADA"

                cursor.execute(
                    """
                    UPDATE convocatorias
                    SET
                        convocatoria_padre_id = %s,
                        titulo = %s,
                        fecha_cierre = %s,

                        categoria_id =
                            COALESCE(
                                categoria_id,
                                %s
                            ),

                        organismo_id =
                            COALESCE(
                                organismo_id,
                                %s
                            ),

                        fuente_id =
                            COALESCE(
                                fuente_id,
                                %s
                            ),

                        url_original = %s,
                        url_hash = %s,
                        estado = %s,
                        fecha_extraccion = NOW(),
                        updated_at = NOW()

                    WHERE id = %s
                    """,
                    (
                        convocatoria_padre_id,
                        titulo,
                        fecha_cierre,
                        padre[
                            "categoria_id"
                        ],
                        padre[
                            "organismo_id"
                        ],
                        padre[
                            "fuente_id"
                        ],
                        url_normalizada,
                        url_hash,
                        nuevo_estado,
                        convocatoria_id,
                    ),
                )

                accion = (
                    "ACTUALIZADO"
                )

            else:
                cursor.execute(
                    """
                    INSERT INTO convocatorias (
                        convocatoria_padre_id,
                        titulo,
                        fecha_cierre,
                        moneda,
                        categoria_id,
                        organismo_id,
                        fuente_id,
                        url_original,
                        url_hash,
                        origen,
                        estado,
                        fecha_extraccion,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        %s,
                        'SCRAPING',
                        %s,
                        NOW(),
                        NOW(),
                        NOW()
                    )
                    RETURNING id
                    """,
                    (
                        convocatoria_padre_id,
                        titulo,
                        fecha_cierre,
                        padre[
                            "moneda"
                        ],
                        padre[
                            "categoria_id"
                        ],
                        padre[
                            "organismo_id"
                        ],
                        padre[
                            "fuente_id"
                        ],
                        url_normalizada,
                        url_hash,
                        nuevo_estado,
                    ),
                )

                convocatoria_id = int(
                    cursor.fetchone()[0]
                )

                accion = (
                    "CREADO"
                )

            guardados.append({
                "convocatoria_id":
                    convocatoria_id,

                "titulo":
                    titulo,

                "url":
                    url_normalizada,

                "fecha_cierre":
                    fecha_cierre,

                "estado_temporal":
                    estado_temporal,

                "estado":
                    nuevo_estado,

                "accion":
                    accion,
            })

        conexion.commit()

    except Exception:
        conexion.rollback()
        raise

    finally:
        cursor.close()
        conexion.close()

    return guardados


def aplicar_padre_cerrado(
    convocatoria_id: int,
    descubrimiento_id: int,
    capitulos: list[dict],
) -> bool:
    if not capitulos:
        return False

    if not all(
        item.get(
            "fecha_cierre"
        )
        for item in capitulos
    ):
        return False

    if not all(
        item.get(
            "estado_temporal"
        )
        == "VENCIDA"
        for item in capitulos
    ):
        return False

    conexion = conectar()
    cursor = conexion.cursor()

    cursor.execute(
        """
        UPDATE convocatorias
        SET
            estado = 'CERRADA',
            updated_at = NOW()
        WHERE id = %s
        """,
        (
            convocatoria_id,
        ),
    )

    cursor.execute(
        """
        UPDATE descubrimientos_web
        SET
            estado = 'PROCESADO',
            error_ultimo = NULL,
            updated_at = NOW()
        WHERE id = %s
          AND convocatoria_id = %s
        """,
        (
            descubrimiento_id,
            convocatoria_id,
        ),
    )

    conexion.commit()
    cursor.close()
    conexion.close()

    return True


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Resuelve convocatorias marco "
            "con capítulos y calendarios."
        )
    )

    parser.add_argument(
        "--url",
        required=True,
    )

    parser.add_argument(
        "--convocatoria-id",
        type=int,
    )

    parser.add_argument(
        "--descubrimiento-id",
        type=int,
    )

    parser.add_argument(
        "--aplicar-padre",
        action="store_true",
    )


    parser.add_argument(
        "--crear-hijas",
        action="store_true",
        help=(
            "Crea o actualiza los capítulos "
            "verificados como convocatorias hijas."
        ),
    )

    args = parser.parse_args()

    capitulos = descubrir_capitulos(
        args.url
    )

    print()
    print(
        "=" * 90
    )
    print(
        "CONVOCATORIA MARCO"
    )
    print(
        "=" * 90
    )

    print(
        "Capítulos encontrados:",
        len(
            capitulos
        ),
    )

    resultados = []

    for numero, capitulo in enumerate(
        capitulos,
        start=1,
    ):
        print()
        print(
            "-" * 90
        )

        print(
            f"{numero}.",
            capitulo[
                "titulo"
            ],
        )

        print(
            capitulo[
                "url"
            ]
        )

        try:
            resultado = resolver_capitulo(
                capitulo
            )

        except Exception as error:
            resultado = {
                **capitulo,

                "fecha_cierre":
                    None,

                "origen":
                    None,

                "estado_temporal":
                    "ERROR",

                "evidencia":
                    None,

                "error":
                    (
                        f"{type(error).__name__}: "
                        f"{error}"
                    ),
            }

        resultados.append(
            resultado
        )

        print(
            "Cierre:",
            resultado.get(
                "fecha_cierre"
            ),
        )

        print(
            "Origen:",
            resultado.get(
                "origen"
            ),
        )

        print(
            "Estado:",
            resultado.get(
                "estado_temporal"
            ),
        )

        if resultado.get(
            "evidencia"
        ):
            print(
                "Evidencia:",
                resultado[
                    "evidencia"
                ][:500],
            )

        if resultado.get(
            "imagen_calendario"
        ):
            print(
                "Calendario:",
                resultado[
                    "imagen_calendario"
                ],
            )

        if resultado.get(
            "metodo_ocr"
        ):
            print(
                "Método OCR:",
                resultado[
                    "metodo_ocr"
                ],
            )

            print(
                "PSM OCR:",
                resultado.get(
                    "psm_ocr"
                ),
            )

        if resultado.get(
            "error"
        ):
            print(
                "Error:",
                resultado[
                    "error"
                ],
            )

    print()
    print(
        "=" * 90
    )
    print(
        "RESUMEN"
    )
    print(
        "=" * 90
    )

    vencidas = sum(
        1
        for item in resultados
        if item.get(
            "estado_temporal"
        )
        == "VENCIDA"
    )

    actuales = sum(
        1
        for item in resultados
        if item.get(
            "estado_temporal"
        )
        == "ACTUAL"
    )

    sin_fecha = sum(
        1
        for item in resultados
        if not item.get(
            "fecha_cierre"
        )
    )

    print(
        "Vencidas:",
        vencidas,
    )

    print(
        "Actuales:",
        actuales,
    )

    print(
        "Sin fecha:",
        sin_fecha,
    )

    if args.crear_hijas:
        if args.convocatoria_id is None:
            raise SystemExit(
                "--crear-hijas requiere "
                "--convocatoria-id."
            )

        hijas = crear_o_actualizar_capitulos(
            args.convocatoria_id,
            resultados,
        )

        print()
        print(
            "=" * 90
        )
        print(
            "HIJAS CREADAS / ACTUALIZADAS"
        )
        print(
            "=" * 90
        )

        for hija in hijas:
            print()
            print(
                "Acción:",
                hija.get(
                    "accion"
                ),
            )

            print(
                "ID:",
                hija.get(
                    "convocatoria_id"
                ),
            )

            print(
                "Título:",
                hija.get(
                    "titulo"
                ),
            )

            print(
                "Fecha:",
                hija.get(
                    "fecha_cierre"
                ),
            )

            print(
                "Estado:",
                hija.get(
                    "estado"
                )
                or hija.get(
                    "motivo"
                ),
            )


        print()
        print(
            "=" * 90
        )
        print(
            "PROCESAMIENTO AUTOMÁTICO DE HIJAS"
        )
        print(
            "=" * 90
        )

        resumen_hijas = procesar_hijas_marco(
            args.convocatoria_id,
        )

        print(
            "Publicadas:",
            resumen_hijas[
                "publicadas"
            ],
        )

        print(
            "Cerradas:",
            resumen_hijas[
                "cerradas"
            ],
        )

        print(
            "Revisión final:",
            resumen_hijas[
                "revision_final"
            ],
        )

        print(
            "Errores temporales:",
            resumen_hijas[
                "temporales"
            ],
        )

    if not args.aplicar_padre:
        print()

        if args.crear_hijas:
            print(
                "CAPÍTULOS PERSISTIDOS: "
                "PostgreSQL fue actualizado "
                "con las convocatorias hijas. "
                "La convocatoria padre no fue "
                "modificada por esta opción."
            )

        else:
            print(
                "MODO VISTA PREVIA: "
                "PostgreSQL no fue modificado."
            )

        return

    if (
        args.convocatoria_id is None
        or args.descubrimiento_id is None
    ):
        raise SystemExit(
            "--aplicar-padre requiere "
            "--convocatoria-id y "
            "--descubrimiento-id."
        )

    aplicado = aplicar_padre_cerrado(
        args.convocatoria_id,
        args.descubrimiento_id,
        resultados,
    )

    print()
    print(
        "Padre actualizado:",
        aplicado,
    )

    if aplicado:
        print(
            "Convocatoria padre -> CERRADA"
        )
        print(
            "Descubrimiento -> PROCESADO"
        )
    else:
        print(
            "No se modificó el padre."
        )


if __name__ == "__main__":
    main()
