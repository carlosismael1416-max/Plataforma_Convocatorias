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
from app.urls import (
    hash_url,
)
from app.publicar_descubrimientos import (
    MAX_INTENTOS,
    buscar_convocatoria_exacta,
    cerrar_pendientes_vencidas,
    cerrar_publicadas_vencidas,
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

    def convocatoria(
        self,
        *,
        estado: str,
        fecha_cierre: str | None = None,
        padre: int | None = None,
    ) -> int:
        self.cursor.execute(
            """
            INSERT INTO convocatorias (
                titulo, estado, origen, url_hash,
                fecha_cierre, convocatoria_padre_id,
                created_at, updated_at
            )
            VALUES (
                'Prueba', %s, 'MANUAL', %s,
                %s::date, %s, NOW(), NOW()
            )
            RETURNING id
            """,
            (
                estado,
                uuid.uuid4().hex,
                fecha_cierre,
                padre,
            ),
        )

        return self.cursor.fetchone()[0]

    def estado_convocatoria(
        self,
        convocatoria_id: int,
    ) -> str:
        self.cursor.execute(
            "SELECT estado FROM convocatorias WHERE id = %s",
            (convocatoria_id,),
        )

        return self.cursor.fetchone()[0]

    def test_publicada_vencida_se_cierra(self):
        vencida = self.convocatoria(
            estado="PUBLICADA",
            fecha_cierre="2020-01-01",
        )
        vigente = self.convocatoria(
            estado="PUBLICADA",
            fecha_cierre="2999-01-01",
        )
        sin_fecha = self.convocatoria(estado="PUBLICADA")
        pendiente_vencida = self.convocatoria(
            estado="PENDIENTE_REVISION",
            fecha_cierre="2020-01-01",
        )

        cerrar_publicadas_vencidas(self.cursor)

        self.assertEqual(self.estado_convocatoria(vencida), "CERRADA")
        self.assertEqual(self.estado_convocatoria(vigente), "PUBLICADA")
        self.assertEqual(self.estado_convocatoria(sin_fecha), "PUBLICADA")
        self.assertEqual(
            self.estado_convocatoria(pendiente_vencida),
            "PENDIENTE_REVISION",
        )

    def test_padre_con_hijas_cerradas_se_cierra(self):
        padre_cerrable = self.convocatoria(estado="PUBLICADA")
        for _ in range(3):
            self.convocatoria(estado="CERRADA", padre=padre_cerrable)

        padre_abierto = self.convocatoria(estado="PUBLICADA")
        self.convocatoria(estado="CERRADA", padre=padre_abierto)
        self.convocatoria(
            estado="PUBLICADA",
            fecha_cierre="2999-01-01",
            padre=padre_abierto,
        )

        cerrar_publicadas_vencidas(self.cursor)

        self.assertEqual(
            self.estado_convocatoria(padre_cerrable),
            "CERRADA",
        )
        self.assertEqual(
            self.estado_convocatoria(padre_abierto),
            "PUBLICADA",
        )

    def test_busqueda_exacta_por_url_hash_ignora_variantes(self):
        url = "https://secihti.mx/prueba-hash/" + uuid.uuid4().hex

        self.cursor.execute(
            """
            INSERT INTO convocatorias (
                titulo, estado, origen, url_original, url_hash,
                created_at, updated_at
            )
            VALUES ('Prueba', 'PENDIENTE_REVISION', 'SCRAPING',
                    %s, %s, NOW(), NOW())
            RETURNING id
            """,
            (url, hash_url(url)),
        )
        convocatoria_id = self.cursor.fetchone()[0]

        variante = url.replace("https://", "https://www.") + "/"
        encontrada = buscar_convocatoria_exacta(self.conexion, variante)

        self.assertIsNotNone(encontrada)
        self.assertEqual(encontrada["id"], convocatoria_id)

        # La misma URL con otra forma choca con el índice único.
        with self.assertRaises(Exception):
            self.cursor.execute(
                """
                INSERT INTO convocatorias (
                    titulo, estado, origen, url_original, url_hash,
                    created_at, updated_at
                )
                VALUES ('Duplicada', 'PENDIENTE_REVISION', 'SCRAPING',
                        %s, %s, NOW(), NOW())
                """,
                (variante, hash_url(variante)),
            )

    def test_pendientes_vencidas_de_scraping_se_cierran(self):
        def crear(origen, fecha):
            self.cursor.execute(
                """
                INSERT INTO convocatorias (
                    titulo, estado, origen, url_hash, fecha_cierre,
                    created_at, updated_at
                )
                VALUES ('Prueba', 'PENDIENTE_REVISION', %s, %s,
                        %s::date, NOW(), NOW())
                RETURNING id
                """,
                (origen, uuid.uuid4().hex, fecha),
            )
            return self.cursor.fetchone()[0]

        scraping_vencida = crear("SCRAPING", "2020-01-01")
        scraping_vigente = crear("SCRAPING", "2999-01-01")
        manual_vencida = crear("MANUAL", "2020-01-01")

        cerrar_pendientes_vencidas(self.cursor)

        self.assertEqual(self.estado_convocatoria(scraping_vencida), "CERRADA")
        self.assertEqual(
            self.estado_convocatoria(scraping_vigente),
            "PENDIENTE_REVISION",
        )
        # Las manuales las revisa un directivo.
        self.assertEqual(
            self.estado_convocatoria(manual_vencida),
            "PENDIENTE_REVISION",
        )


if __name__ == "__main__":
    unittest.main()
