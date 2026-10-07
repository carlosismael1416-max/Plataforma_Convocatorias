import json
import os
from decimal import Decimal
from pathlib import Path

import psycopg
from dotenv import load_dotenv
from psycopg.types.json import Jsonb


BASE_DIR = Path(__file__).resolve().parent.parent

load_dotenv(BASE_DIR / ".env")

ARCHIVO_JSON = (
    BASE_DIR
    / "data"
    / "extraidos"
    / "convocatoria_completa_2025.json"
)

CONVOCATORIA_ID = 2
FUENTE_ID = 2


def main():
    # Leer los financiamientos que ya extrajimos.
    with ARCHIVO_JSON.open(encoding="utf-8") as archivo:
        datos = json.load(archivo)

    grupos = datos["financiamiento"]["tipos_apoyo"]

    if not grupos:
        raise ValueError("El JSON no contiene financiamientos.")

    with psycopg.connect(
        host=os.getenv("DB_HOST", "127.0.0.1"),
        port=int(os.getenv("DB_PORT", "5432")),
        dbname=os.environ["DB_DATABASE"],
        user=os.environ["DB_USERNAME"],
        password=os.environ["DB_PASSWORD"],
    ) as conexion:

        with conexion.cursor() as cursor:

            # Comprobar que estamos trabajando
            # con la convocatoria y fuente correctas.
            cursor.execute(
                """
                SELECT url_original, fuente_id
                FROM convocatorias
                WHERE id = %s
                """,
                (CONVOCATORIA_ID,),
            )

            convocatoria = cursor.fetchone()

            if convocatoria is None:
                raise ValueError(
                    "No existe la convocatoria ID 2."
                )

            if (
                convocatoria[0] != datos["url_original"]
                or convocatoria[1] != FUENTE_ID
            ):
                raise ValueError(
                    "El ID no corresponde a la "
                    "convocatoria de SECIHTI del JSON."
                )

            insertados = 0
            existentes = 0

            for grupo in grupos:
                ejes = grupo["ejes_estrategicos"]

                clave = ",".join(
                    str(eje) for eje in ejes
                )

                etapa_1 = Decimal(
                    str(grupo["etapa_1"]["monto_maximo"])
                )

                etapa_2 = Decimal(
                    str(grupo["etapa_2"]["monto_maximo"])
                )

                total = Decimal(
                    str(grupo["monto_maximo_total"])
                )

                # Verificar los importes antes
                # de guardarlos en PostgreSQL.
                if etapa_1 + etapa_2 != total:
                    raise ValueError(
                        f"Los montos del grupo {clave} "
                        "no coinciden."
                    )

                cursor.execute(
                    """
                    INSERT INTO convocatoria_financiamientos (
                        convocatoria_id,
                        grupo_clave,
                        ejes_estrategicos,
                        etapa_1_anio,
                        etapa_1_monto_maximo,
                        etapa_2_anio,
                        etapa_2_monto_maximo,
                        monto_maximo_total,
                        moneda,
                        created_at,
                        updated_at
                    )
                    VALUES (
                        %s, %s, %s, %s, %s,
                        %s, %s, %s, %s,
                        NOW(), NOW()
                    )
                    ON CONFLICT (
                        convocatoria_id,
                        grupo_clave
                    )
                    DO NOTHING
                    RETURNING id
                    """,
                    (
                        CONVOCATORIA_ID,
                        clave,
                        Jsonb(ejes),
                        grupo["etapa_1"]["anio"],
                        etapa_1,
                        grupo["etapa_2"]["anio"],
                        etapa_2,
                        total,
                        grupo["moneda"],
                    ),
                )

                resultado = cursor.fetchone()

                if resultado:
                    insertados += 1
                    print(
                        f"Grupo {clave}: guardado "
                        f"con ID {resultado[0]}"
                    )
                else:
                    existentes += 1
                    print(
                        f"Grupo {clave}: ya existe, "
                        "no se duplicó."
                    )

            cursor.execute(
                """
                SELECT COUNT(*)
                FROM convocatoria_financiamientos
                WHERE convocatoria_id = %s
                """,
                (CONVOCATORIA_ID,),
            )

            total_guardados = cursor.fetchone()[0]

    print("\n=== RESULTADO ===")
    print("Convocatoria:", CONVOCATORIA_ID)
    print("Grupos nuevos:", insertados)
    print("Grupos ya existentes:", existentes)
    print("Total guardados:", total_guardados)


if __name__ == "__main__":
    main()
