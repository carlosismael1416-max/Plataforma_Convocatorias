"""
Pruebas de descargar_mismo_dominio (SSRF y límite de tamaño).

Sin red: el DNS y las respuestas HTTP se simulan.

Ejecutar desde extractor/:
    ./.venv/bin/python -m unittest tests.test_seguridad_red -v
"""

from __future__ import annotations

import socket
import unittest

from unittest import mock

from app import seguridad_red
from app.seguridad_red import (
    URLNoSeguraError,
    descargar_mismo_dominio,
)


def dns_publico(host, puerto, *args, **kwargs):
    # Todo nombre resuelve a una IP pública de documentación
    # tratada como global por ipaddress (8.8.8.8).
    return [
        (socket.AF_INET, socket.SOCK_STREAM, 6, "", ("8.8.8.8", puerto)),
    ]


class RespuestaFalsa:

    def __init__(
        self,
        *,
        status: int = 200,
        cuerpo: bytes = b"",
        headers: dict | None = None,
        trozo: int = 65536,
    ):
        self.status_code = status
        self.headers = headers or {}
        self._cuerpo = cuerpo
        self._trozo = trozo
        self.leido = 0

    def iter_content(self, chunk_size):
        for inicio in range(0, len(self._cuerpo), self._trozo):
            parte = self._cuerpo[inicio:inicio + self._trozo]
            self.leido += len(parte)
            yield parte

    def raise_for_status(self):
        if self.status_code >= 400:
            raise RuntimeError(self.status_code)

    def close(self):
        pass

    def __enter__(self):
        return self

    def __exit__(self, *args):
        self.close()


@mock.patch.object(
    seguridad_red.socket,
    "getaddrinfo",
    dns_publico,
)
class DescargarMismoDominioTest(unittest.TestCase):

    def descargar(self, respuestas, **kwargs):
        with mock.patch.object(
            seguridad_red.requests.Session,
            "get",
            side_effect=respuestas,
        ):
            return descargar_mismo_dominio(
                "https://secihti.mx/convocatoria.pdf",
                "https://secihti.mx/",
                **kwargs,
            )

    def test_descarga_normal(self):
        url, contenido, tipo = self.descargar(
            [
                RespuestaFalsa(
                    cuerpo=b"%PDF-1.7 hola",
                    headers={"Content-Type": "application/pdf"},
                ),
            ],
            max_bytes=1024,
        )

        self.assertEqual(url, "https://secihti.mx/convocatoria.pdf")
        self.assertEqual(contenido, b"%PDF-1.7 hola")
        self.assertEqual(tipo, "application/pdf")

    def test_respuesta_grande_se_corta_al_leer(self):
        grande = RespuestaFalsa(
            cuerpo=b"x" * (5 * 65536),
        )

        with self.assertRaises(ValueError):
            self.descargar([grande], max_bytes=2 * 65536)

        # Se detuvo al pasar el límite, sin leer los 5 trozos.
        self.assertLessEqual(grande.leido, 3 * 65536)

    def test_content_length_excesivo_se_rechaza_sin_leer(self):
        respuesta = RespuestaFalsa(
            cuerpo=b"x" * 10,
            headers={"Content-Length": str(50 * 1024 * 1024)},
        )

        with self.assertRaises(ValueError):
            self.descargar([respuesta], max_bytes=1024)

        self.assertEqual(respuesta.leido, 0)

    def test_redireccion_a_otro_dominio_se_bloquea(self):
        with self.assertRaises(URLNoSeguraError):
            self.descargar(
                [
                    RespuestaFalsa(
                        status=302,
                        headers={"Location": "https://otro-sitio.com/x"},
                    ),
                ],
                max_bytes=1024,
            )

    def test_redireccion_a_ip_privada_se_bloquea(self):
        with self.assertRaises(URLNoSeguraError):
            self.descargar(
                [
                    RespuestaFalsa(
                        status=302,
                        headers={
                            "Location":
                                "http://169.254.169.254/latest/meta-data/",
                        },
                    ),
                ],
                max_bytes=1024,
            )

    def test_url_inicial_privada_se_bloquea(self):
        with self.assertRaises(URLNoSeguraError):
            descargar_mismo_dominio(
                "http://127.0.0.1:8001/health",
                "http://127.0.0.1:8001/",
                max_bytes=1024,
            )


if __name__ == "__main__":
    unittest.main()
