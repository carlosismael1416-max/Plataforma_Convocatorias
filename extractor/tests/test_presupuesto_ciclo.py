"""
Presupuesto de tiempo de la fase de publicación.

Sin red ni BD: se simulan obtener_aprobados y procesar_descubrimiento.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_presupuesto_ciclo -v
"""

from __future__ import annotations

import time
import unittest

from unittest import mock

from app import publicar_descubrimientos as publicador


def aprobados(cantidad: int) -> list[dict]:
    return [
        {
            "id": numero,
            "titulo": f"Convocatoria {numero}",
            "url_original": f"https://secihti.mx/c/{numero}",
            "evaluacion_score": 100,
        }
        for numero in range(1, cantidad + 1)
    ]


class PresupuestoCicloTest(unittest.TestCase):

    def ejecutar(self, terminar_antes_de):
        procesados = []

        def procesar(descubrimiento):
            procesados.append(descubrimiento["id"])
            return {"estado": "REVISION_FINAL"}

        with mock.patch.object(
            publicador, "obtener_aprobados", return_value=aprobados(3)
        ), mock.patch.object(
            publicador, "procesar_descubrimiento", side_effect=procesar
        ):
            resumen = publicador.procesar_aprobados(
                5,
                terminar_antes_de=terminar_antes_de,
            )

        return procesados, resumen

    def test_sin_tiempo_no_se_empieza_ninguno(self):
        procesados, resumen = self.ejecutar(time.monotonic() + 60)

        self.assertEqual(procesados, [])
        self.assertEqual(resumen["pospuestos"], 3)

    def test_con_tiempo_suficiente_se_procesan_todos(self):
        procesados, resumen = self.ejecutar(
            time.monotonic()
            + 4 * publicador.SEGUNDOS_MAX_POR_DESCUBRIMIENTO
        )

        self.assertEqual(procesados, [1, 2, 3])
        self.assertEqual(resumen["pospuestos"], 0)

    def test_sin_limite_se_procesan_todos(self):
        procesados, _ = self.ejecutar(None)

        self.assertEqual(procesados, [1, 2, 3])


if __name__ == "__main__":
    unittest.main()
