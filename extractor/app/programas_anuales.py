"""
Programas anuales que se siguen por etapas: primero salen sus reglas
(normatividad, lineamientos), después la convocatoria (en el FAM, la
«Guía para la presentación de la propuesta») y al final los resultados
(beneficiarios, asignación).

Por cada ejercicio fiscal hay UNA convocatoria («Fondo de Aportaciones
Múltiples (FAM) 2027: …»). Se crea en cuanto aparecen las reglas o la
guía y queda PENDIENTE_REVISION (interna: guardada para el futuro); cada
etapa nueva solo le agrega documentos y actualiza su etapa. Si un
documento de la convocatoria trae fecha de cierre, se aplica el validador
de siempre (umbral 70) y, si cumple, se publica.

Canales (todos permitidos por su robots.txt):
- la página oficial del programa en DGESUI (guía, anexos, resultados);
- el DOF por el SIDOF (app/dof.py): avisos de distribución y lineamientos,
  que salen antes o junto con la guía;
- el TecNM, que publica el FAM de los tecnológicos (federales y
  descentralizados).
Se revisa una vez al día (ajustes_sistema).

Uso manual (desde extractor/):
    ./.venv/bin/python -m app.programas_anuales           # vista previa
    ./.venv/bin/python -m app.programas_anuales --guardar
"""

from __future__ import annotations

import argparse
import json
import re
from datetime import date
from urllib.parse import unquote, urljoin, urlparse

from selectolax.parser import HTMLParser

from app.analizar_pdf import extraer_texto_pdf_bytes
from app.descubrimiento.guardar_descubrimientos import conectar
from app.extraer_cierre_pdf import extraer_cierre_pdf
from app.seguridad_red import descargar_mismo_dominio
from app.urls import hash_url


HORAS_ENTRE_REVISIONES = 20
CLAVE_AJUSTE = "programas_anuales_revision"
MAX_BYTES_PAGINA = 5 * 1024 * 1024
MAX_BYTES_PDF = 20 * 1024 * 1024

ETAPAS = {
    "REGLAS": "Reglas publicadas",
    "CONVOCATORIA": "Convocatoria publicada",
    "RESULTADOS": "Resultados publicados",
}

FAM = {
    "clave": "FAM",
    "titulo": "Fondo de Aportaciones Múltiples (FAM) {anio}: infraestructura física educativa",
    "pagina": "https://dgesui.ses.sep.gob.mx/programas/fondo-de-aportaciones-multiples",
    "fuente": "DGESUI - Fondo de Aportaciones Múltiples (FAM)",
    "organismo": ("Secretaría de Educación Pública (SEP) - DGESUI", "https://dgesui.ses.sep.gob.mx"),
    # Secciones de la página → etapa (por el texto de su encabezado).
    # DOF (SIDOF): avisos y acuerdos del fondo, que salen antes que la
    # guía o junto con ella (distribución por entidad, lineamientos).
    "dof": r"fondo de aportaciones multiples",
    # TecNM publica el FAM de los tecnológicos (federales y
    # descentralizados) en su sección de transparencia.
    "tecnm": {"pagina": "https://www.tecnm.mx/?vista=Convocatorias_Tecnm", "ruta": "/fam/"},
    "secciones": (
        (r"normatividad|lineamientos|reglas", "REGLAS"),
        (r"gu[ií]a", "CONVOCATORIA"),
        (r"beneficiarios|tipo de proyectos|proyectos apoyados", "RESULTADOS"),
    ),
    "descripcion": (
        "Fondo federal del Ramo 33 para la infraestructura física de la educación superior: "
        "obra nueva, obras de continuidad, equipamiento especializado de aulas, laboratorios, "
        "talleres y centros de información, adecuaciones y mejoras, mantenimiento correctivo y "
        "proyectos técnicos de sustentabilidad. Cada año salen primero sus reglas (Ley de "
        "Coordinación Fiscal y lineamientos), después la guía para presentar la propuesta "
        "institucional (hasta 5 proyectos, capturados en el SIFAM) y, a más tardar el 31 de enero "
        "del ejercicio, la asignación publicada en el DOF."
    ),
    "objetivo": (
        "Financiar la construcción, adecuación, mantenimiento y equipamiento de la infraestructura "
        "física educativa. La guía de DGESUI está dirigida a universidades públicas estatales, de "
        "apoyo solidario e interculturales; para los institutos tecnológicos federales y "
        "descentralizados (como el ITSVA) la asignación del FAM se gestiona a través del TecNM."
    ),
}

PROGRAMAS = (FAM,)


def anio_de(texto: str) -> int | None:
    anios = [int(a) for a in re.findall(r"(?<!\d)(20\d\d)(?!\d)", texto)]
    return max(anios) if anios else None


def documentos_de_pagina(html: str, url_pagina: str, programa: dict) -> list[dict]:
    """
    Documentos de la página del programa con su etapa y ejercicio. El
    ejercicio sale del bloque del año («anexoanio-2027») o del nombre del
    archivo; las reglas sin año valen para todos los ejercicios.
    """
    arbol = HTMLParser(html)
    documentos = []
    vistos = set()

    for enlace in arbol.css("a[href]"):
        url = urljoin(url_pagina, enlace.attributes.get("href") or "").split("#", 1)[0]
        ruta = unquote(urlparse(url).path)

        if not re.search(r"\.(pdf|xlsx?|docx?)$", ruta, re.I) or url in vistos:
            continue

        etapa = None
        anio = None
        nodo = enlace.parent
        while nodo is not None and (etapa is None or anio is None):
            identificador = nodo.attributes.get("id") or ""
            bloque = re.search(r"anexoanio-(20\d\d)", identificador)
            if bloque and anio is None:
                anio = int(bloque.group(1))
            if identificador.startswith("programa-seccion") and etapa is None:
                encabezado = nodo.css_first("h1, h2, h3, h4")
                titulo = (encabezado.text(strip=True) if encabezado else "").casefold()
                etapa = next((e for patron, e in programa["secciones"] if re.search(patron, titulo)), None)
            nodo = nodo.parent

        if etapa is None:
            continue

        nombre_archivo = ruta.rsplit("/", 1)[-1]
        anio = anio or (anio_de(nombre_archivo) if etapa != "REGLAS" else None)
        texto = " ".join(enlace.text().split())
        vistos.add(url)
        documentos.append({
            "url": url,
            "nombre": nombre_documento(nombre_archivo, texto),
            "etapa": etapa,
            "anio": anio,
        })

    return documentos


def nombre_documento(nombre_archivo: str, texto_enlace: str) -> str:
    if texto_enlace and texto_enlace.casefold() not in {"descargar", "ver", "aquí", "aqui"}:
        return texto_enlace[:250]
    nombre = re.sub(r"\.(pdf|xlsx?|docx?)$", "", nombre_archivo, flags=re.I)
    return " ".join(re.sub(r"[_]+", " ", nombre).split())[:250]


def etapa_por_nombre(texto: str) -> str:
    texto = texto.casefold()
    if re.search(r"asignaci[oó]n|beneficiari|resultados|seleccionad", texto):
        return "RESULTADOS"
    if re.search(r"convocatoria|gu[ií]a", texto):
        return "CONVOCATORIA"
    return "REGLAS"


def documentos_dof(notas: list[dict], programa: dict) -> list[dict]:
    """Notas del DOF del programa: reglas del ejercicio (distribución,
    lineamientos) salvo que digan otra cosa."""
    from app.dof import ejercicio_de, normalizar

    documentos = []
    for nota in notas:
        if not re.search(programa["dof"], normalizar(nota["titulo"])):
            continue
        documentos.append({
            "url": nota["url"],
            "nombre": f"DOF {nota['fecha'].strftime('%d/%m/%Y')}: {nota['titulo']}"[:250],
            "etapa": etapa_por_nombre(nota["titulo"]) if "convocatoria" in nota["titulo"].casefold() else "REGLAS",
            "anio": ejercicio_de(nota["titulo"], nota["fecha"]),
        })
    return documentos


def documentos_tecnm(html: str, url_pagina: str, programa: dict) -> list[dict]:
    """Documentos del FAM en el sitio del TecNM (su menú los enlaza desde
    cualquier página): asignación = resultados; lineamientos o
    convocatoria según su nombre."""
    arbol = HTMLParser(html)
    documentos, vistos = [], set()
    for enlace in arbol.css("a[href]"):
        url = urljoin(url_pagina, enlace.attributes.get("href") or "").split("#", 1)[0].split("?", 1)[0]
        ruta = unquote(urlparse(url).path)
        if programa["tecnm"]["ruta"] not in ruta.lower() or not re.search(r"\.(pdf|xlsx?|docx?)$", ruta, re.I) or url in vistos:
            continue
        vistos.add(url)
        archivo = ruta.rsplit("/", 1)[-1]
        texto = " ".join(enlace.text().split())
        documentos.append({
            "url": url,
            "nombre": f"TecNM: {nombre_documento(archivo, texto)}"[:250],
            "etapa": etapa_por_nombre(f"{archivo} {texto}"),
            "anio": anio_de(archivo),
        })
    return documentos


def plan_por_ejercicio(documentos: list[dict], hoy: date | None = None) -> dict[int, dict]:
    """
    {ejercicio: {"etapa", "documentos"}} de los ejercicios vigentes o
    futuros que ya tienen reglas propias o convocatoria. Las reglas sin año
    se agregan a cada uno.
    """
    hoy = hoy or date.today()
    generales = [d for d in documentos if d["etapa"] == "REGLAS" and d["anio"] is None]
    ejercicios = sorted({
        d["anio"]
        for d in documentos
        if d["anio"] and d["anio"] >= hoy.year and d["etapa"] in {"REGLAS", "CONVOCATORIA"}
    })

    plan = {}
    for anio in ejercicios:
        propios = [d for d in documentos if d["anio"] == anio]
        etapas = {d["etapa"] for d in propios}
        etapa = "RESULTADOS" if "RESULTADOS" in etapas else "CONVOCATORIA" if "CONVOCATORIA" in etapas else "REGLAS"
        plan[anio] = {"etapa": etapa, "documentos": [*generales, *propios]}

    return plan


def cierre_de_convocatoria(documentos: list[dict], url_base: str) -> str | None:
    """Fecha de cierre de los PDF de la convocatoria (la guía), si la trae."""
    for documento in documentos:
        if documento["etapa"] != "CONVOCATORIA" or not documento["url"].lower().endswith(".pdf"):
            continue
        try:
            _, contenido, _ = descargar_mismo_dominio(documento["url"], url_base, max_bytes=MAX_BYTES_PDF)
            cierre = extraer_cierre_pdf(extraer_texto_pdf_bytes(contenido))
        except Exception:
            continue
        if cierre.get("usar_como_fecha_cierre") and cierre.get("fecha_cierre_postulacion"):
            return cierre["fecha_cierre_postulacion"]
    return None


def fuente_y_organismo(cursor, programa: dict) -> tuple[int, int]:
    cursor.execute("SELECT id FROM fuentes WHERE url_base = %s LIMIT 1", (programa["pagina"],))
    fila = cursor.fetchone()
    if fila:
        fuente_id = fila[0]
    else:
        # Fuente del programa: la revisa este módulo, no la fase de fuentes.
        cursor.execute(
            """
            INSERT INTO fuentes (nombre, url_base, tipo_fuente, selector_config, requiere_javascript,
                                 activa, frecuencia_scraping, created_at, updated_at)
            VALUES (%s, %s, 'WEB', %s, FALSE, TRUE, 1440, NOW(), NOW())
            RETURNING id
            """,
            (programa["fuente"], programa["pagina"], json.dumps({"programa_anual": programa["clave"]})),
        )
        fuente_id = cursor.fetchone()[0]

    nombre, sitio = programa["organismo"]
    cursor.execute("SELECT id FROM organismos WHERE nombre = %s LIMIT 1", (nombre,))
    fila = cursor.fetchone()
    if fila:
        organismo_id = fila[0]
    else:
        cursor.execute(
            """
            INSERT INTO organismos (nombre, sitio_web, tipo_organismo, pais, estado, created_at, updated_at)
            VALUES (%s, %s, 'FEDERAL', 'México', TRUE, NOW(), NOW())
            RETURNING id
            """,
            (nombre, sitio),
        )
        organismo_id = cursor.fetchone()[0]

    return fuente_id, organismo_id


def guardar_ejercicio(cursor, programa: dict, anio: int, datos: dict, cierre: str | None) -> dict:
    from app.guardar_resultado_scrapy import guardar_documentos

    titulo = programa["titulo"].format(anio=anio)
    url = f"{programa['pagina']}?ejercicio={anio}"
    descripcion = f"{programa['descripcion']}\n\nEjercicio {anio}. Etapa: {ETAPAS[datos['etapa']]}."
    fuente_id, organismo_id = fuente_y_organismo(cursor, programa)

    cursor.execute("SELECT id, estado, fecha_cierre FROM convocatorias WHERE url_hash = %s", (hash_url(url),))
    fila = cursor.fetchone()

    if fila is None and datos["etapa"] == "RESULTADOS":
        # Ejercicio ya terminado que nunca se siguió: no se crea.
        return {"anio": anio, "convocatoria_id": None, "accion": "OMITIDA_TERMINADA", "etapa": datos["etapa"],
                "fecha_cierre": None, "documentos": {"insertados": 0, "existentes": 0}}

    if fila is None:
        cursor.execute(
            """
            INSERT INTO convocatorias (titulo, descripcion, objetivo, fecha_cierre, fuente_id, organismo_id,
                                       url_original, url_hash, origen, estado, tipo, dirigido_a,
                                       fecha_extraccion, created_at, updated_at)
            VALUES (%s, %s, %s, %s, %s, %s, %s, %s, 'PROGRAMA', 'PENDIENTE_REVISION', 'FINANCIAMIENTO',
                    '["INSTITUCION"]'::jsonb, NOW(), NOW(), NOW())
            RETURNING id
            """,
            (titulo, descripcion, programa["objetivo"], cierre, fuente_id, organismo_id, url, hash_url(url)),
        )
        convocatoria_id, accion = cursor.fetchone()[0], "CREADA"
    else:
        convocatoria_id, accion = fila[0], "ACTUALIZADA"
        cursor.execute(
            """
            UPDATE convocatorias
            SET descripcion = %s,
                fecha_cierre = COALESCE(%s, fecha_cierre),
                -- Con resultados, el ejercicio terminó: pasa al historial.
                estado = CASE WHEN %s AND estado = 'PENDIENTE_REVISION' THEN 'CERRADA' ELSE estado END,
                updated_at = NOW()
            WHERE id = %s
            """,
            (descripcion, cierre, datos["etapa"] == "RESULTADOS", convocatoria_id),
        )

    archivos = guardar_documentos(cursor, convocatoria_id, datos["documentos"])
    return {"anio": anio, "convocatoria_id": convocatoria_id, "accion": accion, "etapa": datos["etapa"],
            "fecha_cierre": cierre, "documentos": archivos}


def toca_revisar(cursor) -> bool:
    cursor.execute(
        "SELECT updated_at FROM ajustes_sistema WHERE clave = %s AND updated_at > NOW() - make_interval(hours => %s)",
        (CLAVE_AJUSTE, HORAS_ENTRE_REVISIONES),
    )
    return cursor.fetchone() is None


def marcar_revisado(cursor) -> None:
    cursor.execute(
        """
        INSERT INTO ajustes_sistema (clave, valor, created_at, updated_at)
        VALUES (%s, %s, NOW(), NOW())
        ON CONFLICT (clave) DO UPDATE SET valor = EXCLUDED.valor, updated_at = NOW()
        """,
        (CLAVE_AJUSTE, date.today().isoformat()),
    )


def revisar_programas(guardar: bool = True, forzar: bool = False) -> dict:
    resultado = {"revisados": 0, "ejercicios": [], "errores": [], "omitido": False}

    conexion = conectar()
    try:
        with conexion.cursor() as cursor:
            if guardar and not forzar and not toca_revisar(cursor):
                resultado["omitido"] = True
                return resultado

            # DOF: los días que faltan por revisar (una petición por día).
            from app.dof import guardar_ultima_fecha, notas_nuevas

            notas, dof_leido, errores_dof = notas_nuevas(cursor)
            resultado["dof"] = {"notas": len(notas), "hasta": dof_leido}
            resultado["errores"].extend({"programa": "DOF", "error": e} for e in errores_dof)

            for programa in PROGRAMAS:
                partes = urlparse(programa["pagina"])
                url_base = f"{partes.scheme}://{partes.netloc}/"
                try:
                    url_final, contenido, _ = descargar_mismo_dominio(programa["pagina"], url_base, max_bytes=MAX_BYTES_PAGINA)
                    documentos = documentos_de_pagina(contenido.decode("utf-8", "replace"), url_final, programa)
                except Exception as error:
                    resultado["errores"].append({"programa": programa["clave"], "error": str(error)[:300]})
                    continue

                documentos += documentos_dof(notas, programa)

                if programa.get("tecnm"):
                    pagina_tecnm = programa["tecnm"]["pagina"]
                    partes_tecnm = urlparse(pagina_tecnm)
                    try:
                        url_tecnm, html_tecnm, _ = descargar_mismo_dominio(
                            pagina_tecnm, f"{partes_tecnm.scheme}://{partes_tecnm.netloc}/", max_bytes=MAX_BYTES_PAGINA
                        )
                        documentos += documentos_tecnm(html_tecnm.decode("utf-8", "replace"), url_tecnm, programa)
                    except Exception as error:
                        resultado["errores"].append({"programa": f"{programa['clave']} TecNM", "error": str(error)[:300]})

                resultado["revisados"] += 1

                for anio, datos in plan_por_ejercicio(documentos).items():
                    cierre = cierre_de_convocatoria(datos["documentos"], url_base)
                    if not guardar:
                        resultado["ejercicios"].append({
                            "programa": programa["clave"], "anio": anio, "etapa": datos["etapa"], "fecha_cierre": cierre,
                            "documentos": [f"[{d['etapa']}] {d['nombre']}" for d in datos["documentos"]],
                        })
                        continue
                    resultado["ejercicios"].append({"programa": programa["clave"], **guardar_ejercicio(cursor, programa, anio, datos, cierre)})

            if guardar:
                if dof_leido:
                    guardar_ultima_fecha(cursor, dof_leido)
                marcar_revisado(cursor)
                conexion.commit()
            else:
                conexion.rollback()
    finally:
        conexion.close()

    # Con fecha de cierre vigente, la decide el validador de siempre.
    if guardar:
        from app.validar_convocatoria import validar_convocatoria

        for ejercicio in resultado["ejercicios"]:
            if ejercicio.get("fecha_cierre") and str(ejercicio["fecha_cierre"]) >= date.today().isoformat():
                validacion = validar_convocatoria(ejercicio["convocatoria_id"], publicar=True)
                ejercicio["validacion"] = {k: validacion.get(k) for k in ("publicable", "score", "bloqueos", "estado_final")}

    return resultado


def main() -> None:
    parser = argparse.ArgumentParser(description="Programas anuales por etapas (FAM).")
    parser.add_argument("--guardar", action="store_true", help="Guarda en convocatorias (si no, vista previa).")
    argumentos = parser.parse_args()
    print(json.dumps(revisar_programas(guardar=argumentos.guardar, forzar=True), ensure_ascii=False, indent=2, default=str))


if __name__ == "__main__":
    main()
