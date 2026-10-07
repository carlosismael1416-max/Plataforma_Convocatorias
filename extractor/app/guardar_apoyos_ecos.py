
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

ARCHIVO_PREDETERMINADO = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_apoyos_preparados.json"
)

CLAVES_ESPERADAS = {
    "APOYO_MAXIMO_PROYECTO",
    "MAXIMO_ETAPA_ANUAL",
    "PASAJE_INTERNACIONAL",
    "VIATICOS_INVESTIGADORES",
    "VIATICOS_ESTUDIANTES",
}


def validar_datos(datos):
    convocatoria = datos["convocatoria"]
    apoyos = datos["apoyos"]

    if convocatoria["titulo"] != "Convocatoria Ecos Nord 2026":
        raise ValueError(
            "Este módulo está preparado para ECOS Nord 2026."
        )

    claves = [apoyo["clave"] for apoyo in apoyos]

    if len(claves) != len(set(claves)):
        raise ValueError("Existen conceptos duplicados.")

    if set(claves) != CLAVES_ESPERADAS:
        raise ValueError(
            "Los conceptos no coinciden con los "
            "cinco apoyos validados."
        )

    urls_pdf = {
        documento["url"]
        for documento in convocatoria["documentos"]
    }

    for apoyo in apoyos:
        importe = Decimal(str(apoyo["monto_maximo"]))

        if not importe.is_finite() or importe < 0:
            raise ValueError(
                f"Importe inválido: {apoyo['clave']}"
            )

        if apoyo["moneda"] not in {"MXN", "EUR"}:
            raise ValueError(
                f"Moneda inesperada: {apoyo['clave']}"
            )

        if apoyo["estado_documental"] != "PENDIENTE_REVISION":
            raise ValueError(
                f"Estado inesperado: {apoyo['clave']}"
            )

        incluido = apoyo.get("incluido_en_clave")

        if incluido is not None and incluido not in CLAVES_ESPERADAS:
            raise ValueError(
                f"Referencia de inclusión inválida: {incluido}"
            )

        fuentes = apoyo["fuentes_documentales"]

        if not fuentes:
            raise ValueError(
                f"Falta evidencia documental: {apoyo['clave']}"
            )

        for fuente in fuentes:
            if fuente["url_pdf"] not in urls_pdf:
                raise ValueError(
                    f"El PDF de {apoyo['clave']} "
                    "no pertenece a la convocatoria."
                )

            if int(fuente["pagina"]) < 1:
                raise ValueError("Número de página inválido.")

    por_clave = {
        apoyo["clave"]: apoyo
        for apoyo in apoyos
    }

    anual = por_clave["MAXIMO_ETAPA_ANUAL"]
    total = por_clave["APOYO_MAXIMO_PROYECTO"]

    if anual["incluido_en_clave"] != total["clave"]:
        raise ValueError(
            "El apoyo anual debe estar incluido "
            "en el apoyo máximo del proyecto."
        )

    if (
        Decimal(str(anual["monto_maximo"])) * 4
        != Decimal(str(total["monto_maximo"]))
    ):
        raise ValueError(
            "Las cuatro etapas no coinciden "
            "con el presupuesto total."
        )

    return convocatoria, apoyos


def consultar_convocatoria(convocatoria, fuente_id):
    url = normalizar_url(
        convocatoria["url_original"]
    )

    with conectar() as conexion:
        with conexion.cursor() as cursor:
            cursor.execute(
                "SELECT id FROM fuentes WHERE id = %s",
                (fuente_id,),
            )

            if cursor.fetchone() is None:
                raise ValueError(
                    f"No existe la fuente {fuente_id}."
                )

            existente = buscar_convocatoria(
                cursor,
                url,
                hash_url(url),
            )

            convocatoria_id = (
                existente[0] if existente else None
            )

            apoyos_existentes = 0

            if convocatoria_id is not None:
                cursor.execute(
                    """
                    SELECT COUNT(*)
                    FROM convocatoria_apoyos
                    WHERE convocatoria_id = %s
                    """,
                    (convocatoria_id,),
                )

                apoyos_existentes = cursor.fetchone()[0]

    return convocatoria_id, apoyos_existentes


def guardar_apoyos(convocatoria_id, apoyos):
    nuevos = 0
    existentes = 0

    # Los cinco apoyos se insertan en una
    # única transacción: todos o ninguno.
    with conectar() as conexion:
        with conexion.transaction():
            with conexion.cursor() as cursor:

                for apoyo in apoyos:
                    cursor.execute(
                        """
                        INSERT INTO convocatoria_apoyos (
                            convocatoria_id,
                            clave,
                            concepto,
                            componente,
                            monto_maximo,
                            moneda,
                            unidad,
                            incluido_en_clave,
                            condiciones,
                            fuentes_documentales,
                            estado_documental,
                            created_at,
                            updated_at
                        )
                        VALUES (
                            %s, %s, %s, %s, %s,
                            %s, %s, %s, %s, %s,
                            'PENDIENTE_REVISION',
                            NOW(), NOW()
                        )
                        ON CONFLICT (convocatoria_id, clave)
                        DO NOTHING
                        RETURNING id
                        """,
                        (
                            convocatoria_id,
                            apoyo["clave"],
                            apoyo["concepto"],
                            apoyo["componente"],
                            Decimal(
                                str(apoyo["monto_maximo"])
                            ),
                            apoyo["moneda"],
                            apoyo["unidad"],
                            apoyo["incluido_en_clave"],
                            Jsonb(apoyo["condiciones"]),
                            Jsonb(
                                apoyo["fuentes_documentales"]
                            ),
                        ),
                    )

                    if cursor.fetchone() is not None:
                        nuevos += 1
                        continue

                    # Si ya existe, comparar su contenido.
                    # No sobrescribir cambios ni aprobaciones
                    # realizadas posteriormente por una persona.
                    cursor.execute(
                        """
                        SELECT
                            concepto,
                            componente,
                            monto_maximo,
                            moneda,
                            unidad,
                            incluido_en_clave,
                            condiciones,
                            fuentes_documentales
                        FROM convocatoria_apoyos
                        WHERE convocatoria_id = %s
                          AND clave = %s
                        FOR UPDATE
                        """,
                        (
                            convocatoria_id,
                            apoyo["clave"],
                        ),
                    )

                    registrado = cursor.fetchone()

                    esperado = (
                        apoyo["concepto"],
                        apoyo["componente"],
                        Decimal(
                            str(apoyo["monto_maximo"])
                        ),
                        apoyo["moneda"],
                        apoyo["unidad"],
                        apoyo["incluido_en_clave"],
                        apoyo["condiciones"],
                        apoyo["fuentes_documentales"],
                    )

                    if registrado != esperado:
                        raise ValueError(
                            "El apoyo "
                            f"{apoyo['clave']} ya existe "
                            "con información diferente. "
                            "Se requiere revisión manual."
                        )

                    existentes += 1

    return {
        "nuevos": nuevos,
        "existentes": existentes,
    }


def main():
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--entrada",
        type=Path,
        default=ARCHIVO_PREDETERMINADO,
    )

    parser.add_argument(
        "--guardar",
        action="store_true",
    )

    argumentos = parser.parse_args()

    datos = json.loads(
        argumentos.entrada.read_text(
            encoding="utf-8"
        )
    )

    convocatoria, apoyos = validar_datos(datos)
    fuente_id = int(datos["fuente_id"])

    convocatoria_id, apoyos_existentes = (
        consultar_convocatoria(
            convocatoria,
            fuente_id,
        )
    )

    print("=== ALMACENAMIENTO ECOS NORD ===")
    print("Convocatoria:", convocatoria["titulo"])
    print("Fuente ID:", fuente_id)
    print("Convocatoria existente:", convocatoria_id or "NO")
    print("PDF asociados:", len(convocatoria["documentos"]))
    print("Apoyos preparados:", len(apoyos))
    print("Apoyos existentes:", apoyos_existentes)

    for apoyo in apoyos:
        print(
            apoyo["clave"],
            "|",
            apoyo["monto_maximo"],
            apoyo["moneda"],
            "|",
            apoyo["componente"],
        )

    if not argumentos.guardar:
        print(
            "\nVISTA PREVIA: "
            "No se modificó PostgreSQL."
        )
        return

    registro = guardar_resultado_scrapy(
        convocatoria,
        fuente_id,
    )

    print(
        "\nGuardado de convocatoria:",
        registro["estado"],
    )

    convocatoria_id = registro["convocatoria_id"]

    if not convocatoria_id:
        raise RuntimeError(
            "No se obtuvo el ID de la convocatoria."
        )

    resumen = guardar_apoyos(
        convocatoria_id,
        apoyos,
    )

    print("Convocatoria ID:", convocatoria_id)
    print("Apoyos nuevos:", resumen["nuevos"])
    print("Apoyos existentes:", resumen["existentes"])
    print("Guardado completado.")


if __name__ == "__main__":
    main()
