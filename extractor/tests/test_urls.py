"""
Normalización canónica de URLs (app/urls.py).

El mismo cálculo existe en Laravel (Convocatoria::hashUrl); si cambias
uno, cambia el otro y vuelve a correr scripts/recalcular_url_hash.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_urls -v
"""

from __future__ import annotations

import unittest

from app.urls import hash_url, normalizar_url


class UrlCanonicaTest(unittest.TestCase):

    def test_variantes_de_la_misma_url_dan_el_mismo_hash(self):
        variantes = (
            "https://www.secihti.mx/convocatoria/x/",
            "https://secihti.mx/convocatoria/x",
            "HTTPS://SECIHTI.MX/convocatoria/x",
            "https://secihti.mx/convocatoria/x#bases",
            "  https://secihti.mx/convocatoria/x/  ",
        )

        self.assertEqual(
            len({hash_url(url) for url in variantes}),
            1,
        )

    def test_ruta_y_query_distinguen_urls(self):
        self.assertNotEqual(
            hash_url("https://secihti.mx/convocatoria/x"),
            hash_url("https://secihti.mx/convocatoria/y"),
        )
        self.assertNotEqual(
            hash_url("https://secihti.mx/c?id=1"),
            hash_url("https://secihti.mx/c?id=2"),
        )

    def test_forma_canonica(self):
        self.assertEqual(
            normalizar_url("HTTPS://WWW.Secihti.MX/Ruta/?a=1#f"),
            "https://secihti.mx/Ruta?a=1",
        )
        self.assertEqual(
            normalizar_url("http://secihti.mx"),
            "http://secihti.mx/",
        )


if __name__ == "__main__":
    unittest.main()
