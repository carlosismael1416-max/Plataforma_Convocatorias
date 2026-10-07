
import argparse
import json
from decimal import Decimal
from pathlib import Path

from psycopg.types.json import Jsonb

from app.guardar_resultado_scrapy import (
    conectar,
    guardar_resultado_scrapy,
    buscar_convocatoria,
    normalizar_url,
    hash_url,
)


BASE_DIR = Path(__file__).resolve().parent.parent

ARCHIVO_FINANCIERO = (
    BASE_DIR
    / "data/resultados/ciencia_basica_2025_finanzas_integradas.json"
)

ARCHIVO_SCRAPING = (
    BASE_DIR / "scraping_vista_previa.json"
)


def cargar_datos(archivo_financiero, archivo_scraping):
    financiero = json.loads(
        archivo_financiero.read_text(encoding="utf-8")
    )

    resultados = json.loads(
        archivo_scraping.read_text(encoding="utf-8")
    )

    identidad = financiero["convocatoria"]
    url_buscada = identidad["url_original"].rstrip("/")

    coincidencias = [
        item
        for item in resultados
        if (item.get("url_original") or "").rstrip("/")
        == url_buscada
    ]

    if len(coincidencias) != 1:
        raise ValueError(
            "No se encontró exactamente una convocatoria "
            "con la URL del análisis financiero."
        )

    convocatoria = coincidencias[0]

    if convocatoria["titulo"] != identidad["titulo"]:
        raise ValueError(
            "El título del scraping y el análisis "
            "financiero no coinciden."
        )

    tabla = financiero.get("tabla_financiera")

    if not tabla:
        raise ValueError(
            "El JSON no contiene una tabla financiera."
        )

    if tabla["estado"] != "COINCIDENCIA_DOCUMENTAL":
        raise ValueError(
            "La tabla no presenta coincidencia "
            "entre los documentos."
        )

    documentos_pdf = {
        documento["documento"]: documento
        for documento in financiero["documentos_pdf"]
    }

    referencias = []

    for fuente in tabla["fuentes"]:
        nombre = fuente["documento"]

        if nombre not in documentos_pdf:
            raise ValueError(
                f"No se encontró el PDF: {nombre}"
            )

        documento = documentos_pdf[nombre]

        referencias.append({
            "documento": nombre,
            "url_pdf": documento["url_fuente"],
            "pagina": fuente["pagina"],
            "fragmento": fuente["fragmento"],
        })

    if len(referencias) != 2:
        raise ValueError(
            "Se esperaban dos referencias documentales."
        )

    etapas = tabla["etapas"]

    if not etapas:
        raise ValueError("La tabla no contiene etapas.")

    numeros = [etapa["numero"] for etapa in etapas]

    if len(numeros) != len(set(numeros)):
        raise ValueError(
            "Hay números de etapa duplicados."
        )

    for nombre, modalidad in tabla["modalidades"].items():
        importes = modalidad["etapas"]

        if len(importes) != len(etapas):
            raise ValueError(
                f"La modalidad {nombre} tiene "
                "un número incorrecto de etapas."
            )

        total = Decimal(str(modalidad["total"]))

        suma = sum(
            (Decimal(str(importe)) for importe in importes),
            Decimal("0"),
        )

        if suma != total:
            raise ValueError(
                f"Los montos de {nombre} no suman "
                "el total indicado."
            )

        if total < 0 or any(
            Decimal(str(importe)) < 0
            for importe in importes
        ):
            raise ValueError(
                f"Hay importes negativos en {nombre}."
            )

    return convocatoria, tabla, referencias


def guardar_modalidades(
    convocatoria_id,
    tabla,
    referencias,
):
    modalidades_nuevas = 0
    etapas_nuevas = 0

    nombres = {
        "INDIVIDUAL": "Individual",
        "GRUPO_INVESTIGACION": "Grupo de Investigación",
    }

    # Todas las modalidades y etapas se guardan
    # en una misma transacción.
    with conectar() as conexion:
        with conexion.transaction():
            with conexion.cursor() as cursor:

                for clave, datos in tabla["modalidades"].items():
                    nombre = nombres.get(
                        clave,
                        clave.replace("_", " ").title(),
                    )

                    total = Decimal(
                        str(datos["total"])
                    )

                    cursor.execute(
                        """
                        INSERT INTO convocatoria_modalidades (
                            convocatoria_id,
                            clave,
                            nombre,
                            moneda,
                            monto_maximo_total,
                            estado_documental,
                            fuentes_documentales,
                            created_at,
                            updated_at
                        )
                        VALUES (
                            %s, %s, %s, %s, %s,
                            'PENDIENTE_REVISION',
                            %s, NOW(), NOW()
                        )
                        ON CONFLICT (convocatoria_id, clave)
                        DO NOTHING
                        RETURNING id
                        """,
                        (
                            convocatoria_id,
                            clave,
                            nombre,
                            tabla["moneda"],
                            total,
                            Jsonb(referencias),
                        ),
                    )

                    insertado = cursor.fetchone()

                    if insertado:
                        modalidad_id = insertado[0]
                        modalidades_nuevas += 1
                    else:
                        cursor.execute(
                            """
                            SELECT
                                id,
                                nombre,
                                moneda,
                                monto_maximo_total
                            FROM convocatoria_modalidades
                            WHERE convocatoria_id = %s
                              AND clave = %s
                            FOR UPDATE
                            """,
                            (convocatoria_id, clave),
                        )

                        existente = cursor.fetchone()

                        if not existente:
                            raise RuntimeError(
                                "No se pudo recuperar la modalidad."
                            )

                        modalidad_id = existente[0]

                        if (
                            existente[1] != nombre
                            or existente[2] != tabla["moneda"]
                            or existente[3] != total
                        ):
                            raise ValueError(
                                f"La modalidad {clave} ya existe "
                                "con información diferente. "
                                "Se requiere revisión manual."
                            )

                    for etapa, importe in zip(
                        tabla["etapas"],
                        datos["etapas"],
                    ):
                        monto = Decimal(str(importe))

                        cursor.execute(
                            """
                            INSERT INTO convocatoria_modalidad_etapas (
                                modalidad_id,
                                numero,
                                anio,
                                monto_maximo,
                                created_at,
                                updated_at
                            )
                            VALUES (
                                %s, %s, %s, %s,
                                NOW(), NOW()
                            )
                            ON CONFLICT (modalidad_id, numero)
                            DO NOTHING
                            RETURNING id
                            """,
                            (
                                modalidad_id,
                                etapa["numero"],
                                etapa["anio"],
                                monto,
                            ),
                        )

                        etapa_insertada = cursor.fetchone()

                        if etapa_insertada:
                            etapas_nuevas += 1
                            continue

                        cursor.execute(
                            """
                            SELECT anio, monto_maximo
                            FROM convocatoria_modalidad_etapas
                            WHERE modalidad_id = %s
                              AND numero = %s
                            FOR UPDATE
                            """,
                            (
                                modalidad_id,
                                etapa["numero"],
                            ),
                        )

                        existente = cursor.fetchone()

                        if not existente or (
                            existente[0] != etapa["anio"]
                            or existente[1] != monto
                        ):
                            raise ValueError(
                                "Una etapa existente tiene "
                                "información diferente. "
                                "Se requiere revisión manual."
                            )

    return {
        "modalidades_nuevas": modalidades_nuevas,
        "etapas_nuevas": etapas_nuevas,
    }


def main():
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--fuente-id",
        type=int,
        default=2,
    )

    parser.add_argument(
        "--guardar",
        action="store_true",
        help="Autoriza el guardado en PostgreSQL.",
    )

    parser.add_argument(
        "--financiero",
        type=Path,
        default=ARCHIVO_FINANCIERO,
    )

    parser.add_argument(
        "--scraping",
        type=Path,
        default=ARCHIVO_SCRAPING,
    )

    argumentos = parser.parse_args()

    convocatoria, tabla, referencias = cargar_datos(
        argumentos.financiero,
        argumentos.scraping,
    )

    with conectar() as conexion:
        with conexion.cursor() as cursor:
            cursor.execute(
                "SELECT id FROM fuentes WHERE id = %s",
                (argumentos.fuente_id,),
            )

            if not cursor.fetchone():
                raise ValueError(
                    "No existe el ID de fuente indicado."
                )

            url_normalizada = normalizar_url(
                convocatoria["url_original"]
            )

            existente = buscar_convocatoria(
                cursor,
                url_normalizada,
                hash_url(url_normalizada),
            )

    print("=== ALMACENAMIENTO FINANCIERO ===")
    print("Convocatoria:", convocatoria["titulo"])
    print("Fuente ID:", argumentos.fuente_id)
    print("Convocatoria existente:", existente[0] if existente else "NO")
    print("PDF asociados:", len(convocatoria["documentos"]))
    print("Referencias financieras:", len(referencias))
    print("Modalidades:", len(tabla["modalidades"]))
    print("Etapas por modalidad:", len(tabla["etapas"]))
    print("Moneda:", tabla["moneda"])

    for clave, datos in tabla["modalidades"].items():
        print()
        print(clave)
        print("Montos:", datos["etapas"])
        print("Total:", datos["total"])

    if not argumentos.guardar:
        print(
            "\nVISTA PREVIA: "
            "No se modificó PostgreSQL."
        )
        return

    resultado = guardar_resultado_scrapy(
        convocatoria,
        argumentos.fuente_id,
    )

    print("\nGuardado de convocatoria:", resultado["estado"])

    convocatoria_id = resultado["convocatoria_id"]

    if not convocatoria_id:
        raise RuntimeError(
            "No se obtuvo el ID de la convocatoria."
        )

    resumen = guardar_modalidades(
        convocatoria_id,
        tabla,
        referencias,
    )

    print("Convocatoria ID:", convocatoria_id)
    print("Modalidades nuevas:", resumen["modalidades_nuevas"])
    print("Etapas nuevas:", resumen["etapas_nuevas"])
    print("Guardado completado.")


if __name__ == "__main__":
    main()
