import unittest
from datetime import date
from decimal import Decimal

from app.enriquecer_convocatoria import fechas_en_linea
from app.enriquecer_datos_extra import (
    extraer_requisitos,
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


class FechaEnOtraLineaTest(unittest.TestCase):
    def test_apertura_con_fecha_en_la_linea_siguiente(self):
        lineas = ["Apertura del sistema para captura", "5 de octubre de 2026", "Cierre 23 de octubre de 2026"]
        r = extraer_fechas_publicacion_inicio(lineas, fechas_en_linea)
        self.assertEqual(r["fecha_inicio"], date(2026, 10, 5))


class RequisitosTest(unittest.TestCase):
    def test_extrae_los_incisos_hasta_la_siguiente_seccion(self):
        lineas = [
            "IV. Requisitos",
            "Las instituciones interesadas deberán cumplir con lo siguiente para participar en la convocatoria vigente:",
            "a) Estar registradas en Rizoma con su registro actualizado al momento del cierre.",
            "b) Presentar la propuesta mediante el sistema en línea en tiempo y forma,",
            "con la carta institucional firmada.",
            "c) Contar con una persona responsable técnica adscrita a la institución.",
            "V. Calendario",
            "a) Publicación de resultados en enero.",
        ]
        r = extraer_requisitos(lineas)
        self.assertEqual(len(r), 3)
        self.assertIn("carta institucional firmada", r[1]["descripcion"])
        self.assertEqual(r[2]["orden"], 3)

    def test_sublista_y_pie_de_pagina(self):
        lineas = [
            "Requisitos",
            "a) Declarar tener la nacionalidad de alguno de los Estados Miembros de la OEA.",
            "b) Haber obtenido una Beca Nacional en alguna de las siguientes convocatorias: Página 4 de 10",
            "• Becas Nacionales para Estudios de Posgrado 2025",
            "• Becas Nacionales en Humanidades 2025",
            "c) Contar con carta de aceptación del programa de posgrado.",
        ]
        r = extraer_requisitos(lineas)
        self.assertEqual(len(r), 3)
        self.assertNotIn("Página", r[1]["descripcion"])
        self.assertIn("Humanidades 2025", r[1]["descripcion"])

    def test_poblacion_objetivo_y_restricciones(self):
        lineas = [
            "II. Población objetivo",
            "Instituciones de educación superior públicas y centros de investigación que editen revistas científicas nacionales.",
            "III. Modalidades",
            "a) Individual.",
            "V. Restricciones de participación",
            "a) No serán elegibles las instituciones con adeudos con la Secretaría.",
            "b) No podrán participar revistas con fines de lucro.",
            "VI. Calendario",
        ]
        r = extraer_requisitos(lineas)
        self.assertEqual([q["tipo_requisito"] for q in r], ["requisito", "restriccion", "restriccion"])
        self.assertIn("educación superior", r[0]["descripcion"])

    def test_sin_seccion_no_inventa(self):
        self.assertEqual(extraer_requisitos(["Bases", "a) algo largo de prueba para ver."]), [])


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
