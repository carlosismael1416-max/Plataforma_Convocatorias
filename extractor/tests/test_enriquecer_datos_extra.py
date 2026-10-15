import unittest
from datetime import date
from decimal import Decimal

from app.enriquecer_convocatoria import fechas_en_linea
from app.enriquecer_datos_extra import (
    descripcion_de_pagina,
    extraer_fechas_publicacion_inicio,
    extraer_montos,
)


class MontosTest(unittest.TestCase):
    def test_monto_maximo_con_hasta(self):
        r = extraer_montos("Se otorgará un apoyo de hasta $2,000,000.00 por proyecto.")
        self.assertEqual(r["monto_maximo"], Decimal("2000000.00"))
        self.assertEqual(r["moneda"], "MXN")

    def test_millones_de_pesos_y_minimo(self):
        r = extraer_montos(
            "El monto mínimo será de $500,000 y el financiamiento máximo de 3 millones de pesos."
        )
        self.assertEqual(r["monto_minimo"], Decimal("500000"))
        self.assertEqual(r["monto_maximo"], Decimal("3000000"))

    def test_dolares(self):
        r = extraer_montos("Apoyo de hasta USD 15,000 para cada propuesta.")
        self.assertEqual(r["moneda"], "USD")
        self.assertEqual(r["monto_maximo"], Decimal("15000"))

    def test_ignora_cantidades_sin_contexto_o_pequenas(self):
        r = extraer_montos("Teléfono 999 123 4567. Cuota de inscripción $500. Folio 2026.")
        self.assertIsNone(r["monto_maximo"])
        self.assertIsNone(r["moneda"])


    def test_ignora_tabuladores_de_honorarios(self):
        # Caso real: términos de referencia de la convocatoria de revistas 2026.
        r = extraer_montos(
            "tabulador 2026 (netos) del gasto licenciatura hasta por $16,000.00 cfdi "
            "maestria hasta por $24,000.00 cfdi doctorado hasta por $30,000.00 cfdi "
            "estos montos consideran una colaboracion en el proyecto"
        )
        self.assertIsNone(r["monto_maximo"])


class FechasTest(unittest.TestCase):
    def test_publicacion_e_inicio(self):
        lineas = [
            "Fecha de publicación de la convocatoria: 15 de septiembre de 2026",
            "Apertura del sistema de registro: 1 de octubre de 2026",
            "Cierre: 30 de noviembre de 2026",
        ]
        r = extraer_fechas_publicacion_inicio(lineas, fechas_en_linea)
        self.assertEqual(r["fecha_publicacion"], date(2026, 9, 15))
        self.assertEqual(r["fecha_inicio"], date(2026, 10, 1))

    def test_no_confunde_inicio_del_proyecto(self):
        lineas = [
            "Fecha de inicio de los proyectos: 20 de enero de 2027",
            "Apertura del sistema para captura de solicitudes: 5 de octubre de 2026",
        ]
        r = extraer_fechas_publicacion_inicio(lineas, fechas_en_linea)
        self.assertEqual(r["fecha_inicio"], date(2026, 10, 5))


class DescripcionTest(unittest.TestCase):
    def test_toma_el_texto_despues_del_titulo(self):
        texto = (
            "Inicio Convocatorias Convocatoria Ciencia Abierta 2026 "
            "La Secretaría convoca a instituciones de educación superior a presentar proyectos "
            "que fortalezcan la ciencia abierta en el país. Las propuestas deberán atender "
            "problemáticas nacionales prioritarias. Más información en la página."
        )
        d = descripcion_de_pagina(texto, "Convocatoria Ciencia Abierta 2026")
        self.assertTrue(d.startswith("La Secretaría convoca"))

    def test_sin_texto_suficiente(self):
        self.assertIsNone(descripcion_de_pagina("Inicio Contacto", "Algo"))


if __name__ == "__main__":
    unittest.main()
