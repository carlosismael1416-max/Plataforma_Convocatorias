import unittest
from datetime import date, timedelta

from app.tipo_convocatoria import clasificar_tipo
from app.validar_convocatoria import bloqueo_por_objetivo, evaluar_convocatoria

# Caso real: convocatoria 5 (Apoyo Complementario SECIHTI-OEA).
OBJETIVO_OEA = (
    "Apoyar en la formación de personas mediante el otorgamiento de apoyos complementarios "
    "a estudiantes con nacionalidad de cualquiera de los Estados Miembros de la OEA, con "
    "excepción de estudiantes mexicanos, para gastos de instalación en México."
)


class BloqueoPorObjetivoTest(unittest.TestCase):
    def test_programa_que_excluye_mexicanos_se_bloquea(self):
        self.assertIsNotNone(bloqueo_por_objetivo(OBJETIVO_OEA, ""))

    def test_financiamiento_de_proyectos_no_se_bloquea(self):
        # Caso real: convocatoria 4 (revistas de acceso abierto).
        objetivo = (
            "Impulsar el ecosistema nacional de comunicación científica mediante el apoyo a revistas "
            "nacionales de acceso abierto, por medio del financiamiento de proyectos orientados al "
            "desarrollo de capacidades editoriales."
        )
        self.assertIsNone(bloqueo_por_objetivo(objetivo, "Convocatoria para el fortalecimiento de revistas"))

    def test_becas_de_posgrado_ya_no_se_bloquean(self):
        self.assertIsNone(bloqueo_por_objetivo("Otorgar becas para estudios de posgrado en el extranjero.", ""))


class ClasificarTipoTest(unittest.TestCase):
    def casos(self):
        return [
            # (título, url, objetivo, tipo, dirigido_a)
            ("Convocatoria para el fortalecimiento de revistas e infraestructuras de acceso abierto 2026",
             "https://secihti.mx/convocatoria/ciencias-y-humanidades/revistas-2026", "",
             "FINANCIAMIENTO", ["INSTITUCION"]),
            ("Convocatoria Programa de Becas Económicas de Posgrado Yucatán 2026",
             "https://ciencia.yucatan.gob.mx/convocatorias/becas-posgrado-2026", "",
             "BECA", ["ESTUDIANTES"]),
            ("PREMIO ESTATAL DE TESIS YUCATÁN 2026, NIVEL POSGRADO", "", "",
             "PREMIO", ["ESTUDIANTES"]),
            ("CONCURSO DE ROBÓTICA", "https://ciencia.yucatan.gob.mx/convocatorias/concurso-de-robotica", "",
             "CONCURSO", []),
            ("ExpoCiencias Yucatán 2026", "", "", "EVENTO", []),
            ("Convocatoria Copa FutBotMX: Capítulo Visión por Computadora", "", "", "CONCURSO", []),
            ("Verano de la Ciencia del SIIDETEY", "", "", "FORMACION", ["ESTUDIANTES"]),
            ("Convocatoria 2026 Apoyo Complementario SECIHTI-OEA",
             "https://secihti.mx/convocatoria/becas-nacionales/apoyo-oea", OBJETIVO_OEA,
             "BECA", ["ESTUDIANTES"]),
            ("Convocatoria 2026 del proceso para ocupar el cargo de titular de la Dirección General", "", "",
             "NO_APLICA", []),
            ("Resultados de la convocatoria Ciencia de Frontera 2025", "", "", "NO_APLICA", []),
            ("Convocatoria de Proyectos de Investigación Científica",
             "", "Dirigida a profesores investigadores de los institutos tecnológicos.",
             "FINANCIAMIENTO", ["INSTITUCION", "DOCENTES"]),
        ]

    def test_casos_reales(self):
        for titulo, url, objetivo, tipo, dirigido in self.casos():
            with self.subTest(titulo=titulo):
                resultado = clasificar_tipo(titulo, url, objetivo, "")
                self.assertEqual(resultado["tipo"], tipo)
                self.assertEqual(resultado["dirigido_a"], dirigido)

    def test_el_objetivo_detecta_una_beca_sin_la_palabra_en_el_titulo(self):
        resultado = clasificar_tipo("Convocatoria Apoyos 2026", "", "Otorgamiento de becas a jóvenes talentos.", "")
        self.assertEqual(resultado["tipo"], "BECA")


class ValidadorConTiposTest(unittest.TestCase):
    def base(self, **cambios):
        datos = {
            "id": 1,
            "titulo": "Convocatoria Programa de Becas Económicas de Posgrado Yucatán 2026",
            "descripcion": "La Secretaría de Ciencia convoca a estudiantes a solicitar becas económicas para estudios de posgrado en instituciones del estado.",
            "objetivo": "Otorgar becas económicas a estudiantes de posgrado en Yucatán.",
            "url_original": "https://ciencia.yucatan.gob.mx/convocatorias/becas-posgrado-2026",
            "fuente_id": 4,
            "organismo_id": 3,
            "categoria_id": None,
            "fecha_cierre": date.today() + timedelta(days=20),
            "monto_minimo": None,
            "monto_maximo": None,
            "modalidad": None,
            "ubicacion": None,
        }
        datos.update(cambios)
        return datos

    def test_una_beca_vigente_se_publica_marcada(self):
        resultado = evaluar_convocatoria(self.base())
        self.assertTrue(resultado["publicable"], resultado["bloqueos"])
        self.assertEqual(resultado["tipo"], "BECA")
        self.assertEqual(resultado["dirigido_a"], ["ESTUDIANTES"])

    def test_la_beca_oea_sigue_sin_publicarse(self):
        resultado = evaluar_convocatoria(self.base(
            titulo="Convocatoria 2026 Apoyo Complementario SECIHTI-OEA",
            objetivo=OBJETIVO_OEA,
            url_original="https://secihti.mx/convocatoria/becas-nacionales/apoyo-oea",
        ))
        self.assertFalse(resultado["publicable"])
        self.assertEqual(resultado["tipo"], "NO_APLICA")

    def test_resultados_no_se_publican(self):
        resultado = evaluar_convocatoria(self.base(titulo="Resultados del Programa de Becas de Posgrado 2026"))
        self.assertFalse(resultado["publicable"])
        self.assertEqual(resultado["tipo"], "NO_APLICA")

    def test_vencida_no_se_publica_pero_conserva_su_tipo(self):
        resultado = evaluar_convocatoria(self.base(fecha_cierre=date.today() - timedelta(days=1)))
        self.assertFalse(resultado["publicable"])
        self.assertEqual(resultado["tipo"], "BECA")


class RetirarNoPublicablesTest(unittest.TestCase):
    def test_retira_solo_las_bloqueadas(self):
        from unittest import mock
        from app.seguimiento_convocatorias import retirar_no_publicables

        conexion = mock.MagicMock()
        cursor = conexion.cursor.return_value.__enter__.return_value
        cursor.fetchall.return_value = [
            (5, "Apoyo OEA", OBJETIVO_OEA, ""),
            (4, "Revistas", "financiamiento de proyectos editoriales", ""),
            (6, "Becas posgrado", "otorgar becas a estudiantes de posgrado", ""),
        ]

        retiradas = retirar_no_publicables(conexion)

        self.assertEqual([r["id"] for r in retiradas], [5])


if __name__ == "__main__":
    unittest.main()
