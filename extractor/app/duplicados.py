"""
Una convocatoria que aparece en varios enlaces (su micrositio, el PDF de
bases, la página de la secretaría, la de TecNM) es una sola. El url_hash
solo detecta el mismo enlace; aquí se reconoce la misma convocatoria por
su nombre y su fecha de cierre, para completar la que ya existe en vez
de crear otra.

Reglas (conservadoras: ante la duda, son distintas):
- Años distintos en el título («Premio X 2025» / «Premio X 2026») o
  fechas de cierre distintas → distintas (otra edición u otra etapa).
- Misma fecha de cierre → basta que compartan la mayoría de las palabras
  del nombre.
- Una sin fecha → los nombres deben coincidir casi por completo.
"""

from __future__ import annotations

import re
import unicodedata
from datetime import date


PALABRAS_VACIAS = {
    "convocatoria", "convocatorias", "para", "del", "las", "los", "con",
    "por", "una", "que", "sus", "ante", "edicion", "primer", "primera",
    "segunda", "tercera", "abierta", "oficial", "bases", "the", "and",
}


def palabras(titulo: str | None) -> set[str]:
    texto = unicodedata.normalize("NFKD", titulo or "")
    texto = "".join(c for c in texto if not unicodedata.combining(c)).casefold()
    return {
        p
        for p in re.split(r"[^a-z0-9]+", texto)
        if (len(p) >= 3 or p.isdigit()) and p not in PALABRAS_VACIAS
    }


def anios(conjunto: set[str]) -> set[str]:
    return {p for p in conjunto if re.fullmatch(r"20\d\d", p)}


def misma_convocatoria(
    titulo_a: str | None,
    cierre_a: date | str | None,
    titulo_b: str | None,
    cierre_b: date | str | None,
) -> bool:
    a, b = palabras(titulo_a), palabras(titulo_b)

    if not a or not b:
        return False

    if anios(a) and anios(b) and anios(a) != anios(b):
        return False

    cierre_a = str(cierre_a) if cierre_a else None
    cierre_b = str(cierre_b) if cierre_b else None

    if cierre_a and cierre_b and cierre_a != cierre_b:
        return False

    comunes = a & b
    sin_anio = (a | b) - anios(a | b)
    if not comunes - anios(comunes):
        return False

    union = len(a | b)
    menor = min(len(a), len(b))
    jaccard = len(comunes) / union
    contenido = len(comunes) / menor

    if cierre_a and cierre_b:
        return jaccard >= 0.5 or (contenido >= 0.8 and menor >= 2 and len(sin_anio) >= 2)

    return jaccard >= 0.75 and menor >= 3


def buscar_misma(
    cursor,
    titulo: str,
    fecha_cierre: date | str | None,
) -> tuple | None:
    """
    (id, titulo, fecha_cierre, fuente_id) de una convocatoria ya guardada
    que es la misma, o None. Solo compara con las de fecha compatible.
    """
    cursor.execute(
        """
        SELECT id, titulo, fecha_cierre, fuente_id
        FROM convocatorias
        WHERE estado <> 'DESCARTADA'
          AND (%s::date IS NULL OR fecha_cierre IS NULL OR fecha_cierre = %s::date)
        ORDER BY id DESC
        LIMIT 500
        """,
        (fecha_cierre, fecha_cierre),
    )

    for fila in cursor.fetchall():
        if misma_convocatoria(titulo, fecha_cierre, fila[1], fila[2]):
            return fila

    return None
