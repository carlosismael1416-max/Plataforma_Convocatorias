"""
Página propia de una convocatoria a partir de una nota.

Muchas convocatorias se anuncian en un boletín de prensa o una noticia
(«Presenta COQHCYT la convocatoria…», «Abren convocatoria…») y tienen su
propia página o PDF de bases. Aquí se elige, entre los enlaces del
cuerpo de la nota (sin menús, pies ni barras laterales), el que lleva a
esa página: el que comparte palabras con el título o dice «bases»,
«convocatoria», «registro».

No descarga nada: el enlace elegido se guarda como descubrimiento y el
resto del ciclo (evaluador, extracción, validador) lo procesa igual que
cualquier otro. Solo se sigue si el ITSVA puede participar, tanto por la
nota como por el destino.
"""

from __future__ import annotations

import re

from urllib.parse import (
    unquote,
    urljoin,
    urlsplit,
)

from selectolax.parser import HTMLParser

from app.descubrimiento.analizar_articulo import (
    dominio_de,
    es_dominio_social,
    es_institucional,
    normalizar_texto,
    parece_url_publica,
)
from app.descubrimiento.elegibilidad import (
    evaluar_elegibilidad,
)


PUNTAJE_MINIMO = 10

EXTENSIONES_IMAGEN = (".jpg", ".jpeg", ".png", ".gif", ".webp", ".svg")
MAX_ENLACES_CUERPO = 80

# Partes de la página que no son la nota. Las etiquetas se quitan de
# toda la página; las clases, solo dentro del cuerpo (el <body> o el
# contenedor de la nota también pueden llevar «sidebar» o «menu»).
SELECTOR_ETIQUETAS_RUIDO = "script, style, noscript, nav, header, footer, aside, form"

SELECTOR_RUIDO = (
    "[class*=menu], [id*=menu], [class*=sidebar], [id*=sidebar], "
    "[class*=widget], [class*=related], [class*=relacionad], "
    "[class*=share], [class*=compart], [class*=breadcrumb], "
    "[class*=comment], [id*=comment], [class*=tags], [class*=etiquetas]"
)

SELECTOR_CUERPO = (
    "[itemprop=articleBody]",
    ".entry-content",
    ".post-content",
    ".article-body",
    ".field--name-body",
    "article",
    "main",
)

# Rutas y títulos típicos de una nota o boletín (no de la convocatoria).
MARCAS_RUTA_NOTA = (
    "prensa",
    "noticia",
    "noticias",
    "ultimas-noticias",
    "comunicado",
    "comunicados",
    "boletin",
    "boletines",
    "blog",
    "nota",
    "notas",
)

VERBOS_NOTA = (
    "presenta",
    "presentan",
    "abre",
    "abren",
    "lanza",
    "lanzan",
    "anuncia",
    "anuncian",
    "invita",
    "invitan",
    "publica",
    "publican",
    "emite",
    "emiten",
)

SENALES_CONVOCATORIA = (
    "convocatoria",
    "bases",
    "lineamientos",
    "terminos de referencia",
    "reglas de operacion",
    "registro",
    "registrate",
    "postulacion",
    "postula",
    "inscripcion",
    "inscribete",
    "participa",
    "mas informacion",
    "consulta",
)

# Enlaces que nunca son la convocatoria.
RUIDO = (
    "informe",
    "transparencia",
    "aviso de privacidad",
    "privacidad",
    "codigo de etica",
    "directorio",
    "contacto",
    "mapa del sitio",
    "gaceta",
    "licitacion",
    "nomina",
    "suscribete",
    "cookies",
    "inicio",
)

RUTAS_RUIDO = (
    "/tag/",
    "/tags/",
    "/category/",
    "/categoria/",
    "/author/",
    "/autor/",
    "/page/",
    "/feed",
    "/wp-login",
    "/search",
    "/buscar",
)

# Palabras del título que no distinguen una convocatoria de otra.
PALABRAS_VACIAS = {
    "para", "por", "con", "los", "las", "del", "una", "uno", "que", "sus",
    "como", "esta", "este", "desde", "hasta", "sobre", "entre", "ante",
    "convocatoria", "convocatorias", "abierta", "abiertas", "nueva",
    "programa", "apoyo", "apoyos", "mexico", "estado", "gobierno",
    "asi", "puedes", "participar", "pesos", "millones", "millon",
} | set(VERBOS_NOTA)


def palabras_clave(
    texto: str,
) -> set[str]:
    return {
        palabra
        for palabra in re.split(r"[^a-z0-9]+", normalizar_texto(texto))
        if len(palabra) >= 4
        and not palabra.isdigit()
        and palabra not in PALABRAS_VACIAS
    }


def es_nota(
    url: str,
    titulo: str,
) -> bool:
    """¿La publicación es una nota o boletín (y no la convocatoria)?"""
    ruta = unquote(urlsplit(url).path).casefold()
    segmentos = set(re.split(r"[/_.\-]+", ruta))

    if segmentos & set(MARCAS_RUTA_NOTA):
        return True

    # /2026/02/24/… : ruta fechada de noticia.
    if re.search(r"/20\d{2}/\d{1,2}/", ruta):
        return True

    primera = normalizar_texto(titulo).split(" ", 1)[0] if titulo else ""
    return primera in VERBOS_NOTA


def enlaces_del_cuerpo(
    html: str,
    url_base: str,
) -> list[tuple[str, str]]:
    """(url absoluta, texto) de los enlaces dentro del texto de la nota."""
    try:
        arbol = HTMLParser(html)
    except Exception:
        return []

    for nodo in arbol.css(SELECTOR_ETIQUETAS_RUIDO):
        nodo.decompose()

    # Algunos medios parten la nota en varios bloques (varios
    # articleBody): se toman todos los del primer selector que exista.
    cuerpos = next(
        (
            nodos
            for selector in SELECTOR_CUERPO
            if (nodos := arbol.css(selector))
        ),
        [],
    )

    for cuerpo in cuerpos:
        for nodo in cuerpo.css(SELECTOR_RUIDO):
            if nodo.mem_id != cuerpo.mem_id:
                nodo.decompose()

    enlaces = []
    vistos = set()

    for enlace in (e for cuerpo in cuerpos for e in cuerpo.css("a[href]")):
        href = (enlace.attributes.get("href") or "").strip()

        if not href or href.startswith(("mailto:", "tel:", "javascript:", "data:", "#")):
            continue

        absoluta = urljoin(url_base, href).split("#", 1)[0]

        if absoluta in vistos or not parece_url_publica(absoluta):
            continue

        if urlsplit(absoluta).path.casefold().endswith(EXTENSIONES_IMAGEN):
            continue

        vistos.add(absoluta)
        enlaces.append((absoluta, " ".join(enlace.text(separator=" ").split())))

        if len(enlaces) >= MAX_ENLACES_CUERPO:
            break

    return enlaces


def puntuar_pagina_propia(
    url: str,
    texto_enlace: str,
    titulo_nota: str,
    url_nota: str,
) -> dict:
    dominio = dominio_de(url)
    partes = urlsplit(url)
    ruta = unquote(partes.path or "/")
    texto = normalizar_texto(f"{texto_enlace} {ruta.replace('-', ' ').replace('_', ' ')}")

    def descarte(motivo: str) -> dict:
        return {"puntaje": -100, "coincidencias": [], "razones": [motivo]}

    if es_dominio_social(dominio):
        return descarte("red social")

    if ruta.strip("/") == "":
        return descarte("portada del sitio")

    if url.rstrip("/") == url_nota.split("#", 1)[0].rstrip("/"):
        return descarte("la misma nota")

    if any(marca in ruta.casefold() for marca in RUTAS_RUIDO):
        return descarte("listado o archivo del sitio")

    if any(re.search(rf"\b{re.escape(palabra)}\b", texto) for palabra in RUIDO):
        return descarte("enlace institucional genérico")

    puntaje = 0
    razones = []

    coincidencias = sorted(palabras_clave(titulo_nota) & palabras_clave(texto))
    if coincidencias:
        valor = min(4 * len(coincidencias), 16)
        puntaje += valor
        razones.append(f"+{valor} comparte con el título: {', '.join(coincidencias)}")

    senales = [s for s in SENALES_CONVOCATORIA if re.search(rf"\b{s}\b", texto)]
    if senales:
        puntaje += 6
        razones.append(f"+6 {senales[0]}")

    if ruta.casefold().endswith(".pdf"):
        puntaje += 4
        razones.append("+4 PDF")

    if es_institucional(dominio):
        puntaje += 4
        razones.append("+4 dominio institucional")
    elif not senales:
        # Fuera de sitios institucionales (fundaciones, empresas) solo
        # cuenta si el enlace dice que es la convocatoria; si no, suele
        # ser otra nota con palabras parecidas.
        puntaje = min(puntaje, PUNTAJE_MINIMO - 1)

    # Sin relación con el título, una palabra suelta («consulta») no basta.
    if not coincidencias and not ruta.casefold().endswith(".pdf"):
        puntaje = min(puntaje, PUNTAJE_MINIMO - 1)

    return {"puntaje": puntaje, "coincidencias": coincidencias, "razones": razones}


def buscar_pagina_propia(
    enlaces: list[tuple[str, str]],
    titulo_nota: str,
    url_nota: str,
) -> dict | None:
    """
    El enlace a la página propia de la convocatoria, o None si la nota no
    tiene uno claro o si el ITSVA no puede participar (por la nota o por
    el destino).
    """
    if not evaluar_elegibilidad(titulo_nota, url_nota)["elegible"]:
        return None

    mejores = []

    for url, texto in enlaces:
        evaluacion = puntuar_pagina_propia(url, texto, titulo_nota, url_nota)

        if evaluacion["puntaje"] < PUNTAJE_MINIMO:
            continue

        # El destino se juzga con el texto del enlace y su URL, y también
        # con el título de la nota («COQHCYT», «Edomex» van en la nota).
        destino = evaluar_elegibilidad(f"{titulo_nota} {texto}", url)
        if not destino["elegible"]:
            continue

        mejores.append({
            "url": url,
            "texto": texto,
            "dominio": dominio_de(url),
            "pdf": urlsplit(url).path.casefold().endswith(".pdf"),
            **evaluacion,
        })

    if not mejores:
        return None

    # A igual puntaje, la página antes que el PDF: la página suele
    # enlazar las bases y más documentos.
    mejores.sort(key=lambda e: (e["puntaje"], not e["pdf"]), reverse=True)
    return mejores[0]


def portales_en_nota(
    enlaces: list[tuple[str, str]],
    titulo_nota: str,
    url_nota: str,
) -> list[str]:
    """
    Portadas institucionales que la nota enlaza (el sitio del consejo o
    del sistema de registro, p. ej. proyectos.secihti.mx) cuando no hay
    página propia: se exploran como portales. Solo de otro dominio y si
    el ITSVA puede participar.
    """
    if not evaluar_elegibilidad(titulo_nota, url_nota)["elegible"]:
        return []

    dominio_nota = dominio_de(url_nota)
    portales = []

    for url, texto in enlaces:
        dominio = dominio_de(url)

        if (
            not dominio
            or dominio == dominio_nota
            or not es_institucional(dominio)
            or urlsplit(url).path.strip("/") != ""
            or not evaluar_elegibilidad(f"{titulo_nota} {texto}", url)["elegible"]
        ):
            continue

        if url not in portales:
            portales.append(url)

    return portales[:2]
