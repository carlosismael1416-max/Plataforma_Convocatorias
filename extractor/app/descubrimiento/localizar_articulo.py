from __future__ import annotations

from app.seguridad_red import SesionSegura


import re
import unicodedata

from urllib.parse import (
    quote_plus,
    unquote,
    urljoin,
    urlparse,
)

import requests

from app.descubrimiento.buscar_html_ligero import (
    ExtractorEnlaces,
    cargar_robots,
    normalizar_host,
    permitido_por_robots,
    validar_base,
)


USER_AGENT = "ITSVA-Convocatorias/1.0"


STOPWORDS = {
    "para",
    "como",
    "desde",
    "hasta",
    "sobre",
    "entre",
    "esta",
    "este",
    "estos",
    "estas",
    "del",
    "las",
    "los",
    "una",
    "uno",
    "unos",
    "unas",
    "que",
    "con",
    "sin",
    "por",
    "sus",
    "más",
    "mas",
    "así",
    "asi",
    "puedes",
    "abre",
    "abren",
    "lanza",
    "presenta",
    "nueva",
    "nuevo",
}


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


def limpiar_titulo_fuente(
    titulo: str,
    fuente: str | None,
) -> str:
    titulo = (
        titulo
        or ""
    ).strip()

    fuente = (
        fuente
        or ""
    ).strip()

    if not fuente:
        return titulo

    patron = re.compile(
        r"\s*-\s*"
        + re.escape(fuente)
        + r"\s*$",
        flags=re.I,
    )

    return patron.sub(
        "",
        titulo,
    ).strip()


def tokens_relevantes(
    texto: str,
) -> set[str]:
    normalizado = normalizar_texto(
        texto
    )

    tokens = set()

    for token in normalizado.split():
        if len(token) < 4:
            continue

        if token in STOPWORDS:
            continue

        if token.isdigit():
            if len(token) == 4:
                tokens.add(token)
            continue

        tokens.add(token)

    return tokens


def termino_busqueda(
    titulo: str,
) -> str:
    tokens = list(
        tokens_relevantes(
            titulo
        )
    )

    prioridades = (
        "convocatoria",
        "investigacion",
        "financiamiento",
        "proyectos",
        "proyecto",
        "ciencia",
        "innovacion",
        "tecnologia",
        "beca",
    )

    ordenados = []

    for prioridad in prioridades:
        if prioridad in tokens:
            ordenados.append(
                prioridad
            )

    restantes = sorted(
        token
        for token in tokens
        if token not in ordenados
    )

    ordenados.extend(
        restantes
    )

    return " ".join(
        ordenados[:8]
    )


def texto_url(
    url: str,
) -> str:
    ruta = unquote(
        urlparse(url).path
    )

    return normalizar_texto(
        ruta
    )


def puntuar_coincidencia(
    titulo_objetivo: str,
    texto_enlace: str,
    url: str,
) -> dict:
    objetivo = tokens_relevantes(
        titulo_objetivo
    )

    candidato = tokens_relevantes(
        (
            f"{texto_enlace} "
            f"{texto_url(url)}"
        )
    )

    if not objetivo:
        return {
            "puntaje": 0,
            "cobertura": 0.0,
            "coincidencias": [],
        }

    coincidencias = (
        objetivo
        & candidato
    )

    cobertura = (
        len(coincidencias)
        / len(objetivo)
    )

    puntaje = int(
        cobertura * 100
    )

    puntaje += min(
        len(coincidencias),
        6,
    ) * 3

    titulo_norm = normalizar_texto(
        titulo_objetivo
    )

    enlace_norm = normalizar_texto(
        texto_enlace
    )

    if (
        titulo_norm
        and enlace_norm
        and (
            titulo_norm in enlace_norm
            or enlace_norm in titulo_norm
        )
        and len(enlace_norm) >= 20
    ):
        puntaje += 25

    return {
        "puntaje":
            puntaje,

        "cobertura":
            round(
                cobertura,
                3,
            ),

        "coincidencias":
            sorted(
                coincidencias
            ),
    }


def host_permitido(
    url: str,
    host_base: str,
) -> bool:
    host = normalizar_host(
        urlparse(url).hostname
    )

    return (
        host == host_base
        or host.endswith(
            "." + host_base
        )
    )


def descargar_busqueda(
    session: requests.Session,
    robots: dict,
    url: str,
) -> tuple[str, str] | None:
    if not permitido_por_robots(
        robots,
        url,
    ):
        return None

    try:
        respuesta = session.get(
            url,
            timeout=(5, 15),
            allow_redirects=True,
        )

    except requests.RequestException:
        return None

    if respuesta.status_code != 200:
        return None

    content_type = (
        respuesta.headers
        .get(
            "content-type",
            ""
        )
        .lower()
    )

    if "html" not in content_type:
        return None

    if len(
        respuesta.content
    ) > 2_000_000:
        return None

    return (
        respuesta.url,
        respuesta.text,
    )


def localizar_articulo(
    url_base: str,
    titulo: str,
    fuente_nombre: str | None = None,
    max_peticiones: int = 5,
) -> dict:
    base = validar_base(
        url_base
    )

    titulo_limpio = (
        limpiar_titulo_fuente(
            titulo,
            fuente_nombre,
        )
    )

    termino = termino_busqueda(
        titulo_limpio
    )

    if not termino:
        return {
            "estado":
                "SIN_TERMINOS",

            "titulo":
                titulo_limpio,

            "termino":
                "",

            "mejor":
                None,

            "candidatos":
                [],

            "intentos":
                [],
        }

    session = SesionSegura()

    session.headers.update({
        "User-Agent":
            USER_AGENT,

        "Accept":
            "text/html,"
            "application/xhtml+xml,"
            "*/*;q=0.5",
    })

    robots = cargar_robots(
        session,
        base,
    )

    if robots["estado"] in (
        "BLOQUEADO",
        "ERROR_TEMPORAL",
    ):
        return {
            "estado":
                robots["estado"],

            "titulo":
                titulo_limpio,

            "termino":
                termino,

            "mejor":
                None,

            "candidatos":
                [],

            "intentos":
                [],
        }

    consulta = quote_plus(
        termino
    )

    rutas = [
        base,
        urljoin(
            base,
            f"/?s={consulta}",
        ),
        urljoin(
            base,
            f"/search?q={consulta}",
        ),
        urljoin(
            base,
            f"/buscar?q={consulta}",
        ),
        urljoin(
            base,
            f"/busqueda?q={consulta}",
        ),
    ]

    host_base = normalizar_host(
        urlparse(
            base
        ).hostname
    )

    encontrados = {}

    intentos = []

    peticiones = 0

    for url_busqueda in rutas:
        if peticiones >= max_peticiones:
            break

        peticiones += 1

        descargado = descargar_busqueda(
            session,
            robots,
            url_busqueda,
        )

        if descargado is None:
            intentos.append({
                "url":
                    url_busqueda,

                "estado":
                    "SIN_RESULTADO",
            })

            continue

        url_final, contenido = (
            descargado
        )

        parser = ExtractorEnlaces()

        try:
            parser.feed(
                contenido
            )
        except Exception:
            intentos.append({
                "url":
                    url_busqueda,

                "estado":
                    "HTML_INVALIDO",
            })

            continue

        total_enlaces = 0

        for (
            href,
            texto_enlace,
        ) in parser.enlaces:

            absoluta = urljoin(
                url_final,
                href,
            )

            if not host_permitido(
                absoluta,
                host_base,
            ):
                continue

            total_enlaces += 1

            coincidencia = (
                puntuar_coincidencia(
                    titulo_limpio,
                    texto_enlace,
                    absoluta,
                )
            )

            if (
                coincidencia[
                    "puntaje"
                ] < 35
                or len(
                    coincidencia[
                        "coincidencias"
                    ]
                ) < 2
            ):
                continue

            candidato = {
                "titulo_enlace":
                    texto_enlace,

                "url":
                    absoluta,

                "puntaje":
                    coincidencia[
                        "puntaje"
                    ],

                "cobertura":
                    coincidencia[
                        "cobertura"
                    ],

                "coincidencias":
                    coincidencia[
                        "coincidencias"
                    ],

                "encontrado_en":
                    url_final,
            }

            anterior = encontrados.get(
                absoluta
            )

            if (
                anterior is None
                or candidato[
                    "puntaje"
                ]
                > anterior[
                    "puntaje"
                ]
            ):
                encontrados[
                    absoluta
                ] = candidato

        intentos.append({
            "url":
                url_final,

            "estado":
                "OK",

            "enlaces":
                total_enlaces,
        })

        if encontrados:
            mejor_actual = max(
                encontrados.values(),
                key=lambda item:
                    item["puntaje"],
            )

            if (
                mejor_actual[
                    "puntaje"
                ] >= 90
            ):
                break

    candidatos = sorted(
        encontrados.values(),
        key=lambda item:
            item["puntaje"],
        reverse=True,
    )

    mejor = (
        candidatos[0]
        if candidatos
        else None
    )

    if (
        mejor
        and mejor[
            "puntaje"
        ] >= 55
        and len(
            mejor[
                "coincidencias"
            ]
        ) >= 3
    ):
        estado = "LOCALIZADO"

    elif candidatos:
        estado = "COINCIDENCIA_DEBIL"

    else:
        estado = "NO_LOCALIZADO"

    return {
        "estado":
            estado,

        "titulo":
            titulo_limpio,

        "termino":
            termino,

        "mejor":
            mejor,

        "candidatos":
            candidatos[:10],

        "intentos":
            intentos,
    }
