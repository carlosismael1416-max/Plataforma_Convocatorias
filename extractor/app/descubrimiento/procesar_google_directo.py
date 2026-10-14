from __future__ import annotations

import re

from urllib.parse import (
    parse_qsl,
    unquote,
    urlencode,
    urlsplit,
    urlunsplit,
)

from app.descubrimiento.resolver_google_news import (
    resolver_google_news,
)

from app.descubrimiento.analizar_articulo import (
    analizar_articulo,
)

from app.descubrimiento.buscar_html_ligero import (
    analizar_relevancia_tematica,
    analizar_temporalidad,
    normalizar_texto,
    puntuar_candidato,
)


ESTADOS_GOOGLE_VALIDOS = {
    "POSIBLE_CONVOCATORIA",
    "REVISION_MANUAL",
}


def normalizar_url(
    url: str | None,
) -> str:
    if not url:
        return ""

    try:
        partes = urlsplit(
            url.strip()
        )
    except Exception:
        return (
            url.strip()
            .rstrip("/")
        )

    query = []

    for clave, valor in parse_qsl(
        partes.query,
        keep_blank_values=True,
    ):
        clave_lower = (
            clave.lower()
        )

        if (
            clave_lower.startswith(
                "utm_"
            )
            or clave_lower in {
                "fbclid",
                "gclid",
                "mc_cid",
                "mc_eid",
            }
        ):
            continue

        query.append(
            (
                clave,
                valor,
            )
        )

    ruta = partes.path

    if ruta != "/":
        ruta = ruta.rstrip(
            "/"
        )

    return urlunsplit((
        partes.scheme.lower(),
        partes.netloc.lower(),
        ruta,
        urlencode(
            query,
            doseq=True,
        ),
        "",
    ))


def dominio_de(
    url: str,
) -> str:
    try:
        return (
            urlsplit(url)
            .netloc
            .lower()
            .removeprefix(
                "www."
            )
        )
    except Exception:
        return ""


def limpiar_titulo(
    titulo: str,
    fuente: str | None,
) -> str:
    titulo = (
        titulo
        or ""
    ).strip()

    titulo = titulo.lstrip(
        "» "
    )

    fuente = (
        fuente
        or ""
    ).strip()

    if fuente:
        titulo = re.sub(
            r"\s*-\s*"
            + re.escape(fuente)
            + r"\s*$",
            "",
            titulo,
            flags=re.I,
        ).strip()

    return titulo


# Palabras que no describen el contenido de un enlace
# ("edición 54", "Descargar PDF", "Consulta aquí las bases").
PALABRAS_ENLACE_GENERICO = {
    "aqui", "clic", "click", "descargar", "descarga",
    "consulta", "consultar", "consultala", "ver", "mas",
    "pdf", "enlace", "liga", "link", "documento", "archivo",
    "edicion", "leer", "bases", "pagina", "sitio", "nota",
    "convocatoria", "convocatorias",
}


def palabras_informativas(
    texto: str,
) -> int:
    return sum(
        1
        for palabra in normalizar_texto(texto).split()
        if len(palabra) >= 3
        and not palabra.isdigit()
        and palabra not in PALABRAS_ENLACE_GENERICO
    )


def titulo_desde_archivo(
    url: str,
) -> str:
    # Convocatoria_BEXT_ET_JICA_2026-20260728T005548.pdf
    # -> "Convocatoria BEXT ET JICA 2026"
    nombre = unquote(urlsplit(url).path).rsplit("/", 1)[-1]
    nombre = re.sub(r"\.pdf$", "", nombre, flags=re.I)
    nombre = re.sub(r"[-_]?\d{8}T\d{6}", "", nombre)
    nombre = re.sub(r"[_\-+]+", " ", nombre)

    return " ".join(nombre.split())


def elegir_titulo_candidato(
    texto_enlace: str,
    titulo_articulo: str,
    url: str,
) -> str:
    """
    Título para un enlace hallado dentro de una noticia: el
    texto del enlace solo si describe algo; si es genérico, el
    nombre del PDF o, en último caso, el título de la noticia.
    """

    texto_enlace = (texto_enlace or "").strip()

    if palabras_informativas(texto_enlace) >= 2:
        return texto_enlace

    if urlsplit(url).path.lower().endswith(".pdf"):
        desde_archivo = titulo_desde_archivo(url)

        if palabras_informativas(desde_archivo) >= 2:
            return desde_archivo

    return titulo_articulo or texto_enlace


def tipo_url(
    url: str,
    titulo: str,
) -> str:
    partes = urlsplit(
        url
    )

    ruta = (
        partes.path
        or "/"
    )

    texto = normalizar_texto(
        f"{titulo} {ruta}"
    )

    if ruta.casefold().endswith(
        ".pdf"
    ):
        return "PDF"

    if ruta in (
        "",
        "/",
    ):
        return "PORTAL_INSTITUCIONAL"

    ruta_limpia = (
        ruta.strip("/")
        .casefold()
    )

    if (
        ruta_limpia in {
            "convocatoria",
            "convocatorias",
        }
        or ruta_limpia.endswith(
            "/convocatorias"
        )
    ):
        return "PAGINA_INDICE"

    if any(
        expresion in texto
        for expresion in (
            "convocatorias vigentes",
            "proyectos y convocatorias",
            "convocatorias abiertas",
        )
    ):
        return "PAGINA_INDICE"

    return "PAGINA"


def construir_candidato(
    *,
    url: str,
    titulo: str,
    metodo: str,
    pagina_origen: str | None,
    estado_fuente: str,
    evidencia_google: dict,
) -> dict:
    relevancia = (
        analizar_relevancia_tematica(
            url,
            titulo,
        )
    )

    temporalidad = (
        analizar_temporalidad(
            url,
            titulo,
        )
    )

    puntaje, razones = (
        puntuar_candidato(
            url,
            titulo,
        )
    )

    return {
        "titulo":
            titulo,

        "url":
            url,

        "url_normalizada":
            normalizar_url(
                url
            ),

        "dominio":
            dominio_de(
                url
            ),

        "metodo_descubrimiento":
            metodo,

        "pagina_origen":
            pagina_origen,

        "tipo":
            tipo_url(
                url,
                titulo,
            ),

        "puntaje":
            puntaje,

        "relevancia_tematica":
            relevancia[
                "relevancia"
            ],

        "estado_temporal":
            temporalidad[
                "estado"
            ],

        "anios_detectados":
            temporalidad[
                "anios"
            ],

        "origen_anio":
            temporalidad[
                "origen_anio"
            ],

        "razones":
            razones,

        "estado_fuente":
            estado_fuente,

        "evidencia_google":
            evidencia_google,
    }


def candidato_util(
    candidato: dict,
) -> bool:
    if candidato[
        "estado_temporal"
    ] == "HISTORICA":
        return False

    if candidato[
        "relevancia_tematica"
    ] == "BAJA":
        return False

    if candidato[
        "puntaje"
    ] < 3:
        return False

    return True


def es_portal(
    url: str,
    texto: str,
) -> bool:
    tipo = tipo_url(
        url,
        texto,
    )

    return tipo in {
        "PORTAL_INSTITUCIONAL",
        "PAGINA_INDICE",
    }


def procesar_resultados_google(
    resultados_google: list[dict],
    max_resultados: int = 12,
) -> dict:
    seleccionados = [
        item
        for item in resultados_google
        if item.get(
            "estado_google"
        ) in ESTADOS_GOOGLE_VALIDOS
    ]

    seleccionados.sort(
        key=lambda item:
            item.get(
                "puntaje_google",
                0,
            ),
        reverse=True,
    )

    seleccionados = (
        seleccionados[
            :max_resultados
        ]
    )

    candidatos = []
    portales = []
    evidencias = []
    dominios_fallback = set()

    for numero, item in enumerate(
        seleccionados,
        start=1,
    ):
        titulo = limpiar_titulo(
            item.get(
                "titulo",
                ""
            ),
            item.get(
                "fuente_nombre"
            ),
        )

        evidencia = {
            "titulo_google":
                item.get(
                    "titulo"
                ),

            "titulo_limpio":
                titulo,

            "fuente_nombre":
                item.get(
                    "fuente_nombre"
                ),

            "dominio_google":
                item.get(
                    "dominio"
                ),

            "puntaje_google":
                item.get(
                    "puntaje_google"
                ),

            "estado_google":
                item.get(
                    "estado_google"
                ),

            "url_google":
                item.get(
                    "url_google"
                ),

            "decoder":
                None,

            "analisis_articulo":
                None,
        }

        print()
        print(
            f"Directo {numero}/"
            f"{len(seleccionados)}:"
        )

        print(
            titulo
        )

        resolucion = (
            resolver_google_news(
                item.get(
                    "url_google",
                    ""
                ),
                item.get(
                    "dominio"
                ),
                reintentos=3,
                usar_cache=True,
            )
        )

        evidencia[
            "decoder"
        ] = resolucion

        print(
            "  Decoder:",
            resolucion[
                "estado"
            ],
            (
                "(cache)"
                if resolucion.get(
                    "desde_cache"
                )
                else ""
            ),
        )

        if (
            resolucion[
                "estado"
            ] != "RESUELTO"
        ):
            dominio = item.get(
                "dominio"
            )

            if dominio:
                dominios_fallback.add(
                    dominio
                )

            evidencias.append(
                evidencia
            )

            continue

        url_original = resolucion[
            "url_original"
        ]

        articulo = analizar_articulo(
            url_original
        )

        evidencia[
            "url_original"
        ] = url_original

        evidencia[
            "analisis_articulo"
        ] = {
            "estado":
                articulo[
                    "estado"
                ],

            "publicacion_institucional":
                articulo[
                    "publicacion_institucional"
                ],

            "error":
                articulo.get(
                    "error"
                ),

            "total_enlaces_relevantes":
                len(
                    articulo[
                        "enlaces_relevantes"
                    ]
                ),
        }

        print(
            "  Artículo:",
            articulo[
                "estado"
            ],
        )

        print(
            "  Institucional:",
            articulo[
                "publicacion_institucional"
            ],
        )

        # ==================================
        # La propia publicación es
        # institucional.
        # ==================================

        if articulo[
            "publicacion_institucional"
        ]:
            estado_fuente = (
                "INSTITUCIONAL_DIRECTA"
                if articulo[
                    "estado"
                ] == "COMPLETADO"
                else
                "INSTITUCIONAL_PENDIENTE_DESCARGA"
            )

            candidato = (
                construir_candidato(
                    url=
                        url_original,

                    titulo=
                        titulo,

                    metodo=
                        "GOOGLE_NEWS_INSTITUCIONAL",

                    pagina_origen=
                        item.get(
                            "url_google"
                        ),

                    estado_fuente=
                        estado_fuente,

                    evidencia_google={
                        "titulo":
                            item.get(
                                "titulo"
                            ),

                        "fuente":
                            item.get(
                                "fuente_nombre"
                            ),

                        "puntaje":
                            item.get(
                                "puntaje_google"
                            ),
                    },
                )
            )

            if es_portal(
                url_original,
                titulo,
            ):
                portales.append({
                    "url":
                        url_original,

                    "dominio":
                        dominio_de(
                            url_original
                        ),

                    "titulo":
                        titulo,

                    "origen":
                        "GOOGLE_NEWS_INSTITUCIONAL",

                    "prioridad":
                        100
                        + int(
                            item.get(
                                "puntaje_google",
                                0,
                            )
                        ),
                })

            elif candidato_util(
                candidato
            ):
                candidatos.append(
                    candidato
                )

            evidencias.append(
                evidencia
            )

            continue

        # ==================================
        # Medio / sitio no institucional:
        # buscamos enlaces oficiales.
        # ==================================

        if articulo[
            "estado"
        ] != "COMPLETADO":
            dominio = item.get(
                "dominio"
            )

            if dominio:
                dominios_fallback.add(
                    dominio
                )

            evidencias.append(
                evidencia
            )

            continue

        institucionales = [
            enlace
            for enlace in articulo[
                "enlaces_relevantes"
            ]
            if enlace.get(
                "institucional"
            )
        ]

        if not institucionales:
            evidencias.append(
                evidencia
            )

            continue

        for enlace in institucionales:
            enlace_url = enlace[
                "url"
            ]

            texto_enlace = (
                enlace.get(
                    "texto"
                )
                or ""
            ).strip()

            # ==================================
            # ENLACE INSTITUCIONAL DÉBIL
            # ==================================
            #
            # Si el artículo contiene un enlace
            # institucional con texto vacío o
            # genérico, no heredamos automáticamente
            # el título completo de la noticia.
            #
            # Evita falsos positivos como:
            # /home
            # /contacto
            # portadas institucionales irrelevantes.

            relevancia_enlace = (
                analizar_relevancia_tematica(
                    enlace_url,
                    texto_enlace,
                )
            )

            if (
                len(
                    texto_enlace.strip()
                ) < 8
                and relevancia_enlace[
                    "relevancia"
                ] == "BAJA"
            ):
                continue

            if es_portal(
                enlace_url,
                texto_enlace,
            ):
                relevancia_portal = (
                    analizar_relevancia_tematica(
                        enlace_url,
                        texto_enlace,
                    )
                )

                # Un dominio institucional por
                # sí solo no es un portal de
                # convocatorias.
                #
                # Ejemplo de ruido:
                # comunicacionsocial.senado.gob.mx
                #
                # Ejemplo útil:
                # proyectos.secihti.mx
                if (
                    relevancia_portal[
                        "relevancia"
                    ] != "BAJA"
                ):
                    portales.append({
                        "url":
                            enlace_url,

                        "dominio":
                            enlace[
                                "dominio"
                            ],

                        "titulo":
                            (
                                texto_enlace
                                or titulo
                            ),

                        "origen":
                            "ENLACE_DESDE_NOTICIA",

                        "prioridad":
                            120
                            + enlace[
                                "puntaje"
                            ],
                    })

                continue

            titulo_candidato = (
                elegir_titulo_candidato(
                    texto_enlace,
                    titulo,
                    enlace_url,
                )
            )

            candidato = (
                construir_candidato(
                    url=
                        enlace_url,

                    titulo=
                        titulo_candidato,

                    metodo=
                        "ENLACE_INSTITUCIONAL_DESDE_NOTICIA",

                    pagina_origen=
                        url_original,

                    estado_fuente=
                        "INSTITUCIONAL_DESCUBIERTA_DESDE_NOTICIA",

                    evidencia_google={
                        "titulo":
                            item.get(
                                "titulo"
                            ),

                        "fuente":
                            item.get(
                                "fuente_nombre"
                            ),

                        "articulo":
                            url_original,

                        "puntaje_enlace":
                            enlace[
                                "puntaje"
                            ],
                    },
                )
            )

            if candidato_util(
                candidato
            ):
                candidatos.append(
                    candidato
                )

        evidencias.append(
            evidencia
        )

    # ----------------------------------
    # Deduplicar portales
    # ----------------------------------

    portales_unicos = {}

    for portal in portales:
        clave = normalizar_url(
            portal[
                "url"
            ]
        )

        anterior = (
            portales_unicos.get(
                clave
            )
        )

        if (
            anterior is None
            or portal[
                "prioridad"
            ] > anterior[
                "prioridad"
            ]
        ):
            portales_unicos[
                clave
            ] = portal

    portales = list(
        portales_unicos.values()
    )

    portales.sort(
        key=lambda item:
            item[
                "prioridad"
            ],
        reverse=True,
    )

    return {
        "procesados":
            len(
                seleccionados
            ),

        "candidatos":
            candidatos,

        "portales":
            portales,

        "evidencias":
            evidencias,

        "dominios_fallback":
            sorted(
                dominios_fallback
            ),
    }
