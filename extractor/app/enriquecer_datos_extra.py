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

    for linea in lineas:
        normal = normalizar(linea)

        if publicacion is None and _ETIQUETAS_PUBLICACION.search(normal):
            fechas = fechas_en_linea(linea)
            publicacion = fechas[0] if fechas else None

        if inicio is None and _ETIQUETAS_INICIO.search(normal):
            fechas = fechas_en_linea(linea)
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
