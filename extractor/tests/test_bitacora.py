import json
import unittest
from pathlib import Path
from unittest import mock

from app import bitacora


class IncidenciasDelCicloTest(unittest.TestCase):
    def test_extrae_un_error_por_fase(self):
        reporte = {
            "descubrimiento": {"ok": False, "codigo": 1, "error": "Google no respondió", "salida": "..."},
            "fuentes": {"resultados": [{"fuente_id": 4, "nombre": "Yucatán", "ok": False, "error": "HTTP 503"}]},
            "seguimiento": {"resultados": [{"id": 9, "estado": "ERROR", "error": "ReadTimeout"}, {"id": 4, "estado": "SIN_CAMBIOS"}]},
            "revalidacion": {"resultados": [
                {"convocatoria_id": 916, "estado": "ERROR", "error": "ReadTimeout: ciencia.yucatan.gob.mx"},
                {"convocatoria_id": 5, "estado": "PENDIENTE_REVISION", "bloqueos": ["No elegible"]},
            ]},
            "mantenimiento": {"error": None},
        }

        tipos = [i["tipo"] for i in bitacora.incidencias_del_ciclo(reporte)]

        self.assertEqual(tipos, ["BUSCADOR", "FUENTE", "SEGUIMIENTO", "REVALIDACION"])

    def test_ciclo_sin_errores_no_registra_nada(self):
        with mock.patch.object(bitacora, "conectar") as conectar:
            resultado = bitacora.registrar_incidencias_del_ciclo({"revalidacion": {"resultados": []}})
        conectar.assert_not_called()
        self.assertEqual(resultado, {"nuevas": 0, "repetidas": 0, "error": None})


class RegistrarIncidenciaTest(unittest.TestCase):
    def conexion(self, existente):
        conexion = mock.MagicMock()
        cursor = conexion.cursor.return_value.__enter__.return_value
        cursor.fetchone.return_value = existente
        return conexion, cursor

    def test_nueva(self):
        conexion, cursor = self.conexion(None)
        self.assertEqual(bitacora.registrar_incidencia(conexion, tipo="REVALIDACION", mensaje="ReadTimeout"), "NUEVA")
        self.assertIn("INSERT INTO bitacora_errores", cursor.execute.call_args_list[-1].args[0])

    def test_repetida_no_duplica(self):
        conexion, cursor = self.conexion((77,))
        self.assertEqual(bitacora.registrar_incidencia(conexion, tipo="REVALIDACION", mensaje="ReadTimeout"), "REPETIDA")
        ultima = cursor.execute.call_args_list[-1].args
        self.assertIn("UPDATE bitacora_errores", ultima[0])
        self.assertEqual(ultima[1][1], 77)


if __name__ == "__main__":
    unittest.main()
