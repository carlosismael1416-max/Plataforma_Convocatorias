-- ============================================================
-- PLATAFORMA DE GESTIÓN DE CONVOCATORIAS ITSVA
-- BASE DE DATOS PARA VERSIÓN DE PRUEBA
-- PostgreSQL
--
-- IMPORTANTE:
-- Este script NO crea roles internos de PostgreSQL.
-- Este script NO crea usuarios internos de PostgreSQL.
-- Se ejecuta utilizando el usuario PostgreSQL:
-- convocatorias_app
--
-- Base:
-- convocatorias_itsva
-- ============================================================

BEGIN;

SET search_path TO public;

-- ============================================================
-- 1. FUNCIÓN GENERAL PARA fecha_actualizacion
-- ============================================================

CREATE OR REPLACE FUNCTION actualizar_fecha_modificacion()
RETURNS TRIGGER
LANGUAGE plpgsql
AS $$
BEGIN
    NEW.fecha_actualizacion = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$;


-- ============================================================
-- 2. ROLES DE LA APLICACIÓN
-- ============================================================
-- Estos NO son roles PostgreSQL.
-- Son roles funcionales de nuestra aplicación.
-- ============================================================

CREATE TABLE roles (
    id_rol BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(50) NOT NULL UNIQUE,

    descripcion TEXT,

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- 3. PERMISOS
-- ============================================================

CREATE TABLE permisos (
    id_permiso BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL UNIQUE,

    descripcion TEXT,

    modulo VARCHAR(100),

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- 4. ROL - PERMISOS
-- ============================================================

CREATE TABLE rol_permisos (
    id_rol BIGINT NOT NULL,

    id_permiso BIGINT NOT NULL,

    fecha_asignacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (id_rol, id_permiso),

    CONSTRAINT fk_rol_permisos_rol
        FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE CASCADE,

    CONSTRAINT fk_rol_permisos_permiso
        FOREIGN KEY (id_permiso)
        REFERENCES permisos(id_permiso)
        ON DELETE CASCADE
);


-- ============================================================
-- 5. DEPARTAMENTOS
-- ============================================================

CREATE TABLE departamentos (
    id_departamento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL UNIQUE,

    descripcion TEXT,

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- 6. USUARIOS
-- ============================================================

CREATE TABLE usuarios (
    id_usuario BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(100) NOT NULL,

    apellidos VARCHAR(150) NOT NULL,

    correo VARCHAR(255) NOT NULL UNIQUE,

    contrasena_hash TEXT NOT NULL,

    id_rol BIGINT NOT NULL,

    id_departamento BIGINT,

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    ultimo_acceso TIMESTAMPTZ,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_usuarios_rol
        FOREIGN KEY (id_rol)
        REFERENCES roles(id_rol)
        ON DELETE RESTRICT,

    CONSTRAINT fk_usuarios_departamento
        FOREIGN KEY (id_departamento)
        REFERENCES departamentos(id_departamento)
        ON DELETE SET NULL
);

COMMENT ON COLUMN usuarios.contrasena_hash IS
'Hash generado por Laravel utilizando bcrypt o Argon2id. Nunca almacenar contraseñas en texto plano.';


-- ============================================================
-- 7. CATEGORÍAS
-- ============================================================

CREATE TABLE categorias (
    id_categoria BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(150) NOT NULL UNIQUE,

    descripcion TEXT,

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP
);


-- ============================================================
-- 8. ORGANISMOS
-- ============================================================

CREATE TABLE organismos (
    id_organismo BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(255) NOT NULL UNIQUE,

    descripcion TEXT,

    sitio_web TEXT,

    tipo_organismo VARCHAR(30) NOT NULL DEFAULT 'OTRO',

    pais VARCHAR(100),

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_organismos_tipo
        CHECK (
            tipo_organismo IN (
                'FEDERAL',
                'ESTATAL',
                'MUNICIPAL',
                'UNIVERSIDAD',
                'EMPRESA',
                'INTERNACIONAL',
                'OTRO'
            )
        )
);


-- ============================================================
-- 9. FUENTES WEB
-- ============================================================

CREATE TABLE fuentes (
    id_fuente BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    nombre VARCHAR(255) NOT NULL,

    url_base TEXT NOT NULL UNIQUE,

    tipo_fuente VARCHAR(30) NOT NULL DEFAULT 'WEB',

    selector_config JSONB,

    requiere_javascript BOOLEAN NOT NULL DEFAULT FALSE,

    activa BOOLEAN NOT NULL DEFAULT TRUE,

    -- Minutos entre ejecuciones automáticas
    frecuencia_scraping INTEGER,

    ultima_ejecucion TIMESTAMPTZ,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT chk_fuentes_tipo
        CHECK (
            tipo_fuente IN (
                'WEB',
                'API',
                'RSS',
                'OTRO'
            )
        ),

    CONSTRAINT chk_fuentes_frecuencia
        CHECK (
            frecuencia_scraping IS NULL
            OR frecuencia_scraping > 0
        )
);


-- ============================================================
-- 10. CONVOCATORIAS
-- ============================================================

CREATE TABLE convocatorias (
    id_convocatoria BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    titulo VARCHAR(500) NOT NULL,

    descripcion TEXT,

    objetivo TEXT,

    fecha_publicacion DATE,

    fecha_inicio DATE,

    fecha_cierre DATE,

    monto_minimo NUMERIC(15,2),

    monto_maximo NUMERIC(15,2),

    moneda VARCHAR(10) NOT NULL DEFAULT 'MXN',

    modalidad VARCHAR(100),

    ubicacion VARCHAR(255),

    id_categoria BIGINT,

    id_organismo BIGINT,

    id_fuente BIGINT,

    url_original TEXT,

    -- SHA-256 normalmente ocupa 64 caracteres,
    -- pero dejamos margen por si cambiamos algoritmo.
    url_hash VARCHAR(128),

    contenido_hash VARCHAR(128),

    origen VARCHAR(30) NOT NULL DEFAULT 'SCRAPING',

    estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE_REVISION',

    fecha_extraccion TIMESTAMPTZ,

    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_convocatorias_categoria
        FOREIGN KEY (id_categoria)
        REFERENCES categorias(id_categoria)
        ON DELETE SET NULL,

    CONSTRAINT fk_convocatorias_organismo
        FOREIGN KEY (id_organismo)
        REFERENCES organismos(id_organismo)
        ON DELETE SET NULL,

    CONSTRAINT fk_convocatorias_fuente
        FOREIGN KEY (id_fuente)
        REFERENCES fuentes(id_fuente)
        ON DELETE SET NULL,

    CONSTRAINT chk_convocatorias_origen
        CHECK (
            origen IN (
                'SCRAPING',
                'MANUAL',
                'ADMINISTRADOR',
                'API'
            )
        ),

    CONSTRAINT chk_convocatorias_estado
        CHECK (
            estado IN (
                'BORRADOR',
                'PENDIENTE_REVISION',
                'PUBLICADA',
                'ACTIVA',
                'CERRADA',
                'DESCARTADA',
                'ARCHIVADA'
            )
        ),

    CONSTRAINT chk_convocatorias_monto_minimo
        CHECK (
            monto_minimo IS NULL
            OR monto_minimo >= 0
        ),

    CONSTRAINT chk_convocatorias_monto_maximo
        CHECK (
            monto_maximo IS NULL
            OR monto_maximo >= 0
        ),

    CONSTRAINT chk_convocatorias_rango_montos
        CHECK (
            monto_minimo IS NULL
            OR monto_maximo IS NULL
            OR monto_maximo >= monto_minimo
        ),

    CONSTRAINT chk_convocatorias_fechas
        CHECK (
            fecha_inicio IS NULL
            OR fecha_cierre IS NULL
            OR fecha_cierre >= fecha_inicio
        )
);


-- ============================================================
-- 11. REQUISITOS DE CONVOCATORIAS
-- ============================================================

CREATE TABLE convocatoria_requisitos (
    id_requisito BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_convocatoria BIGINT NOT NULL,

    titulo VARCHAR(255),

    descripcion TEXT NOT NULL,

    obligatorio BOOLEAN NOT NULL DEFAULT TRUE,

    tipo_requisito VARCHAR(100),

    orden INTEGER NOT NULL DEFAULT 0,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_convocatoria_requisitos_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE CASCADE,

    CONSTRAINT uq_convocatoria_requisitos_orden
        UNIQUE (id_convocatoria, orden),

    CONSTRAINT chk_convocatoria_requisitos_orden
        CHECK (orden >= 0)
);


-- ============================================================
-- 12. ARCHIVOS DE CONVOCATORIAS
-- ============================================================
-- Aquí se almacenan referencias a PDFs, anexos, formatos, etc.
-- El archivo físico NO necesariamente se guarda dentro de PostgreSQL.
-- ============================================================

CREATE TABLE convocatoria_archivos (
    id_archivo BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_convocatoria BIGINT NOT NULL,

    nombre VARCHAR(255) NOT NULL,

    tipo_archivo VARCHAR(50),

    mime_type VARCHAR(150),

    url_archivo TEXT,

    ruta_archivo TEXT,

    tamano_bytes BIGINT,

    hash_archivo VARCHAR(128),

    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_convocatoria_archivos_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE CASCADE,

    CONSTRAINT chk_convocatoria_archivos_tipo
        CHECK (
            tipo_archivo IS NULL
            OR tipo_archivo IN (
                'PDF',
                'ANEXO',
                'FORMATO',
                'TERMINOS_REFERENCIA',
                'REGLAS_OPERACION',
                'IMAGEN',
                'OTRO'
            )
        ),

    CONSTRAINT chk_convocatoria_archivos_tamano
        CHECK (
            tamano_bytes IS NULL
            OR tamano_bytes >= 0
        )
);


-- ============================================================
-- 13. REVISIONES DE CONVOCATORIAS
-- ============================================================

CREATE TABLE convocatoria_revisiones (
    id_revision BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_convocatoria BIGINT NOT NULL,

    id_usuario BIGINT,

    estado_anterior VARCHAR(30),

    estado_nuevo VARCHAR(30),

    comentarios TEXT,

    cambios JSONB,

    fecha_revision TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_convocatoria_revisiones_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE CASCADE,

    CONSTRAINT fk_convocatoria_revisiones_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);


-- ============================================================
-- 14. MIS CONVOCATORIAS + FAVORITOS
-- ============================================================
-- IMPORTANTE:
-- NO EXISTE una tabla favoritos.
-- NO EXISTE historial_actividad.
--
-- Esta tabla concentra:
-- - Mis Convocatorias
-- - Favoritos
-- - Seguimiento
-- - Prioridad
-- - Notas
-- - Última acción
-- ============================================================

CREATE TABLE usuario_convocatorias (
    id_usuario_convocatoria BIGINT
        GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_usuario BIGINT NOT NULL,

    id_convocatoria BIGINT NOT NULL,

    es_favorita BOOLEAN NOT NULL DEFAULT TRUE,

    estado_seguimiento VARCHAR(40)
        NOT NULL DEFAULT 'GUARDADA',

    prioridad VARCHAR(20)
        NOT NULL DEFAULT 'MEDIA',

    notas TEXT,

    ultima_accion VARCHAR(150),

    fecha_ultima_accion TIMESTAMPTZ,

    fecha_agregado TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_usuario_convocatorias
        UNIQUE (id_usuario, id_convocatoria),

    CONSTRAINT fk_usuario_convocatorias_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_usuario_convocatorias_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE CASCADE,

    CONSTRAINT chk_usuario_convocatorias_estado
        CHECK (
            estado_seguimiento IN (
                'GUARDADA',
                'REVISANDO',
                'PREPARANDO_PROPUESTA',
                'ENVIADA',
                'ACEPTADA',
                'RECHAZADA',
                'FINALIZADA'
            )
        ),

    CONSTRAINT chk_usuario_convocatorias_prioridad
        CHECK (
            prioridad IN (
                'BAJA',
                'MEDIA',
                'ALTA'
            )
        )
);


-- ============================================================
-- 15. CALENDARIO
-- ============================================================

CREATE TABLE eventos_calendario (
    id_evento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_usuario BIGINT NOT NULL,

    id_convocatoria BIGINT,

    titulo VARCHAR(255) NOT NULL,

    descripcion TEXT,

    fecha_inicio TIMESTAMPTZ NOT NULL,

    fecha_fin TIMESTAMPTZ,

    tipo_evento VARCHAR(30) NOT NULL DEFAULT 'OTRO',

    recordatorio BOOLEAN NOT NULL DEFAULT FALSE,

    minutos_recordatorio INTEGER,

    estado BOOLEAN NOT NULL DEFAULT TRUE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_eventos_calendario_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_eventos_calendario_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE SET NULL,

    CONSTRAINT chk_eventos_calendario_tipo
        CHECK (
            tipo_evento IN (
                'FECHA_CIERRE',
                'REUNION',
                'RECORDATORIO',
                'ENTREGABLE',
                'OTRO'
            )
        ),

    CONSTRAINT chk_eventos_calendario_fechas
        CHECK (
            fecha_fin IS NULL
            OR fecha_fin >= fecha_inicio
        ),

    CONSTRAINT chk_eventos_calendario_recordatorio
        CHECK (
            minutos_recordatorio IS NULL
            OR minutos_recordatorio >= 0
        )
);


-- ============================================================
-- 16. PROPUESTAS BASE GENERADAS CON IA
-- ============================================================
-- Este módulo es diferente al procesador documental.
--
-- Procesador documental:
-- PDF/web -> datos estructurados.
--
-- IA generativa:
-- convocatoria estructurada -> propuesta base.
-- ============================================================

CREATE TABLE propuestas_base (
    id_propuesta_base BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_convocatoria BIGINT NOT NULL,

    contenido_generado JSONB,

    modelo_ia VARCHAR(150),

    prompt_utilizado TEXT,

    version INTEGER NOT NULL DEFAULT 1,

    estado VARCHAR(30) NOT NULL DEFAULT 'GENERANDO',

    mensaje_error TEXT,

    fecha_generacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_propuestas_base_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE CASCADE,

    CONSTRAINT chk_propuestas_base_version
        CHECK (version > 0),

    CONSTRAINT chk_propuestas_base_estado
        CHECK (
            estado IN (
                'GENERANDO',
                'GENERADA',
                'ERROR'
            )
        )
);


-- ============================================================
-- 17. PROPUESTAS
-- ============================================================

CREATE TABLE propuestas (
    id_propuesta BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_usuario BIGINT NOT NULL,

    id_convocatoria BIGINT NOT NULL,

    id_propuesta_base BIGINT,

    titulo VARCHAR(500) NOT NULL,

    resumen TEXT,

    justificacion TEXT,

    metodologia TEXT,

    impacto_esperado TEXT,

    estado VARCHAR(30) NOT NULL DEFAULT 'BORRADOR',

    version INTEGER NOT NULL DEFAULT 1,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_envio TIMESTAMPTZ,

    CONSTRAINT fk_propuestas_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE RESTRICT,

    CONSTRAINT fk_propuestas_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE RESTRICT,

    CONSTRAINT fk_propuestas_base
        FOREIGN KEY (id_propuesta_base)
        REFERENCES propuestas_base(id_propuesta_base)
        ON DELETE SET NULL,

    CONSTRAINT chk_propuestas_version
        CHECK (version > 0),

    CONSTRAINT chk_propuestas_estado
        CHECK (
            estado IN (
                'BORRADOR',
                'GENERADA',
                'EDITANDO',
                'LISTA',
                'ENVIADA',
                'APROBADA',
                'RECHAZADA'
            )
        )
);


-- ============================================================
-- 18. OBJETIVOS DE PROPUESTA
-- ============================================================

CREATE TABLE propuesta_objetivos (
    id_objetivo BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    tipo VARCHAR(20) NOT NULL,

    descripcion TEXT NOT NULL,

    orden INTEGER NOT NULL DEFAULT 0,

    CONSTRAINT fk_propuesta_objetivos_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT chk_propuesta_objetivos_tipo
        CHECK (
            tipo IN (
                'GENERAL',
                'ESPECIFICO'
            )
        ),

    CONSTRAINT chk_propuesta_objetivos_orden
        CHECK (orden >= 0)
);


-- ============================================================
-- 19. REQUISITOS DE PROPUESTA
-- ============================================================

CREATE TABLE propuesta_requisitos (
    id_propuesta_requisito BIGINT
        GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    id_requisito_convocatoria BIGINT NOT NULL,

    cumplido BOOLEAN NOT NULL DEFAULT FALSE,

    observaciones TEXT,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT uq_propuesta_requisitos
        UNIQUE (
            id_propuesta,
            id_requisito_convocatoria
        ),

    CONSTRAINT fk_propuesta_requisitos_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT fk_propuesta_requisitos_requisito
        FOREIGN KEY (id_requisito_convocatoria)
        REFERENCES convocatoria_requisitos(id_requisito)
        ON DELETE RESTRICT
);


-- ============================================================
-- 20. PRESUPUESTO
-- ============================================================

CREATE TABLE presupuesto_items (
    id_item BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    concepto VARCHAR(255) NOT NULL,

    descripcion TEXT,

    cantidad NUMERIC(12,2) NOT NULL DEFAULT 1,

    precio_unitario NUMERIC(15,2) NOT NULL DEFAULT 0,

    subtotal NUMERIC(17,2)
        GENERATED ALWAYS AS (
            cantidad * precio_unitario
        ) STORED,

    categoria_gasto VARCHAR(30),

    moneda VARCHAR(10) NOT NULL DEFAULT 'MXN',

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_presupuesto_items_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT chk_presupuesto_cantidad
        CHECK (cantidad > 0),

    CONSTRAINT chk_presupuesto_precio
        CHECK (precio_unitario >= 0),

    CONSTRAINT chk_presupuesto_categoria
        CHECK (
            categoria_gasto IS NULL
            OR categoria_gasto IN (
                'EQUIPO',
                'MATERIALES',
                'SERVICIOS',
                'VIATICOS',
                'PERSONAL',
                'INFRAESTRUCTURA',
                'OTRO'
            )
        )
);


-- ============================================================
-- 21. COTIZACIONES
-- ============================================================

CREATE TABLE cotizaciones (
    id_cotizacion BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    id_item_presupuesto BIGINT,

    proveedor VARCHAR(255),

    concepto VARCHAR(255),

    monto NUMERIC(15,2) NOT NULL,

    moneda VARCHAR(10) NOT NULL DEFAULT 'MXN',

    archivo_url TEXT,

    fecha_cotizacion DATE,

    fecha_registro TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cotizaciones_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT fk_cotizaciones_item
        FOREIGN KEY (id_item_presupuesto)
        REFERENCES presupuesto_items(id_item)
        ON DELETE SET NULL,

    CONSTRAINT chk_cotizaciones_monto
        CHECK (monto >= 0)
);


-- ============================================================
-- 22. CRONOGRAMA
-- ============================================================

CREATE TABLE cronograma_actividades (
    id_actividad BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    actividad VARCHAR(255) NOT NULL,

    descripcion TEXT,

    fecha_inicio DATE NOT NULL,

    fecha_fin DATE NOT NULL,

    responsable VARCHAR(255),

    estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE',

    porcentaje_avance NUMERIC(5,2) NOT NULL DEFAULT 0,

    orden INTEGER NOT NULL DEFAULT 0,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_actualizacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_cronograma_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT chk_cronograma_fechas
        CHECK (
            fecha_fin >= fecha_inicio
        ),

    CONSTRAINT chk_cronograma_avance
        CHECK (
            porcentaje_avance >= 0
            AND porcentaje_avance <= 100
        ),

    CONSTRAINT chk_cronograma_orden
        CHECK (
            orden >= 0
        ),

    CONSTRAINT chk_cronograma_estado
        CHECK (
            estado IN (
                'PENDIENTE',
                'EN_PROCESO',
                'COMPLETADA',
                'CANCELADA'
            )
        )
);


-- ============================================================
-- 23. ENTREGABLES
-- ============================================================

CREATE TABLE propuesta_entregables (
    id_entregable BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    id_actividad BIGINT,

    nombre VARCHAR(255) NOT NULL,

    descripcion TEXT,

    fecha_limite DATE,

    estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE',

    fecha_entrega TIMESTAMPTZ,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_propuesta_entregables_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT fk_propuesta_entregables_actividad
        FOREIGN KEY (id_actividad)
        REFERENCES cronograma_actividades(id_actividad)
        ON DELETE SET NULL,

    CONSTRAINT chk_propuesta_entregables_estado
        CHECK (
            estado IN (
                'PENDIENTE',
                'EN_PROCESO',
                'ENTREGADO',
                'APROBADO',
                'RECHAZADO'
            )
        )
);


-- ============================================================
-- 24. ARCHIVOS DE PROPUESTAS
-- ============================================================

CREATE TABLE propuesta_archivos (
    id_archivo BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    nombre VARCHAR(255) NOT NULL,

    ruta_archivo TEXT,

    url_archivo TEXT,

    tipo_archivo VARCHAR(100),

    mime_type VARCHAR(150),

    tamano_bytes BIGINT,

    fecha_subida TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_propuesta_archivos_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT chk_propuesta_archivos_tamano
        CHECK (
            tamano_bytes IS NULL
            OR tamano_bytes >= 0
        )
);


-- ============================================================
-- 25. EVIDENCIAS
-- ============================================================

CREATE TABLE evidencias (
    id_evidencia BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_propuesta BIGINT NOT NULL,

    id_requisito BIGINT,

    id_entregable BIGINT,

    nombre VARCHAR(255) NOT NULL,

    descripcion TEXT,

    ruta_archivo TEXT,

    url_archivo TEXT,

    fecha_subida TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_evidencias_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE CASCADE,

    CONSTRAINT fk_evidencias_requisito
        FOREIGN KEY (id_requisito)
        REFERENCES propuesta_requisitos(id_propuesta_requisito)
        ON DELETE SET NULL,

    CONSTRAINT fk_evidencias_entregable
        FOREIGN KEY (id_entregable)
        REFERENCES propuesta_entregables(id_entregable)
        ON DELETE SET NULL
);


-- ============================================================
-- 26. NOTIFICACIONES
-- ============================================================

CREATE TABLE notificaciones (
    id_notificacion BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_usuario BIGINT NOT NULL,

    id_convocatoria BIGINT,

    id_propuesta BIGINT,

    titulo VARCHAR(255) NOT NULL,

    mensaje TEXT NOT NULL,

    tipo VARCHAR(50) NOT NULL,

    leida BOOLEAN NOT NULL DEFAULT FALSE,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_lectura TIMESTAMPTZ,

    CONSTRAINT fk_notificaciones_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE,

    CONSTRAINT fk_notificaciones_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE SET NULL,

    CONSTRAINT fk_notificaciones_propuesta
        FOREIGN KEY (id_propuesta)
        REFERENCES propuestas(id_propuesta)
        ON DELETE SET NULL,

    CONSTRAINT chk_notificaciones_tipo
        CHECK (
            tipo IN (
                'NUEVA_CONVOCATORIA',
                'CONVOCATORIA_POR_CERRAR',
                'CAMBIO_CONVOCATORIA',
                'PROPUESTA_GENERADA',
                'PROPUESTA_POR_VENCER',
                'SCRAPING_ERROR',
                'SISTEMA'
            )
        )
);


-- ============================================================
-- 27. EJECUCIONES DEL MOTOR DE EXTRACCIÓN
-- ============================================================

CREATE TABLE ejecuciones_scraping (
    id_ejecucion BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    fecha_inicio TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_fin TIMESTAMPTZ,

    estado VARCHAR(40) NOT NULL DEFAULT 'INICIADO',

    total_fuentes INTEGER NOT NULL DEFAULT 0,

    fuentes_exitosas INTEGER NOT NULL DEFAULT 0,

    fuentes_fallidas INTEGER NOT NULL DEFAULT 0,

    total_encontradas INTEGER NOT NULL DEFAULT 0,

    nuevas_convocatorias INTEGER NOT NULL DEFAULT 0,

    convocatorias_actualizadas INTEGER NOT NULL DEFAULT 0,

    duplicados_detectados INTEGER NOT NULL DEFAULT 0,

    total_errores INTEGER NOT NULL DEFAULT 0,

    duracion_segundos NUMERIC(12,2),

    CONSTRAINT chk_ejecuciones_estado
        CHECK (
            estado IN (
                'INICIADO',
                'EJECUTANDO',
                'COMPLETADO',
                'COMPLETADO_CON_ERRORES',
                'FALLIDO'
            )
        ),

    CONSTRAINT chk_ejecuciones_contadores
        CHECK (
            total_fuentes >= 0
            AND fuentes_exitosas >= 0
            AND fuentes_fallidas >= 0
            AND total_encontradas >= 0
            AND nuevas_convocatorias >= 0
            AND convocatorias_actualizadas >= 0
            AND duplicados_detectados >= 0
            AND total_errores >= 0
        ),

    CONSTRAINT chk_ejecuciones_fechas
        CHECK (
            fecha_fin IS NULL
            OR fecha_fin >= fecha_inicio
        )
);


-- ============================================================
-- 28. EJECUCIÓN POR FUENTE
-- ============================================================

CREATE TABLE ejecucion_fuentes (
    id_ejecucion_fuente BIGINT
        GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_ejecucion BIGINT NOT NULL,

    id_fuente BIGINT NOT NULL,

    fecha_inicio TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_fin TIMESTAMPTZ,

    estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE',

    registros_encontrados INTEGER NOT NULL DEFAULT 0,

    registros_nuevos INTEGER NOT NULL DEFAULT 0,

    registros_actualizados INTEGER NOT NULL DEFAULT 0,

    duplicados INTEGER NOT NULL DEFAULT 0,

    errores INTEGER NOT NULL DEFAULT 0,

    http_status INTEGER,

    duracion_segundos NUMERIC(12,2),

    CONSTRAINT uq_ejecucion_fuentes
        UNIQUE (id_ejecucion, id_fuente),

    CONSTRAINT fk_ejecucion_fuentes_ejecucion
        FOREIGN KEY (id_ejecucion)
        REFERENCES ejecuciones_scraping(id_ejecucion)
        ON DELETE CASCADE,

    CONSTRAINT fk_ejecucion_fuentes_fuente
        FOREIGN KEY (id_fuente)
        REFERENCES fuentes(id_fuente)
        ON DELETE RESTRICT,

    CONSTRAINT chk_ejecucion_fuentes_estado
        CHECK (
            estado IN (
                'PENDIENTE',
                'EJECUTANDO',
                'COMPLETADO',
                'ERROR'
            )
        ),

    CONSTRAINT chk_ejecucion_fuentes_contadores
        CHECK (
            registros_encontrados >= 0
            AND registros_nuevos >= 0
            AND registros_actualizados >= 0
            AND duplicados >= 0
            AND errores >= 0
        ),

    CONSTRAINT chk_ejecucion_fuentes_fechas
        CHECK (
            fecha_fin IS NULL
            OR fecha_fin >= fecha_inicio
        )
);


-- ============================================================
-- 29. PROCESAMIENTO DE DOCUMENTOS / PDF
-- ============================================================
-- Sustituye la antigua idea del módulo NLP.
--
-- Python podrá registrar aquí:
-- - PDF descargado
-- - si necesitó OCR
-- - herramienta utilizada
-- - texto obtenido
-- - datos estructurados encontrados
--
-- Herramientas previstas:
-- DOCLING
-- PYMUPDF
-- OCRMYPDF
-- TESSERACT
-- ============================================================

CREATE TABLE procesamientos_documentos (
    id_procesamiento BIGINT
        GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_archivo BIGINT NOT NULL,

    id_ejecucion_fuente BIGINT,

    motor_extraccion VARCHAR(50) NOT NULL,

    requiere_ocr BOOLEAN NOT NULL DEFAULT FALSE,

    paginas INTEGER,

    estado VARCHAR(30) NOT NULL DEFAULT 'PENDIENTE',

    texto_extraido TEXT,

    datos_extraidos JSONB,

    hash_documento VARCHAR(128),

    mensaje_error TEXT,

    fecha_inicio TIMESTAMPTZ,

    fecha_fin TIMESTAMPTZ,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_procesamientos_archivo
        FOREIGN KEY (id_archivo)
        REFERENCES convocatoria_archivos(id_archivo)
        ON DELETE CASCADE,

    CONSTRAINT fk_procesamientos_ejecucion_fuente
        FOREIGN KEY (id_ejecucion_fuente)
        REFERENCES ejecucion_fuentes(id_ejecucion_fuente)
        ON DELETE SET NULL,

    CONSTRAINT chk_procesamientos_motor
        CHECK (
            motor_extraccion IN (
                'DOCLING',
                'PYMUPDF',
                'OCRMYPDF',
                'TESSERACT',
                'COMBINADO'
            )
        ),

    CONSTRAINT chk_procesamientos_estado
        CHECK (
            estado IN (
                'PENDIENTE',
                'PROCESANDO',
                'COMPLETADO',
                'ERROR'
            )
        ),

    CONSTRAINT chk_procesamientos_paginas
        CHECK (
            paginas IS NULL
            OR paginas >= 0
        ),

    CONSTRAINT chk_procesamientos_fechas
        CHECK (
            fecha_inicio IS NULL
            OR fecha_fin IS NULL
            OR fecha_fin >= fecha_inicio
        )
);


-- ============================================================
-- 30. BITÁCORA DE ERRORES
-- ============================================================

CREATE TABLE bitacora_errores (
    id_error BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_ejecucion BIGINT,

    id_ejecucion_fuente BIGINT,

    id_fuente BIGINT,

    tipo_error VARCHAR(150),

    codigo_error VARCHAR(100),

    mensaje TEXT NOT NULL,

    detalle TEXT,

    stack_trace TEXT,

    url TEXT,

    fecha_hora TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    resuelto BOOLEAN NOT NULL DEFAULT FALSE,

    fecha_resolucion TIMESTAMPTZ,

    id_usuario_resolucion BIGINT,

    CONSTRAINT fk_bitacora_ejecucion
        FOREIGN KEY (id_ejecucion)
        REFERENCES ejecuciones_scraping(id_ejecucion)
        ON DELETE SET NULL,

    CONSTRAINT fk_bitacora_ejecucion_fuente
        FOREIGN KEY (id_ejecucion_fuente)
        REFERENCES ejecucion_fuentes(id_ejecucion_fuente)
        ON DELETE SET NULL,

    CONSTRAINT fk_bitacora_fuente
        FOREIGN KEY (id_fuente)
        REFERENCES fuentes(id_fuente)
        ON DELETE SET NULL,

    CONSTRAINT fk_bitacora_usuario
        FOREIGN KEY (id_usuario_resolucion)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
);


-- ============================================================
-- 31. EVENTOS API
-- ============================================================
-- Registro de comunicación:
-- Motor Python -> API -> Plataforma Laravel
-- ============================================================

CREATE TABLE api_eventos (
    id_evento BIGINT GENERATED ALWAYS AS IDENTITY PRIMARY KEY,

    id_convocatoria BIGINT,

    id_ejecucion BIGINT,

    tipo_evento VARCHAR(50) NOT NULL,

    endpoint TEXT NOT NULL,

    metodo_http VARCHAR(10) NOT NULL DEFAULT 'POST',

    payload JSONB,

    codigo_respuesta INTEGER,

    respuesta JSONB,

    estado VARCHAR(20) NOT NULL DEFAULT 'PENDIENTE',

    intentos INTEGER NOT NULL DEFAULT 0,

    fecha_creacion TIMESTAMPTZ NOT NULL DEFAULT CURRENT_TIMESTAMP,

    fecha_envio TIMESTAMPTZ,

    CONSTRAINT fk_api_eventos_convocatoria
        FOREIGN KEY (id_convocatoria)
        REFERENCES convocatorias(id_convocatoria)
        ON DELETE SET NULL,

    CONSTRAINT fk_api_eventos_ejecucion
        FOREIGN KEY (id_ejecucion)
        REFERENCES ejecuciones_scraping(id_ejecucion)
        ON DELETE SET NULL,

    CONSTRAINT chk_api_eventos_tipo
        CHECK (
            tipo_evento IN (
                'CONVOCATORIA_CREADA',
                'CONVOCATORIA_ACTUALIZADA',
                'SCRAPING_COMPLETADO',
                'SCRAPING_ERROR'
            )
        ),

    CONSTRAINT chk_api_eventos_estado
        CHECK (
            estado IN (
                'PENDIENTE',
                'ENVIADO',
                'ERROR'
            )
        ),

    CONSTRAINT chk_api_eventos_metodo
        CHECK (
            metodo_http IN (
                'POST',
                'PUT',
                'PATCH',
                'DELETE'
            )
        ),

    CONSTRAINT chk_api_eventos_intentos
        CHECK (
            intentos >= 0
        )
);


-- ============================================================
-- 32. ÍNDICES DE CONVOCATORIAS
-- ============================================================

CREATE INDEX idx_convocatorias_fecha_cierre
    ON convocatorias(fecha_cierre);

CREATE INDEX idx_convocatorias_estado
    ON convocatorias(estado);

CREATE INDEX idx_convocatorias_categoria
    ON convocatorias(id_categoria);

CREATE INDEX idx_convocatorias_organismo
    ON convocatorias(id_organismo);

CREATE INDEX idx_convocatorias_fuente
    ON convocatorias(id_fuente);

CREATE INDEX idx_convocatorias_contenido_hash
    ON convocatorias(contenido_hash);

CREATE UNIQUE INDEX uq_convocatorias_url_hash
    ON convocatorias(url_hash)
    WHERE url_hash IS NOT NULL;


-- ============================================================
-- 33. BÚSQUEDA FULL TEXT EN ESPAÑOL
-- ============================================================

CREATE INDEX idx_convocatorias_busqueda_texto
ON convocatorias
USING GIN (
    to_tsvector(
        'spanish'::regconfig,
        COALESCE(titulo, '') || ' ' ||
        COALESCE(descripcion, '') || ' ' ||
        COALESCE(objetivo, '')
    )
);


-- ============================================================
-- 34. ÍNDICES DE MIS CONVOCATORIAS
-- ============================================================

CREATE INDEX idx_usuario_convocatorias_usuario
    ON usuario_convocatorias(id_usuario);

CREATE INDEX idx_usuario_convocatorias_convocatoria
    ON usuario_convocatorias(id_convocatoria);

CREATE INDEX idx_usuario_convocatorias_favoritas
    ON usuario_convocatorias(id_usuario, es_favorita);

CREATE INDEX idx_usuario_convocatorias_estado
    ON usuario_convocatorias(estado_seguimiento);

CREATE INDEX idx_usuario_convocatorias_prioridad
    ON usuario_convocatorias(prioridad);


-- ============================================================
-- 35. ÍNDICES DE PROPUESTAS
-- ============================================================

CREATE INDEX idx_propuestas_usuario
    ON propuestas(id_usuario);

CREATE INDEX idx_propuestas_convocatoria
    ON propuestas(id_convocatoria);

CREATE INDEX idx_propuestas_estado
    ON propuestas(estado);


-- ============================================================
-- 36. ÍNDICES DE NOTIFICACIONES
-- ============================================================

CREATE INDEX idx_notificaciones_usuario_leida
    ON notificaciones(id_usuario, leida);


-- ============================================================
-- 37. ÍNDICES DEL MOTOR DE EXTRACCIÓN
-- ============================================================

CREATE INDEX idx_ejecuciones_scraping_fecha
    ON ejecuciones_scraping(fecha_inicio);

CREATE INDEX idx_ejecuciones_scraping_estado
    ON ejecuciones_scraping(estado);

CREATE INDEX idx_bitacora_fecha
    ON bitacora_errores(fecha_hora);

CREATE INDEX idx_bitacora_resuelto
    ON bitacora_errores(resuelto);

CREATE INDEX idx_api_eventos_estado
    ON api_eventos(estado);

CREATE INDEX idx_procesamientos_documentos_estado
    ON procesamientos_documentos(estado);

CREATE INDEX idx_procesamientos_documentos_archivo
    ON procesamientos_documentos(id_archivo);

CREATE INDEX idx_procesamientos_documentos_hash
    ON procesamientos_documentos(hash_documento);


-- ============================================================
-- 38. ÍNDICES JSONB
-- ============================================================

CREATE INDEX idx_fuentes_selector_config
    ON fuentes
    USING GIN(selector_config);

CREATE INDEX idx_propuestas_base_contenido
    ON propuestas_base
    USING GIN(contenido_generado);

CREATE INDEX idx_procesamientos_datos_extraidos
    ON procesamientos_documentos
    USING GIN(datos_extraidos);


-- ============================================================
-- 39. TRIGGERS DE ACTUALIZACIÓN
-- ============================================================

CREATE TRIGGER trg_roles_actualizacion
BEFORE UPDATE ON roles
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_departamentos_actualizacion
BEFORE UPDATE ON departamentos
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_usuarios_actualizacion
BEFORE UPDATE ON usuarios
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_categorias_actualizacion
BEFORE UPDATE ON categorias
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_organismos_actualizacion
BEFORE UPDATE ON organismos
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_fuentes_actualizacion
BEFORE UPDATE ON fuentes
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_convocatorias_actualizacion
BEFORE UPDATE ON convocatorias
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_usuario_convocatorias_actualizacion
BEFORE UPDATE ON usuario_convocatorias
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_eventos_calendario_actualizacion
BEFORE UPDATE ON eventos_calendario
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_propuestas_actualizacion
BEFORE UPDATE ON propuestas
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_propuesta_requisitos_actualizacion
BEFORE UPDATE ON propuesta_requisitos
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_presupuesto_actualizacion
BEFORE UPDATE ON presupuesto_items
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


CREATE TRIGGER trg_cronograma_actualizacion
BEFORE UPDATE ON cronograma_actividades
FOR EACH ROW
EXECUTE FUNCTION actualizar_fecha_modificacion();


-- ============================================================
-- 40. VISTA: CONVOCATORIAS ACTIVAS
-- ============================================================

CREATE VIEW vw_convocatorias_activas AS
SELECT
    c.id_convocatoria,

    c.titulo,

    cat.nombre AS categoria,

    o.nombre AS organismo,

    f.nombre AS fuente,

    c.fecha_publicacion,

    c.fecha_cierre,

    CASE
        WHEN c.fecha_cierre IS NULL THEN NULL
        ELSE c.fecha_cierre - CURRENT_DATE
    END AS dias_restantes,

    c.monto_minimo,

    c.monto_maximo,

    c.moneda,

    c.modalidad,

    c.url_original

FROM convocatorias c

LEFT JOIN categorias cat
    ON cat.id_categoria = c.id_categoria

LEFT JOIN organismos o
    ON o.id_organismo = c.id_organismo

LEFT JOIN fuentes f
    ON f.id_fuente = c.id_fuente

WHERE c.estado IN (
    'ACTIVA',
    'PUBLICADA'
)

AND (
    c.fecha_cierre IS NULL
    OR c.fecha_cierre >= CURRENT_DATE
);


-- ============================================================
-- 41. VISTA: MIS CONVOCATORIAS
-- ============================================================

CREATE VIEW vw_mis_convocatorias AS
SELECT
    uc.id_usuario_convocatoria,

    u.id_usuario,

    CONCAT(
        u.nombre,
        ' ',
        u.apellidos
    ) AS usuario,

    c.id_convocatoria,

    c.titulo AS convocatoria,

    uc.es_favorita,

    uc.estado_seguimiento,

    uc.prioridad,

    uc.notas,

    uc.ultima_accion,

    uc.fecha_ultima_accion,

    c.fecha_cierre,

    CASE
        WHEN c.fecha_cierre IS NULL THEN NULL
        ELSE c.fecha_cierre - CURRENT_DATE
    END AS dias_restantes,

    uc.fecha_agregado,

    uc.fecha_actualizacion

FROM usuario_convocatorias uc

JOIN usuarios u
    ON u.id_usuario = uc.id_usuario

JOIN convocatorias c
    ON c.id_convocatoria = uc.id_convocatoria;


-- ============================================================
-- 42. VISTA: FAVORITOS
-- ============================================================
-- FAVORITOS ES UNA VISTA.
-- NO EXISTE UNA TABLA favoritos.
-- ============================================================

CREATE VIEW vw_favoritos AS
SELECT
    uc.id_usuario_convocatoria,

    u.id_usuario,

    CONCAT(
        u.nombre,
        ' ',
        u.apellidos
    ) AS usuario,

    c.id_convocatoria,

    c.titulo AS convocatoria,

    cat.nombre AS categoria,

    o.nombre AS organismo,

    c.fecha_cierre,

    uc.estado_seguimiento,

    uc.prioridad,

    uc.fecha_agregado

FROM usuario_convocatorias uc

JOIN usuarios u
    ON u.id_usuario = uc.id_usuario

JOIN convocatorias c
    ON c.id_convocatoria = uc.id_convocatoria

LEFT JOIN categorias cat
    ON cat.id_categoria = c.id_categoria

LEFT JOIN organismos o
    ON o.id_organismo = c.id_organismo

WHERE uc.es_favorita = TRUE;


-- ============================================================
-- 43. VISTA: CONVOCATORIAS PENDIENTES DE REVISIÓN
-- ============================================================

CREATE VIEW vw_convocatorias_por_revisar AS
SELECT
    c.id_convocatoria,

    c.titulo,

    o.nombre AS organismo,

    cat.nombre AS categoria,

    f.nombre AS fuente,

    c.fecha_publicacion,

    c.fecha_cierre,

    c.fecha_registro

FROM convocatorias c

LEFT JOIN organismos o
    ON o.id_organismo = c.id_organismo

LEFT JOIN categorias cat
    ON cat.id_categoria = c.id_categoria

LEFT JOIN fuentes f
    ON f.id_fuente = c.id_fuente

WHERE c.estado = 'PENDIENTE_REVISION';


-- ============================================================
-- 44. VISTA: PRESUPUESTO TOTAL POR PROPUESTA
-- ============================================================

CREATE VIEW vw_propuesta_presupuesto AS
SELECT
    p.id_propuesta,

    p.titulo,

    COALESCE(
        SUM(pi.subtotal),
        0::NUMERIC
    ) AS presupuesto_total

FROM propuestas p

LEFT JOIN presupuesto_items pi
    ON pi.id_propuesta = p.id_propuesta

GROUP BY
    p.id_propuesta,
    p.titulo;


-- ============================================================
-- 45. VISTA: RESUMEN DE SCRAPING
-- ============================================================

CREATE VIEW vw_resumen_scraping AS
SELECT
    e.id_ejecucion,

    e.fecha_inicio,

    e.fecha_fin,

    e.estado,

    e.total_fuentes,

    e.fuentes_exitosas,

    e.fuentes_fallidas,

    e.total_encontradas AS convocatorias_encontradas,

    e.nuevas_convocatorias,

    e.convocatorias_actualizadas,

    e.duplicados_detectados,

    e.total_errores,

    e.duracion_segundos

FROM ejecuciones_scraping e;


-- ============================================================
-- 46. DATOS INICIALES: ROLES
-- ============================================================

INSERT INTO roles (
    nombre,
    descripcion
)
VALUES
(
    'DOCENTE',
    'Usuario docente de la plataforma'
),
(
    'DIRECTIVO',
    'Usuario encargado de revisar y aprobar convocatorias'
),
(
    'ADMINISTRADOR',
    'Administrador general de la plataforma'
),
(
    'SISTEMA',
    'Procesos automáticos y motor de extracción'
);


-- ============================================================
-- 47. DATOS INICIALES: PERMISOS
-- ============================================================

INSERT INTO permisos (
    nombre,
    descripcion,
    modulo
)
VALUES
(
    'ver_convocatorias',
    'Consultar convocatorias',
    'CONVOCATORIAS'
),
(
    'crear_convocatorias',
    'Crear convocatorias',
    'CONVOCATORIAS'
),
(
    'editar_convocatorias',
    'Editar convocatorias',
    'CONVOCATORIAS'
),
(
    'eliminar_convocatorias',
    'Eliminar convocatorias',
    'CONVOCATORIAS'
),
(
    'aprobar_convocatorias',
    'Aprobar convocatorias',
    'CONVOCATORIAS'
),
(
    'gestionar_usuarios',
    'Administrar usuarios',
    'USUARIOS'
),
(
    'gestionar_fuentes',
    'Administrar fuentes web',
    'EXTRACCION'
),
(
    'ejecutar_scraping',
    'Ejecutar el motor de extracción',
    'EXTRACCION'
),
(
    'ver_bitacora',
    'Consultar la bitácora de errores',
    'EXTRACCION'
),
(
    'generar_propuestas',
    'Generar propuestas',
    'PROPUESTAS'
),
(
    'editar_propuestas',
    'Editar propuestas',
    'PROPUESTAS'
),
(
    'generar_reportes',
    'Generar reportes',
    'REPORTES'
),
(
    'ver_estadisticas',
    'Consultar estadísticas',
    'REPORTES'
);


-- ============================================================
-- 48. PERMISOS: DOCENTE
-- ============================================================

INSERT INTO rol_permisos (
    id_rol,
    id_permiso
)
SELECT
    r.id_rol,
    p.id_permiso

FROM roles r

CROSS JOIN permisos p

WHERE r.nombre = 'DOCENTE'

AND p.nombre IN (
    'ver_convocatorias',
    'generar_propuestas',
    'editar_propuestas'
);


-- ============================================================
-- 49. PERMISOS: DIRECTIVO
-- ============================================================

INSERT INTO rol_permisos (
    id_rol,
    id_permiso
)
SELECT
    r.id_rol,
    p.id_permiso

FROM roles r

CROSS JOIN permisos p

WHERE r.nombre = 'DIRECTIVO'

AND p.nombre IN (
    'ver_convocatorias',
    'aprobar_convocatorias',
    'generar_reportes',
    'ver_estadisticas'
);


-- ============================================================
-- 50. PERMISOS: ADMINISTRADOR
-- ============================================================

INSERT INTO rol_permisos (
    id_rol,
    id_permiso
)
SELECT
    r.id_rol,
    p.id_permiso

FROM roles r

CROSS JOIN permisos p

WHERE r.nombre = 'ADMINISTRADOR';


-- ============================================================
-- 51. PERMISOS: SISTEMA
-- ============================================================

INSERT INTO rol_permisos (
    id_rol,
    id_permiso
)
SELECT
    r.id_rol,
    p.id_permiso

FROM roles r

CROSS JOIN permisos p

WHERE r.nombre = 'SISTEMA'

AND p.nombre IN (
    'crear_convocatorias',
    'editar_convocatorias',
    'ejecutar_scraping'
);


-- ============================================================
-- 52. DEPARTAMENTO INICIAL
-- ============================================================

INSERT INTO departamentos (
    nombre,
    descripcion
)
VALUES (
    'Sistemas y Computación',
    'Departamento académico de Sistemas y Computación'
);


-- ============================================================
-- 53. CATEGORÍAS INICIALES
-- ============================================================

INSERT INTO categorias (
    nombre,
    descripcion
)
VALUES
(
    'Investigación',
    'Convocatorias relacionadas con investigación'
),
(
    'Innovación',
    'Convocatorias para proyectos de innovación'
),
(
    'Educación',
    'Programas y proyectos educativos'
),
(
    'Infraestructura',
    'Apoyos relacionados con infraestructura'
),
(
    'Emprendimiento',
    'Programas de emprendimiento'
),
(
    'Becas',
    'Programas y convocatorias de becas'
),
(
    'Desarrollo Tecnológico',
    'Proyectos de desarrollo tecnológico'
),
(
    'Ciencia y Tecnología',
    'Convocatorias relacionadas con ciencia y tecnología'
);


-- ============================================================
-- 54. ORGANISMOS DE PRUEBA
-- ============================================================

INSERT INTO organismos (
    nombre,
    descripcion,
    sitio_web,
    tipo_organismo,
    pais
)
VALUES
(
    'Secretaría de Ciencia, Humanidades, Tecnología e Innovación',
    'Organismo público federal relacionado con ciencia y tecnología',
    'https://secihti.mx/',
    'FEDERAL',
    'México'
),
(
    'Instituto Tecnológico Superior de Valladolid',
    'Institución de educación superior',
    NULL,
    'UNIVERSIDAD',
    'México'
);


-- ============================================================
-- 55. FUENTES DE PRUEBA
-- ============================================================

INSERT INTO fuentes (
    nombre,
    url_base,
    tipo_fuente,
    requiere_javascript,
    activa,
    frecuencia_scraping
)
VALUES
(
    'SECIHTI',
    'https://secihti.mx/',
    'WEB',
    FALSE,
    TRUE,
    1440
),
(
    'Portal Institucional ITSVA',
    'https://itsva.edu.mx/',
    'WEB',
    FALSE,
    TRUE,
    1440
);


-- ============================================================
-- 56. CONVOCATORIA DE PRUEBA
-- ============================================================

INSERT INTO convocatorias (
    titulo,
    descripcion,
    objetivo,
    fecha_publicacion,
    fecha_inicio,
    fecha_cierre,
    monto_minimo,
    monto_maximo,
    moneda,
    modalidad,
    id_categoria,
    id_organismo,
    id_fuente,
    url_original,
    url_hash,
    contenido_hash,
    origen,
    estado,
    fecha_extraccion
)
SELECT
    'Convocatoria de prueba para proyectos de innovación',

    'Registro utilizado para comprobar el funcionamiento de la plataforma.',

    'Impulsar proyectos académicos de innovación y desarrollo tecnológico.',

    CURRENT_DATE,

    CURRENT_DATE,

    CURRENT_DATE + 60,

    50000,

    250000,

    'MXN',

    'NACIONAL',

    cat.id_categoria,

    org.id_organismo,

    f.id_fuente,

    'https://ejemplo.local/convocatorias/innovacion-001',

    'TEST_HASH_URL_001',

    'TEST_HASH_CONTENIDO_001',

    'SCRAPING',

    'ACTIVA',

    CURRENT_TIMESTAMP

FROM categorias cat

CROSS JOIN organismos org

CROSS JOIN fuentes f

WHERE cat.nombre = 'Innovación'

AND org.nombre =
    'Secretaría de Ciencia, Humanidades, Tecnología e Innovación'

AND f.nombre = 'SECIHTI';


-- ============================================================
-- 57. REQUISITOS DE PRUEBA
-- ============================================================

INSERT INTO convocatoria_requisitos (
    id_convocatoria,
    titulo,
    descripcion,
    obligatorio,
    tipo_requisito,
    orden
)
SELECT
    id_convocatoria,

    'Responsable del proyecto',

    'El proyecto deberá contar con un responsable académico.',

    TRUE,

    'ADMINISTRATIVO',

    1

FROM convocatorias

WHERE url_hash = 'TEST_HASH_URL_001';


INSERT INTO convocatoria_requisitos (
    id_convocatoria,
    titulo,
    descripcion,
    obligatorio,
    tipo_requisito,
    orden
)
SELECT
    id_convocatoria,

    'Presupuesto',

    'Se deberá presentar un presupuesto detallado.',

    TRUE,

    'FINANCIERO',

    2

FROM convocatorias

WHERE url_hash = 'TEST_HASH_URL_001';


COMMIT;


-- ============================================================
-- 58. CONSULTAS DE COMPROBACIÓN
-- ============================================================
-- Estas consultas no modifican información.
-- Solo permiten verificar que todo se creó.
-- ============================================================

SELECT current_database() AS base_datos;

SELECT current_user AS usuario_postgresql;

SELECT COUNT(*) AS total_roles
FROM roles;

SELECT COUNT(*) AS total_permisos
FROM permisos;

SELECT COUNT(*) AS total_categorias
FROM categorias;

SELECT COUNT(*) AS total_organismos
FROM organismos;

SELECT COUNT(*) AS total_fuentes
FROM fuentes;

SELECT COUNT(*) AS total_convocatorias
FROM convocatorias;

SELECT *
FROM vw_convocatorias_activas;

SELECT *
FROM vw_convocatorias_por_revisar;

SELECT *
FROM vw_resumen_scraping;
