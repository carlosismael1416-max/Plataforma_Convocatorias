import json
import os
import tempfile
import unittest
from pathlib import Path
from unittest import mock

from scripts import registrar_fallo_ciclo as registro


class RegistrarFalloTest(unittest.TestCase):
    def test_ciclo_exitoso_no_hace_nada(self):
        with tempfile.TemporaryDirectory() as carpeta, \
             mock.patch.object(registro, "REPORTE", Path(carpeta) / "r.json"), \
             mock.patch.dict(os.environ, {"SERVICE_RESULT": "success"}):
            registro.main()
            self.assertFalse((Path(carpeta) / "r.json").exists())

    def test_ciclo_fallido_deja_reporte_y_bitacora(self):
        conexion = mock.MagicMock()
        conexion.__enter__.return_value = conexion

        with tempfile.TemporaryDirectory() as carpeta, \
             mock.patch.object(registro, "REPORTE", Path(carpeta) / "r.json"), \
             mock.patch.object(registro, "ultimas_lineas", return_value="ModuleNotFoundError: app.x"), \
             mock.patch.dict(os.environ, {"SERVICE_RESULT": "exit-code", "EXIT_STATUS": "1", "DB_DATABASE": "x", "DB_USERNAME": "y"}), \
             mock.patch("psycopg.connect", return_value=conexion):
            registro.main()
            reporte = json.loads((Path(carpeta) / "r.json").read_text(encoding="utf-8"))

        self.assertEqual(reporte["estado"], "FALLIDO")
        self.assertIn("ModuleNotFoundError", reporte["detalle"])
        sql, parametros = conexion.execute.call_args.args
        self.assertIn("CICLO_FALLIDO", sql)
        self.assertEqual(parametros[0], "1")


if __name__ == "__main__":
    unittest.main()
