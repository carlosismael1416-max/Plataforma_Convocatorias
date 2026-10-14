"""
Pruebas de los barridos del publicador contra PostgreSQL.

Cada prueba inserta filas de prueba dentro de una transacción y la
revierte al final: no deja datos. Se omiten si no hay conexión.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_publicacion_bd -v
"""

from __future__ import annotations

import hashlib
import unittest
import uuid

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)
from app.publicar_descubrimientos import (
    MAX_INTENTOS,
    cerrar_temporales_agotados,
    descartar_temporales_no_elegibles,
    recuperar_en_proceso_abandonados,
)


def conexion_disponible() -> bool:
    try:
        conectar().close()
        return True

    except Exception:
        return False


@unittest.skipUnless(
    conexion_disponible(),
    "PostgreSQL no disponible",
)
class BarridosPublicadorTest(unittest.TestCase):

    def setUp(self):
        self.conexion = conectar()
        self.cursor = self.conexion.cursor()

    def tearDown(self):
        self.conexion.rollback()
        self.conexion.close()

    def insertar(
        self,
        *,
        estado: str,
        intentos: int = 1,
        antiguedad: str = "0 minutes",
        titulo: str = "Convocatoria de investigación 2026",
        url: str | None = None,
    ) -> int:
        url = url or (
            "https://secihti.mx/prueba/"
            + uuid.uuid4().hex
        )

        self.cursor.execute(
            """
            INSERT INTO descubrimientos_web (
                titulo,
                url_original,
                url_normalizada,
                url_hash,
                estado,
                intentos_procesamiento,
                evaluacion_score,
                updated_at
            )
            VALUES (
                %s, %s, %s, %s, %s, %s, 100,
                NOW() - %s::interval
            )
            RETURNING id
            """,
            (
                titulo,
                url,
                url,
                hashlib.sha256(url.encode()).hexdigest(),
                estado,
                intentos,
                antiguedad,
            ),
        )

        return self.cursor.fetchone()[0]

    def estado(
        self,
        descubrimiento_id: int,
    ) -> str:
        self.cursor.execute(
            "SELECT estado FROM descubrimientos_web WHERE id = %s",
            (descubrimiento_id,),
        )

        return self.cursor.fetchone()[0]

    def test_en_proceso_abandonado_vuelve_a_error_temporal(self):
        abandonado = self.insertar(
            estado="EN_PROCESO",
            antiguedad="3 hours",
        )
        reciente = self.insertar(
            estado="EN_PROCESO",
            antiguedad="10 minutes",
        )

        recuperar_en_proceso_abandonados(self.cursor)

        self.assertEqual(self.estado(abandonado), "ERROR_TEMPORAL")
        self.assertEqual(self.estado(reciente), "EN_PROCESO")

    def test_error_temporal_con_intentos_agotados_se_cierra(self):
        agotado = self.insertar(
            estado="ERROR_TEMPORAL",
            intentos=MAX_INTENTOS,
        )
        con_intentos = self.insertar(
            estado="ERROR_TEMPORAL",
            intentos=1,
        )

        cerrar_temporales_agotados(self.cursor)

        self.assertEqual(self.estado(agotado), "FUENTE_INACCESIBLE")
        self.assertEqual(self.estado(con_intentos), "ERROR_TEMPORAL")

    def test_error_temporal_no_elegible_se_descarta(self):
        otro_estado = self.insertar(
            estado="ERROR_TEMPORAL",
            titulo=(
                "Presenta COQHCYT la convocatoria de Fomento "
                "e Impulso a la Investigación Aplicada 2026"
            ),
            url=(
                "https://cgc.qroo.gob.mx/prueba-"
                + uuid.uuid4().hex
            ),
        )

        descartar_temporales_no_elegibles(self.cursor)

        self.assertEqual(self.estado(otro_estado), "DESCARTADO")

    def test_abandonado_sin_intentos_termina_inaccesible(self):
        # Mismo orden que obtener_aprobados().
        descubrimiento = self.insertar(
            estado="EN_PROCESO",
            intentos=MAX_INTENTOS,
            antiguedad="3 hours",
        )

        recuperar_en_proceso_abandonados(self.cursor)
        descartar_temporales_no_elegibles(self.cursor)
        cerrar_temporales_agotados(self.cursor)

        self.assertEqual(
            self.estado(descubrimiento),
            "FUENTE_INACCESIBLE",
        )


if __name__ == "__main__":
    unittest.main()
