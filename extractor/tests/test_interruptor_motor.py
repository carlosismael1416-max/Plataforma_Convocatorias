import json
import unittest
from unittest import mock

from app import ejecutar_ciclo_automatico as ciclo
from app import interruptor_motor


def conexion_con(valor):
    conexion = mock.MagicMock()
    cursor = conexion.cursor.return_value.__enter__.return_value
    cursor.fetchone.return_value = None if valor is None else (valor,)
    return conexion


class InterruptorTest(unittest.TestCase):
    def test_lee_encendido_y_apagado(self):
        with mock.patch.object(interruptor_motor, "conectar", return_value=conexion_con("0")):
            self.assertFalse(interruptor_motor.motor_activo())

        with mock.patch.object(interruptor_motor, "conectar", return_value=conexion_con("1")):
            self.assertTrue(interruptor_motor.motor_activo())

    def test_sin_fila_o_con_error_queda_encendido(self):
        with mock.patch.object(interruptor_motor, "conectar", return_value=conexion_con(None)):
            self.assertTrue(interruptor_motor.motor_activo())

        with mock.patch.object(interruptor_motor, "conectar", side_effect=RuntimeError("sin base")):
            self.assertTrue(interruptor_motor.motor_activo())

    def test_el_ciclo_no_trabaja_si_esta_apagado(self):
        with mock.patch.object(ciclo, "motor_activo", return_value=False), \
             mock.patch.object(ciclo, "conectar") as conectar, \
             mock.patch.object(ciclo, "ejecutar_descubridor") as descubridor, \
             mock.patch("pathlib.Path.write_text") as escribir:
            resultado = ciclo.ejecutar_ciclo(cantidad_google=1, max_dominios=1, max_publicaciones=1)

        self.assertEqual(resultado["estado"], "APAGADO")
        conectar.assert_not_called()
        descubridor.assert_not_called()
        self.assertEqual(json.loads(escribir.call_args.args[0])["estado"], "APAGADO")


if __name__ == "__main__":
    unittest.main()
