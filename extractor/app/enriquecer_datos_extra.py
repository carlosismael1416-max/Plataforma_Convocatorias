"""Datos que el enriquecimiento no extraía: montos, fechas de publicación e
inicio, y una descripción tomada de la página cuando no hay PDF.

Funciones puras y conservadoras: ante la duda devuelven None (es preferible
un campo vacío a un dato inventado).
"""

from __future__ import annotations

import re
import unicodedata
from datetime import date
from decimal import Decimal, InvalidOperation
from typing import Callable

MONTO_MINIMO_RAZONABLE = Decimal("1000")
MONTO_MAXIMO_RAZONABLE = Decimal("10000000000")

_CONTEXTO_MAXIMO = re.compile(
    r"(hasta|maximo|monto maximo|tope|como maximo|apoyo de|financiamiento de|"
    r"por proyecto|por propuesta|monto total|recursos? de|apoyos? econ)"
)
_CONTEXTO_MINIMO = re.compile(r"(minimo|desde|al menos|a partir de)")
_CONTEXTO_MONTO = re.compile(r"(monto|apoyo|financ|recurso|presupuesto|otorgar)")
# Importes que no son el monto del proyecto: tabuladores de pago a
# colaboradores, honorarios, viáticos, cuotas...
_EXCLUSIONES = re.compile(
    r"(tabulador|honorario|cfdi|mensual|por mes|salario|sueldo|viatic|"
    r"inscripcion|cuota|costo de|colaboracion|nomina|hospedaje|pasaje|boleto)"
)

_IMPORTE = re.compile(
    r"(?:\$\s*|mxn\s*|usd\s*)"
    r"(\d{1,3}(?:[,.]\d{3})+(?:\.\d{1,2})?|\d+(?:\.\d{1,2})?)"
    r"(?:\s*(millones|millon|mdp|mil)\b)?"
    r"|(\d+(?:[.,]\d+)?)\s*(millones|millon|mil)\s+de\s+(pesos|dolares|euros)"
)


def normalizar(texto: str) -> str:
    sin_acentos = unicodedata.normalize("NFKD", texto or "")
    sin_acentos = "".join(c for c in sin_acentos if not unicodedata.combining(c))
    return " ".join(sin_acentos.lower().split())


def _a_decimal(numero: str) -> Decimal | None:
    limpio = numero.strip()

    # 1.500.000 o 1,500,000 -> separadores de miles; 2.5 -> decimal.
    if re.fullmatch(r"\d{1,3}([,.]\d{3})+", limpio):
        limpio = re.sub(r"[,.]", "", limpio)
    elif re.fullmatch(r"\d{1,3}(,\d{3})+\.\d{1,2}", limpio):
        limpio = limpio.replace(",", "")
    else:
        limpio = limpio.replace(",", ".")

    try:
        return Decimal(limpio)
    except InvalidOperation:
        return None


def _multiplicador(palabra: str | None) -> Decimal:
    if not palabra:
        return Decimal(1)

    if palabra.startswith("millon") or palabra == "mdp":
        return Decimal(1_000_000)

    if palabra == "mil":
        return Decimal(1_000)

    return Decimal(1)


def extraer_montos(texto: str) -> dict:
    """Monto mínimo/máximo y moneda con contexto de financiamiento."""

    normal = normalizar(texto)
    maximos: list[Decimal] = []
    minimos: list[Decimal] = []
    generales: list[Decimal] = []
    moneda = "MXN"

    for m in _IMPORTE.finditer(normal):
        numero = m.group(1) or m.group(3)
        palabra = m.group(2) or m.group(4)
        valor = _a_decimal(numero)

        if valor is None:
            continue

        valor *= _multiplicador(palabra)

        if not (MONTO_MINIMO_RAZONABLE <= valor <= MONTO_MAXIMO_RAZONABLE):
            continue

        antes = normal[max(0, m.start() - 70):m.start()]
        alrededor = normal[max(0, m.start() - 70):m.end() + 40]

        if not _CONTEXTO_MONTO.search(alrededor) or _EXCLUSIONES.search(
            normal[max(0, m.start() - 160):m.end() + 60]
        ):
            continue

        if re.search(r"\b(usd|dolares)\b", alrededor) or m.group(5) == "dolares":
            moneda = "USD"
        elif re.search(r"\b(eur|euros)\b", alrededor) or m.group(5) == "euros":
            moneda = "EUR"

        if _CONTEXTO_MINIMO.search(antes[-30:]):
            minimos.append(valor)
        elif _CONTEXTO_MAXIMO.search(antes):
            maximos.append(valor)
        else:
            generales.append(valor)

    maximo = max(maximos or generales) if (maximos or generales) else None
    minimo = min(minimos) if minimos else None

    if maximo is not None and minimo is not None and minimo > maximo:
        minimo = None

    return {
        "monto_minimo": minimo,
        "monto_maximo": maximo,
        "moneda": moneda if maximo is not None else None,
    }


_ETIQUETAS_PUBLICACION = re.compile(r"(fecha de publicacion|publicacion de la convocatoria|se publica|publicada el)")
# Solo la apertura del registro/recepción de solicitudes (no el inicio del
# proyecto, que suele ser posterior al cierre).
_ETIQUETAS_INICIO = re.compile(
    r"(apertura del (sistema|registro|periodo)|apertura de (la )?(convocatoria|recepcion|registro)|"
    r"inicio de (la )?(recepcion|registro|postulacion|captura)|"
    r"(recepcion|registro|captura) de (solicitudes|propuestas)|periodo de (registro|recepcion))"
)


def extraer_fechas_publicacion_inicio(
    lineas: list[str],
    fechas_en_linea: Callable[[str], list[date]],
) -> dict:
    publicacion = None
    inicio = None

    for posicion, linea in enumerate(lineas):
        normal = normalizar(linea)
        # En los PDF la etiqueta y la fecha suelen quedar en líneas
        # distintas (tablas de calendario): se mira también la siguiente.
        cercanas = " ".join(lineas[posicion:posicion + 2])

        if publicacion is None and _ETIQUETAS_PUBLICACION.search(normal):
            fechas = fechas_en_linea(linea) or fechas_en_linea(cercanas)
            publicacion = fechas[0] if fechas else None

        if inicio is None and _ETIQUETAS_INICIO.search(normal):
            fechas = fechas_en_linea(linea) or fechas_en_linea(cercanas)
            inicio = fechas[0] if fechas else None

        if publicacion and inicio:
            break

    return {"fecha_publicacion": publicacion, "fecha_inicio": inicio}


def descripcion_de_pagina(texto_pagina: str, titulo: str | None, maximo: int = 900) -> str | None:
    """Primer bloque de texto de la página después del título."""

    texto = " ".join((texto_pagina or "").split())

    if not texto:
        return None

    inicio = 0

    if titulo:
        posicion = normalizar(texto).find(normalizar(titulo)[:60])

        if posicion >= 0:
            inicio = posicion + len(titulo)

    fragmento = texto[inicio:inicio + maximo * 2].strip(" :.-–")

    if len(fragmento) < 120:
        return None

    corte = fragmento[:maximo]
    ultimo_punto = corte.rfind(". ")

    if ultimo_punto >= 120:
        corte = corte[:ultimo_punto + 1]

    return corte.strip()


# Secciones que describen quién puede participar (requisito) y quién no
# (restriccion), con los nombres que usan SECIHTI y otras dependencias.
_SECCIONES_REQUISITOS = (
    (
        re.compile(
            r"^\W*(?:[ivxlc]+[.)]|\d+[.)])?\s*(requisitos(?: (?:de|para) (?:participacion|participar|elegibilidad))?|"
            r"poblacion objetivo|participantes|quienes pueden participar|elegibilidad|"
            r"bases de participacion|condiciones de participacion)\s*:?$"
        ),
        "requisito",
    ),
    (
        re.compile(r"^\W*(?:[ivxlc]+[.)]|\d+[.)])?\s*(restricciones(?: de participacion)?|causas de no elegibilidad)\s*:?$"),
        "restriccion",
    ),
)
# Secciones en romanos con mayúscula («V. Calendario»); los incisos van en
# minúscula («a)», «b.») o numerados/viñetas.
_CABECERA_SECCION = re.compile(r"^\W*[IVXLC]+[.)]\s+[A-ZÁÉÍÓÚÑ]")
_INCISO = re.compile(r"^\s*(?:([a-z])[.)]|(\d{1,2})[.)]|[•·\-–▪])\s+(.*)$")


def _textos_de_seccion(lineas: list[str], inicio: int, maximo: int) -> list[str]:
    incisos: list[list[str]] = []
    parrafo: list[str] = []
    tipo_principal = None

    for linea in lineas[inicio:]:
        # Pies de página del PDF («Página 4 de 10»).
        texto = re.sub(r"\bP[áa]gina \d+ de \d+\b", "", linea).strip()

        if not texto:
            continue

        # Siguiente sección numerada (p. ej. «VI. Calendario»): termina.
        if _CABECERA_SECCION.match(texto) and len(texto) <= 90:
            break

        coincide = _INCISO.match(texto)

        if coincide:
            tipo = "letra" if coincide.group(1) else "numero" if coincide.group(2) else "vineta"
            tipo_principal = tipo_principal or tipo

            # Una sublista (otro tipo de viñeta) pertenece al inciso que la
            # introduce, p. ej. «b) Haber obtenido una beca en: • …».
            if tipo != tipo_principal and incisos:
                incisos[-1].append("; " + coincide.group(3).strip())
            else:
                incisos.append([coincide.group(3).strip()])
        elif incisos:
            incisos[-1].append(texto)
        else:
            parrafo.append(texto)

        if len(incisos) > maximo:
            break

    if incisos:
        return [" ".join(p) for p in incisos[:maximo]]

    # Sección sin incisos: el párrafo completo es un solo requisito.
    texto = " ".join(parrafo)[:800]
    return [texto] if len(texto) >= 60 else []


def extraer_requisitos(lineas: list[str], maximo: int = 25) -> list[dict]:
    """Requisitos y restricciones de participación de la convocatoria."""

    requisitos = []
    usadas = set()

    for posicion, linea in enumerate(lineas):
        normal = normalizar(linea)

        if len(normal) > 70:
            continue

        for patron, tipo in _SECCIONES_REQUISITOS:
            if tipo in usadas or not patron.match(normal):
                continue

            usadas.add(tipo)

            for texto in _textos_de_seccion(lineas, posicion + 1, maximo):
                descripcion = " ".join(texto.split()).replace(" ;", ";").replace(":;", ":")

                if 15 <= len(descripcion) <= 1200:
                    requisitos.append({
                        "titulo": ("Restricción " if tipo == "restriccion" else "Requisito ") + str(len(requisitos) + 1),
                        "descripcion": descripcion,
                        "obligatorio": True,
                        "tipo_requisito": tipo,
                        "orden": len(requisitos) + 1,
                    })

    return requisitos[:maximo * 2]
