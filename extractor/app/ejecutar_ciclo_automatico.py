from __future__ import annotations

import argparse
import json
import os
import queue
import signal
import subprocess
import sys
import threading
import time

from datetime import datetime
from pathlib import Path

from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.evaluar_descubrimientos import (
    evaluar_descubrimientos,
)

from app.publicar_descubrimientos import (
    procesar_aprobados,
)


BASE_DIR = (
    Path(__file__)
    .resolve()
    .parent
    .parent
)

LOCK_ID = 9262028

# systemd corta el ciclo a los 3600 s (TimeoutStartSec en
# extractor/systemd/convocatorias-itsva.service); 5 min de margen.
SEGUNDOS_MAX_CICLO = 3600 - 300


def adquirir_lock(
    conexion,
) -> bool:
    cursor = conexion.cursor()

    cursor.execute(
        """
        SELECT pg_try_advisory_lock(%s)
        """,
        (
            LOCK_ID,
        ),
    )

    adquirido = bool(
        cursor.fetchone()[0]
    )

    cursor.close()

    return adquirido


def liberar_lock(
    conexion,
) -> None:
    try:
        cursor = conexion.cursor()

        cursor.execute(
            """
            SELECT pg_advisory_unlock(%s)
            """,
            (
                LOCK_ID,
            ),
        )

        cursor.close()
        conexion.commit()

    except Exception:
        pass


def detener_arbol_proceso(
    proceso: subprocess.Popen,
    *,
    espera_terminate: int = 10,
) -> None:
    """
    Detiene el proceso y también sus procesos
    hijos cuando se ejecuta en Linux/WSL.
    """

    if proceso.poll() is not None:
        return

    try:
        os.killpg(
            proceso.pid,
            signal.SIGTERM,
        )

    except (
        ProcessLookupError,
        PermissionError,
    ):
        try:
            proceso.terminate()
        except Exception:
            return

    try:
        proceso.wait(
            timeout=espera_terminate
        )
        return

    except subprocess.TimeoutExpired:
        pass

    try:
        os.killpg(
            proceso.pid,
            signal.SIGKILL,
        )

    except (
        ProcessLookupError,
        PermissionError,
    ):
        try:
            proceso.kill()
        except Exception:
            pass

    try:
        proceso.wait(
            timeout=5
        )
    except Exception:
        pass


def ejecutar_proceso_stream(
    comando: list[str],
    *,
    timeout_segundos: int,
) -> dict:
    """
    Ejecuta un subproceso mostrando su salida
    en tiempo real, pero con timeout de tiempo
    de pared real.

    El timeout sigue corriendo aunque el hijo
    deje de producir stdout.
    """

    lineas: list[str] = []

    proceso = subprocess.Popen(
        comando,
        cwd=str(
            BASE_DIR
        ),
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        bufsize=1,
        start_new_session=True,
    )

    assert proceso.stdout is not None

    cola: queue.Queue = (
        queue.Queue()
    )

    def leer_stdout() -> None:
        try:
            for linea in proceso.stdout:
                cola.put(
                    (
                        "LINEA",
                        linea,
                    )
                )

        finally:
            cola.put(
                (
                    "FIN",
                    None,
                )
            )

    lector = threading.Thread(
        target=leer_stdout,
        daemon=True,
    )

    lector.start()

    inicio = time.monotonic()
    stdout_terminado = False

    try:
        while True:
            transcurrido = (
                time.monotonic()
                - inicio
            )

            if (
                transcurrido
                >= timeout_segundos
            ):
                detener_arbol_proceso(
                    proceso
                )

                lector.join(
                    timeout=2
                )

                return {
                    "ok": False,
                    "codigo": None,
                    "error": "TIMEOUT",
                    "salida": "".join(
                        lineas
                    )[-30000:],
                    "duracion_segundos":
                        round(
                            transcurrido,
                            2,
                        ),
                }

            try:
                tipo, valor = cola.get(
                    timeout=0.5
                )

            except queue.Empty:
                # No hay salida nueva. El bucle
                # continúa para comprobar el
                # deadline global.
                continue

            if tipo == "FIN":
                stdout_terminado = True

            elif tipo == "LINEA":
                linea = str(
                    valor
                )

                print(
                    linea,
                    end="",
                    flush=True,
                )

                lineas.append(
                    linea
                )

                if len(
                    lineas
                ) > 3000:
                    lineas = lineas[
                        -2000:
                    ]

            if (
                stdout_terminado
                and proceso.poll()
                    is not None
            ):
                break

        codigo = proceso.wait(
            timeout=5
        )

    except KeyboardInterrupt:
        detener_arbol_proceso(
            proceso
        )

        print()
        print(
            "Subproceso interrumpido "
            "por el usuario."
        )

        raise

    duracion = (
        time.monotonic()
        - inicio
    )

    salida = "".join(
        lineas
    )

    return {
        "ok":
            codigo == 0,

        "codigo":
            codigo,

        "error":
            (
                None
                if codigo == 0
                else (
                    "RETURN_CODE_"
                    + str(
                        codigo
                    )
                )
            ),

        "salida":
            salida[-30000:],

        "duracion_segundos":
            round(
                duracion,
                2,
            ),
    }


def ejecutar_descubridor(
    cantidad_google: int,
    max_dominios: int,
) -> dict:
    comando = [
        sys.executable,
        "-m",
        "app.ejecutar_descubrimiento",

        "--cantidad-google",
        str(
            cantidad_google
        ),

        "--max-dominios",
        str(
            max_dominios
        ),

        "--guardar",
    ]

    print()
    print(
        "Comando descubridor:"
    )
    print(
        " ".join(
            comando
        )
    )
    print()

    # 15 minutos máximos de tiempo real.
    # No depende de que stdout siga enviando
    # líneas.
    return ejecutar_proceso_stream(
        comando,
        timeout_segundos=900,
    )

def ejecutar_ciclo(
    *,
    cantidad_google: int,
    max_dominios: int,
    max_publicaciones: int,
) -> dict:
    terminar_antes_de = (
        time.monotonic()
        + SEGUNDOS_MAX_CICLO
    )

    conexion_lock = conectar()

    if not adquirir_lock(
        conexion_lock
    ):
        conexion_lock.close()

        return {
            "estado":
                "YA_EN_EJECUCION",

            "mensaje":
                "Ya existe otro ciclo "
                "automático activo.",
        }

    inicio = (
        datetime.now()
        .astimezone()
    )

    try:
        print()
        print(
            "=" * 70
        )
        print(
            "CICLO AUTOMÁTICO ITSVA"
        )
        print(
            "=" * 70
        )

        # =====================================================
        # 1. DESCUBRIR
        # =====================================================

        print()
        print(
            "FASE 1/3: DESCUBRIMIENTO"
        )

        descubrimiento = (
            ejecutar_descubridor(
                cantidad_google,
                max_dominios,
            )
        )

        print(
            "Descubridor:",
            (
                "OK"
                if descubrimiento[
                    "ok"
                ]
                else "ERROR"
            ),
        )

        # =====================================================
        # 2. EVALUAR
        # =====================================================

        print()
        print(
            "FASE 2/3: "
            "EVALUACIÓN AUTOMÁTICA"
        )

        evaluacion = (
            evaluar_descubrimientos(
                aplicar=True
            )
        )

        print(
            "Evaluados:",
            evaluacion[
                "evaluados"
            ],
        )

        print(
            "Aprobados:",
            evaluacion[
                "aprobados"
            ],
        )

        print(
            "Revisión:",
            evaluacion[
                "revision"
            ],
        )

        print(
            "Descartados:",
            evaluacion[
                "descartados"
            ],
        )

        # =====================================================
        # 3. EXTRAER / VALIDAR / PUBLICAR
        # =====================================================

        print()
        print(
            "FASE 3/3: "
            "EXTRACCIÓN Y PUBLICACIÓN"
        )

        publicacion = (
            procesar_aprobados(
                limite=
                    max_publicaciones,
                terminar_antes_de=
                    terminar_antes_de,
            )
        )

        print(
            "Publicados:",
            publicacion[
                "publicados"
            ],
        )

        print(
            "Revisión final:",
            publicacion[
                "revision_final"
            ],
        )

        print(
            "Temporales:",
            publicacion.get(
                "temporales",
                0,
            ),
        )

        print(
            "Errores:",
            publicacion[
                "errores"
            ],
        )

        print(
            "Pospuestos por tiempo:",
            publicacion.get(
                "pospuestos",
                0,
            ),
        )

        fin = (
            datetime.now()
            .astimezone()
        )

        advertencias = (
            not descubrimiento[
                "ok"
            ]
            or publicacion[
                "errores"
            ] > 0
        )

        resultado = {
            "estado":
                (
                    "COMPLETADO_CON_ADVERTENCIAS"
                    if advertencias
                    else "COMPLETADO"
                ),

            "inicio":
                inicio.isoformat(),

            "fin":
                fin.isoformat(),

            "descubrimiento":
                descubrimiento,

            "evaluacion":
                evaluacion,

            "publicacion":
                publicacion,
        }

        archivo = (
            BASE_DIR
            / "ciclo_automatico_ultimo.json"
        )

        archivo.write_text(
            json.dumps(
                resultado,
                ensure_ascii=False,
                indent=2,
                default=str,
            ),
            encoding="utf-8",
        )

        print()
        print(
            "=" * 70
        )
        print(
            "CICLO TERMINADO"
        )
        print(
            "=" * 70
        )
        print(
            "Estado:",
            resultado[
                "estado"
            ],
        )
        print(
            "Reporte:",
            archivo,
        )

        return resultado

    finally:
        liberar_lock(
            conexion_lock
        )

        conexion_lock.close()


def main() -> None:
    parser = argparse.ArgumentParser(
        description=(
            "Ejecuta el ciclo automático "
            "de convocatorias ITSVA."
        )
    )

    parser.add_argument(
        "--cantidad-google",
        type=int,
        default=6,
    )

    parser.add_argument(
        "--max-dominios",
        type=int,
        default=5,
    )

    parser.add_argument(
        "--max-publicaciones",
        type=int,
        default=5,
    )

    args = parser.parse_args()

    resultado = ejecutar_ciclo(
        cantidad_google=max(
            1,
            min(
                args.cantidad_google,
                30,
            ),
        ),

        max_dominios=max(
            1,
            min(
                args.max_dominios,
                20,
            ),
        ),

        max_publicaciones=max(
            1,
            min(
                args.max_publicaciones,
                20,
            ),
        ),
    )

    if (
        resultado.get(
            "estado"
        )
        == "YA_EN_EJECUCION"
    ):
        print(
            resultado[
                "mensaje"
            ]
        )


if __name__ == "__main__":
    main()
