"""
Tipo de convocatoria y a quién va dirigida.

Desde el 18-oct-2026 la plataforma también publica becas, premios,
concursos, eventos y actividades de formación, marcados para que cada
usuario sepa qué es y a quién le sirve (la institución, docentes e
investigadores o estudiantes). Reglas puras sobre texto normalizado:
título y ruta de la URL deciden el tipo; objetivo y descripción ayudan
a saber a quién va dirigida.

Tipos:
    FINANCIAMIENTO  apoyo económico a proyectos (el caso original)
    BECA            becas y apoyos personales
    PREMIO          premios, reconocimientos, distinciones
    CONCURSO        concursos, olimpiadas, retos, hackatones
    EVENTO          congresos, ferias, foros, expociencias
    FORMACION       cursos, talleres, diplomados, veranos, estancias
    NO_APLICA       interno: resultados, vacantes, no elegibles (nunca
                    se publica ni se muestra en el historial)
"""

from __future__ import annotations

import re
import unicodedata
from urllib.parse import unquote, urlparse

TIPOS = ("FINANCIAMIENTO", "BECA", "PREMIO", "CONCURSO", "EVENTO", "FORMACION", "NO_APLICA")

AUDIENCIAS = ("INSTITUCION", "DOCENTES", "ESTUDIANTES")

# Orden = prioridad: el primero que aparezca en título o ruta decide.
# (Un «concurso para el premio…» es concurso; «beca de investigación»
# es beca.)
_PATRONES_TIPO = (
    ("BECA", r"\bbecas?\b|\bbecari[oa]s?\b|\bmanutencion\b"),
    ("CONCURSO", r"\bconcursos?\b|\bcopa\b|\btorneos?\b|\bolimpiadas?\b|\bhackat[oh]n\b|\bretos?\b|\bcertamen\b|\bcompetencia\b"),
    ("PREMIO", r"\bpremios?\b|\breconocimientos?\b|\bdistincion\b|\bpresea\b|\bgalardon\b|\bmedalla\b"),
    ("EVENTO", r"\bcongresos?\b|\bferias?\b|\bforos?\b|\bsimposios?\b|\bseminarios?\b|\bencuentros?\b|\bjornadas?\b|\bexpociencias\b|\bcoloquios?\b|\bsemana (nacional |estatal )?de (la )?ciencia\b"),
    ("FORMACION", r"\bcursos?\b|\btalleres?\b|\bdiplomados?\b|\bverano de la (ciencia|investigacion)\b|\bformacion temprana\b|\bestancias?\b|\bescuela de verano\b|\bcapacitacion\b"),
)

_PATRONES_AUDIENCIA = (
    ("ESTUDIANTES", r"\bestudiantes?\b|\balumn[oa]s?\b|\bjovenes\b|\bbachillerato\b|\blicenciatura\b|\bpregrado\b|\bnivel (medio )?superior\b|\btesistas?\b|\btesis\b|\bposgrado\b|\bmaestria\b|\bdoctorado\b|\bverano de la (ciencia|investigacion)\b|\bformacion temprana\b|\bninas? y ninos\b|\bninez\b"),
    ("DOCENTES", r"\bdocentes?\b|\bprofesor(es|as)?\b|\binvestigador(es|as)?\b|\bpersonal academico\b|\bacademic[oa]s\b|\bcientific[oa]s\b|\bsnii?\b|\bsistema nacional de investigador"),
    ("INSTITUCION", r"\binstituciones?\b|\bies\b|\buniversidad(es)?\b|\btecnologicos?\b|\bcentros? (publicos? )?de investigacion\b|\bentidades?\b|\borganizaciones?\b|\bempresas?\b|\bdependencias?\b|\bgrupos? de investigacion\b|\bcuerpos? academicos?\b|\bredes? de investigacion\b|\brevistas?\b"),
)

# Nunca son convocatorias para participar: anuncios de resultados y
# vacantes o nombramientos.
_PATRONES_NO_APLICA = (
    (r"\bresultados?\b|\bganador(es|as)\b|\blista de (aceptad|seleccionad)|\bfallo\b|\bseleccionad[oa]s\b", "anuncio de resultados"),
    (r"\bvacantes?\b|\bplazas?\b|\bocupar el cargo\b|\bnombramiento\b|\btitular de la direccion\b|\bproceso de admision\b", "vacante, plaza o admisión"),
)

# Programas que excluyen a quienes tienen nacionalidad mexicana (caso
# real: «Apoyo Complementario SECIHTI-OEA», solo para extranjeros).
_EXCLUYE_MEXICANOS = r"\b(con excepcion|a excepcion|excepto|salvo)( de)? (los |las )?(estudiantes |personas |ciudadan[oa]s )?(de nacionalidad )?mexican[oa]s\b|\bno (podran participar|son elegibles) (los |las )?(estudiantes |personas )?mexican[oa]s\b"


def normalizar(valor: str | None) -> str:
    valor = unicodedata.normalize("NFKD", valor or "")
    valor = "".join(c for c in valor if not unicodedata.combining(c))
    return re.sub(r"\s+", " ", valor.casefold()).strip()


def texto_de_ruta(url: str | None) -> str:
    try:
        ruta = unquote(urlparse(url or "").path)
    except Exception:
        return ""
    return normalizar(re.sub(r"[/_\-.]+", " ", ruta))


def excluye_mexicanos(objetivo: str | None, descripcion: str | None) -> bool:
    texto = normalizar(f"{objetivo or ''} {(descripcion or '')[:3000]}")
    return re.search(_EXCLUYE_MEXICANOS, texto) is not None


def motivo_no_aplica(titulo: str | None, url: str | None) -> str | None:
    texto = normalizar(f"{titulo or ''} {texto_de_ruta(url)}")
    for patron, motivo in _PATRONES_NO_APLICA:
        if re.search(patron, texto):
            return motivo
    return None


def clasificar_tipo(
    titulo: str | None,
    url: str | None = None,
    objetivo: str | None = None,
    descripcion: str | None = None,
) -> dict:
    """
    Devuelve {"tipo", "dirigido_a" (lista), "motivo"}. NO_APLICA solo por
    resultados/vacantes; la elegibilidad geográfica la decide el validador.
    """
    titulo_ruta = normalizar(f"{titulo or ''} {texto_de_ruta(url)}")
    cuerpo = normalizar(f"{objetivo or ''} {(descripcion or '')[:2000]}")

    motivo = motivo_no_aplica(titulo, url)
    if motivo:
        return {"tipo": "NO_APLICA", "dirigido_a": [], "motivo": motivo}

    tipo = "FINANCIAMIENTO"
    for nombre, patron in _PATRONES_TIPO:
        if re.search(patron, titulo_ruta):
            tipo = nombre
            break

    # Sin pista en el título: una beca o apoyo personal a estudiantes se
    # reconoce por el objetivo («otorgar becas…», «apoyos a estudiantes»).
    if tipo == "FINANCIAMIENTO" and re.search(
        r"\b(otorgamiento|otorgar|otorga) de (una |las |los )?becas?\b"
        r"|\bapoyos? (economicos? |complementarios? )?(a|para) (estudiantes|alumn[oa]s|becari[oa]s)\b",
        cuerpo,
    ):
        tipo = "BECA"

    texto_audiencia = f"{titulo_ruta} {cuerpo}"
    dirigido_a = [
        audiencia
        for audiencia, patron in _PATRONES_AUDIENCIA
        if re.search(patron, texto_audiencia)
    ]

    # Una beca o un curso son personales: «instituciones» en su texto
    # suele ser dónde se estudia, no a quién se dirige.
    if tipo in ("BECA", "FORMACION") and dirigido_a != ["INSTITUCION"]:
        dirigido_a = [a for a in dirigido_a if a != "INSTITUCION"]

    if not dirigido_a:
        dirigido_a = {
            "FINANCIAMIENTO": ["INSTITUCION", "DOCENTES"],
            "BECA": ["ESTUDIANTES"],
            "FORMACION": ["ESTUDIANTES"],
        }.get(tipo, [])

    # Orden estable para mostrar.
    dirigido_a = [a for a in AUDIENCIAS if a in dirigido_a]

    return {"tipo": tipo, "dirigido_a": dirigido_a, "motivo": None}
