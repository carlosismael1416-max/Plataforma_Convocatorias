import unittest
from datetime import date, timedelta

from app.seguimiento_convocatorias import comparar, hay_cambios, resumen, texto_cambio

TEXTO = " ".join(f"palabra{i}" for i in range(400))


class CompararTest(unittest.TestCase):
    def actual(self, **extra):
        datos = {"texto": TEXTO, "documentos": [{"nombre": "Bases", "url": "https://x.mx/bases.pdf"}], "fecha_cierre_web": None}
        datos.update(extra)
        return datos

    def previo(self):
        return {"texto": TEXTO, "documentos": [{"nombre": "Bases", "url": "https://x.mx/bases.pdf"}]}

    def test_primera_consulta_no_reporta_cambios(self):
        self.assertFalse(hay_cambios(comparar(None, self.actual(), None)))

    def test_sin_cambios(self):
        self.assertFalse(hay_cambios(comparar(self.previo(), self.actual(), date.today() + timedelta(days=10))))

    def test_documento_nuevo(self):
        actual = self.actual(documentos=[
            {"nombre": "Bases", "url": "https://x.mx/bases.pdf"},
            {"nombre": "Anexo técnico", "url": "https://x.mx/anexo.pdf"},
        ])
        cambios = comparar(self.previo(), actual, None)

        self.assertEqual([d["url"] for d in cambios["documentos_nuevos"]], ["https://x.mx/anexo.pdf"])
        self.assertIn("Anexo técnico", resumen(cambios))

    def test_ampliacion_de_fecha_y_fecha_adelantada(self):
        hoy = date.today()
        ampliada = comparar(self.previo(), self.actual(fecha_cierre_web=hoy + timedelta(days=30)), hoy + timedelta(days=10))
        adelantada = comparar(self.previo(), self.actual(fecha_cierre_web=hoy + timedelta(days=5)), hoy + timedelta(days=10))

        self.assertEqual(ampliada["fecha_ampliada"], hoy + timedelta(days=30))
        self.assertIsNone(adelantada["fecha_ampliada"])
        self.assertEqual(adelantada["fecha_distinta"], hoy + timedelta(days=5))
        self.assertIn("verifícala", resumen(adelantada))

    def test_fecha_pasada_en_la_pagina_no_cuenta(self):
        cambios = comparar(self.previo(), self.actual(fecha_cierre_web=date.today() - timedelta(days=1)), None)
        self.assertFalse(hay_cambios(cambios))


class TextoTest(unittest.TestCase):
    def test_cambio_menor_no_cuenta(self):
        casi_igual = TEXTO.replace("palabra7 ", "visitas: 1234 ", 1)
        self.assertFalse(texto_cambio(TEXTO, casi_igual))

    def test_cambio_grande_cuenta(self):
        nuevo = TEXTO + " " + " ".join(f"nuevo{i}" for i in range(60))
        self.assertTrue(texto_cambio(TEXTO, nuevo))


if __name__ == "__main__":
    unittest.main()
