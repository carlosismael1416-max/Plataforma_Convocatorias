import unittest
from unittest import mock

from app import importar_desde_url as imp


class TituloDePaginaTest(unittest.TestCase):
    def test_prefiere_og_title_y_quita_el_nombre_del_sitio(self):
        html = '<html><head><meta property="og:title" content="Convocatoria Ciencia de Frontera 2026 | SECIHTI"><title>x</title></head></html>'
        self.assertEqual(imp.titulo_de_pagina(html), "Convocatoria Ciencia de Frontera 2026")

    def test_usa_h1_si_no_hay_og_title(self):
        self.assertEqual(imp.titulo_de_pagina("<h1>  Premio Estatal de Tesis 2026 </h1>"), "Premio Estatal de Tesis 2026")

    def test_sin_titulo(self):
        self.assertIsNone(imp.titulo_de_pagina("<p>hola</p>"))


class AnalizarUrlTest(unittest.TestCase):
    def test_rechaza_direcciones_privadas_sin_conectar_a_la_base(self):
        with mock.patch.object(imp, "conectar") as conectar:
            with self.assertRaises(imp.EnlaceInvalido):
                imp.analizar_url("http://127.0.0.1:8001/health")
            conectar.assert_not_called()

    def test_rechaza_esquemas_no_web(self):
        with self.assertRaises(imp.EnlaceInvalido):
            imp.analizar_url("file:///etc/passwd")


if __name__ == "__main__":
    unittest.main()
