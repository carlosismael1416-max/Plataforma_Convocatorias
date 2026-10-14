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
            "dominio": "cgc.qroo.gob.mx",
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


if __name__ == "__main__":
    unittest.main()
