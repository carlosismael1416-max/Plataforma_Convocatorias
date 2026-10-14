"""
Revalidación de convocatorias pendientes (fase 4 del ciclo).

Sin red ni BD: se simulan la conexión, el enriquecimiento y el validador.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_revalidacion -v
"""

from __future__ import annotations

import time
import unittest

from unittest import mock

from app import publicar_descubrimientos as publicador


def validacion(publicable: bool) -> dict:
    return {
        "publicable": publicable,
        "score": 80 if publicable else 45,
        "umbral": 70,
        "bloqueos": [] if publicable else ["La extracción no obtuvo información suficiente."],
    }


class RevalidacionTest(unittest.TestCase):

    def ejecutar(self, terminar_antes_de=None):
        conexion = mock.MagicMock()
        cursor = conexion.cursor.return_value
        cursor.rowcount = 2
        cursor.fetchall.return_value = [(101, "Vigente completa"), (102, "Vigente incompleta")]

        with mock.patch.object(publicador, "conectar", return_value=conexion), \
             mock.patch.object(publicador, "cargar_contexto", return_value={}), \
             mock.patch.object(publicador, "construir_enriquecimiento", return_value={}), \
             mock.patch.object(publicador, "aplicar_enriquecimiento"), \
             mock.patch.object(publicador, "marcar_revisada") as marcar, \
             mock.patch.object(
                 publicador,
                 "validar_convocatoria",
                 side_effect=lambda cid, _, publicar: validacion(cid == 101),
             ):
            resumen = publicador.revalidar_pendientes(
                terminar_antes_de=terminar_antes_de,
            )

        consultas = " ".join(str(c.args[0]) for c in cursor.execute.call_args_list)

        return resumen, marcar, consultas

    def test_publica_las_completas_y_rota_las_demas(self):
        resumen, marcar, _ = self.ejecutar()

        self.assertEqual(resumen["cerradas_vencidas"], 2)
        self.assertEqual(resumen["revisadas"], 2)
        self.assertEqual(resumen["publicadas"], 1)
        self.assertEqual(resumen["siguen_pendientes"], 1)
        marcar.assert_called_once_with(102)

    def test_solo_considera_convocatorias_de_scraping(self):
        _, _, consultas = self.ejecutar()

        self.assertIn("origen = 'SCRAPING'", consultas)
        self.assertNotIn("MANUAL", consultas)

    def test_respeta_el_plazo_del_ciclo(self):
        resumen, _, _ = self.ejecutar(terminar_antes_de=time.monotonic() + 10)

        self.assertEqual(resumen["revisadas"], 0)
        self.assertEqual(resumen["pospuestas"], 2)


if __name__ == "__main__":
    unittest.main()
