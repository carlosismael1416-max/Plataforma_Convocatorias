import unittest

from app.descubrimiento.codificacion import decodificar_html


class RespuestaFalsa:
    def __init__(self, content_type: str, encoding: str | None):
        self.headers = {"Content-Type": content_type}
        self.encoding = encoding


class DecodificarHtmlTest(unittest.TestCase):
    def test_utf8_sin_charset_no_produce_mojibake(self):
        # requests asigna ISO-8859-1 cuando no hay charset en la cabecera.
        html = "<title>Américo Villarreal</title>".encode("utf-8")
        respuesta = RespuestaFalsa("text/html", "ISO-8859-1")

        self.assertIn("Américo", decodificar_html(html, respuesta))

    def test_respeta_el_charset_declarado(self):
        html = "<title>Año</title>".encode("latin-1")
        respuesta = RespuestaFalsa("text/html; charset=ISO-8859-1", "ISO-8859-1")

        self.assertIn("Año", decodificar_html(html, respuesta))

    def test_usa_el_meta_charset_si_no_hay_cabecera(self):
        html = '<meta charset="windows-1252"><title>Educación</title>'.encode("cp1252")
        respuesta = RespuestaFalsa("text/html", "ISO-8859-1")

        self.assertIn("Educación", decodificar_html(html, respuesta))

    def test_latin1_sin_declarar_no_falla(self):
        html = "<title>Investigación</title>".encode("latin-1")
        respuesta = RespuestaFalsa("text/html", None)

        self.assertIn("Investigación", decodificar_html(html, respuesta))


if __name__ == "__main__":
    unittest.main()
