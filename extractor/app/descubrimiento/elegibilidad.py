"""
¿Puede participar el ITSVA (Valladolid, Yucatán; TecNM)?

Reglas por título, último segmento de la URL y dominio. Ante la duda
se considera elegible: el resto del ciclo (evaluador ≥ 80 y validador
≥ 70) sigue filtrando. Solo se descarta cuando hay un motivo claro:

- Convocatoria de otro estado de México.
- Convocatoria de una institución de otro país.
- No es una convocatoria para participar (vacantes, admisiones).
"""

from __future__ import annotations

from urllib.parse import urlparse

from app.descubrimiento.clasificar_objetivo import (
    contiene,
    normalizar,
    ultimo_segmento,
)


# Menciones que hacen elegible una convocatoria aunque también
# aparezca otro estado (regionales o nacionales).
MARCAS_INCLUYENTES = (
    "yucatan",
    "yucateco",
    "yucateca",
    "peninsula de yucatan",
    "sur sureste",
    "region sureste",
    "nacional",
    "todo el pais",
    "toda la republica",
    # El ITSVA es parte del TecNM.
    "tecnm",
    "tecnologico nacional de mexico",
)

# Nombre del estado -> palabras que lo identifican en el título
# (incluye siglas de consejos estatales de ciencia).
# Los nombres que también son apellidos o el país (Guerrero,
# Hidalgo, Morelos, México) solo cuentan con "estado de ..." o
# el gentilicio.
ESTADOS = {
    "Aguascalientes": ("aguascalientes", "hidrocalido"),
    # Antes que "Baja California" para nombrar bien el estado.
    "Baja California Sur": ("baja california sur", "sudcaliforniano"),
    "Baja California": ("baja california", "bajacaliforniano"),
    "Campeche": ("campeche", "campechano"),
    "Chiapas": ("chiapas", "chiapaneco"),
    "Chihuahua": ("chihuahua", "chihuahuense"),
    "Ciudad de México": ("ciudad de mexico", "cdmx"),
    "Coahuila": ("coahuila", "coahuilense"),
    "Colima": ("colima", "colimense"),
    "Durango": ("durango", "duranguense"),
    "Estado de México": ("estado de mexico", "edomex", "mexiquense", "comecyt"),
    "Guanajuato": ("guanajuato", "guanajuatense", "concyteg"),
    "Guerrero": ("estado de guerrero", "guerrerense"),
    "Hidalgo": ("estado de hidalgo", "hidalguense"),
    "Jalisco": ("jalisco", "jalisciense", "coecytjal"),
    "Michoacán": ("michoacan", "michoacano"),
    "Morelos": ("estado de morelos", "morelense"),
    "Nayarit": ("nayarit", "nayarita"),
    "Nuevo León": ("nuevo leon", "neoleones"),
    "Oaxaca": ("oaxaca", "oaxaqueno"),
    "Puebla": ("puebla", "poblano"),
    "Querétaro": ("queretaro", "queretano"),
    "Quintana Roo": ("quintana roo", "quintanarroense", "coqhcyt", "coqcyt"),
    "San Luis Potosí": ("san luis potosi", "potosino", "copocyt"),
    "Sinaloa": ("sinaloa", "sinaloense"),
    "Sonora": ("sonora", "sonorense"),
    "Tabasco": ("tabasco", "tabasqueno", "ccytet"),
    "Tamaulipas": ("tamaulipas", "tamaulipeco"),
    "Tlaxcala": ("tlaxcala", "tlaxcalteca"),
    "Veracruz": ("veracruz", "veracruzano", "coveicydet"),
    "Zacatecas": ("zacatecas", "zacatecano", "cozcyt"),
}

# Etiqueta del dominio *.gob.mx -> estado (gobiernos estatales).
DOMINIOS_ESTATALES = {
    "aguascalientes": "Aguascalientes",
    "bajacalifornia": "Baja California",
    "bcs": "Baja California Sur",
    "campeche": "Campeche",
    "chiapas": "Chiapas",
    "chihuahua": "Chihuahua",
    "cdmx": "Ciudad de México",
    "coahuila": "Coahuila",
    "colima": "Colima",
    "durango": "Durango",
    "edomex": "Estado de México",
    "guanajuato": "Guanajuato",
    "guerrero": "Guerrero",
    "hidalgo": "Hidalgo",
    "jalisco": "Jalisco",
    "michoacan": "Michoacán",
    "morelos": "Morelos",
    "nayarit": "Nayarit",
    "nl": "Nuevo León",
    "oaxaca": "Oaxaca",
    "puebla": "Puebla",
    "queretaro": "Querétaro",
    "qroo": "Quintana Roo",
    "slp": "San Luis Potosí",
    "sinaloa": "Sinaloa",
    "sonora": "Sonora",
    "tabasco": "Tabasco",
    "tamaulipas": "Tamaulipas",
    "tlaxcala": "Tlaxcala",
    "veracruz": "Veracruz",
    "zacatecas": "Zacatecas",
    "yucatan": "Yucatán",
}

# Dominios de primer nivel genéricos o de México.
DOMINIOS_NO_PAIS = {
    "mx", "com", "org", "net", "edu", "gob", "gov", "int",
    "info", "io",
}

# Menciones que mantienen elegible una convocatoria extranjera.
MARCAS_INTERNACIONALES = (
    "mexico",
    "mexicano",
    "mexicana",
    "internacional",
    "latinoamerica",
    "latinoamericano",
    "america latina",
    "iberoamerica",
    "iberoamericano",
)

# No son convocatorias en las que el ITSVA participe.
NO_PARTICIPABLES = (
    "vacante",
    "plaza",
    "ocupar el cargo",
    "bolsa de trabajo",
    "oferta laboral",
    "admision",
    "nuevo ingreso",
)


def elegible(
    motivo: str | None = None,
) -> dict:
    return {
        "elegible": motivo is None,
        "motivo": motivo,
    }


def estado_por_dominio(
    dominio: str,
) -> str | None:
    etiquetas = dominio.split(".")

    if not dominio.endswith(".gob.mx"):
        return None

    for etiqueta in etiquetas[:-2]:
        if etiqueta in DOMINIOS_ESTATALES:
            return DOMINIOS_ESTATALES[etiqueta]

    return None


def quitar_medio(
    titulo: str,
) -> str:
    # Google News agrega " - Medio" al título ("... - El Heraldo
    # de Tabasco"); el nombre del medio no indica el alcance.
    principal = titulo.split(" - ", 1)[0]

    if len(principal.strip()) >= 20:
        return principal

    return titulo


def evaluar_elegibilidad(
    titulo: str | None,
    url: str | None,
) -> dict:
    texto = normalizar(
        f"{quitar_medio(titulo or '')} "
        f"{ultimo_segmento(url or '')}"
    )

    dominio = (
        urlparse(url or "").hostname
        or ""
    ).lower().removeprefix("www.")

    if contiene(texto, NO_PARTICIPABLES):
        return elegible(
            "No es una convocatoria de participación "
            "(vacante o admisión)."
        )

    if contiene(texto, MARCAS_INCLUYENTES):
        return elegible()

    estado = estado_por_dominio(dominio)

    if estado is None:
        estado = next(
            (
                nombre
                for nombre, palabras in ESTADOS.items()
                if contiene(texto, palabras)
            ),
            None,
        )

    if estado and estado != "Yucatán":
        return elegible(
            f"Convocatoria estatal de {estado}; "
            "el ITSVA está en Yucatán."
        )

    pais = dominio.rsplit(".", 1)[-1] if dominio else ""

    if (
        pais
        and pais not in DOMINIOS_NO_PAIS
        and not contiene(texto, MARCAS_INTERNACIONALES)
    ):
        return elegible(
            f"Institución de otro país (.{pais}); "
            "no indica apertura a México."
        )

    return elegible()
