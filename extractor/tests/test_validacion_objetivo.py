import unittest

from app.validar_convocatoria import bloqueo_por_objetivo


class BloqueoPorObjetivoTest(unittest.TestCase):
    def test_beca_oea_se_detecta_por_el_objetivo(self):
        # Caso real: convocatoria 5 (Apoyo Complementario SECIHTI-OEA).
        objetivo = (
            "Apoyar en la formación de personas mediante el otorgamiento de apoyos complementarios "
            "a estudiantes con nacionalidad de cualquiera de los Estados Miembros de la OEA."
        )
        descripcion = "La Secretaría, a través de la Dirección General de Becas y Apoyos a la Comunidad Científica..."
        self.assertIsNotNone(bloqueo_por_objetivo(objetivo, descripcion))

    def test_financiamiento_de_proyectos_no_se_bloquea(self):
        # Caso real: convocatoria 4 (revistas de acceso abierto).
        objetivo = (
            "Impulsar el ecosistema nacional de comunicación científica mediante el apoyo a revistas "
            "nacionales de acceso abierto, por medio del financiamiento de proyectos orientados al "
            "desarrollo de capacidades editoriales."
        )
        self.assertIsNone(bloqueo_por_objetivo(objetivo, "Convocatoria para el fortalecimiento de revistas"))

    def test_proyecto_que_menciona_estudiantes_no_se_bloquea(self):
        objetivo = (
            "Financiar proyectos de investigación de instituciones de educación superior que "
            "incorporen la participación de estudiantes en sus equipos."
        )
        self.assertIsNone(bloqueo_por_objetivo(objetivo, ""))

    def test_becas_de_posgrado(self):
        self.assertIsNotNone(bloqueo_por_objetivo("Otorgar becas para estudios de posgrado en el extranjero.", ""))


if __name__ == "__main__":
    unittest.main()


class RetirarNoPublicablesTest(unittest.TestCase):
    def test_retira_solo_las_bloqueadas(self):
        from unittest import mock
        from app.seguimiento_convocatorias import retirar_no_publicables

        conexion = mock.MagicMock()
        cursor = conexion.cursor.return_value.__enter__.return_value
        cursor.fetchall.return_value = [
            (5, "Apoyo OEA", "otorgamiento de apoyos complementarios a estudiantes de la OEA", ""),
            (4, "Revistas", "financiamiento de proyectos editoriales", ""),
        ]

        retiradas = retirar_no_publicables(conexion)

        self.assertEqual([r["id"] for r in retiradas], [5])
        actualizaciones = [c for c in cursor.execute.call_args_list if "UPDATE" in c.args[0]]
        self.assertEqual(len(actualizaciones), 1)
        self.assertEqual(actualizaciones[0].args[1], (5,))
