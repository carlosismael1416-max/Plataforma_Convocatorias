"""
Analiza una convocatoria a partir de un enlace que da un usuario
(«Subir convocatoria» → «Desde un enlace»). Hace el mismo trabajo que el
motor, pero sin guardar nada: descarga la página (defensas SSRF de
app/seguridad_red.py), extrae datos con el enriquecimiento (página y sus
PDF), clasifica tipo y audiencia y aplica el validador. La web muestra el
resultado; el usuario decide si publica.

Uso manual (desde extractor/):
    ./.venv/bin/python -m app.importar_desde_url https://www.secihti.mx/convocatoria/...
"""

from __future__ import annotations

import json
import re
import sys
from datetime import date
from decimal import Decimal
from urllib.parse import urlparse

import requests
from selectolax.parser import HTMLParser

from app.descubrimiento.guardar_descubrimientos import conectar
from app.enriquecer_convocatoria import (
    construir_enriquecimiento,
    construir_item_pagina_directa,
    host_url,
)
from app.seguridad_red import descargar_mismo_dominio, validar_url_publica
from app.urls import hash_url
from app.validar_convocatoria import evaluar_convocatoria


# Igual que «Subir desde un archivo».
MAX_BYTES_ARCHIVO = 15 * 1024 * 1024


class EnlaceInvalido(ValueError):
    """La URL no es http(s) pública o no se pudo leer."""


def titulo_de_pagina(html: str) -> str | None:
    """
    og:title, h1 o <title>. Se prefiere el primero que nombre algo (dos
    palabras o más): en micrositios el og:title suele ser solo la marca
    («InnovaFest») y el h1 el nombre de la convocatoria.
    """
    arbol = HTMLParser(html)
    candidatos = []

    for selector, atributo in (
        ('meta[property="og:title"]', "content"),
        ("h1", None),
        ("title", None),
    ):
        nodo = arbol.css_first(selector)
        if nodo is None:
            continue
        texto = (nodo.attributes.get(atributo) if atributo else nodo.text(strip=True)) or ""
        texto = " ".join(texto.split())
        # «Título | Sitio» → «Título»
        for separador in (" | ", " – ", " - "):
            if separador in texto and len(texto.split(separador)[0]) >= 15:
                texto = texto.split(separador)[0]
        if len(texto) >= 8:
            candidatos.append(texto[:500])

    for texto in candidatos:
        if len(re.findall(r"[^\W\d_]{3,}", texto)) >= 2:
            return texto

    return candidatos[0] if candidatos else None


def fuente_por_dominio(conexion, url: str) -> dict | None:
    host = host_url(url)
    cursor = conexion.cursor()
    cursor.execute("SELECT id, nombre, url_base FROM fuentes WHERE activa = TRUE ORDER BY id")
    for fuente_id, nombre, url_base in cursor.fetchall():
        if host_url(url_base or "") == host:
            cursor.close()
            return {"id": fuente_id, "nombre": nombre, "url_base": url_base}
    cursor.close()
    return None


def convocatoria_existente(conexion, url: str) -> dict | None:
    cursor = conexion.cursor()
    cursor.execute(
        "SELECT id, titulo, estado FROM convocatorias WHERE url_hash = %s ORDER BY id LIMIT 1",
        (hash_url(url),),
    )
    fila = cursor.fetchone()
    cursor.close()
    return {"id": fila[0], "titulo": fila[1], "estado": fila[2]} if fila else None


def misma_ya_registrada(datos: dict) -> dict | None:
    """
    La misma convocatoria guardada con otro enlace (mismo nombre y fecha de
    cierre; app/duplicados.py), para avisar antes de subirla otra vez.
    """
    from app.duplicados import buscar_misma

    if not datos.get("titulo"):
        return None

    try:
        conexion = conectar()
    except Exception:
        return None

    try:
        with conexion.cursor() as cursor:
            fila = buscar_misma(cursor, datos["titulo"], datos.get("fecha_cierre"))
            if fila is None:
                return None
            cursor.execute("SELECT estado FROM convocatorias WHERE id = %s", (fila[0],))
            return {"id": fila[0], "titulo": fila[1], "estado": cursor.fetchone()[0]}
    except Exception:
        return None
    finally:
        conexion.rollback()
        conexion.close()


def _json(valor):
    if isinstance(valor, (date,)):
        return valor.isoformat()
    if isinstance(valor, Decimal):
        return float(valor)
    return valor


def datos_y_validacion(extraido: dict, titulo: str | None, url: str | None, fuente_id: int | None) -> dict:
    """
    Datos para el formulario y resultado del validador a partir de lo que
    sacó el enriquecimiento (enlace o archivo). No guarda nada.
    """
    datos = {
        "titulo": titulo,
        "url_original": url,
        "descripcion": extraido.get("descripcion"),
        "objetivo": extraido.get("objetivo"),
        "fecha_cierre": extraido.get("fecha_cierre"),
        "fecha_publicacion": extraido.get("fecha_publicacion"),
        "fecha_inicio": extraido.get("fecha_inicio"),
        "monto_minimo": extraido.get("monto_minimo"),
        "monto_maximo": extraido.get("monto_maximo"),
        "moneda": extraido.get("moneda") or "MXN",
        "organismo_id": extraido.get("organismo_id"),
        "categoria_id": extraido.get("categoria_id"),
        "fuente_id": fuente_id,
    }

    # Mismas restricciones que la tabla (si no, el guardado fallaría).
    if datos["fecha_inicio"] and datos["fecha_cierre"] and datos["fecha_inicio"] > datos["fecha_cierre"]:
        datos["fecha_inicio"] = None
    if (
        datos["monto_minimo"] is not None
        and datos["monto_maximo"] is not None
        and datos["monto_minimo"] > datos["monto_maximo"]
    ):
        datos["monto_minimo"] = None

    validacion = evaluar_convocatoria({"id": 0, **datos})
    datos["tipo"] = validacion["tipo"]
    datos["dirigido_a"] = validacion["dirigido_a"]

    return {
        "datos": {clave: _json(valor) for clave, valor in datos.items()},
        "requisitos": [
            r.get("descripcion") if isinstance(r, dict) else str(r)
            for r in (extraido.get("requisitos") or [])
        ][:15],
        "validacion": {
            "publicable": validacion["publicable"],
            "score": validacion["score"],
            "umbral": validacion["umbral"],
            "razones": validacion["razones"],
            "bloqueos": validacion["bloqueos"],
        },
        "documentos": extraido.get("documentos_procesados") or [],
    }


def analizar_url(url: str) -> dict:
    url = (url or "").strip()

    try:
        validar_url_publica(url)
    except Exception as error:
        raise EnlaceInvalido(f"El enlace no es una dirección web pública válida: {error}") from error

    partes = urlparse(url)
    url_base = f"{partes.scheme}://{partes.netloc}/"

    conexion = conectar()
    try:
        existente = convocatoria_existente(conexion, url)
        fuente = fuente_por_dominio(conexion, url)
    finally:
        conexion.rollback()
        conexion.close()

    try:
        url_final, contenido, tipo_contenido = descargar_mismo_dominio(url, url_base, max_bytes=MAX_BYTES_ARCHIVO)
    except Exception as error:
        raise EnlaceInvalido(f"No se pudo leer la página: {error}") from error

    # Un enlace directo al PDF de la convocatoria (p. ej. las bases de una
    # edición): se lee como documento, no como página.
    if contenido.startswith(b"%PDF") or "pdf" in (tipo_contenido or "").lower():
        from app.importar_desde_archivo import ArchivoInvalido, analizar_archivo

        nombre = urlparse(url_final).path.rsplit("/", 1)[-1] or "convocatoria.pdf"
        try:
            resultado = analizar_archivo(contenido, nombre, url=url_final, fuente_id=fuente["id"] if fuente else None)
        except ArchivoInvalido as error:
            raise EnlaceInvalido(str(error)) from error

        return {
            **resultado,
            "url_final": url_final,
            "fuente": fuente["nombre"] if fuente else None,
            "existente": existente or misma_ya_registrada(resultado["datos"]),
        }

    codificacion = requests.utils.get_encoding_from_headers({"content-type": tipo_contenido}) or "utf-8"
    html = contenido.decode(codificacion, errors="replace")

    titulo = titulo_de_pagina(html)

    contexto = {
        "convocatoria_id": None,
        "titulo": titulo or "",
        "url_original": url_final,
        "fuente_url_base": url_base,
        "fuente_nombre": fuente["nombre"] if fuente else (partes.hostname or ""),
        "categoria_objetivo": None,
        "descripcion": None,
        "objetivo": None,
        "fecha_cierre": None,
        "categoria_id": None,
        "organismo_id": None,
        "monto_minimo": None,
        "monto_maximo": None,
        "moneda": None,
        "fecha_publicacion": None,
        "fecha_inicio": None,
    }

    item = construir_item_pagina_directa(contexto, descarga=(url_final, html))
    extraido = construir_enriquecimiento(contexto, item=item)

    resultado = datos_y_validacion(extraido, titulo, url_final, fuente["id"] if fuente else None)

    return {
        "url_final": url_final,
        "fuente": fuente["nombre"] if fuente else None,
        "existente": existente or misma_ya_registrada(resultado["datos"]),
        **resultado,
    }


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python -m app.importar_desde_url <url>")
        sys.exit(2)
    print(json.dumps(analizar_url(sys.argv[1]), ensure_ascii=False, indent=2, default=str))
