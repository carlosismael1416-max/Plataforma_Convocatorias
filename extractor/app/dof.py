"""
Diario Oficial de la Federación por su canal abierto: el servicio del
SIDOF (sidof.segob.gob.mx) que lista las notas de un día
(/dof/sidof/notas/dd-mm-aaaa) y el texto de cada nota
(/notas/docFuente/{código}). Su robots.txt lo permite; el de dof.gob.mx
prohíbe /nota_detalle.php, que por eso no se usa.

Una petición por día publicado. Se recorre desde el último día revisado
(ajustes_sistema.dof_ultima_fecha) hasta hoy, con un máximo por corrida;
así se recuperan los días en que el equipo estuvo apagado.
"""

from __future__ import annotations

import re
import time
import unicodedata
from datetime import date, timedelta

import requests

from app.seguridad_red import USER_AGENT, validar_url_publica


SERVICIO = "https://sidof.segob.gob.mx/dof/sidof/notas/{fecha}"
NOTA = "https://sidof.segob.gob.mx/notas/docFuente/{codigo}"
CLAVE_AJUSTE = "dof_ultima_fecha"
DIAS_INICIALES = 14
MAX_DIAS_POR_CORRIDA = 15
PAUSA_SEGUNDOS = 1
EDICIONES = ("NotasMatutinas", "NotasVespertinas", "NotasExtraordinarias")


def normalizar(texto: str | None) -> str:
    texto = unicodedata.normalize("NFKD", texto or "")
    return " ".join("".join(c for c in texto if not unicodedata.combining(c)).casefold().split())


def notas_del_dia(fecha: date, sesion: requests.Session | None = None) -> list[dict]:
    url = SERVICIO.format(fecha=fecha.strftime("%d-%m-%Y"))
    validar_url_publica(url)
    sesion = sesion or requests.Session()
    respuesta = sesion.get(url, headers={"User-Agent": USER_AGENT}, timeout=(20, 60), allow_redirects=False)
    respuesta.raise_for_status()
    datos = respuesta.json()

    notas = []
    for edicion in EDICIONES:
        for nota in datos.get(edicion) or []:
            if not nota.get("codNota") or not nota.get("titulo"):
                continue
            notas.append({
                "codigo": int(nota["codNota"]),
                "titulo": " ".join(str(nota["titulo"]).split()),
                "dependencia": nota.get("codOrgaDos") or nota.get("nombreCodOrgaUno"),
                "fecha": fecha,
                "url": NOTA.format(codigo=int(nota["codNota"])),
            })
    return notas


def dias_pendientes(ultima: date | None, hoy: date | None = None) -> list[date]:
    hoy = hoy or date.today()
    inicio = (ultima + timedelta(days=1)) if ultima else hoy - timedelta(days=DIAS_INICIALES)
    dias = []
    dia = inicio
    while dia <= hoy and len(dias) < MAX_DIAS_POR_CORRIDA:
        # El DOF no publica en domingo.
        if dia.weekday() != 6:
            dias.append(dia)
        dia += timedelta(days=1)
    return dias


def ultima_fecha(cursor) -> date | None:
    cursor.execute("SELECT valor FROM ajustes_sistema WHERE clave = %s", (CLAVE_AJUSTE,))
    fila = cursor.fetchone()
    try:
        return date.fromisoformat(fila[0]) if fila else None
    except ValueError:
        return None


def guardar_ultima_fecha(cursor, fecha: date) -> None:
    cursor.execute(
        """
        INSERT INTO ajustes_sistema (clave, valor, created_at, updated_at)
        VALUES (%s, %s, NOW(), NOW())
        ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor, updated_at = NOW()
        """,
        (CLAVE_AJUSTE, fecha.isoformat()),
    )


def notas_nuevas(cursor) -> tuple[list[dict], date | None, list[str]]:
    """
    (notas de los días no revisados, último día leído, errores). Si un día
    falla se detiene ahí, para reintentarlo en la siguiente corrida.
    """
    notas, errores, leido = [], [], None
    with requests.Session() as sesion:
        for dia in dias_pendientes(ultima_fecha(cursor)):
            try:
                notas.extend(notas_del_dia(dia, sesion))
            except Exception as error:
                errores.append(f"DOF {dia.isoformat()}: {str(error)[:200]}")
                break
            leido = dia
            time.sleep(PAUSA_SEGUNDOS)
    return notas, leido, errores


def ejercicio_de(titulo: str, fecha: date) -> int:
    """«… durante el ejercicio fiscal de 2027 …» → 2027; si no, el año de la nota."""
    anio = re.search(r"ejercicio fiscal (?:de |del )?(20\d\d)", normalizar(titulo))
    return int(anio.group(1)) if anio else fecha.year
