"""
Pruebas de clasificación, evaluación y validación con casos reales.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest discover -s tests -t .

No usan red ni PostgreSQL.
"""

from __future__ import annotations

import unittest

from datetime import date, timedelta

from app.descubrimiento.clasificar_objetivo import (
    clasificar_objetivo_convocatoria,
)
from app.descubrimiento.elegibilidad import (
    evaluar_elegibilidad,
)
from app.evaluar_descubrimientos import (
    evaluar_fila,
)
from app.validar_convocatoria import (
    evaluar_convocatoria,
)


def clasificar(
    titulo: str,
    url: str = "",
) -> tuple[str, str]:
    resultado = clasificar_objetivo_convocatoria(
        titulo,
        url,
    )

    return (
        resultado["categoria_objetivo"],
        resultado["decision_objetivo"],
    )


class ClasificarObjetivoTest(unittest.TestCase):
    # Casos tomados de descubrimientos_web (oct-2026).

    def test_convocatoria_de_investigacion_se_acepta(self):
        self.assertEqual(
            clasificar(
                "Convocatoria Nacional de Investigación "
                "Científica y Humanística 2026"
            ),
            ("PROYECTO_INVESTIGACION", "ACEPTAR"),
        )

        self.assertEqual(
            clasificar(
                "Presenta COQHCYT la convocatoria de Fomento "
                "e Impulso a la Investigación Aplicada 2026"
            ),
            ("PROYECTO_INVESTIGACION", "ACEPTAR"),
        )

    def test_convocatoria_cientifica_general_va_a_revision(self):
        # FECTI es un fondo, pero el título no lo dice;
        # se deja a revisión en lugar de aprobarlo a ciegas.
        self.assertEqual(
            clasificar(
                "Abre FECTI 2026 convocatorias para impulsar "
                "la ciencia y tecnología en Chihuahua"
            ),
            ("CIENCIA_TECNOLOGIA_GENERAL", "REVISION"),
        )

    def test_becas_van_a_revision(self):
        self.assertEqual(
            clasificar(
                "Convocatoria abierta: Beca de investigación "
                "CALAS – Fundación Heinrich Böll 2027"
            ),
            ("BECA_INVESTIGACION", "REVISION"),
        )

        self.assertEqual(
            clasificar(
                "Convocatoria para becarios de posgrado"
            ),
            ("BECA", "REVISION"),
        )

    def test_eventos_se_descartan(self):
        self.assertEqual(
            clasificar(
                "Abierta convocatoria para participar en el "
                "Congreso Internacional del Programa Delfín 2026"
            ),
            ("EVENTO", "DESCARTAR"),
        )

        self.assertEqual(
            clasificar(
                "Convocatoria a talleres de investigación"
            ),
            ("EVENTO", "DESCARTAR"),
        )

    def test_palabra_dentro_de_otra_no_es_evento(self):
        # "curso" está dentro de "recursos"; antes se
        # descartaba como EVENTO.
        self.assertEqual(
            clasificar(
                "Abre convocatoria con recursos para "
                "investigación aplicada"
            ),
            ("PROYECTO_INVESTIGACION", "ACEPTAR"),
        )

    def test_acentos_no_afectan(self):
        self.assertEqual(
            clasificar(
                "CONVOCATORIA DE INVESTIGACION CIENTIFICA"
            ),
            clasificar(
                "Convocatoria de investigación científica"
            ),
        )

    def test_nota_sin_relacion_se_descarta(self):
        self.assertEqual(
            clasificar(
                "",
                "https://www.eluniversal.com.mx/mundo/"
                "fiscalia-de-bolivia-inicia-investigacion-"
                "contra-evo-morales",
            ),
            ("NO_OBJETIVO", "DESCARTAR"),
        )


class EvaluarFilaTest(unittest.TestCase):

    def fila(self, **cambios) -> dict:
        base = {
            "decision_objetivo": "ACEPTAR",
            "categoria_objetivo": "PROYECTO_INVESTIGACION",
            "estado_temporal": "ACTUAL",
            "relevancia_tematica": "ALTA",
            "tipo": "PAGINA",
            "dominio": "secihti.mx",
            "estado_fuente": "INSTITUCIONAL_DIRECTA",
        }
        base.update(cambios)
        return base

    def test_institucional_aceptada_se_aprueba(self):
        resultado = evaluar_fila(self.fila())

        self.assertEqual(resultado["estado"], "APROBADO")
        self.assertGreaterEqual(resultado["score"], 80)

    def test_categoria_general_no_se_aprueba(self):
        resultado = evaluar_fila(
            self.fila(
                decision_objetivo="REVISION",
                categoria_objetivo="CIENCIA_TECNOLOGIA_GENERAL",
                dominio="chihuahua.gob.mx",
            )
        )

        self.assertEqual(resultado["estado"], "REVISION")
        self.assertFalse(
            resultado["condiciones"]["categoria_valida"]
        )

    def test_historica_no_se_aprueba(self):
        resultado = evaluar_fila(
            self.fila(estado_temporal="HISTORICA")
        )

        self.assertNotEqual(resultado["estado"], "APROBADO")

    def test_no_elegible_se_descarta(self):
        resultado = evaluar_fila(
            self.fila(
                titulo=(
                    "Presenta COQHCYT la convocatoria de Fomento "
                    "e Impulso a la Investigación Aplicada 2026"
                ),
                url_original=(
                    "https://cgc.qroo.gob.mx/presenta-coqhcyt-la-"
                    "convocatoria-de-fomento-e-impulso-a-la-"
                    "investigacion-aplicada-2026/"
                ),
                dominio="cgc.qroo.gob.mx",
            )
        )

        self.assertEqual(resultado["estado"], "DESCARTADO")
        self.assertFalse(resultado["condiciones"]["elegible"])

    def test_evento_se_descarta(self):
        resultado = evaluar_fila(
            self.fila(
                decision_objetivo="DESCARTAR",
                categoria_objetivo="EVENTO",
            )
        )

        self.assertEqual(resultado["estado"], "DESCARTADO")


class EvaluarConvocatoriaTest(unittest.TestCase):

    def convocatoria(self, **cambios) -> dict:
        base = {
            "titulo": (
                "Convocatoria de Proyectos de Investigación "
                "Básica y Aplicada 2026"
            ),
            "descripcion": "x" * 120,
            "objetivo": "y" * 60,
            "url_original": "https://ejemplo.gob.mx/convocatoria",
            "fuente_id": 1,
            "organismo_id": 1,
            "categoria_id": 1,
            "fecha_cierre": date.today() + timedelta(days=30),
        }
        base.update(cambios)
        return base

    def test_vigente_y_completa_es_publicable(self):
        resultado = evaluar_convocatoria(self.convocatoria())

        self.assertTrue(resultado["publicable"], resultado)

    def test_vencida_no_es_publicable(self):
        # Prueba negativa: FECTI 2026 cerró el 17-jul-2026.
        resultado = evaluar_convocatoria(
            self.convocatoria(
                fecha_cierre=date.today() - timedelta(days=1)
            )
        )

        self.assertFalse(resultado["publicable"])
        self.assertIn(
            "La fecha de cierre ya pasó.",
            resultado["bloqueos"],
        )

    def test_sin_fecha_de_cierre_no_es_publicable(self):
        resultado = evaluar_convocatoria(
            self.convocatoria(fecha_cierre=None)
        )

        self.assertFalse(resultado["publicable"])

    def test_resultados_y_eventos_se_bloquean(self):
        for titulo in (
            "Secihti informa resultados de Convocatoria "
            "ECOS Nord 2025",
            "Convocatoria al Congreso Nacional de Investigación",
            "Convocatoria de Becas de Posgrado 2026",
        ):
            with self.subTest(titulo=titulo):
                resultado = evaluar_convocatoria(
                    self.convocatoria(titulo=titulo)
                )

                self.assertFalse(resultado["publicable"])

    def test_convocatoria_de_otro_estado_se_bloquea(self):
        resultado = evaluar_convocatoria(
            self.convocatoria(
                titulo=(
                    "Abre FECTI 2026 convocatorias para impulsar "
                    "la ciencia y tecnología en Chihuahua"
                ),
                url_original=(
                    "https://chihuahua.gob.mx/prensa/abre-fecti-2026"
                ),
            )
        )

        self.assertFalse(resultado["publicable"])

    def test_recursos_no_se_bloquea_como_curso(self):
        resultado = evaluar_convocatoria(
            self.convocatoria(
                titulo=(
                    "Convocatoria con recursos para proyectos "
                    "de investigación 2026"
                )
            )
        )

        self.assertTrue(resultado["publicable"], resultado)


class ElegibilidadTest(unittest.TestCase):
    # Casos reales de descubrimientos y vistas previas (oct-2026).

    def motivo(
        self,
        titulo: str,
        url: str = "",
    ) -> str | None:
        return evaluar_elegibilidad(titulo, url)["motivo"]

    def test_otros_estados_no_son_elegibles(self):
        casos = (
            (
                "Abre FECTI 2026 convocatorias para impulsar la "
                "ciencia y tecnología en Chihuahua",
                "https://chihuahua.gob.mx/prensa/abre-fecti-2026",
                "Chihuahua",
            ),
            (
                "Presenta COQHCYT la convocatoria de Fomento e "
                "Impulso a la Investigación Aplicada 2026",
                "",
                "Quintana Roo",
            ),
            (
                "Hasta 200 mil pesos dará el gobierno mexiquense a "
                "proyectos de científicas - Portal",
                "",
                "Estado de México",
            ),
            (
                "SECTEI emite Convocatoria 2025 para impulsar "
                "proyectos CTI en CDMX - CDMX",
                "",
                "Ciudad de México",
            ),
            (
                "Copocyt ofrece hasta tres becas para una estancia "
                "académica en McMaster, Canadá - Líder Empresarial",
                "",
                "San Luis Potosí",
            ),
        )

        for titulo, url, estado in casos:
            with self.subTest(titulo=titulo):
                self.assertIn(estado, self.motivo(titulo, url) or "")

    def test_estado_por_dominio_de_gobierno(self):
        self.assertIn(
            "Quintana Roo",
            self.motivo(
                "Convocatoria de investigación aplicada 2026",
                "https://cgc.qroo.gob.mx/convocatoria",
            ),
        )

    def test_nacionales_yucatan_y_tecnm_son_elegibles(self):
        for titulo, url in (
            (
                "Convocatoria Nacional de Investigación Científica "
                "y Humanística 2026",
                "https://secihti.mx/convocatoria/x",
            ),
            (
                "Convocatoria de Ciencia de Yucatán y Quintana Roo "
                "para la región sur sureste",
                "https://cgc.qroo.gob.mx/x",
            ),
            (
                "Convocatoria de proyectos de investigación del "
                "TecNM 2026 sede Puebla",
                "",
            ),
            (
                "Convocatoria estatal de investigación 2026",
                "https://www.yucatan.gob.mx/x",
            ),
        ):
            with self.subTest(titulo=titulo):
                self.assertIsNone(self.motivo(titulo, url))

    def test_nombre_del_medio_no_cuenta(self):
        # Google News agrega " - Medio" al título.
        self.assertIsNone(
            self.motivo(
                "Qué necesito para tener una beca en Japón - "
                "El Heraldo de Tabasco - El Sol de México"
            )
        )

    def test_extranjeras_sin_apertura_a_mexico(self):
        self.assertIsNotNone(
            self.motivo(
                "Convocatoria abierta: Beca de investigación CALAS "
                "– Fundación Heinrich Böll 2027 - Universidad "
                "Nacional de San Martín (UNSAM)",
                "https://noticias.unsam.edu.ar/2026/09/28/calas",
            )
        )

        self.assertIsNone(
            self.motivo(
                "Convocatoria internacional de investigación 2026",
                "https://ejemplo.edu.co/convocatoria",
            )
        )

    def test_vacantes_y_admisiones_no_son_elegibles(self):
        for titulo in (
            "Convocatoria 2026 del proceso para ocupar el cargo de "
            "titular de la Dirección General",
            "Secihti lanza convocatoria de 200 plazas en 26 centros "
            "públicos de investigación - La Jornada",
            "Convocatoria de admisión 2026",
        ):
            with self.subTest(titulo=titulo):
                self.assertIsNotNone(self.motivo(titulo))


if __name__ == "__main__":
    unittest.main()
