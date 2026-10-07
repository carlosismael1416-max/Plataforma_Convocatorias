from datetime import time


def registrar_cambio_fecha(
    conexion,
    convocatoria_id,
    fuente_id,
    fecha_nueva,
    hora_nueva,
    fuente_url,
):
    """
    Guarda una revisión pendiente cuando cambia la fecha
    o la hora de cierre publicada en la web.

    Devuelve el ID de la revisión creada, o None
    si el dato ya estaba registrado.
    """

    hora_nueva = time.fromisoformat(hora_nueva)

    with conexion.transaction():
        with conexion.cursor() as cursor:

            # Bloqueamos la convocatoria durante la
            # comprobación para evitar registros simultáneos.
            cursor.execute(
                """
                SELECT fecha_cierre
                FROM convocatorias
                WHERE id = %s
                  AND fuente_id = %s
                FOR UPDATE
                """,
                (convocatoria_id, fuente_id),
            )

            convocatoria = cursor.fetchone()

            if convocatoria is None:
                raise ValueError(
                    "La convocatoria no existe o "
                    "no pertenece a la fuente indicada."
                )

            fecha_cierre_actual = convocatoria[0]

            # Consultamos la última fecha detectada
            # anteriormente en la página web.
            cursor.execute(
                """
                SELECT
                    fecha_cierre_nueva,
                    hora_cierre
                FROM convocatoria_revisions
                WHERE convocatoria_id = %s
                  AND tipo_revision = 'FECHA_CIERRE'
                  AND fuente_tipo = 'WEB'
                ORDER BY fecha_revision DESC, id DESC
                LIMIT 1
                """,
                (convocatoria_id,),
            )

            ultima_revision = cursor.fetchone()

            if ultima_revision:
                fecha_anterior = ultima_revision[0]
                hora_anterior = ultima_revision[1]

                # Evitamos guardar el mismo dato dos veces.
                if (
                    fecha_nueva == fecha_anterior
                    and hora_nueva == hora_anterior
                ):
                    return None
            else:
                fecha_anterior = fecha_cierre_actual

                # No hay una revisión WEB previa.
                # Si la fecha coincide con la registrada,
                # no inventamos un cambio histórico.
                if fecha_nueva == fecha_anterior:
                    return None

            # Registramos el cambio detectado, pero
            # todavía no modificamos la convocatoria.
            cursor.execute(
                """
                INSERT INTO convocatoria_revisions (
                    convocatoria_id,
                    tipo_revision,
                    fecha_cierre_anterior,
                    fecha_cierre_nueva,
                    hora_cierre,
                    zona_horaria,
                    fuente_tipo,
                    fuente_url,
                    observaciones,
                    requiere_verificacion,
                    fecha_revision,
                    created_at,
                    updated_at
                )
                VALUES (
                    %s,
                    'FECHA_CIERRE',
                    %s,
                    %s,
                    %s,
                    'America/Mexico_City',
                    'WEB',
                    %s,
                    %s,
                    TRUE,
                    NOW(),
                    NOW(),
                    NOW()
                )
                RETURNING id
                """,
                (
                    convocatoria_id,
                    fecha_anterior,
                    fecha_nueva,
                    hora_nueva,
                    fuente_url,
                    (
                        "Posible cambio detectado automáticamente "
                        "en la página web. Pendiente de verificar "
                        "contra el aviso oficial."
                    ),
                ),
            )

            return cursor.fetchone()[0]
