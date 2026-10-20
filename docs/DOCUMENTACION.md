# Plataforma de Gestión de Convocatorias ITSVA — Documentación

Instituto Tecnológico Superior de Valladolid (Yucatán, TecNM).
Estado del proyecto al 15 de octubre de 2026.

---

## 1. Qué hace la plataforma

Busca en internet convocatorias de **financiamiento para proyectos de investigación** en las que el ITSVA puede participar, extrae sus datos, las valida y las publica para que el personal académico las consulte y prepare propuestas para participar.

El flujo completo es:

```
Descubrir → extraer → clasificar → evaluar → validar → guardar → publicar → dar seguimiento → consultar en la web
```

Principios del diseño:

- **Publicación automática, sin intervención humana.** Una convocatoria se publica sola cuando el validador la aprueba (o al momento, si la sube a mano un docente o directivo). Nadie revisa convocatorias a mano.
- **El directivo revisa propuestas, no convocatorias.** Docentes y directivos preparan propuestas para participar; un directivo las aprueba, devuelve con observaciones o rechaza.
- **Solo lo correcto se publica.** Umbrales de calidad fijos (80 para el evaluador, 70 para el validador). Cero publicaciones puede ser un resultado correcto.
- **Bajo consumo.** Peticiones HTTP sencillas antes que navegadores; ciclos secuenciales y limitados.
- **Seguridad.** Defensas SSRF en el extractor, respeto a `robots.txt`, permisos validados en el servidor y secretos solo en `.env`.

---

## 2. Arquitectura

```
                    ┌─────────────────────────────┐
                    │  systemd timer (cada 6 h)   │
                    │  00:00 06:00 12:00 18:00    │
                    └──────────────┬──────────────┘
                                   │
                                   ▼
┌──────────────────────── extractor/ (Python) ────────────────────────┐
│  Ciclo automático (6 fases) ─────────────┐                          │
│  API FastAPI 127.0.0.1:8001 ──────────┐  │                          │
└───────────────────────────────────────┼──┼──────────────────────────┘
                                        │  │  lee / escribe
                                        ▼  ▼
                              ┌──────────────────────┐
                              │  PostgreSQL 16        │
                              └──────────▲───────────┘
                                         │
┌──────────────────────── web/ (Laravel + Filament) ──────────────────┐
│  /login (único)  →  paneles /docente  /directivo  /admin            │
│  El admin llama a la API del extractor («Ejecutar ciclo»)           │
└─────────────────────────────────────────────────────────────────────┘
```

| Carpeta | Contenido |
|---|---|
| `web/` | Aplicación web (Laravel 13, Filament 5, Livewire 4). Tres paneles y el login. |
| `extractor/` | Motor de búsqueda y extracción (Python 3.12, Scrapy, FastAPI). |
| `extractor/systemd/` | Copias versionadas de las units de systemd (las instaladas en `/etc/systemd/system/` son las que rigen). |
| `docs/` | Esta documentación y la guía de instalación. |
| `CLAUDE.md` | Notas técnicas internas y reglas de trabajo del proyecto. |

---

## 3. Tecnologías

| Capa | Tecnología | Versión probada |
|---|---|---|
| Sistema | Ubuntu 24.04 (WSL2 en Windows o servidor Linux) | 24.04 |
| Base de datos | PostgreSQL | 16 |
| Web | PHP | 8.3 |
| | Composer | 2.7 |
| | Laravel | 13.33 |
| | Filament (paneles) | 5 |
| | Livewire | 4 |
| | barryvdh/laravel-dompdf (PDF de propuestas) | 3.1 |
| | openspout (Excel de propuestas y reportes) | — |
| Extractor | Python | 3.12 |
| | Scrapy (spider de convocatorias) | 2.19 |
| | FastAPI + Uvicorn (API) | 0.141 / 0.53 |
| | psycopg 3 (PostgreSQL) | 3.3 |
| | selectolax, lxml (HTML) | 0.4 / 6.1 |
| | PyMuPDF (texto de PDF) | 1.28 |
| | googlenewsdecoder (Google News RSS) | 0.2 |
| | pytesseract + Tesseract OCR (solo convocatorias marco) | 0.3 / 5 (`spa`) |
| Automatización | systemd (timer + servicios) | — |
| Diseño | Figma Make (referencia visual) | — |

Fuentes tipográficas del diseño: DM Sans e Inter, servidas desde Bunny Fonts. Los íconos son SVG lucide incluidos en el código.

---

## 4. Roles y paneles

Hay un solo login en `/login`. Según el rol, el usuario entra a su panel; las rutas `/admin/login`, `/directivo/login` y `/docente/login` redirigen a `/login`. Incluye «Recordarme» y recuperación de contraseña (`/recuperar-contrasena`).

### 4.1 Docente (`/docente`)
| Página | Para qué sirve |
|---|---|
| Dashboard | Banner, estadísticas (enlazan a su sección), convocatorias recomendadas, próximos cierres, propuestas recientes. |
| Buscar convocatorias | Convocatorias **vigentes** (publicadas y no vencidas), con filtros por **tipo** y **a quién va dirigida**. Cada tarjeta lleva sus marcas, ☆ favorita y guardar/quitar; toda la tarjeta abre el detalle. |
| Detalle de convocatoria | Información, requisitos, financiamiento, archivos y fechas; última verificación en el sitio oficial. Botones «Guardar / Quitar de Mis convocatorias», «☆ Favorita» y «Generar propuesta». Una convocatoria cerrada se abre solo de consulta. |
| Mis convocatorias | Convocatorias guardadas y su seguimiento; pestañas **Todas / Favoritas**. |
| Historial | Pestaña «Historial (cerradas)» de Buscar convocatorias: convocatorias que ya cerraron (solo consulta), con filtros por tipo, organismo y año. |
| Subir convocatoria | Dos secciones con botones arriba. **Automática** (por defecto; `?modo=manual` abre la otra): se pega la URL, el motor extrae y valida, y el formulario se llena; si no cumple los criterios se avisa con los motivos y el usuario elige «Publicar de todas maneras» o «No publicar». **Manual:** el formulario completo. Al pasar de automática a manual se conserva lo ya extraído. En automática no se puede publicar sin analizar un enlace (se valida en el servidor). Se publica al momento (no si ya venció); un enlace que ya está en la plataforma no se duplica. Su autor puede corregirla. |
| Calendario | Cierres y eventos personales. |
| Propuestas | Propuestas propias en tarjetas (toda la fila abre el editor), con búsqueda, filtro por estado y los totales como filtros. |
| Flujo de propuesta | Generar → Editor → Objetivos → Requisitos → Presupuesto → Cotizaciones → Cronograma → Entregables → Vista previa (PDF/Excel y **Enviar a revisión**). |
| Notificaciones | Avisos y preferencias (fechas de cierre, cambios en convocatorias que sigo, cambios en propuestas). |
| Perfil | Datos de la cuenta (desde el menú del avatar). |

### 4.2 Directivo (`/directivo`)
Tiene **todas las páginas de trabajo del docente** (mismas clases, el rol exigido sale del panel) y además:

| Página | Para qué sirve |
|---|---|
| Dashboard | Propuestas por revisar, aprobadas y devueltas del mes, convocatorias recientes, resumen del mes. |
| Propuestas por revisar | Propuestas enviadas por docentes y directivos (incluidas las suyas). |
| Revisar propuesta | Vista previa de solo lectura + dictamen: **Aprobar**, **Devolver con observaciones** (vuelve a edición) o **Rechazar**. |
| Estadísticas | Convocatorias detectadas y publicadas por mes, por área y organismo; propuestas por estado. |
| Reportes | Exportación PDF/Excel/CSV de convocatorias, propuestas y financiamiento. |

### 4.3 Administrador (`/admin`)
| Página | Para qué sirve |
|---|---|
| Dashboard | Estadísticas, **interruptor del motor**, tarjeta del último ciclo automático, historial de ejecuciones y estado del sistema. |
| Usuarios, Roles y permisos | Cuentas, roles (DOCENTE, DIRECTIVO, ADMINISTRADOR, SISTEMA) y permisos (`rol_permisos`). |
| Fuentes web | Fuentes oficiales que consulta el motor. |
| Motor de extracción | Estado de la API, **interruptor del motor**, «Ejecutar ciclo» por fuente, ejecuciones. |
| Descubrimientos | Pestaña de **Motor de extracción** (no está en el menú): candidatos que el buscador encontró en internet. No son convocatorias y no se mezclan con ellas. Tarjetas que filtran (Descubiertos, En espera, Convertidos, Descartados, Con error); cada fila muestra título y dominio, estado y decisión del clasificador, evaluación (se aprueba con 80) y enlace a la convocatoria si ya se convirtió. Solo consulta. |
| Bitácora de errores | Incidencias de los procesos automáticos. |
| Convocatorias | Gestión completa de **todas** las convocatorias, en cualquier estado (por eso el admin no tiene historial aparte). Las tarjetas de arriba filtran la tabla: Todas, No publicadas, Vigentes, Cerradas. Cada fila muestra título y organismo, tipo y audiencia, estado (con «Vencida» y si vino del motor o se subió a mano) y cierre con tiempo relativo; toda la fila abre el detalle. |
| Categorías, Organismos, Departamentos, Propuestas | Catálogos. |

---

## 5. Criterio de «convocatoria vigente»

Es **el mismo en los tres paneles**:

- `Convocatoria::visibles()` → estado `PUBLICADA` o `ACTIVA` **y** fecha de cierre vacía o igual/posterior a hoy.
- `Convocatoria::historial()` → `CERRADA` o publicada cuya fecha de cierre ya pasó, sin las de tipo `NO_APLICA`.

### Tipos y audiencia (desde el 18-oct-2026)

Además de financiamiento a proyectos se publican **becas, premios, concursos, eventos y formación**, cada una marcada con su tipo (`convocatorias.tipo`) y a quién va dirigida o a quién puede ayudar (`dirigido_a`: institución, docentes e investigadores, estudiantes). Las reglas están en `extractor/app/tipo_convocatoria.py` (título y ruta deciden el tipo; objetivo y descripción, la audiencia). `NO_APLICA` es interno: anuncios de resultados, vacantes/plazas, no elegibles para ITSVA y programas que excluyen a mexicanos; nunca se muestra. Las convocatorias anteriores sin tipo cuentan como financiamiento.
- `PENDIENTE_REVISION` es un estado **interno del motor**: el validador no la aprobó (datos incompletos, vencida, no elegible…) y el ciclo la vuelve a validar sola. No se muestra a docentes ni directivos.

Estados de convocatoria usados: `PENDIENTE_REVISION`, `PUBLICADA`, `CERRADA` (y `DESCARTADA`, `ACTIVA` como alias histórico de `PUBLICADA`).

---

## 6. Motor de búsqueda y extracción (`extractor/`)

### 6.1 Ciclo automático (`app/ejecutar_ciclo_automatico.py`)

Lo ejecuta systemd cada 6 horas. Plazo máximo 3.300 s. Escribe su reporte en `extractor/ciclo_automatico_ultimo.json`. Antes de empezar revisa el **interruptor del motor**: si está apagado, no hace nada (reporte `APAGADO`).

| Fase | Qué hace |
|---|---|
| 1. Descubrimiento | Google News RSS + portales institucionales `.mx` (sitemaps, WordPress, HTML ligero). Guarda candidatos en `descubrimientos_web`. Ya no rastrea sitios de noticias. |
| 2. Evaluación | Puntaje por reglas; aprueba con ≥ 80 **y** condiciones obligatorias (incluida la elegibilidad para ITSVA). No elegibles → `DESCARTADO`. |
| 3. Extracción y publicación | Extrae los descubrimientos aprobados, deduplica por `url_hash`, enriquece y valida; publica si el validador da ≥ 70 sin bloqueos. |
| 4. Fuentes oficiales | Consulta las fuentes activas sin JavaScript cuya frecuencia ya pasó (24 h). Lo nuevo entra como `PENDIENTE_REVISION`. |
| 5. Seguimiento | Reconsulta cada ~20 h las convocatorias vigentes con enlace (máx. 5 por ciclo): agrega documentos nuevos, aplica ampliaciones de fecha, avisa adelantos, detecta cambios de texto > 2 % y **avisa a quienes la siguen o tienen propuesta**. Completa datos faltantes con el enriquecimiento. |
| 6. Revalidación | Cierra vencidas y enriquece + valida las pendientes (máx. 5 por ciclo; las recién guardadas primero). Publica las que pasan el validador. |
| Mantenimiento | Al final (`app/mantenimiento.py`, nunca rompe el ciclo): borra descubrimientos `DESCARTADO` sin convocatoria de más de 180 días, notificaciones leídas de más de 180 días y temporales de `web/storage/app/tmp` de más de 24 h; clasifica tipo y audiencia de las convocatorias que quedaron sin tipo (p. ej. pendientes cerradas por vencidas). |

**Carga inicial** (para llenar la base la primera vez; mismo plazo de 3.300 s y mismo bloqueo, no choca con el timer):

```bash
cd extractor
./.venv/bin/python -m app.ejecutar_ciclo_automatico --cantidad-google 12 --max-dominios 8 \
  --max-publicaciones 10 --limite-fuentes 30 --max-revalidaciones 15
```

**Si el ciclo falla** (excepción, falta de memoria o tiempo agotado), `ExecStopPost` ejecuta `scripts/registrar_fallo_ciclo.py`: deja el reporte con estado `FALLIDO` y registra el error (con las últimas líneas del journal) en `bitacora_errores`. El dashboard del admin muestra una alerta roja si el último ciclo falló, si lleva más de 7 h sin correr o si no hay reporte.

### 6.2 Módulos principales

| Archivo | Función |
|---|---|
| `app/ejecutar_descubrimiento.py` | Buscador (fase 1). |
| `app/descubrimiento/clasificar_objetivo.py` | Clasifica título+URL: ACEPTAR / REVISION / DESCARTAR. |
| `app/descubrimiento/elegibilidad.py` | ¿Puede participar ITSVA? Excluye otros estados, extranjeros sin apertura, vacantes. Ante la duda, elegible. |
| `app/evaluar_descubrimientos.py` | Evaluador (umbral 80). |
| `app/publicar_descubrimientos.py` | Extracción persistente, publicación, revalidación y cierre de vencidas. |
| `app/validar_convocatoria.py` | Validador final (umbral 70). Marca tipo y audiencia y los guarda. Bloquea resultados, vacantes, no elegibles, programas que excluyen a mexicanos y vencidas o sin fecha. El seguimiento retira (`PENDIENTE_REVISION`) las publicadas del motor que hoy quedarían bloqueadas. |
| `app/tipo_convocatoria.py` | Tipo y a quién va dirigida (reglas puras). |
| `app/importar_desde_url.py` | «Subir desde un enlace»: extrae y valida una URL sin guardar. |
| `app/mantenimiento.py` | Limpieza periódica al final del ciclo. |
| `app/enriquecer_convocatoria.py` | Completa descripción, objetivo, fecha de cierre, categoría, organismo, montos, fechas de publicación y apertura, requisitos y restricciones (desde la página y sus PDF). Solo llena campos vacíos. |
| `app/enriquecer_datos_extra.py` | Reglas puras de montos, fechas, requisitos y descripción. |
| `app/spiders/convocatorias.py` | Spider Scrapy: reglas propias para SECIHTI y **listado genérico** para cualquier otra fuente. |
| `app/ejecutar_scraping.py`, `app/ejecutar_fuente.py` | Ejecutan el spider (vista previa o `--guardar`). |
| `app/seguimiento_convocatorias.py` | Seguimiento de publicadas (fase 5). |
| `app/interruptor_motor.py` | Lee el interruptor `ajustes_sistema.motor_activo`. |
| `app/seguridad_red.py` | Sesión HTTP segura (bloqueo de IP privadas y redirecciones inseguras). |
| `app/descubrimiento/codificacion.py` | Decodificación correcta de páginas sin charset declarado. |
| `app/main.py` | API FastAPI (`/health`, `/db-check`, `POST /scraping/ejecutar`, `POST /convocatorias/analizar-url`). |

### 6.3 Fuentes oficiales configuradas

| ID | Fuente | Estado |
|---|---|---|
| 2 | SECIHTI — `https://www.secihti.mx/convocatorias/` | Activa (reglas propias del spider). |
| 4 | Secretaría de Ciencia de Yucatán — `https://ciencia.yucatan.gob.mx/convocatorias/` | Activa (listado genérico). Publica sobre todo premios, ferias y becas, que el validador bloquea. |
| 1 | Impulsora de Innovación México | Requiere JavaScript y responde 403 (Akamai): el ciclo la omite. No se evade la protección. |

Para agregar una fuente: **Admin → Fuentes web → Nueva fuente** con la URL del listado de convocatorias. Si no requiere JavaScript, el ciclo la consultará con el listado genérico.

### 6.4 Interruptor del motor

En el **dashboard del admin** y en **Motor de extracción** hay un botón *Encender / Apagar motor*. Solo un administrador con el permiso `ejecutar_scraping` puede usarlo (se valida en el servidor). Apagado: el ciclo no busca ni extrae y la API rechaza ejecuciones (409); lo publicado sigue visible. Se guarda quién lo cambió y cuándo (`ajustes_sistema`).

---

## 7. Flujo de propuestas

1. El docente (o directivo) abre una convocatoria y pulsa **Generar propuesta**: la propuesta se crea con la convocatoria preseleccionada y datos iniciales.
2. Completa las secciones (datos, objetivos, requisitos, presupuesto, cotizaciones, cronograma, entregables).
3. En la **Vista previa** pulsa **Enviar a revisión** (se habilita cuando las secciones principales están completas). Pasa a `ENVIADA` y se avisa a los directivos.
4. Un directivo dictamina en **Propuestas por revisar**: `APROBADA`, devuelta a `EDITANDO` con observaciones, o `RECHAZADA`.
5. Cada envío y dictamen queda en `propuesta_revisiones` y el autor recibe una notificación.

Estados de propuesta: `BORRADOR`, `GENERADA`, `EDITANDO`, `LISTA`, `ENVIADA`, `APROBADA`, `RECHAZADA`.

---

## 8. Notificaciones

Las genera la plataforma según las preferencias de cada usuario (`notificacion_preferencias`):

| Preferencia | Aviso |
|---|---|
| `fechas_proximas` | Convocatoria guardada o propuesta sin enviar cuya convocatoria cierra en 7 días. |
| `convocatorias_revision` | Cambios detectados en una convocatoria que el usuario sigue o para la que tiene propuesta. |
| `nuevas_propuestas` | Propuestas enviadas (directivos) y dictámenes (autores). |

**Correo electrónico:** `MAIL_MAILER=log` por ahora (los correos, como la recuperación de contraseña, se escriben en `web/storage/logs/laravel.log`). Para enviarlos de verdad hay que configurar SMTP en `web/.env`.

---

## 9. Base de datos

PostgreSQL. Las tablas se crean con `php artisan migrate`. Tablas principales:

| Grupo | Tablas |
|---|---|
| Usuarios y permisos | `users`, `roles`, `permisos`, `rol_permisos`, `departamentos`, `password_reset_tokens`, `sessions` |
| Convocatorias | `convocatorias`, `convocatoria_archivos`, `convocatoria_requisitos`, `convocatoria_financiamientos`, `convocatoria_modalidades`, `convocatoria_modalidad_etapas`, `convocatoria_apoyos`, `convocatoria_revisions`, `convocatoria_seguimientos`, `categorias`, `organismos` |
| Motor | `fuentes`, `descubrimientos_web`, `ejecuciones_scraping`, `ejecucion_fuentes`, `bitacora_errores`, `api_eventos`, `procesamientos_documentos`, `ajustes_sistema` |
| Seguimiento y propuestas | `usuario_convocatorias`, `eventos_calendario`, `propuestas`, `propuestas_base`, `propuesta_objetivos`, `propuesta_requisitos`, `presupuesto_items`, `cotizaciones`, `cronograma_actividades`, `propuesta_entregables`, `propuesta_archivos`, `evidencias`, `propuesta_revisiones` |
| Avisos | `notificaciones`, `notificacion_preferencias` |
| Laravel | `cache`, `cache_locks`, `jobs`, `job_batches`, `failed_jobs` |

Notas:
- `convocatorias.url_hash` = SHA-256 de la URL normalizada; evita duplicados. Debe coincidir entre `extractor/app/urls.py` y `Convocatoria::hashUrl()`.
- Relación padre-hijas por `convocatoria_padre_id` (convocatorias marco).
- Datos iniciales: `php artisan db:seed` carga roles, permisos, la categoría «investigación», el organismo SECIHTI y las fuentes oficiales.

---

## 10. Comandos importantes

### Web (`cd web`)
```bash
php artisan serve --host=127.0.0.1 --port=8000   # servidor de desarrollo
php artisan migrate                               # aplicar migraciones (pedir confirmación antes)
php artisan migrate --pretend                     # ver el SQL sin aplicarlo
php artisan db:seed                               # roles, permisos y catálogo inicial
php artisan itsva:crear-admin correo@itsva.edu.mx # crear/reactivar el administrador (pide contraseña)
php artisan test                                  # 89 pruebas (base convocatorias_itsva_test)
php tests/Verificacion/todas_las_paginas.php      # recorre las rutas GET de los 3 paneles (revierte datos)
php artisan route:list --path=admin               # rutas de un panel
php artisan view:clear && php artisan optimize:clear
```

### Extractor (`cd extractor`)
```bash
./.venv/bin/python -m unittest discover -s tests -t .                    # 114 pruebas
./.venv/bin/python -m app.importar_desde_url <url>                      # analizar un enlace (no guarda)
./.venv/bin/python -m scripts.clasificar_tipos [--aplicar] [--todas]     # tipo y audiencia de las existentes
./.venv/bin/python -m app.ejecutar_ciclo_automatico                      # un ciclo completo a mano
./.venv/bin/python -m app.ejecutar_scraping --fuente-id 2 --limite 10    # vista previa de una fuente (no guarda)
./.venv/bin/python -m app.ejecutar_fuente --fuente-id 2 --limite 10 --guardar
./.venv/bin/python -m app.ejecutar_descubrimiento --cantidad-google 2 --max-dominios 2   # buscador sin guardar
./.venv/bin/python -m json.tool ciclo_automatico_ultimo.json             # último reporte del ciclo
```

### Servicios
```bash
systemctl status convocatorias-itsva.timer convocatorias-itsva-api.service --no-pager
systemctl list-timers --all | grep convocatorias
journalctl -u convocatorias-itsva.service -n 100 --no-pager    # salida de los ciclos
sudo systemctl restart convocatorias-itsva-api.service           # tras cambiar código de la API
curl -s http://127.0.0.1:8001/health ; curl -s http://127.0.0.1:8001/db-check
```

### Producción, respaldos y logs

- **Producción separada de desarrollo:** los servicios corren desde `~/produccion/convocatorias-itsva`, un clon que solo cambia con `scripts/desplegar.sh` y siempre desde la rama `main`. Cambiar de rama o editar en la carpeta de desarrollo no afecta al motor. El script actualiza el código, instala dependencias solo si cambiaron, comprueba que el extractor importe (si no, no reinicia nada), muestra migraciones pendientes (las aplica solo con `--migrar`), cachea configuración/rutas/vistas y reinicia la API y la web. `--instalar-units` copia las units apuntando a esa carpeta.
- **Web como servicio:** `convocatorias-itsva-web.service` (`php artisan serve` en 127.0.0.1:8000, 4 workers, `MemoryMax=512M`); no depende de una terminal abierta.
- **Respaldos:** `convocatorias-itsva-respaldo.timer` (03:30) ejecuta `extractor/scripts/respaldar_bd.sh`: `pg_dump` comprimido en `~/respaldos/convocatorias-itsva/diario/` (se conservan 7) y los domingos en `semanal/` (se conservan 4). Restaurar: `pg_restore --clean --no-owner -d <base> <archivo>.dump`.
- **Logs:** Laravel usa `LOG_STACK=daily` con 14 días; el journal de systemd conviene limitarlo con `SystemMaxUse=200M` en `/etc/systemd/journald.conf`.

---

## 11. Pruebas

| Conjunto | Cantidad | Cómo |
|---|---|---|
| Web (PHPUnit) | 95 | `cd web && php artisan test`. Usa la base `convocatorias_itsva_test`; `tests/TestCase.php` aborta si el nombre no termina en `_test` (la vacía en cada prueba). |
| Extractor (unittest) | 115 | `cd extractor && ./.venv/bin/python -m unittest discover -s tests -t .` |
| Verificaciones contra la base real | 5 scripts | `cd web && php tests/Verificacion/<script>.php` (revierten sus datos): `todas_las_paginas`, `permisos_por_rol`, `docente_no_ve_vencidas`, `propuesta_id_bloqueado`, `convocatoria_dependencias`. |
| Recorrido en navegador | 3 paneles | `tests/Recorrido/preparar.sh` copia la base real a `convocatorias_itsva_test` y levanta una copia de la web en 127.0.0.1:8010 (la base real no se toca). Después `node tests/Recorrido/recorrer.cjs docente\|directivo\|admin` (clic en cada botón: errores 5xx/JS y botones sin efecto) y `node tests/Recorrido/llenar.cjs docente` (lo escrito se guarda tras recargar). Resultados en `tests/Recorrido/salida/`. No correr `php artisan test` mientras tanto (vacía esa base). Requiere Node y el Chromium de Playwright (`PLAYWRIGHT_CORE`, `CHROMIUM`). |

---

## 12. Seguridad

- Secretos solo en `web/.env` y `extractor/.env` (fuera de git).
- La API del extractor no tiene autenticación: **debe escuchar solo en 127.0.0.1**.
- Defensas SSRF (`app/seguridad_red.py`): bloqueo de IP privadas y redirecciones inseguras. El extractor respeta `robots.txt` y se identifica con el User-Agent `ITSVA-Convocatorias/1.0`.
- No se evaden protecciones de sitios (403/Akamai): la fuente se marca inaccesible.
- Permisos validados en el servidor (`canAccess`, servicios), no solo ocultando botones.
- El avatar se genera en el servidor con iniciales (no se envían nombres a servicios externos).
- `APP_DEBUG=true` solo en desarrollo.

---

## 13. Reglas de trabajo

1. Diagnosticar antes de modificar (código, logs, reporte del ciclo).
2. No bajar los umbrales 80/70 para forzar publicaciones.
3. No editar a mano estados ni contadores en la base.
4. No evadir protecciones de sitios.
5. Mantener las defensas SSRF.
6. Bajo consumo: HTTP antes que navegador; sin OCR por defecto; sin modelos de IA pesados.
7. Confirmar antes de: `migrate`, `migrate:fresh`, regenerar recursos de Filament (`make:filament-resource` sobrescribe autorizaciones propias), `git push/reset/checkout`, cambios en units de systemd y borrados.
8. Commits pequeños y descriptivos en español.

---

## 14. Problemas conocidos y pendientes

- **Pocas convocatorias vigentes:** depende de las fuentes oficiales; hoy SECIHTI y la Secretaría de Ciencia de Yucatán. Agregar más fuentes (Admin → Fuentes web) amplía la cobertura.
- **Fechas en imágenes o PDF escaneados:** varias convocatorias de Yucatán no traen fecha de cierre en texto; quedan pendientes hasta que se suban a mano (o desde un enlace completando la fecha).
- **Búsqueda en noticias:** el clasificador del descubrimiento sigue descartando eventos y premios en notas de prensa (solo traían ruido de otros estados); becas y eventos entran por las fuentes oficiales.
- **Correo:** pendiente configurar SMTP (`MAIL_MAILER=log`).
- **WSL:** el ciclo solo corre mientras WSL está encendido (`Persistent=true` recupera el ciclo perdido al encender).
- **Zona horaria:** la aplicación guarda en UTC; las vistas que lo requieren muestran hora de Yucatán (`America/Merida`).
- El estado `ACTIVA` se conserva como alias de `PUBLICADA`.
- Elegibilidad por título, URL y dominio: una convocatoria estatal publicada en un medio nacional sin mencionar el estado podría no detectarse.
