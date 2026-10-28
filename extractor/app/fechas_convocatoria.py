"""
Fechas de cierre en el texto de una convocatoria (PDF o página), para los
casos que el método por frases exactas no resuelve:

- Tablas de calendario donde la actividad queda partida en varias líneas
  («Cierre de recepción de» / «solicitudes.») o la fecha queda en la línea
  vecina.
- «10 de abril del 2025» (con «del»), «marzo 11, 2026», «10/04/2025».
- Fechas sin año («Del 21 de mayo al 05 de junio»): toman el año de la
  convocatoria (el del título o el más mencionado en el texto).
- Rangos: el cierre es la fecha final.

Solo se aceptan fechas junto a palabras de cierre o de recepción, y nunca
las de resultados, evaluación o formalización.
"""

from __future__ import annotations

import re
import unicodedata
from collections import Counter
from datetime import date

MESES = {
    "enero": 1, "febrero": 2, "marzo": 3, "abril": 4, "mayo": 5, "junio": 6,
    "julio": 7, "agosto": 8, "septiembre": 9, "setiembre": 9, "octubre": 10,
    "noviembre": 11, "diciembre": 12,
}

_MES = "(" + "|".join(MESES) + ")"

# 21 de mayo [de|del [año]] 2026 · 21 de mayo (sin año)
_DIA_MES = re.compile(r"\b(\d{1,2})\s+de\s+" + _MES + r"(?:\s+(?:de|del)\s+(?:ano\s+)?(20\d{2}))?\b")
# mayo 21, 2026
_MES_DIA = re.compile(r"\b" + _MES + r"\s+(\d{1,2}),?\s+(?:de\s+)?(20\d{2})\b")
# 21/05/2026 · 21-05-2026
_NUMERICA = re.compile(r"\b(\d{1,2})[/-](\d{1,2})[/-](20\d{2})\b")
# «del 10 al 21 de mayo de 2026»: el día suelto toma el mes y año siguientes
_DIA_SUELTO_RANGO = re.compile(r"\b(?:del|de)\s+(\d{1,2})\s+al\s+(\d{1,2})\s+de\s+" + _MES + r"(?:\s+(?:de|del)\s+(20\d{2}))?\b")

# Prioridad de cada clase de línea (más alto, más confiable).
_CLAVES_CIERRE = (
    (3, re.compile(r"\b(cierre|cierra|fecha limite|limite para|a mas tardar|vence|vencimiento)\b")),
    (2, re.compile(r"\b(hasta el|hasta las|ultimo dia|termina|concluye|finaliza)\b")),
    (1, re.compile(r"\b(recepcion|registro|inscripcion|inscripciones|postulacion|postulaciones|entrega de (?:solicitudes|propuestas|documentos)|envio de (?:solicitudes|propuestas)|periodo de (?:registro|recepcion|postulacion)|seran recibidos|se recibiran)\b")),
)

# Fechas de otras etapas: no son el cierre de la convocatoria.
_OTRAS_ETAPAS = re.compile(
    r"\b(resultad|evaluaci|dictam|formalizaci|publicacion de la convocatoria|apertura|"
    r"inicio de actividades|ministraci|prevenci|seleccionad|admitid|admision|validacion de documentos|"
    r"entrega de premios|premiacion|ceremonia|nacimiento|nacid|egresad|vigencia del apoyo|ejercicio fiscal|"
    # «A más tardar el 31 de enero se publicará en el DOF la asignación» (FAM).
    r"asignacion|se publicara|diario oficial|"
    # Plazos de ejecución del proyecto («la etapa se concluirá a más tardar…»).
    r"se concluir|deberan concluir|ejecucion)"
)


def normalizar(texto: str | None) -> str:
    texto = unicodedata.normalize("NFKD", texto or "")
    texto = "".join(c for c in texto if not unicodedata.combining(c))
    return " ".join(texto.lower().split())


def anio_de_referencia(titulo: str | None, texto: str | None) -> int | None:
    """Año de la convocatoria: el del título o el más mencionado en el texto."""
    en_titulo = re.findall(r"\b(20\d{2})\b", titulo or "")

    if en_titulo:
        return max(int(a) for a in en_titulo)

    conteo = Counter(int(a) for a in re.findall(r"\b(20\d{2})\b", texto or ""))

    return conteo.most_common(1)[0][0] if conteo else None


def _fecha(anio: int | None, mes: int, dia: int) -> date | None:
    if anio is None:
        return None

    try:
        return date(anio, mes, dia)
    except ValueError:
        return None


def fechas_en_texto(linea: str, anio_referencia: int | None = None) -> list[date]:
    """Fechas de una línea en orden de aparición; sin año usan anio_referencia."""
    texto = normalizar(linea)
    encontradas: list[tuple[int, date]] = []

    # Año explícito más cercano a la derecha (para «21 de mayo al 05 de junio de 2026»).
    def anio_a_la_derecha(posicion: int) -> int | None:
        siguiente = re.search(r"\b(20\d{2})\b", texto[posicion:])
        return int(siguiente.group(1)) if siguiente and siguiente.start() < 60 else None

    for m in _DIA_SUELTO_RANGO.finditer(texto):
        anio = int(m.group(4)) if m.group(4) else (anio_a_la_derecha(m.end()) or anio_referencia)
        if (f := _fecha(anio, MESES[m.group(3)], int(m.group(1)))):
            encontradas.append((m.start(1), f))

    for m in _DIA_MES.finditer(texto):
        anio = int(m.group(3)) if m.group(3) else (anio_a_la_derecha(m.end()) or anio_referencia)
        if (f := _fecha(anio, MESES[m.group(2)], int(m.group(1)))):
            encontradas.append((m.start(), f))

    for m in _MES_DIA.finditer(texto):
        if (f := _fecha(int(m.group(3)), MESES[m.group(1)], int(m.group(2)))):
            encontradas.append((m.start(), f))

    for m in _NUMERICA.finditer(texto):
        if (f := _fecha(int(m.group(3)), int(m.group(2)), int(m.group(1)))):
            encontradas.append((m.start(), f))

    vistas: set[date] = set()
    resultado = []

    for _, f in sorted(encontradas):
        if f not in vistas:
            vistas.add(f)
            resultado.append(f)

    return resultado


def _prioridad(texto_normalizado: str) -> int:
    for prioridad, patron in _CLAVES_CIERRE:
        if patron.search(texto_normalizado):
            return prioridad

    return 0


def extraer_cierre_calendario(
    lineas: list[str],
    anio_referencia: int | None = None,
) -> tuple[date | None, str | None]:
    """
    Fecha de cierre por palabras clave con ventana de líneas vecinas.
    Devuelve (fecha, evidencia) o (None, None).
    """
    candidatas: list[tuple[int, date, str]] = []

    for indice, linea in enumerate(lineas):
        actual = normalizar(linea)
        # La actividad puede venir partida: «Cierre de recepción de» + «solicitudes.»
        contexto = normalizar(" ".join(lineas[indice:indice + 2]))
        prioridad = _prioridad(actual) or (_prioridad(contexto) if len(actual) < 60 else 0)

        if not prioridad or _OTRAS_ETAPAS.search(actual):
            continue

        # La clave vino de la línea siguiente («IX. ASIGNACIÓN» + «A más
        # tardar el 31 de enero…»): esa línea también debe ser de cierre.
        if not _prioridad(actual) and _OTRAS_ETAPAS.search(contexto):
            continue

        # «Registro/recepción» que continúa una etapa posterior («Fase 4:
        # inscripción en caso de ser admitido.» / «Presentar … 30 de marzo»).
        if prioridad == 1 and indice and _OTRAS_ETAPAS.search(normalizar(lineas[indice - 1])):
            continue

        fechas = fechas_en_texto(linea, anio_referencia)
        evidencia = linea

        # Tabla: la fecha quedó en la línea de abajo o de arriba.
        if not fechas:
            for vecina in (lineas[indice + 1] if indice + 1 < len(lineas) else "", lineas[indice - 1] if indice else ""):
                if vecina and not _OTRAS_ETAPAS.search(normalizar(vecina)) and _prioridad(normalizar(vecina)) in (0, prioridad):
                    fechas = fechas_en_texto(vecina, anio_referencia)
                    if fechas:
                        evidencia = f"{linea} | {vecina}"
                        break

        if fechas:
            # En un rango («del 22 de abril al 15 de mayo») el cierre es la última.
            candidatas.append((prioridad, fechas[-1], evidencia.strip()[:300]))

    if not candidatas:
        return None, None

    mejor_prioridad = max(c[0] for c in candidatas)
    # Varias etapas de recepción (investigadores, estudiantes): la última.
    mejor = max((c for c in candidatas if c[0] == mejor_prioridad), key=lambda c: c[1])

    return mejor[1], mejor[2]
