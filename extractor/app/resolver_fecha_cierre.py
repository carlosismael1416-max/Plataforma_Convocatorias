def resolver_fecha_cierre(resultado):
    """
    Determina cuál es la mejor fecha de cierre utilizable
    de una convocatoria.

    Prioridad:
    1. AMPLIACION encontrada en HTML.
    2. CIERRE_NORMAL encontrado en HTML.
    3. CIERRE_POSTULACION encontrado en PDF.
    4. Sin fecha utilizable.

    No modifica PostgreSQL.
    """

    tipo_html = resultado.get(
        "tipo_fecha_cierre"
    )

    fecha_html = resultado.get(
        "fecha_cierre_web"
    )

    hora_html = resultado.get(
        "hora_cierre_web"
    )

    etiqueta_html = resultado.get(
        "etiqueta_fecha_cierre"
    )

    # 1. Una ampliación publicada en la web
    # tiene prioridad.
    if (
        tipo_html == "AMPLIACION"
        and fecha_html
    ):
        return {
            "fecha": fecha_html,
            "hora": hora_html,
            "origen": "HTML",
            "tipo": "AMPLIACION",
            "etiqueta": etiqueta_html,
            "url_fuente": resultado.get(
                "url_original"
            ),
            "utilizable": True,
            "requiere_verificacion": True,
        }

    # 2. Cierre normal publicado en la página.
    if (
        tipo_html == "CIERRE_NORMAL"
        and fecha_html
    ):
        return {
            "fecha": fecha_html,
            "hora": hora_html,
            "origen": "HTML",
            "tipo": "CIERRE_NORMAL",
            "etiqueta": etiqueta_html,
            "url_fuente": resultado.get(
                "url_original"
            ),
            "utilizable": True,
            "requiere_verificacion": False,
        }

    cierre_pdf = resultado.get(
        "cierre_pdf"
    )

    if cierre_pdf:
        # 3. El PDF contiene específicamente una
        # fecha límite de postulación/recepción.
        if (
            cierre_pdf.get("tipo")
            == "CIERRE_POSTULACION"
            and cierre_pdf.get(
                "usar_como_fecha_cierre"
            )
            and cierre_pdf.get(
                "fecha_cierre_postulacion"
            )
        ):
            return {
                "fecha": cierre_pdf.get(
                    "fecha_cierre_postulacion"
                ),
                "hora": cierre_pdf.get(
                    "hora_cierre_postulacion"
                ),
                "origen": "PDF",
                "tipo": "CIERRE_POSTULACION",
                "etiqueta": cierre_pdf.get(
                    "etiqueta"
                ),
                "url_fuente": cierre_pdf.get(
                    "url"
                ),
                "utilizable": True,
                "requiere_verificacion": False,
            }

        # El final administrativo del proceso
        # se conserva como información, pero no
        # se usa como fecha límite.
        if (
            cierre_pdf.get("tipo")
            == "CIERRE_PROCESO"
        ):
            return {
                "fecha": None,
                "hora": None,
                "origen": "PDF",
                "tipo": "CIERRE_PROCESO",
                "etiqueta": cierre_pdf.get(
                    "etiqueta"
                ),
                "url_fuente": cierre_pdf.get(
                    "url"
                ),
                "utilizable": False,
                "requiere_verificacion": False,
                "fecha_informativa": (
                    cierre_pdf.get(
                        "fecha_cierre_proceso"
                    )
                ),
            }

    return {
        "fecha": None,
        "hora": None,
        "origen": None,
        "tipo": "SIN_FECHA_UTILIZABLE",
        "etiqueta": None,
        "url_fuente": None,
        "utilizable": False,
        "requiere_verificacion": False,
    }
