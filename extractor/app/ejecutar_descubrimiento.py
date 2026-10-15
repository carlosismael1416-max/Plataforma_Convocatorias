from __future__ import annotations

import argparse
import json
import os
import re
import time

from datetime import datetime
from pathlib import Path
from urllib.parse import (
    parse_qsl,
    urlencode,
    urlsplit,
    urlunsplit,
)

from dotenv import load_dotenv

from app.descubrimiento.buscar_google_news import (
    GoogleNewsError,
    buscar_google_news,
)

from app.descubrimiento.clasificar_google_news import (
    clasificar_google_news,
    normalizar_texto,
)

from app.descubrimiento.buscar_sitemaps import (
    buscar_sitemaps,
)

from app.descubrimiento.buscar_wordpress import (
    buscar_wordpress,
)

from app.descubrimiento.buscar_html_ligero import (
    analizar_relevancia_tematica,
    analizar_temporalidad,
    buscar_html_ligero,
    puntuar_candidato,
)

from app.descubrimiento.procesar_google_directo import (
    procesar_resultados_google,
)


from app.descubrimiento.clasificar_objetivo import (
    clasificar_objetivo_convocatoria,
)


from app.descubrimiento.guardar_descubrimientos import (
    guardar_descubrimientos,
)


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parent
    .parent
)

load_dotenv(
    BASE_DIR / ".env"
)


CONSULTAS_GOOGLE = [
    (
        '"convocatoria" '
        '"proyectos de investigación" '
        'México'
    ),
    (
        '"convocatoria" '
        'financiamiento investigación '
        'México'
    ),
    (
        '"convocatoria" '
        'ciencia tecnología innovación '
        'México'
    ),
    (
        '"fondo" investigación '
        'México convocatoria'
    ),
    (
        '"beca de investigación" '
        'México convocatoria'
    ),
]


PARAMETROS_TRACKING = {
    "fbclid",
    "gclid",
    "mc_cid",
    "mc_eid",
}


def normalizar_url(
    url: str | None,
) -> str:
    if not url:
        return ""

    url = url.strip()

    try:
        partes = urlsplit(
            url
        )
    except Exception:
        return url.rstrip("/")

    query = []

    for clave, valor in parse_qsl(
        partes.query,
        keep_blank_values=True,
    ):
        clave_lower = clave.lower()

        if (
            clave_lower.startswith(
                "utm_"
            )
            or clave_lower
            in PARAMETROS_TRACKING
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
        ruta = ruta.rstrip("/")

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


def base_desde_resultado(
    resultado: dict,
) -> str | None:
    fuente_url = (
        resultado.get(
            "fuente_url"
        )
        or ""
    ).strip()

    if fuente_url.startswith(
        (
            "http://",
            "https://",
        )
    ):
        return fuente_url

    dominio = (
        resultado.get(
            "dominio"
        )
        or ""
    ).strip()

    if not dominio:
        return None

    return (
        "https://"
        + dominio
        + "/"
    )


def dominio_de_url(
    url: str,
) -> str:
    try:
        return (
            urlsplit(url)
            .netloc
            .lower()
            .removeprefix("www.")
        )
    except Exception:
        return ""


def tipo_candidato(
    url: str,
    titulo: str,
) -> str:
    texto = normalizar_texto(
        f"{url} {titulo}"
    )

    ruta = (
        urlsplit(url)
        .path
        .casefold()
    )

    if ruta.endswith(".pdf"):
        return "PDF"

    if any(
        termino in texto
        for termino in (
            "convocatorias-vigentes",
            "convocatorias vigentes",
            "proyectos y convocatorias",
        )
    ):
        return "PAGINA_INDICE"

    return "PAGINA"


def es_error_temporal(
    texto: str,
) -> bool:
    texto = (
        texto
        or ""
    ).casefold()

    return any(
        patron in texto
        for patron in (
            "timed out",
            "timeout",
            "connecttimeouterror",
            "max retries exceeded",
            "connection refused",
            "temporary failure",
        )
    )


def clasificar_url_descubierta(
    *,
    url: str,
    titulo: str,
    metodo: str,
    pagina_origen: str | None = None,
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
            dominio_de_url(
                url
            ),

        "metodo_descubrimiento":
            metodo,

        "pagina_origen":
            pagina_origen,

        "tipo":
            tipo_candidato(
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


def cargar_urls_postgresql() -> dict:
    """
    Consulta PostgreSQL en una transacción
    READ ONLY.

    No modifica ninguna tabla.
    """

    host = os.getenv(
        "DB_HOST",
        "127.0.0.1",
    )

    port = os.getenv(
        "DB_PORT",
        "5432",
    )

    database = (
        os.getenv(
            "DB_DATABASE"
        )
        or os.getenv(
            "DB_NAME"
        )
    )

    user = (
        os.getenv(
            "DB_USERNAME"
        )
        or os.getenv(
            "DB_USER"
        )
    )

    password = (
        os.getenv(
            "DB_PASSWORD"
        )
        or os.getenv(
            "DB_PASS"
        )
    )

    if not (
        database
        and user
    ):
        return {
            "ok": False,
            "error": (
                "No se encontraron las "
                "variables de conexión "
                "PostgreSQL."
            ),
            "urls": {},
        }

    modulo = None

    try:
        import psycopg as modulo

        conexion = modulo.connect(
            host=host,
            port=port,
            dbname=database,
            user=user,
            password=password,
        )

    except ImportError:
        try:
            import psycopg2 as modulo

            conexion = modulo.connect(
                host=host,
                port=port,
                dbname=database,
                user=user,
                password=password,
            )

        except ImportError:
            return {
                "ok": False,
                "error": (
                    "No está instalado "
                    "psycopg ni psycopg2."
                ),
                "urls": {},
            }

        except Exception as error:
            return {
                "ok": False,
                "error": str(error),
                "urls": {},
            }

    except Exception as error:
        return {
            "ok": False,
            "error": str(error),
            "urls": {},
        }

    existentes = {}

    try:
        cursor = conexion.cursor()

        # Primera sentencia de la transacción.
        cursor.execute(
            "SET TRANSACTION READ ONLY"
        )

        cursor.execute(
            """
            SELECT
                id,
                titulo,
                url_original
            FROM convocatorias
            WHERE url_original IS NOT NULL
              AND BTRIM(url_original) <> ''
            """
        )

        for (
            convocatoria_id,
            titulo,
            url,
        ) in cursor.fetchall():

            normalizada = (
                normalizar_url(
                    url
                )
            )

            if not normalizada:
                continue

            existentes[
                normalizada
            ] = {
                "id":
                    convocatoria_id,

                "titulo":
                    titulo,

                "url_original":
                    url,
            }

        conexion.rollback()

        cursor.close()
        conexion.close()

        return {
            "ok": True,
            "error": None,
            "urls": existentes,
        }

    except Exception as error:
        try:
            conexion.rollback()
            conexion.close()
        except Exception:
            pass

        return {
            "ok": False,
            "error": str(error),
            "urls": {},
        }


def descubrir_google(
    cantidad: int,
) -> dict:
    candidatos = {}

    errores = []

    print()
    print("=" * 70)
    print("1. GOOGLE NEWS RSS")
    print("=" * 70)

    for numero, consulta in enumerate(
        CONSULTAS_GOOGLE,
        start=1,
    ):
        print()
        print(
            f"[{numero}/"
            f"{len(CONSULTAS_GOOGLE)}]"
        )

        print(
            consulta
        )

        try:
            resultados = (
                buscar_google_news(
                    consulta=consulta,
                    cantidad=cantidad,
                )
            )

        except GoogleNewsError as error:
            print(
                "ERROR:",
                error,
            )

            errores.append({
                "consulta":
                    consulta,

                "error":
                    str(error),
            })

            continue

        print(
            "Resultados:",
            len(resultados),
        )

        for resultado in resultados:
            clasificacion = (
                clasificar_google_news(
                    titulo=resultado[
                        "titulo"
                    ],
                    fuente_nombre=resultado[
                        "fuente_nombre"
                    ],
                    dominio=resultado[
                        "dominio"
                    ],
                    fecha_publicacion=resultado[
                        "fecha_publicacion"
                    ],
                )
            )

            candidato = {
                **resultado,

                "consulta":
                    consulta,

                "puntaje_google":
                    clasificacion[
                        "puntaje"
                    ],

                "estado_google":
                    clasificacion[
                        "estado"
                    ],

                "razones_google":
                    clasificacion[
                        "razones"
                    ],

                "dias_desde_publicacion":
                    clasificacion.get(
                        "dias_desde_publicacion"
                    ),
            }

            clave = (
                normalizar_texto(
                    candidato[
                        "dominio"
                    ]
                )
                + "|"
                + normalizar_texto(
                    candidato[
                        "titulo"
                    ]
                )
            )

            existente = (
                candidatos.get(
                    clave
                )
            )

            if (
                existente is None
                or candidato[
                    "puntaje_google"
                ]
                > existente[
                    "puntaje_google"
                ]
            ):
                candidatos[
                    clave
                ] = candidato

        # Pausa ligera.
        if (
            numero
            < len(
                CONSULTAS_GOOGLE
            )
        ):
            time.sleep(1.5)

    lista = list(
        candidatos.values()
    )

    lista.sort(
        key=lambda item: (
            item[
                "puntaje_google"
            ],
            item[
                "fecha_publicacion"
            ]
            or "",
        ),
        reverse=True,
    )

    return {
        "resultados":
            lista,

        "errores":
            errores,
    }


def extraer_sitemap(
    base_url: str,
) -> dict:
    try:
        resultado = buscar_sitemaps(
            base_url,
            max_sitemaps=6,
            max_urls=700,
        )

    except Exception as error:
        return {
            "estado":
                "ERROR",

            "error":
                str(error),

            "candidatos":
                [],
        }

    errores = (
        resultado.get(
            "errores"
        )
        or []
    )

    if (
        not resultado[
            "sitemaps_procesados"
        ]
        and errores
        and all(
            es_error_temporal(
                str(
                    error.get(
                        "error",
                        ""
                    )
                )
            )
            for error in errores
        )
    ):
        return {
            "estado":
                "ERROR_TEMPORAL",

            "error":
                errores[0][
                    "error"
                ],

            "candidatos":
                [],
        }

    candidatos = []

    for url in resultado[
        "urls_candidatas"
    ]:
        candidato = (
            clasificar_url_descubierta(
                url=url,
                titulo="",
                metodo="SITEMAP",
                pagina_origen=None,
            )
        )

        if candidato_util(
            candidato
        ):
            candidatos.append(
                candidato
            )

    return {
        "estado":
            "COMPLETADO",

        "error":
            None,

        "candidatos":
            candidatos,

        "detalle":
            resultado,
    }


def extraer_wordpress(
    base_url: str,
) -> dict:
    try:
        resultado = buscar_wordpress(
            url_base=base_url,
            termino="convocatoria",
            cantidad=15,
        )

    except Exception as error:
        return {
            "estado":
                "ERROR",

            "error":
                str(error),

            "candidatos":
                [],
        }

    if (
        not resultado[
            "wordpress"
        ]
    ):
        error = (
            resultado.get(
                "error"
            )
            or ""
        )

        return {
            "estado":
                (
                    "ERROR_TEMPORAL"
                    if es_error_temporal(
                        error
                    )
                    else "NO_DISPONIBLE"
                ),

            "error":
                error,

            "candidatos":
                [],
        }

    candidatos = []

    for item in resultado[
        "resultados"
    ]:
        candidato = (
            clasificar_url_descubierta(
                url=item[
                    "url"
                ],
                titulo=item[
                    "titulo"
                ],
                metodo=
                    "WORDPRESS_REST_API",
                pagina_origen=None,
            )
        )

        candidato[
            "fecha_publicacion"
        ] = item.get(
            "fecha"
        )

        if candidato_util(
            candidato
        ):
            candidatos.append(
                candidato
            )

    return {
        "estado":
            "COMPLETADO",

        "error":
            None,

        "candidatos":
            candidatos,

        "detalle":
            resultado,
    }


def extraer_html(
    base_url: str,
) -> dict:
    try:
        resultado = (
            buscar_html_ligero(
                url_base=base_url,
                max_paginas=12,
                max_profundidad=2,
                max_candidatas=40,
            )
        )

    except Exception as error:
        return {
            "estado":
                "ERROR",

            "error":
                str(error),

            "candidatos":
                [],
        }

    if resultado[
        "estado"
    ] != "COMPLETADO":
        return {
            "estado":
                resultado[
                    "estado"
                ],

            "error":
                resultado[
                    "robots"
                ].get(
                    "error"
                ),

            "candidatos":
                [],
        }

    candidatos = []

    for item in resultado[
        "candidatas"
    ]:
        if (
            item.get(
                "estado_temporal"
            )
            == "HISTORICA"
        ):
            continue

        if (
            item.get(
                "relevancia_tematica"
            )
            == "BAJA"
        ):
            continue

        candidato = {
            "titulo":
                item[
                    "titulo_enlace"
                ],

            "url":
                item[
                    "url"
                ],

            "url_normalizada":
                normalizar_url(
                    item[
                        "url"
                    ]
                ),

            "dominio":
                dominio_de_url(
                    item[
                        "url"
                    ]
                ),

            "metodo_descubrimiento":
                "HTML_LIGERO",

            "pagina_origen":
                item[
                    "pagina_origen"
                ],

            "tipo":
                tipo_candidato(
                    item[
                        "url"
                    ],
                    item[
                        "titulo_enlace"
                    ],
                ),

            "puntaje":
                item[
                    "puntaje"
                ],

            "relevancia_tematica":
                item[
                    "relevancia_tematica"
                ],

            "estado_temporal":
                item[
                    "estado_temporal"
                ],

            "anios_detectados":
                item[
                    "anios_detectados"
                ],

            "origen_anio":
                item.get(
                    "origen_anio"
                ),

            "razones":
                item[
                    "razones"
                ],
        }

        candidatos.append(
            candidato
        )

    return {
        "estado":
            "COMPLETADO",

        "error":
            None,

        "candidatos":
            candidatos,

        "detalle":
            {
                "paginas_visitadas":
                    resultado[
                        "paginas_visitadas"
                    ],

                "paginas_descargadas":
                    resultado[
                        "paginas_descargadas"
                    ],

                "total_candidatas":
                    resultado[
                        "total_candidatas"
                    ],

                "errores":
                    resultado[
                        "errores"
                    ],
            },
    }


def explorar_dominio(
    dominio: dict,
) -> dict:
    base_url = dominio[
        "base_url"
    ]

    print()
    print(
        "-" * 70
    )

    print(
        dominio[
            "dominio"
        ]
    )

    print(
        "Base:",
        base_url,
    )

    resultado = {
        "dominio":
            dominio[
                "dominio"
            ],

        "base_url":
            base_url,

        "titulo_origen_google":
            dominio[
                "titulo_origen"
            ],

        "metodo_utilizado":
            None,

        "estado":
            None,

        "intentos":
            [],

        "candidatos":
            [],
    }

    # ----------------------------------
    # 1. Sitemap
    # ----------------------------------

    sitemap = extraer_sitemap(
        base_url
    )

    resultado[
        "intentos"
    ].append({
        "metodo":
            "SITEMAP",

        "estado":
            sitemap[
                "estado"
            ],

        "error":
            sitemap.get(
                "error"
            ),

        "candidatos":
            len(
                sitemap[
                    "candidatos"
                ]
            ),
    })

    print(
        "Sitemap:",
        sitemap[
            "estado"
        ],
        "- candidatos:",
        len(
            sitemap[
                "candidatos"
            ]
        ),
    )

    if sitemap[
        "estado"
    ] == "ERROR_TEMPORAL":
        resultado[
            "estado"
        ] = "ERROR_TEMPORAL"

        return resultado

    if sitemap[
        "candidatos"
    ]:
        resultado[
            "metodo_utilizado"
        ] = "SITEMAP"

        resultado[
            "estado"
        ] = "COMPLETADO"

        resultado[
            "candidatos"
        ] = sitemap[
            "candidatos"
        ]

        return resultado

    # ----------------------------------
    # 2. WordPress
    # ----------------------------------

    wordpress = (
        extraer_wordpress(
            base_url
        )
    )

    resultado[
        "intentos"
    ].append({
        "metodo":
            "WORDPRESS_REST_API",

        "estado":
            wordpress[
                "estado"
            ],

        "error":
            wordpress.get(
                "error"
            ),

        "candidatos":
            len(
                wordpress[
                    "candidatos"
                ]
            ),
    })

    print(
        "WordPress:",
        wordpress[
            "estado"
        ],
        "- candidatos:",
        len(
            wordpress[
                "candidatos"
            ]
        ),
    )

    if wordpress[
        "estado"
    ] == "ERROR_TEMPORAL":
        resultado[
            "estado"
        ] = "ERROR_TEMPORAL"

        return resultado

    if wordpress[
        "candidatos"
    ]:
        resultado[
            "metodo_utilizado"
        ] = (
            "WORDPRESS_REST_API"
        )

        resultado[
            "estado"
        ] = "COMPLETADO"

        resultado[
            "candidatos"
        ] = wordpress[
            "candidatos"
        ]

        return resultado

    # ----------------------------------
    # 3. HTML ligero
    # ----------------------------------

    html = extraer_html(
        base_url
    )

    resultado[
        "intentos"
    ].append({
        "metodo":
            "HTML_LIGERO",

        "estado":
            html[
                "estado"
            ],

        "error":
            html.get(
                "error"
            ),

        "candidatos":
            len(
                html[
                    "candidatos"
                ]
            ),
    })

    print(
        "HTML:",
        html[
            "estado"
        ],
        "- candidatos:",
        len(
            html[
                "candidatos"
            ]
        ),
    )

    resultado[
        "metodo_utilizado"
    ] = (
        "HTML_LIGERO"
        if html[
            "estado"
        ] == "COMPLETADO"
        else None
    )

    resultado[
        "estado"
    ] = html[
        "estado"
    ]

    resultado[
        "candidatos"
    ] = html[
        "candidatos"
    ]

    return resultado


def deduplicar_candidatos(
    dominios_explorados: list[dict],
) -> list[dict]:
    encontrados = {}

    prioridad_metodo = {
        "GOOGLE_NEWS_INSTITUCIONAL": 6,
        "ENLACE_INSTITUCIONAL_DESDE_NOTICIA": 5,
        "HTML_LIGERO": 3,
        "WORDPRESS_REST_API": 2,
        "SITEMAP": 1,
    }

    for dominio in dominios_explorados:
        for candidato in dominio[
            "candidatos"
        ]:
            clave = candidato[
                "url_normalizada"
            ]

            if not clave:
                continue

            existente = (
                encontrados.get(
                    clave
                )
            )

            if existente is None:
                encontrados[
                    clave
                ] = candidato

                continue

            puntaje_nuevo = candidato[
                "puntaje"
            ]

            puntaje_anterior = existente[
                "puntaje"
            ]

            if (
                puntaje_nuevo
                > puntaje_anterior
            ):
                encontrados[
                    clave
                ] = candidato

                continue

            if (
                puntaje_nuevo
                == puntaje_anterior
                and prioridad_metodo.get(
                    candidato[
                        "metodo_descubrimiento"
                    ],
                    0,
                )
                > prioridad_metodo.get(
                    existente[
                        "metodo_descubrimiento"
                    ],
                    0,
                )
            ):
                encontrados[
                    clave
                ] = candidato

    lista = list(
        encontrados.values()
    )

    lista.sort(
        key=lambda item: (
            (
                1
                if item[
                    "estado_temporal"
                ] == "ACTUAL"
                else 0
            ),
            (
                1
                if item[
                    "estado_temporal"
                ] == "FUTURA"
                else 0
            ),
            (
                1
                if item[
                    "relevancia_tematica"
                ] == "ALTA"
                else 0
            ),
            item[
                "puntaje"
            ],
        ),
        reverse=True,
    )

    return lista


def comparar_postgresql(
    candidatos: list[dict],
    existentes: dict,
) -> None:
    urls_db = existentes[
        "urls"
    ]

    for candidato in candidatos:
        normalizada = candidato[
            "url_normalizada"
        ]

        registro = (
            urls_db.get(
                normalizada
            )
        )

        if registro:
            candidato[
                "estado_db"
            ] = "YA_EXISTE"

            candidato[
                "convocatoria_existente"
            ] = registro

        else:
            candidato[
                "estado_db"
            ] = "NUEVA"

            candidato[
                "convocatoria_existente"
            ] = None


def ejecutar(
    *,
    cantidad_google: int,
    max_dominios: int,
    guardar: bool = False,
) -> None:
    print(
        "=" * 70
    )
    print(
        "BOT DESCUBRIDOR ITSVA"
    )
    print(
        (
            "MODO: GUARDAR DESCUBRIMIENTOS"
            if guardar
            else
            "MODO: VISTA PREVIA / SOLO LECTURA"
        )
    )
    print(
        "=" * 70
    )

    # ==================================
    # 1. Google News
    # ==================================

    google = descubrir_google(
        cantidad_google
    )

    resultados_google = google[
        "resultados"
    ]

    posibles_google = [
        item
        for item in resultados_google
        if item[
            "estado_google"
        ] == "POSIBLE_CONVOCATORIA"
    ]

    revision_google = [
        item
        for item in resultados_google
        if item[
            "estado_google"
        ] == "REVISION_MANUAL"
    ]

    print()
    print(
        "Google únicos:",
        len(
            resultados_google
        ),
    )
    print(
        "Posibles:",
        len(
            posibles_google
        ),
    )
    print(
        "Revisión:",
        len(
            revision_google
        ),
    )

    # ==================================
    # 2. Ruta directa Google News
    # ==================================

    print()
    print(
        "=" * 70
    )
    print(
        "2. RESOLUCIÓN DIRECTA GOOGLE NEWS"
    )
    print(
        "=" * 70
    )

    directo = procesar_resultados_google(
        resultados_google,
        max_resultados=max(
            8,
            max_dominios * 2,
        ),
    )

    print()
    print(
        "Resultados procesados:",
        directo[
            "procesados"
        ],
    )
    print(
        "Candidatos institucionales:",
        len(
            directo[
                "candidatos"
            ]
        ),
    )
    print(
        "Portales institucionales:",
        len(
            directo[
                "portales"
            ]
        ),
    )
    print(
        "Dominios fallback:",
        len(
            directo[
                "dominios_fallback"
            ]
        ),
    )

    if directo[
        "portales"
    ]:
        print()
        print(
            "Portales descubiertos:"
        )

        for portal in directo[
            "portales"
        ]:
            print(
                " -",
                portal[
                    "url"
                ],
            )

    # ==================================
    # 3. Construir fallback
    # ==================================

    objetivos = []

    # Solo se exploran portales institucionales
    # mexicanos: los portales extranjeros y los
    # sitios de noticias generaban candidatos que
    # el clasificador terminaba descartando.
    for portal in directo[
        "portales"
    ]:
        if not str(
            portal["dominio"]
        ).endswith(".mx"):
            continue

        objetivos.append({
            "dominio":
                portal[
                    "dominio"
                ],

            "base_url":
                portal[
                    "url"
                ],

            "prioridad":
                portal[
                    "prioridad"
                ],

            "titulo_origen":
                portal[
                    "titulo"
                ],

            "puntaje_google":
                portal[
                    "prioridad"
                ],
        })

    # Ya no se rastrean los sitios de noticias
    # (dominios_fallback): sus sitemaps solo
    # aportaban notas que no eran convocatorias.
    # Las de las fuentes oficiales se obtienen en
    # la fase de fuentes del ciclo.

    # Deduplicar por URL base.
    objetivos_unicos = {}

    for objetivo in objetivos:
        clave = normalizar_url(
            objetivo[
                "base_url"
            ]
        )

        anterior = (
            objetivos_unicos.get(
                clave
            )
        )

        if (
            anterior is None
            or objetivo.get(
                "prioridad",
                0,
            )
            > anterior.get(
                "prioridad",
                0,
            )
        ):
            objetivos_unicos[
                clave
            ] = objetivo

    objetivos = list(
        objetivos_unicos.values()
    )

    objetivos.sort(
        key=lambda item:
            item.get(
                "prioridad",
                0,
            ),
        reverse=True,
    )

    objetivos = objetivos[
        :max_dominios
    ]

    print()
    print(
        "=" * 70
    )
    print(
        "3. EXPLORACIÓN FALLBACK / PORTALES"
    )
    print(
        "=" * 70
    )

    if not objetivos:
        print(
            "No se requiere exploración "
            "adicional."
        )

    else:
        for numero, objetivo in enumerate(
            objetivos,
            start=1,
        ):
            print(
                f"{numero}. "
                f"{objetivo['base_url']}"
            )

    explorados = []

    for objetivo in objetivos:
        resultado = explorar_dominio(
            objetivo
        )

        explorados.append(
            resultado
        )

    # ==================================
    # 4. Unificar
    # ==================================

    origen_directo = {
        "dominio":
            "GOOGLE_NEWS_DIRECTO",

        "base_url":
            None,

        "titulo_origen_google":
            None,

        "metodo_utilizado":
            "GOOGLE_NEWS_DIRECTO",

        "estado":
            "COMPLETADO",

        "intentos":
            [],

        "candidatos":
            directo[
                "candidatos"
            ],
    }

    candidatos = (
        deduplicar_candidatos(
            [
                origen_directo,
                *explorados,
            ]
        )
    )

    # ==================================
    # Clasificación por objetivo ITSVA
    # ==================================

    for candidato in candidatos:
        objetivo = (
            clasificar_objetivo_convocatoria(
                candidato.get(
                    "titulo",
                    ""
                ),
                candidato.get(
                    "url",
                    ""
                ),
                # pop: el texto no se guarda ni va a la vista previa.
                contexto=candidato.pop(
                    "contexto",
                    "",
                ),
            )
        )

        candidato.update(
            objetivo
        )

    # ==================================
    # 5. PostgreSQL READ ONLY
    # ==================================

    print()
    print(
        "=" * 70
    )
    print(
        "4. COMPARACIÓN POSTGRESQL"
    )
    print(
        "=" * 70
    )

    existentes = (
        cargar_urls_postgresql()
    )

    if existentes[
        "ok"
    ]:
        print(
            "Convocatorias con URL "
            "en PostgreSQL:",
            len(
                existentes[
                    "urls"
                ]
            ),
        )

        comparar_postgresql(
            candidatos,
            existentes,
        )

    else:
        print(
            "ADVERTENCIA:",
            existentes[
                "error"
            ],
        )

        for candidato in candidatos:
            candidato[
                "estado_db"
            ] = "NO_COMPARADO"

            candidato[
                "convocatoria_existente"
            ] = None

    nuevas = [
        item
        for item in candidatos
        if item[
            "estado_db"
        ] == "NUEVA"
    ]

    ya_existen = [
        item
        for item in candidatos
        if item[
            "estado_db"
        ] == "YA_EXISTE"
    ]

    alta_nueva = [
        item
        for item in nuevas
        if (
            item[
                "relevancia_tematica"
            ] == "ALTA"
            and item[
                "estado_temporal"
            ] in (
                "ACTUAL",
                "FUTURA",
            )
            and item.get(
                "decision_objetivo"
            ) == "ACEPTAR"
            and item[
                "tipo"
            ] not in (
                "PAGINA_INDICE",
                "PORTAL_INSTITUCIONAL",
            )
        )
    ]

    pendientes_descarga = [
        item
        for item in candidatos
        if item.get(
            "estado_fuente"
        )
        == "INSTITUCIONAL_PENDIENTE_DESCARGA"
    ]

    revision_objetivo = [
        item
        for item in nuevas
        if item.get(
            "decision_objetivo"
        ) == "REVISION"
    ]

    descartados_objetivo = [
        item
        for item in nuevas
        if item.get(
            "decision_objetivo"
        ) == "DESCARTAR"
    ]

    # ==================================
    # 5B. Persistencia opcional
    # ==================================

    resultado_guardado = {
        "ejecutado": False,
        "recibidos": 0,
        "insertados": 0,
        "actualizados": 0,
        "errores": [],
        "registros": [],
    }

    if guardar:
        print()
        print(
            "=" * 70
        )
        print(
            "5. GUARDADO EN descubrimientos_web"
        )
        print(
            "=" * 70
        )

        # Guardamos los descubrimientos nuevos.
        #
        # YA_EXISTE significa que la URL ya se
        # encuentra en convocatorias; no tiene
        # sentido crear un descubrimiento nuevo.
        para_guardar = [
            item
            for item in candidatos
            if item.get(
                "estado_db"
            ) == "NUEVA"
        ]

        resultado_guardado = (
            guardar_descubrimientos(
                para_guardar
            )
        )

        resultado_guardado[
            "ejecutado"
        ] = True

        print(
            "Recibidos:",
            resultado_guardado[
                "recibidos"
            ],
        )
        print(
            "Insertados:",
            resultado_guardado[
                "insertados"
            ],
        )
        print(
            "Actualizados:",
            resultado_guardado[
                "actualizados"
            ],
        )
        print(
            "Errores:",
            len(
                resultado_guardado[
                    "errores"
                ]
            ),
        )

    # ==================================
    # 6. JSON
    # ==================================

    salida = {
        "generado_en":
            datetime.now()
            .astimezone()
            .isoformat(),

        "modo":
            (
                "GUARDAR_DESCUBRIMIENTOS"
                if guardar
                else
                "VISTA_PREVIA_SOLO_LECTURA"
            ),

        "motor": {
            "google_news":
                True,

            "google_news_decoder":
                True,

            "cache_decoder":
                True,

            "analisis_articulo":
                True,

            "sitemap_fallback":
                True,

            "wordpress_fallback":
                True,

            "html_ligero_fallback":
                True,

            "gdelt":
                False,
        },

        "resumen": {
            "resultados_google":
                len(
                    resultados_google
                ),

            "posibles_google":
                len(
                    posibles_google
                ),

            "revision_google":
                len(
                    revision_google
                ),

            "google_directos_procesados":
                directo[
                    "procesados"
                ],

            "candidatos_directos":
                len(
                    directo[
                        "candidatos"
                    ]
                ),

            "portales_institucionales":
                len(
                    directo[
                        "portales"
                    ]
                ),

            "dominios_fallback":
                len(
                    directo[
                        "dominios_fallback"
                    ]
                ),

            "objetivos_explorados":
                len(
                    explorados
                ),

            "candidatos_unicos":
                len(
                    candidatos
                ),

            "nuevos":
                len(
                    nuevas
                ),

            "ya_existen":
                len(
                    ya_existen
                ),

            "alta_actual_futura_nueva":
                len(
                    alta_nueva
                ),

            "pendientes_descarga":
                len(
                    pendientes_descarga
                ),

            "revision_objetivo":
                len(
                    revision_objetivo
                ),

            "descartados_objetivo":
                len(
                    descartados_objetivo
                ),

            "errores_google":
                len(
                    google[
                        "errores"
                    ]
                ),
        },

        "postgresql": {
            "comparacion_ok":
                existentes[
                    "ok"
                ],

            "error":
                existentes[
                    "error"
                ],
        },

        "guardado_descubrimientos":
            resultado_guardado,

        "google_news":
            resultados_google,

        "google_directo":
            directo,

        "exploracion_fallback":
            explorados,

        "candidatos":
            candidatos,
    }

    archivo = (
        BASE_DIR
        / "descubrimiento_vista_previa.json"
    )

    archivo.write_text(
        json.dumps(
            salida,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    # ==================================
    # 7. Terminal
    # ==================================

    print()
    print(
        "=" * 70
    )
    print(
        "RESUMEN FINAL"
    )
    print(
        "=" * 70
    )

    print(
        "Google News:",
        len(
            resultados_google
        ),
    )
    print(
        "Directos procesados:",
        directo[
            "procesados"
        ],
    )
    print(
        "Candidatos directos:",
        len(
            directo[
                "candidatos"
            ]
        ),
    )
    print(
        "Portales institucionales:",
        len(
            directo[
                "portales"
            ]
        ),
    )
    print(
        "Fallback explorados:",
        len(
            explorados
        ),
    )
    print(
        "Candidatos únicos:",
        len(
            candidatos
        ),
    )
    print(
        "Nuevos:",
        len(
            nuevas
        ),
    )
    print(
        "Ya existentes:",
        len(
            ya_existen
        ),
    )
    print(
        "Pendientes de descarga:",
        len(
            pendientes_descarga
        ),
    )
    print(
        "Revisión por objetivo:",
        len(
            revision_objetivo
        ),
    )
    print(
        "Descartadas por objetivo:",
        len(
            descartados_objetivo
        ),
    )
    print(
        "ACEPTADAS + ALTA + ACTUAL/FUTURA + NUEVA:",
        len(
            alta_nueva
        ),
    )
    print(
        "Archivo:",
        archivo,
    )

    print()
    print(
        "=" * 70
    )
    print(
        "MEJORES CANDIDATOS NUEVOS"
    )
    print(
        "=" * 70
    )

    if not alta_nueva:
        print(
            "No hubo candidatos ALTA "
            "actuales/futuros nuevos "
            "en este ciclo."
        )

    for numero, item in enumerate(
        alta_nueva[:20],
        start=1,
    ):
        print()
        print(
            f"{numero}. "
            f"[{item['puntaje']}] "
            f"[{item['estado_temporal']}] "
            f"[{item['metodo_descubrimiento']}]"
        )

        print(
            "Título:",
            item[
                "titulo"
            ],
        )
        print(
            "Estado fuente:",
            item.get(
                "estado_fuente"
            ),
        )
        print(
            "Categoría objetivo:",
            item.get(
                "categoria_objetivo"
            ),
        )
        print(
            "Decisión objetivo:",
            item.get(
                "decision_objetivo"
            ),
        )
        print(
            "Tipo:",
            item[
                "tipo"
            ],
        )
        print(
            "Dominio:",
            item[
                "dominio"
            ],
        )
        print(
            "URL:",
            item[
                "url"
            ],
        )
        print(
            "Estado DB:",
            item[
                "estado_db"
            ],
        )
        print(
            "Razones:",
            ", ".join(
                item[
                    "razones"
                ]
            ),
        )

    print()
    print(
        "IMPORTANTE:"
    )

    if guardar:
        print(
            "Se guardaron únicamente "
            "descubrimientos en la tabla "
            "descubrimientos_web."
        )
        print(
            "La tabla convocatorias NO "
            "fue insertada, actualizada "
            "ni eliminada por este proceso."
        )
    else:
        print(
            "Esta ejecución NO insertó, "
            "actualizó ni eliminó registros "
            "en PostgreSQL."
        )


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Bot Descubridor ITSVA. "
            "Descubre convocatorias y "
            "compara con PostgreSQL "
            "sin modificar la base."
        )
    )

    parser.add_argument(
        "--cantidad-google",
        type=int,
        default=8,
        help=(
            "Resultados máximos por "
            "consulta Google News."
        ),
    )

    parser.add_argument(
        "--max-dominios",
        type=int,
        default=6,
        help=(
            "Número máximo de dominios "
            "a explorar directamente."
        ),
    )


    parser.add_argument(
        "--guardar",
        action="store_true",
        help=(
            "Guarda o actualiza los "
            "resultados únicamente en "
            "descubrimientos_web. "
            "No modifica convocatorias."
        ),
    )


    argumentos = (
        parser.parse_args()
    )

    if not (
        1
        <= argumentos.cantidad_google
        <= 30
    ):
        parser.error(
            "--cantidad-google debe estar "
            "entre 1 y 30."
        )

    if not (
        1
        <= argumentos.max_dominios
        <= 20
    ):
        parser.error(
            "--max-dominios debe estar "
            "entre 1 y 20."
        )

    ejecutar(
        cantidad_google=
            argumentos.cantidad_google,

        max_dominios=
            argumentos.max_dominios,

        guardar=
            argumentos.guardar,
    )


if __name__ == "__main__":
    main()
