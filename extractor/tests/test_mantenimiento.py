import os
import tempfile
import time
import unittest
from pathlib import Path
from unittest import mock

from app import mantenimiento


class MantenimientoTest(unittest.TestCase):
    def test_borra_solo_temporales_viejos(self):
        with tempfile.TemporaryDirectory() as carpeta:
            viejo = Path(carpeta) / "propuesta_1.xlsx"
            nuevo = Path(carpeta) / "propuesta_2.xlsx"
            viejo.write_text("x")
            nuevo.write_text("x")
            (Path(carpeta) / ".gitignore").write_text("*")
            hace_dos_dias = time.time() - 48 * 3600
            os.utime(viejo, (hace_dos_dias, hace_dos_dias))

            self.assertEqual(mantenimiento.limpiar_temporales(Path(carpeta)), 1)
            self.assertFalse(viejo.exists())
            self.assertTrue(nuevo.exists())

    def test_un_error_de_base_no_tumba_el_ciclo(self):
        with mock.patch.object(mantenimiento, "conectar", side_effect=RuntimeError("sin base")):
            resultado = mantenimiento.ejecutar_mantenimiento()

        self.assertIn("sin base", resultado["error"])

    def test_solo_borra_descartados_viejos_sin_convocatoria(self):
        conexion = mock.MagicMock()
        cursor = conexion.cursor.return_value.__enter__.return_value
        cursor.rowcount = 0

        with mock.patch.object(mantenimiento, "conectar", return_value=conexion), \
             mock.patch.object(mantenimiento, "limpiar_temporales", return_value=0):
            mantenimiento.ejecutar_mantenimiento()

        sql = cursor.execute.call_args_list[0].args[0]
        self.assertIn("estado = 'DESCARTADO'", sql)
        self.assertIn("convocatoria_id IS NULL", sql)
        conexion.commit.assert_called_once()


if __name__ == "__main__":
    unittest.main()
