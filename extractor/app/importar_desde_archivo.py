"""
«Subir convocatoria» → «Desde un archivo»: el usuario sube el PDF o una
imagen (cartel, captura) de la convocatoria. Se lee el texto (PDF directo,
OCR solo en páginas que son imagen; OCR para imágenes), se analiza igual
que el enriquecimiento del motor (descripción, objetivo, fechas, montos,
requisitos, categoría) y se aplica el validador. No guarda nada.

Uso manual (desde extractor/):
    ./.venv/bin/python -m app.importar_desde_archivo ruta/convocatoria.pdf
"""

from __future__ import annotations

import io
import json
import re
import sys
import unicodedata
from pathlib import Path

import pymupdf

from app.analizar_pdf import extraer_texto_pdf_bytes
from app.descubrimiento.guardar_descubrimientos import conectar
from app.enriquecer_convocatoria import analizar_textos
from app.importar_desde_url import datos_y_validacion

MAX_BYTES = 15 * 1024 * 1024
# Bajo consumo: imágenes enormes se reducen antes del OCR.
MAX_LADO_IMAGEN = 3000
MIN_CARACTERES = 80
SEGUNDOS_MAX_OCR = 90


class ArchivoInvalido(ValueError):
    """El archivo no es un PDF o imagen legible."""


def detectar_tipo(contenido: bytes) -> str:
    """Por el contenido, no por el nombre ni lo que diga el navegador."""
    if contenido.startswith(b"%PDF"):
        return "pdf"
    if contenido.startswith(b"\x89PNG\r\n\x1a\n"):
        return "png"
    if contenido.startswith(b"\xff\xd8\xff"):
        return "jpg"
    if contenido[:4] == b"RIFF" and contenido[8:12] == b"WEBP":
        return "webp"
    raise ArchivoInvalido("El archivo debe ser un PDF o una imagen (JPG, PNG o WebP).")


def texto_de_imagen(contenido: bytes) -> str:
    """OCR en español de un cartel o captura."""
    import pytesseract
    from PIL import Image, ImageOps

    try:
        imagen = Image.open(io.BytesIO(contenido))
        imagen = ImageOps.exif_transpose(imagen)
    except Exception as error:
        raise ArchivoInvalido("No se pudo abrir la imagen.") from error

    imagen = ImageOps.grayscale(imagen)
    ancho, alto = imagen.size

    # Texto chico: se amplía; imágenes muy grandes: se reducen.
    if max(ancho, alto) < 1500:
        factor = 1500 / max(ancho, alto)
        imagen = imagen.resize((int(ancho * factor), int(alto * factor)))
    elif max(ancho, alto) > MAX_LADO_IMAGEN:
        imagen.thumbnail((MAX_LADO_IMAGEN, MAX_LADO_IMAGEN))

    imagen = ImageOps.autocontrast(imagen)

    try:
        return pytesseract.image_to_string(imagen, lang="spa", timeout=SEGUNDOS_MAX_OCR)
    except RuntimeError as error:  # tiempo agotado
        raise ArchivoInvalido("La imagen tardó demasiado en leerse; prueba con una más pequeña o con el PDF.") from error


def enlaces_en_imagen(contenido: bytes) -> list[str]:
    """
    Enlaces de los códigos QR del cartel («Convocatoria aquí», «Escanea el
    QR»). Los carteles casi nunca traen la fecha de cierre ni las bases: eso
    está en la página a la que lleva el QR.
    """
    try:
        import zxingcpp
        from PIL import Image, ImageOps

        imagen = ImageOps.exif_transpose(Image.open(io.BytesIO(contenido)))
        if max(imagen.size) > MAX_LADO_IMAGEN:
            imagen.thumbnail((MAX_LADO_IMAGEN, MAX_LADO_IMAGEN))
        codigos = zxingcpp.read_barcodes(imagen)
    except Exception:
        return []

    enlaces = []
    for codigo in codigos:
        texto = (codigo.text or "").strip()
        if re.match(r"(?i)^https?://[^\s]+$", texto) and texto not in enlaces:
            enlaces.append(texto)

    return enlaces[:3]


def analisis_del_enlace(enlaces: list[str]) -> tuple[str, dict] | tuple[None, None]:
    """El primer enlace del QR que se pueda analizar como «Subir desde un enlace»."""
    from app.importar_desde_url import EnlaceInvalido, analizar_url

    for enlace in enlaces:
        try:
            return enlace, analizar_url(enlace)
        except EnlaceInvalido:
            continue
        except Exception:
            continue

    return None, None


def titulo_de_pdf(contenido: bytes) -> str | None:
    """El texto con la letra más grande de la primera página."""
    try:
        with pymupdf.open(stream=contenido, filetype="pdf") as documento:
            if not len(documento):
                return None
            bloques = documento[0].get_text("dict").get("blocks", [])
    except Exception:
        return None

    spans = [
        (round(span.get("size", 0), 1), span.get("text", "").strip())
        for bloque in bloques
        for linea in bloque.get("lines", [])
        for span in linea.get("spans", [])
        if span.get("text", "").strip()
    ]

    if not spans:
        return None

    for tamano in sorted({s for s, _ in spans}, reverse=True)[:3]:
        texto = " ".join(t for s, t in spans if s == tamano)
        texto = " ".join(texto.split()).strip(" .:-“”\"")
        # Un título tiene al menos tres palabras de verdad (no «i. ii. iii.»).
        palabras = re.findall(r"[a-záéíóúñ]{3,}", texto, re.I)
        if 12 <= len(texto) <= 220 and len(palabras) >= 3:
            return texto

    return None


_ENLACES = {"de", "del", "la", "las", "el", "los", "y", "e", "en", "para", "a", "al", "con", "por", "sobre"}


def _completar_titulo(lineas: list[str], i: int) -> str:
    """Un título partido en renglones («… PÚBLICAS DE» / «EDUCACIÓN MEDIA SUPERIOR»)."""
    titulo = lineas[i]
    j = i + 1
    while j < len(lineas) and j <= i + 2 and titulo.split()[-1].casefold() in _ENLACES and len(titulo) < 200:
        titulo = f"{titulo} {lineas[j]}"
        j += 1
    return titulo.strip(" .:-")


def _juntar_letras(linea: str) -> str:
    """«C O N V O C A T O R I A» → «CONVOCATORIA» (títulos con letras espaciadas)."""
    if re.fullmatch(r"(?:\w ){3,}\w", linea):
        return linea.replace(" ", "")
    return linea


def titulo_de_texto(texto: str) -> str | None:
    """
    El nombre de la convocatoria en el texto. Las convocatorias mexicanas
    casi siempre dicen «… convoca(n) … a participar en el/la NOMBRE»; si no,
    el nombre entre comillas tras «CONVOCA(N)» o la primera línea con
    «convocatoria».
    """
    corrido = " ".join(texto[:4000].split())

    participar = re.search(
        r"(?i)a participar en(?:\s+(?:el|la|los|las))?\s*[«“\"]?\s*([^«“\"”»,.;:]{8,160}?)\s*(?:[»”\"]|,|\.|;|:|\bcon el tema\b|\bque se\b|$)",
        corrido,
    )
    if participar and sum(c.isalpha() for c in participar.group(1)) >= 8:
        return participar.group(1).strip()

    lineas = [_juntar_letras(" ".join(l.split())) for l in texto.splitlines() if len(l.strip()) >= 4][:40]

    # Encabezado «CONVOCATORIA» solo en su renglón: el nombre va en los
    # renglones siguientes («Exhibición de Innovación y Patentes 2026» /
    # «Edición Mérida»), antes del primer párrafo.
    for i, linea in enumerate(lineas[:10]):
        if re.fullmatch(r"(?i)convocatorias?[:.]?", linea):
            nombre = []
            for siguiente in lineas[i + 1:i + 4]:
                if len(siguiente) > 120 or siguiente.endswith((",", ";")) or re.match(r"(?i)(en el marco|el |la |los |las |con fundamento)", siguiente):
                    break
                nombre.append(siguiente)
            if nombre and sum(c.isalpha() for c in " ".join(nombre)) >= 10:
                return " ".join(nombre).strip(" .:-")

    for i, linea in enumerate(lineas):
        if re.fullmatch(r"(?i)convoca[n]?\s*[:.]?", linea) and i + 1 < len(lineas):
            siguiente = " ".join(lineas[i + 1:i + 8])
            comillas = re.search(r"[«“\"](.{12,200}?)[»”\"]", siguiente)
            if comillas:
                return comillas.group(1).strip()

    for i, linea in enumerate(lineas):
        if re.search(r"(?i)\bconvocatoria\b", linea) and 15 <= len(linea) <= 200:
            return _completar_titulo(lineas, i)

    for i, linea in enumerate(lineas):
        if 15 <= len(linea) <= 200 and sum(c.isalpha() for c in linea) > 10:
            return _completar_titulo(lineas, i)

    return None


def _normalizar(texto: str) -> str:
    texto = unicodedata.normalize("NFKD", texto or "")
    return " ".join("".join(c for c in texto if not unicodedata.combining(c)).casefold().split())


def organismo_en_texto(texto: str) -> int | None:
    """Organismo del catálogo cuyo nombre aparece al inicio del documento."""
    inicio = _normalizar(texto[:4000])

    try:
        conexion = conectar()
        try:
            with conexion.cursor() as cursor:
                cursor.execute("SELECT id, nombre FROM organismos WHERE estado = TRUE ORDER BY LENGTH(nombre) DESC")
                for organismo_id, nombre in cursor.fetchall():
                    nombre = _normalizar(nombre)
                    # «Secretaría de Ciencia, Humanidades… (SECIHTI)»: basta el nombre sin siglas.
                    sin_siglas = re.sub(r"\s*\([^)]*\)\s*$", "", nombre)
                    siglas = re.findall(r"\(([a-z]{3,10})\)", nombre)
                    if sin_siglas and sin_siglas in inicio or any(re.search(rf"\b{s}\b", inicio) for s in siglas):
                        return organismo_id
        finally:
            conexion.close()
    except Exception:
        return None

    return None


def analizar_archivo(
    contenido: bytes,
    nombre: str = "convocatoria",
    url: str | None = None,
    fuente_id: int | None = None,
) -> dict:
    """
    Con url (un enlace que lleva directo a un PDF) el resultado se valida
    como cualquier enlace: con su URL y la fuente de su dominio.
    """
    if not contenido:
        raise ArchivoInvalido("El archivo está vacío.")

    if len(contenido) > MAX_BYTES:
        raise ArchivoInvalido("El archivo pesa más de 15 MB.")

    tipo = detectar_tipo(contenido)

    if tipo == "pdf":
        try:
            texto = extraer_texto_pdf_bytes(contenido)
        except Exception as error:
            raise ArchivoInvalido("No se pudo leer el PDF.") from error
        titulo = titulo_de_texto(texto) or titulo_de_pdf(contenido)
    else:
        # Primero el QR (rápido): si lleva a la página de la convocatoria,
        # sus datos son los buenos y no hace falta el OCR del cartel.
        if url is None:
            enlace, desde_enlace = analisis_del_enlace(enlaces_en_imagen(contenido))
            if desde_enlace is not None:
                return {
                    **desde_enlace,
                    "archivo": nombre,
                    "tipo_archivo": tipo,
                    "caracteres_leidos": 0,
                    "enlace_detectado": enlace,
                }

        texto = texto_de_imagen(contenido)
        titulo = titulo_de_texto(texto)

    texto = re.sub(r"\n?=+\nPÁGINA \d+\n=+\n", "\n", texto)

    if len(texto.strip()) < MIN_CARACTERES:
        raise ArchivoInvalido("No se encontró texto legible en el archivo. Si es una foto, que se vea completa y nítida.")

    titulo = titulo or Path(nombre).stem.replace("_", " ").replace("-", " ").strip()

    contexto = {
        "convocatoria_id": None,
        "titulo": titulo,
        "url_original": url or "",
        "fuente_url_base": "",
        "fuente_nombre": "",
        "categoria_objetivo": None,
    }

    # El mismo texto hace de «página»: sirve de respaldo para la descripción,
    # el objetivo, los requisitos y el cierre cuando no hay encabezados.
    extraido = analizar_textos(
        contexto,
        {"titulo": titulo, "documentos": [], "_texto_pagina": texto},
        [texto],
        texto,
        [{"nombre": nombre, "caracteres": len(texto)}],
        [],
    )

    if not extraido.get("organismo_id"):
        extraido["organismo_id"] = organismo_en_texto(texto)

    from app.importar_desde_url import misma_ya_registrada

    resultado = datos_y_validacion(extraido, titulo, url, fuente_id)

    return {
        "archivo": nombre,
        "tipo_archivo": tipo,
        "fuente": None,
        "existente": misma_ya_registrada(resultado["datos"]) if url is None else None,
        "caracteres_leidos": len(texto.strip()),
        **resultado,
    }


if __name__ == "__main__":
    if len(sys.argv) != 2:
        print("Uso: python -m app.importar_desde_archivo <archivo.pdf|imagen>")
        sys.exit(2)
    ruta = Path(sys.argv[1])
    print(json.dumps(analizar_archivo(ruta.read_bytes(), ruta.name), ensure_ascii=False, indent=2, default=str))
