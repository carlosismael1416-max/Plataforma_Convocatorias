"""
Uso del texto de la nota al clasificar (solo sube desde REVISION).

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_contexto -v
"""

from __future__ import annotations

import unittest

from app.descubrimiento.analizar_articulo import (
    extraer_texto_principal,
)
from app.descubrimiento.clasificar_objetivo import (
    clasificar_objetivo_convocatoria,
)


FECTI = (
    "Abre FECTI 2026 convocatorias para impulsar la ciencia "
    "y tecnología en Chihuahua"
)

# Extracto real de la nota de chihuahua.gob.mx (02-jun-2026).
NOTA_FECTI = (
    "El Gobierno del Estado destinará 58 mdp para apoyar proyectos "
    "de infraestructura científica, investigación, formación de "
    "talento e innovación. Este año se contará con una inversión de "
    "58 millones de pesos para respaldar proyectos estratégicos."
)


def clasificar(titulo: str, contexto: str = "") -> tuple[str, str]:
    resultado = clasificar_objetivo_convocatoria(
        titulo,
        "",
        contexto,
    )

    return (
        resultado["categoria_objetivo"],
        resultado["decision_objetivo"],
    )


class ContextoTest(unittest.TestCase):

    def test_titulo_general_con_nota_de_financiamiento_se_acepta(self):
        self.assertEqual(
            clasificar(FECTI),
            ("CIENCIA_TECNOLOGIA_GENERAL", "REVISION"),
        )
        self.assertEqual(
            clasificar(FECTI, NOTA_FECTI),
            ("FINANCIAMIENTO_INVESTIGACION", "ACEPTAR"),
        )

    def test_nota_sin_financiamiento_no_cambia_la_decision(self):
        self.assertEqual(
            clasificar(
                FECTI,
                "Se realizó una ceremonia con autoridades estatales "
                "y estudiantes de varias universidades.",
            ),
            ("CIENCIA_TECNOLOGIA_GENERAL", "REVISION"),
        )

    def test_eventos_y_becas_no_suben_por_contexto(self):
        self.assertEqual(
            clasificar(
                "Convocatoria al Congreso Nacional de Ciencia 2026",
                NOTA_FECTI,
            ),
            ("EVENTO", "DESCARTAR"),
        )
        self.assertEqual(
            clasificar(
                "Convocatoria de becas de posgrado 2026",
                NOTA_FECTI,
            ),
            ("BECA", "REVISION"),
        )

    def test_nota_sobre_becas_no_sube_un_titulo_sin_la_palabra(self):
        # Caso real: el PDF "Convocatoria BEXT ET JICA 2026" enlazado
        # desde una nota sobre becas para ir a Japón.
        self.assertEqual(
            clasificar(
                "Convocatoria BEXT ET JICA 2026",
                "México tiene becas para ir a Japón a aprender "
                "tecnología. La beca cubre proyectos de investigación "
                "y un apoyo económico mensual.",
            ),
            ("CONVOCATORIA_GENERAL", "REVISION"),
        )

    def test_texto_principal_ignora_menus_y_laterales(self):
        html = (
            "<html><body><nav>Convocatorias Becas Premios</nav>"
            "<article><h1>Fondo estatal</h1><p>Apoyo a proyectos de "
            "investigación</p></article><aside>Otra nota sobre un "
            "congreso</aside><footer>Contacto</footer></body></html>"
        )

        self.assertEqual(
            extraer_texto_principal(html),
            "Fondo estatal Apoyo a proyectos de investigación",
        )


if __name__ == "__main__":
    unittest.main()
