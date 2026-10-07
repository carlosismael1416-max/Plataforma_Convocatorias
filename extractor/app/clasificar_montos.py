
import argparse
import json
import re
import unicodedata
from decimal import Decimal
from pathlib import Path


def normalizar(texto):
    texto = unicodedata.normalize(
        "NFKD",
        texto.lower(),
    )

    texto = "".join(
        caracter
        for caracter in texto
        if not unicodedata.combining(caracter)
    )

    return re.sub(r"\s+", " ", texto).strip()


PATRON_IMPORTE = re.compile(
    r"(?<!\w)"
    r"(?P<simbolo>MXN|USD|EUR|MX\$|US\$|\$|€)"
    r"\s*"
    r"(?P<importe>\d[\d,.]*)",
    re.IGNORECASE,
)


def localizar_importe(fragmento, monto):
    """
    Localiza la cantidad exacta dentro del
    fragmento, aunque existan otras cantidades.
    """

    importe_buscado = (
        monto["importe_original"].rstrip(".,")
    )

    simbolo_buscado = (
        monto["simbolo_original"].upper()
    )

    for coincidencia in PATRON_IMPORTE.finditer(
        fragmento
    ):
        importe = (
            coincidencia.group("importe")
            .rstrip(".,")
        )

        simbolo = (
            coincidencia.group("simbolo")
            .upper()
        )

        if (
            importe == importe_buscado
            and simbolo == simbolo_buscado
        ):
            return coincidencia

    return None


def identificar_moneda(monto, texto_posterior):
    moneda = monto["moneda"]

    if moneda != "NO_DETERMINADA":
        return moneda, "SIMBOLO_EXPLICITO"

    # Reconocer códigos ISO inmediatamente
    # después de un importe con símbolo ambiguo.
    codigo_posterior = re.match(
        r"^\s*(USD|MXN|EUR)\b",
        texto_posterior,
        re.IGNORECASE,
    )

    if codigo_posterior:
        return (
            codigo_posterior.group(1).upper(),
            "CODIGO_POSTERIOR_AL_IMPORTE",
        )

    # Aceptar MXN únicamente cuando
    # el importe tiene una aclaración
    # explícita de pesos M.N.
    aclaracion = re.search(
        r"\([^)]{0,100}"
        r"\bpesos\b"
        r"[^)]{0,35}"
        r"\bm\s*\.?\s*n\s*\.?\s*\)",
        texto_posterior,
        re.IGNORECASE,
    )

    if aclaracion:
        return "MXN", "ACLARACION_DOCUMENTAL"

    return "NO_DETERMINADA", "PENDIENTE_REVISION"


def identificar_concepto(antes, despues):
    """
    Reglas conservadoras basadas en las
    expresiones que rodean cada importe.
    """

    if (
        "por parte de la oea" in antes
        and re.search(
            r"un aporte unico con valor de\s*$",
            antes,
        )
    ):
        return "APORTE_UNICO_COMPLEMENTARIO_OEA"

    if re.search(
        r"apoyo total de hasta\s*$",
        antes,
    ):
        return "APOYO_MAXIMO_PROYECTO"

    if re.search(
        r"etapa por ano de hasta\s*$",
        antes,
    ):
        return "MAXIMO_ETAPA_ANUAL"

    if (
        "viaje redondo" in antes
        and "cada uno, por ano" in despues
    ):
        return "PASAJE_INTERNACIONAL"

    if re.search(
        r"investigador(?:as|es)\s+frances(?:es)?",
        antes,
    ):
        return "VIATICOS_INVESTIGADORES"

    if re.search(
        r"estudiantes?\s+frances(?:es)?",
        antes,
    ):
        return "VIATICOS_ESTUDIANTES"

    return "SIN_CLASIFICAR"


def clasificar_monto(monto, documento):
    fragmento = monto["fragmento"]

    coincidencia = localizar_importe(
        fragmento,
        monto,
    )

    importe = Decimal(
        monto["importe_original"].replace(",", "")
    )

    if importe == importe.to_integral_value():
        valor = int(importe)
    else:
        valor = float(importe)

    resultado = {
        "valor": valor,
        "moneda": monto["moneda"],
        "concepto": "SIN_CLASIFICAR",
        "estado": "PENDIENTE_REVISION",
        "fuente": {
            "documento": documento,
            "pagina": monto["pagina"],
            "fragmento": fragmento,
        },
    }

    if coincidencia is None:
        return resultado

    # Separar el texto anterior y posterior
    # a la cantidad que estamos clasificando.
    antes = normalizar(
        fragmento[:coincidencia.start()]
    )[-180:]

    despues = normalizar(
        fragmento[coincidencia.end():]
    )[:180]

    moneda, origen_moneda = identificar_moneda(
        monto,
        despues,
    )

    concepto = identificar_concepto(
        antes,
        despues,
    )

    resultado["moneda"] = moneda
    resultado["origen_moneda"] = origen_moneda
    resultado["concepto"] = concepto

    if (
        concepto == "APORTE_UNICO_COMPLEMENTARIO_OEA"
        and moneda == "USD"
        and "pagados en pesos mexicanos" in despues
        and "tipo de cambio oficial" in despues
    ):
        resultado["desembolso"] = {
            "moneda_entrega": "MXN",
            "criterio_conversion": (
                "TIPO_CAMBIO_OFICIAL_DIA_DEPOSITO"
            ),
            "importe_en_mxn": None,
        }

    if (
        concepto != "SIN_CLASIFICAR"
        and moneda != "NO_DETERMINADA"
    ):
        resultado["estado"] = "REGLA_COINCIDENTE"

    return resultado


def main():
    parser = argparse.ArgumentParser(
        description=(
            "Clasificar montos candidatos "
            "según su contexto documental."
        )
    )

    parser.add_argument(
        "--entradas",
        nargs="+",
        required=True,
        type=Path,
    )

    parser.add_argument(
        "--salida",
        required=True,
        type=Path,
    )

    argumentos = parser.parse_args()

    clasificados = []

    for ruta in argumentos.entradas:
        datos = json.loads(
            ruta.read_text(encoding="utf-8")
        )

        for monto in datos["montos_candidatos"]:
            clasificados.append(
                clasificar_monto(
                    monto,
                    datos["archivo"],
                )
            )

    resultado = {
        "montos_clasificados": clasificados,
        "advertencia": (
            "Las reglas identifican conceptos "
            "candidatos. La información requiere "
            "verificación antes de incorporarse "
            "como dato financiero definitivo."
        ),
    }

    argumentos.salida.parent.mkdir(
        parents=True,
        exist_ok=True,
    )

    argumentos.salida.write_text(
        json.dumps(
            resultado,
            ensure_ascii=False,
            indent=2,
        ),
        encoding="utf-8",
    )

    print("\n=== CLASIFICACIÓN DE MONTOS ===")

    for monto in clasificados:
        print()
        print("Valor:", monto["valor"])
        print("Moneda:", monto["moneda"])
        print("Concepto:", monto["concepto"])
        print("Estado:", monto["estado"])
        print(
            "Documento:",
            monto["fuente"]["documento"],
        )
        print(
            "Página:",
            monto["fuente"]["pagina"],
        )

    print("\nJSON generado:", argumentos.salida)


if __name__ == "__main__":
    main()
