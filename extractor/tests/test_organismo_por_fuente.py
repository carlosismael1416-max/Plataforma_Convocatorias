import unittest
from unittest import mock

from app import enriquecer_convocatoria as enr

ORGANISMOS = [
    (1, "Diagram consultores sector", "https://www.diagramconsultores.com/convocatorias/"),
    (2, "Secretaría de Ciencia, Humanidades, Tecnología e Innovación (SECIHTI)", "https://secihti.mx"),
]


class OrganismoPorFuenteTest(unittest.TestCase):
    def buscar(self, url, nombre, organismos=ORGANISMOS):
        conexion = mock.MagicMock()
        conexion.cursor.return_value.fetchall.return_value = organismos
        with mock.patch.object(enr, "conectar", return_value=conexion):
            return enr.buscar_organismo_existente(url, nombre)

    def test_por_dominio(self):
        self.assertEqual(self.buscar("https://www.secihti.mx/convocatorias/", "SECIHTI - Convocatorias"), 2)

    def test_secretaria_de_yucatan_no_es_secihti(self):
        # Caso real: la Feria de Medicina Tradicional Maya quedó con SECIHTI.
        self.assertIsNone(self.buscar(
            "https://ciencia.yucatan.gob.mx/convocatorias/",
            "Secretaría de Ciencia de Yucatán - Convocatorias",
        ))

    def test_por_nombre_solo_sin_sitio_web_y_con_palabras_propias(self):
        organismos = [(7, "Fundación Gonzalo Río Arronte", None)]
        self.assertEqual(self.buscar("https://fundacion.org.mx/apoyos", "Fundación Gonzalo Río Arronte", organismos), 7)
        self.assertIsNone(self.buscar("https://otra.org.mx/", "Secretaría de Ciencia Nacional", organismos))


if __name__ == "__main__":
    unittest.main()
