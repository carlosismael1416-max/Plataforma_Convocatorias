
import argparse
import json
from decimal import Decimal
from pathlib import Path

from psycopg.types.json import Jsonb

from app.guardar_resultado_scrapy import conectar


BASE_DIR = Path(__file__).resolve().parent.parent

ARCHIVO = (
    BASE_DIR
    / "data/resultados/ecos_nord_2026_apoyos_preparados.json"
)

CONVOCATORIA_ID = 9

# Condiciones provisionales que guardamos
# originalmente en PostgreSQL.
CONDICIONES_ANTERIORES = {
    "PASAJE_INTERNACIONAL": {
        "verificar_beneficiarios_y_limites": True,
    },
    "VIATICOS_INVESTIGADORES": {
        "verificar_limite_de_dias": True,
    },
    "VIATICOS_ESTUDIANTES": {
        "verificar_limite_de_dias": True,
    },
}


def main():
    parser = argparse.ArgumentParser()

    parser.add_argument(
        "--guardar",
        action="store_true",
        help="Aplicar los cambios en PostgreSQL.",
    )

    argumentos = parser.parse_args()

    datos = json.loads(
        ARCHIVO.read_text(encoding="utf-8")
    )

    if (
        datos["convocatoria"]["titulo"]
        != "Convocatoria Ecos Nord 2026"
    ):
        raise ValueError(
            "El JSON no corresponde a ECOS Nord 2026."
        )

    apoyos = {
        apoyo["clave"]: apoyo
        for apoyo in datos["apoyos"]
    }

    if len(apoyos) != 5:
        raise ValueError(
            "Se esperaban los cinco apoyos originales."
        )

    claves = set(CONDICIONES_ANTERIORES)

    franceses = {
        clave: apoyo
        for clave, apoyo in apoyos.items()
        if apoyo["componente"] == "FRANCES"
    }

    if set(franceses) != claves:
        raise ValueError(
            "Los tres apoyos franceses no coinciden."
        )

    with conectar() as conexion:
        with conexion.transaction():
            with conexion.cursor() as cursor:

                cursor.execute(
                    """
                    SELECT titulo, url_original
                    FROM convocatorias
                    WHERE id = %s
                    """,
                    (CONVOCATORIA_ID,),
                )

                convocatoria = cursor.fetchone()

                if not convocatoria:
                    raise ValueError(
                        "No existe la convocatoria 9."
                    )

                if (
                    convocatoria[0]
                    != datos["convocatoria"]["titulo"]
                    or
                    (convocatoria[1] or "").rstrip("/")
                    != datos["convocatoria"]["url_original"].rstrip("/")
                ):
                    raise ValueError(
                        "La identidad de la convocatoria "
                        "no coincide con el JSON."
                    )

                cursor.execute(
                    """
                    SELECT
                        id,
                        clave,
                        monto_maximo,
                        moneda,
                        estado_documental,
                        condiciones
                    FROM convocatoria_apoyos
                    WHERE convocatoria_id = %s
                      AND componente = 'FRANCES'
                    FOR UPDATE
                    """,
                    (CONVOCATORIA_ID,),
                )

                registros = {
                    fila[1]: fila
                    for fila in cursor.fetchall()
                }

                if set(registros) != claves:
                    raise ValueError(
                        "Los apoyos franceses registrados "
                        "no coinciden con los esperados."
                    )

                pendientes = []

                print("=== CONDICIONES DE ECOS NORD ===")

                for clave, apoyo in franceses.items():
                    registro = registros[clave]

                    (
                        apoyo_id,
                        _,
                        monto,
                        moneda,
                        estado,
                        condiciones_actuales,
                    ) = registro

                    condiciones_nuevas = apoyo["condiciones"]
                    condiciones_anteriores = (
                        CONDICIONES_ANTERIORES[clave]
                    )

                    if (
                        monto != Decimal(
                            str(apoyo["monto_maximo"])
                        )
                        or moneda != apoyo["moneda"]
                    ):
                        raise ValueError(
                            f"El importe de {clave} cambió."
                        )

                    if estado != "PENDIENTE_REVISION":
                        raise ValueError(
                            f"El apoyo {clave} ya tiene "
                            "otro estado de revisión."
                        )

                    if condiciones_actuales == condiciones_nuevas:
                        situacion = "YA_ACTUALIZADO"

                    elif (
                        condiciones_actuales
                        == condiciones_anteriores
                    ):
                        situacion = "LISTO_PARA_ACTUALIZAR"

                        pendientes.append((
                            apoyo_id,
                            clave,
                            condiciones_anteriores,
                            condiciones_nuevas,
                        ))

                    else:
                        raise ValueError(
                            f"{clave} tiene condiciones "
                            "diferentes de las provisionales. "
                            "No se sobrescribirán."
                        )

                    print("\nConcepto:", clave)
                    print("Estado:", situacion)
                    print(
                        "Condiciones nuevas:",
                        json.dumps(
                            condiciones_nuevas,
                            ensure_ascii=False,
                        ),
                    )

                print(
                    "\nApoyos que necesitan actualización:",
                    len(pendientes),
                )

                if not argumentos.guardar:
                    print(
                        "VISTA PREVIA: "
                        "No se modificó PostgreSQL."
                    )
                    return

                for (
                    apoyo_id,
                    clave,
                    anteriores,
                    nuevas,
                ) in pendientes:
                    cursor.execute(
                        """
                        UPDATE convocatoria_apoyos
                        SET condiciones = %s,
                            updated_at = NOW()
                        WHERE id = %s
                          AND condiciones = %s
                          AND estado_documental =
                              'PENDIENTE_REVISION'
                        RETURNING id
                        """,
                        (
                            Jsonb(nuevas),
                            apoyo_id,
                            Jsonb(anteriores),
                        ),
                    )

                    if cursor.fetchone() is None:
                        raise RuntimeError(
                            f"No se pudo actualizar {clave}. "
                            "Se revertirán todos los cambios."
                        )

                print(
                    "\nApoyos actualizados:",
                    len(pendientes),
                )
                print("Actualización completada.")


if __name__ == "__main__":
    main()
