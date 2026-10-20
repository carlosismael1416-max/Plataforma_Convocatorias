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

from app.bitacora import registrar_incidencias_del_ciclo
from app.descubrimiento.guardar_descubrimientos import (
    conectar,
)

from app.evaluar_descubrimientos import (
    evaluar_descubrimientos,
)

from app.publicar_descubrimientos import (
    procesar_aprobados,
    revalidar_pendientes,
)

from app.seguimiento_convocatorias import (
    seguir_convocatorias,
)

from app.interruptor_motor import (
    motor_activo,
)

from app.mantenimiento import (
    ejecutar_mantenimiento,
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


# Tiempo mínimo que debe quedar del ciclo para empezar una fuente.
SEGUNDOS_MIN_FUENTE = 420


def fuentes_pendientes(conexion) -> list[dict]:
    """
    Fuentes oficiales activas, configuradas a mano, que ya cumplieron
    su frecuencia (frecuencia_scraping en minutos; 1440 si no tiene).
    Se omiten las que requieren JavaScript (bajo consumo, sin
    navegador) y las creadas automáticamente al publicar descubrimientos.
    """

    with conexion.cursor() as cursor:
        cursor.execute(
            """
            SELECT id, nombre
            FROM fuentes
            WHERE activa = TRUE
              AND COALESCE(requiere_javascript, FALSE) = FALSE
              -- Las fuentes que crea el motor al publicar un descubrimiento
              -- solo registran el origen; no son listados que raspar.
              AND COALESCE(selector_config->>'creada_automaticamente', 'false') <> 'true'
              AND (
                  ultima_ejecucion IS NULL
                  OR ultima_ejecucion
                      < NOW() - make_interval(mins => COALESCE(frecuencia_scraping, 1440))
              )
            ORDER BY ultima_ejecucion NULLS FIRST, id
            """
        )

        return [
            {"id": fila[0], "nombre": fila[1]}
            for fila in cursor.fetchall()
        ]


def ejecutar_fuentes_oficiales(
    *,
    terminar_antes_de: float,
    limite: int = 10,
) -> dict:
    """
    Extrae las convocatorias de las fuentes oficiales configuradas.
    Lo nuevo entra como PENDIENTE_REVISION (sin duplicados por
    url_hash) y la fase de revalidación decide si se publica.
    """

    conexion = conectar()

    try:
        fuentes = fuentes_pendientes(conexion)
    finally:
        conexion.close()

    resultado = {
        "consultadas": 0,
        "errores": 0,
        "pospuestas": 0,
        "omitidas_por_frecuencia": 0,
        "resultados": [],
    }

    if not fuentes:
        print("Ninguna fuente cumple su frecuencia todavía.")

    for fuente in fuentes:
        restante = terminar_antes_de - time.monotonic()

        if restante < SEGUNDOS_MIN_FUENTE:
            resultado["pospuestas"] += 1
            print("Pospuesta por tiempo:", fuente["nombre"])
            continue

        print()
        print("Fuente:", fuente["nombre"], f"(id {fuente['id']})")

        ejecucion = ejecutar_proceso_stream(
            [
                sys.executable,
                "-m",
                "app.ejecutar_fuente",
                "--fuente-id",
                str(fuente["id"]),
                "--limite",
                str(limite),
                "--guardar",
            ],
            timeout_segundos=int(min(600, restante - 60)),
        )

        resultado["consultadas"] += 1

        if not ejecucion["ok"]:
            resultado["errores"] += 1

        resultado["resultados"].append({
            "fuente_id": fuente["id"],
            "nombre": fuente["nombre"],
            "ok": ejecucion["ok"],
            "error": ejecucion["error"],
            "duracion_segundos": ejecucion["duracion_segundos"],
        })

    return resultado


def ejecutar_ciclo(
    *,
    cantidad_google: int,
    max_dominios: int,
    max_publicaciones: int,
    limite_fuentes: int = 10,
    max_revalidaciones: int | None = None,
) -> dict:
    terminar_antes_de = (
        time.monotonic()
        + SEGUNDOS_MAX_CICLO
    )

    # El administrador puede apagar el buscador y el motor desde la web.
    if not motor_activo():
        ahora = datetime.now().astimezone().isoformat()
        resultado = {
            "estado": "APAGADO",
            "inicio": ahora,
            "fin": ahora,
            "mensaje": "El motor está apagado desde el panel de administración.",
        }

        (BASE_DIR / "ciclo_automatico_ultimo.json").write_text(
            json.dumps(resultado, ensure_ascii=False, indent=2),
            encoding="utf-8",
        )

        print("Motor apagado desde el panel de administración: no se ejecuta el ciclo.")

        return resultado

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
            "FASE 1/6: DESCUBRIMIENTO"
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
            "FASE 2/6: "
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
            "FASE 3/6: "
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

        # =====================================================
        # 4. FUENTES OFICIALES CONFIGURADAS
        # =====================================================

        print()
        print(
            "FASE 4/6: "
            "FUENTES OFICIALES"
        )

        fuentes = ejecutar_fuentes_oficiales(
            terminar_antes_de=
                terminar_antes_de,
            limite=limite_fuentes,
        )

        print("Fuentes consultadas:", fuentes["consultadas"])
        print("Errores:", fuentes["errores"])
        print("Pospuestas por tiempo:", fuentes["pospuestas"])

        # =====================================================
        # 5. SEGUIMIENTO DE CONVOCATORIAS PUBLICADAS
        # =====================================================

        print()
        print(
            "FASE 5/6: "
            "SEGUIMIENTO DE PUBLICADAS"
        )

        seguimiento = seguir_convocatorias(
            terminar_antes_de=
                terminar_antes_de,
        )

        print("Revisadas:", seguimiento["revisadas"])
        print("Con cambios (se avisó):", seguimiento["con_cambios"])
        print("Primera consulta:", seguimiento["iniciales"])
        print("Errores:", seguimiento["errores"])

        # =====================================================
        # 6. REVALIDAR CONVOCATORIAS PENDIENTES
        # =====================================================

        print()
        print(
            "FASE 6/6: "
            "REVALIDACIÓN DE PENDIENTES"
        )

        revalidacion = revalidar_pendientes(
            **(
                {"limite": max_revalidaciones}
                if max_revalidaciones
                else {}
            ),
            terminar_antes_de=
                terminar_antes_de,
        )

        for etiqueta, clave in (
            ("Pendientes vencidas cerradas:", "cerradas_vencidas"),
            ("Revisadas:", "revisadas"),
            ("Publicadas:", "publicadas"),
            ("Siguen pendientes:", "siguen_pendientes"),
            ("Errores:", "errores"),
        ):
            print(
                etiqueta,
                revalidacion[clave],
            )

        # Mantenimiento ligero (nunca tumba el ciclo).
        mantenimiento = ejecutar_mantenimiento()

        print()
        print(
            "Mantenimiento:",
            mantenimiento,
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
            or revalidacion[
                "errores"
            ] > 0
            or fuentes["errores"] > 0
            or seguimiento["errores"] > 0
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

            "fuentes":
                fuentes,

            "seguimiento":
                seguimiento,

            "mantenimiento":
                mantenimiento,

            "revalidacion":
                revalidacion,
        }

        # Cada error de cada fase queda en la bitácora del administrador
        # (antes solo en este reporte).
        resultado["bitacora"] = registrar_incidencias_del_ciclo(resultado)

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

    # Carga inicial: más convocatorias por fuente y más pendientes
    # validadas en un solo ciclo (el ciclo normal usa 10 y 3).
    parser.add_argument(
        "--limite-fuentes",
        type=int,
        default=10,
    )

    parser.add_argument(
        "--max-revalidaciones",
        type=int,
        default=None,
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

        limite_fuentes=max(1, min(args.limite_fuentes, 50)),

        max_revalidaciones=(
            max(1, min(args.max_revalidaciones, 30))
            if args.max_revalidaciones
            else None
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
