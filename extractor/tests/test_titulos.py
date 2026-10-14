"""
Título de candidatos hallados como enlaces dentro de noticias.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_titulos -v
"""

from __future__ import annotations

import unittest

from app.descubrimiento.procesar_google_directo import (
    elegir_titulo_candidato,
)


ARTICULO = (
    "Ir a Japón para aprender tecnología e innovación es "
    "posible con una beca de México"
)


class ElegirTituloCandidatoTest(unittest.TestCase):

    def test_pdf_con_texto_generico_usa_nombre_del_archivo(self):
        # Caso real (descubrimiento 4): el enlace decía "edición 54".
        self.assertEqual(
            elegir_titulo_candidato(
                "edición 54",
                ARTICULO,
                "https://secihti.mx/wp-content/uploads/2026/07/"
                "Convocatoria_BEXT_ET_JICA_2026-20260728T005548.pdf",
            ),
            "Convocatoria BEXT ET JICA 2026",
        )

    def test_enlace_generico_sin_pdf_usa_titulo_de_la_noticia(self):
        for texto in ("aquí", "Descargar PDF", "Consulta las bases"):
            with self.subTest(texto=texto):
                self.assertEqual(
                    elegir_titulo_candidato(
                        texto,
                        ARTICULO,
                        "https://secihti.mx/convocatoria/x/",
                    ),
                    ARTICULO,
                )

    def test_texto_descriptivo_se_conserva(self):
        texto = "Bases de la Convocatoria Ciencia de Frontera 2026"

        self.assertEqual(
            elegir_titulo_candidato(
                texto,
                ARTICULO,
                "https://secihti.mx/bases.pdf",
            ),
            texto,
        )

    def test_archivo_con_espacios_codificados(self):
        self.assertEqual(
            elegir_titulo_candidato(
                "PDF",
                ARTICULO,
                "https://ejemplo.gob.mx/Bases%20Fondo%20Sectorial-2026.pdf",
            ),
            "Bases Fondo Sectorial 2026",
        )


if __name__ == "__main__":
    unittest.main()
