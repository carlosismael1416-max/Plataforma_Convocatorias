import time
import unittest
from unittest import mock

from scrapy.http import HtmlResponse

from app import ejecutar_ciclo_automatico as ciclo
from app.spiders.convocatorias import ConvocatoriasSpider


PORTADA = b"""
<html><body>
<a href="/convocatoria/ciencias-y-humanidades/revistas-acceso-abierto-2026">Revistas 2026</a>
<a href="/convocatoria/becas-nacionales/apoyo-complementario-oea">Beca OEA</a>
<a href="/convocatoria/centros-publicos-de-investigacion/titular-2026">Cargo titular</a>
<a href="/convocatoria/desarrollo-tecnologico-vinculacion-e-innovacion/jovenes">Jovenes</a>
<a href="https://otro.mx/convocatoria/x">Externa</a>
<a href="/convocatoria/ciencias-y-humanidades/ecos-nord/ecos-2026">Ecos 2026</a>
</body></html>
"""


class PortadaSecihtiTest(unittest.TestCase):
    def respuesta(self):
        return HtmlResponse(
            url="https://www.secihti.mx/convocatorias/",
            body=PORTADA,
            encoding="utf-8",
        )

    def test_toma_las_de_portada_incluidas_becas_y_omite_plazas(self):
        spider = ConvocatoriasSpider(url="https://www.secihti.mx/convocatorias/", limite=10)
        urls = [r.url for r in spider.convocatorias_de_portada(self.respuesta())]

        self.assertEqual(
            urls,
            [
                "https://www.secihti.mx/convocatoria/ciencias-y-humanidades/revistas-acceso-abierto-2026",
                "https://www.secihti.mx/convocatoria/becas-nacionales/apoyo-complementario-oea",
                "https://www.secihti.mx/convocatoria/desarrollo-tecnologico-vinculacion-e-innovacion/jovenes",
                "https://www.secihti.mx/convocatoria/ciencias-y-humanidades/ecos-nord/ecos-2026",
            ],
        )

    def test_respeta_el_limite(self):
        spider = ConvocatoriasSpider(url="https://www.secihti.mx/convocatorias/", limite=1)
        urls = [r.url for r in spider.convocatorias_de_portada(self.respuesta())]

        self.assertEqual(len(urls), 1)


class FaseFuentesOficialesTest(unittest.TestCase):
    def test_pospone_si_no_alcanza_el_tiempo(self):
        conexion = mock.MagicMock()

        with mock.patch.object(ciclo, "conectar", return_value=conexion), \
             mock.patch.object(ciclo, "fuentes_pendientes", return_value=[{"id": 2, "nombre": "SECIHTI"}]), \
             mock.patch.object(ciclo, "ejecutar_proceso_stream") as proceso:
            resultado = ciclo.ejecutar_fuentes_oficiales(
                terminar_antes_de=time.monotonic() + 60,
            )

        proceso.assert_not_called()
        self.assertEqual(resultado["pospuestas"], 1)
        self.assertEqual(resultado["consultadas"], 0)

    def test_ejecuta_la_fuente_con_guardar(self):
        conexion = mock.MagicMock()
        salida = {"ok": True, "error": None, "duracion_segundos": 12.0}

        with mock.patch.object(ciclo, "conectar", return_value=conexion), \
             mock.patch.object(ciclo, "fuentes_pendientes", return_value=[{"id": 2, "nombre": "SECIHTI"}]), \
             mock.patch.object(ciclo, "ejecutar_proceso_stream", return_value=salida) as proceso:
            resultado = ciclo.ejecutar_fuentes_oficiales(
                terminar_antes_de=time.monotonic() + 3000,
            )

        comando = proceso.call_args.args[0]
        self.assertIn("app.ejecutar_fuente", comando)
        self.assertIn("--guardar", comando)
        self.assertEqual(comando[comando.index("--fuente-id") + 1], "2")
        self.assertEqual(resultado["consultadas"], 1)
        self.assertEqual(resultado["errores"], 0)


if __name__ == "__main__":
    unittest.main()


LISTADO_WORDPRESS = """
<html><body>
<a href="https://ciencia.yucatan.gob.mx/convocatorias/">Convocatorias</a>
<a href="https://ciencia.yucatan.gob.mx/convocatorias/fondo-investigacion-2026/">Fondo 2026</a>
<a href="https://ciencia.yucatan.gob.mx/convocatorias/fondo-investigacion-2026/">Fondo 2026 (repetido)</a>
<a href="https://ciencia.yucatan.gob.mx/tag/convocatorias/">Etiqueta</a>
<a href="https://ciencia.yucatan.gob.mx/convocatoria-abierta/">Sección</a>
<a href="https://ciencia.yucatan.gob.mx/convocatoria-abierta/proyectos-siidetey/">Proyectos</a>
<a href="https://ciencia.yucatan.gob.mx/wp-content/uploads/bases.pdf">PDF</a>
<a href="https://www.facebook.com/sharer/sharer.php?u=https://ciencia.yucatan.gob.mx/convocatorias/x">Compartir</a>
<a href="https://ciencia.yucatan.gob.mx/contacto/">Contacto</a>
</body></html>
""".encode("utf-8")


class ListadoGenericoTest(unittest.TestCase):
    def test_toma_paginas_de_convocatoria_del_mismo_sitio(self):
        spider = ConvocatoriasSpider(url="https://ciencia.yucatan.gob.mx/convocatorias/", limite=10)
        respuesta = HtmlResponse(
            url="https://ciencia.yucatan.gob.mx/convocatorias/",
            body=LISTADO_WORDPRESS,
            encoding="utf-8",
        )
        urls = [r.url for r in spider.convocatorias_de_listado(respuesta)]

        self.assertEqual(
            urls,
            [
                "https://ciencia.yucatan.gob.mx/convocatorias/fondo-investigacion-2026/",
                "https://ciencia.yucatan.gob.mx/convocatoria-abierta/proyectos-siidetey/",
            ],
        )
