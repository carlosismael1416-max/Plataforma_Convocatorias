# Plataforma de Gestión de Convocatorias ITSVA

Plataforma del Instituto Tecnológico Superior de Valladolid que descubre, extrae, clasifica, valida y publica convocatorias de financiamiento para investigación.

**Prioridad del proyecto:** un ciclo completo, verificable y de bajo consumo:
Descubrir → extraer → clasificar → evaluar → validar → guardar → publicar → consultar en la web.
No agregar funciones nuevas hasta que ese ciclo esté demostrado con registros reales.

## Estructura

- Documentación completa: `docs/DOCUMENTACION.md`; instalación en otra máquina: `docs/INSTALACION.md`. Datos iniciales: `php artisan db:seed` (roles, permisos, categoría, organismo SECIHTI, fuentes) y `php artisan itsva:crear-admin <correo>`.

- `web/` — Laravel 13 + Filament 5 + Livewire 4, PHP 8.3. Paneles: docente, directivo, admin. (`web/CLAUDE.md` contiene las guías de Laravel Boost.)
- `extractor/` — Python: Scrapy, FastAPI/Uvicorn (puerto 8001), selectolax, googlenewsdecoder.
- Esquema de la base: lo crean las migraciones de Laravel (`web/database/migrations`).
- Automatización: systemd `convocatorias-itsva.service` + `convocatorias-itsva.timer` (00:00, 06:00, 12:00, 18:00; `Persistent=true`). En WSL solo corre mientras WSL está encendido. Copias versionadas de las units en `extractor/systemd/` (la instalada en `/etc/systemd/system/` es la que rige). También: `convocatorias-itsva-web.service` (web en 127.0.0.1:8000) y `convocatorias-itsva-respaldo.{service,timer}` (pg_dump 03:30 a `~/respaldos/convocatorias-itsva`, 7 diarios + 4 semanales). Si el ciclo falla, `ExecStopPost` → `extractor/scripts/registrar_fallo_ciclo.py` (reporte `FALLIDO` + `bitacora_errores`; alerta en el dashboard admin).
- **Producción:** `scripts/desplegar.sh` mantiene `~/produccion/convocatorias-itsva` (clon desde `main`; `--migrar`, `--instalar-units`). Las units instaladas deben apuntar ahí, no a la carpeta de desarrollo.
- Pruebas del extractor: `cd extractor && ./.venv/bin/python -m unittest discover -s tests -t .` (114; `test_publicacion_bd.py` usa PostgreSQL en transacción revertida y se omite sin conexión).
- Pruebas de la web: `cd web && php artisan test` (90, PHPUnit sobre PostgreSQL). La base se fuerza a `convocatorias_itsva_test` y `tests/TestCase.php` aborta si no termina en `_test` (RefreshDatabase la vacía). Requiere crear esa base una vez (ver Problemas conocidos).
- Verificaciones contra la base real (revierten sus datos): `cd web && php tests/Verificacion/<script>.php` — `todas_las_paginas.php` (75 rutas GET), `permisos_por_rol.php`, `docente_no_ve_vencidas.php`, `propuesta_id_bloqueado.php`, `convocatoria_dependencias.php`. Usan usuarios id 1 (admin), 2 (docente), 3 (directivo).
- Recorrido en navegador (base de pruebas, nunca la real): `web/tests/Recorrido/preparar.sh` y luego `node tests/Recorrido/recorrer.cjs <panel>` / `llenar.cjs <panel>`. Atributos `wire:*` siempre en una sola línea (Livewire no recorta espacios; `tests/Unit/AtributosLivewireTest.php`).
- Panel directivo: reutiliza las páginas de trabajo del docente (subclases en `app/Filament/Directivo/Pages`; el rol exigido sale del panel con `RolDelPanel`; rutas con `'filament.' . filament()->getId() . '.pages.…'`). Propuestas: el autor las envía desde la vista previa (`ENVIADA`) y un directivo dictamina en `RevisarPropuesta` (`APROBADA`, devuelta a `EDITANDO` o `RECHAZADA`), también las suyas; lógica en `app/Services/RevisionPropuesta.php`. Cada envío y dictamen queda en `propuesta_revisiones` (migración `2026_10_14_220000`), visible en la vista previa; el autor también recibe el dictamen como notificación.
- **Modelo de publicación:** las convocatorias se publican solas. Las del motor cuando el validador las aprueba (umbrales 80/70 sin cambios; lo que no pasa queda `PENDIENTE_REVISION` interno y se revalida); las subidas a mano por docentes o directivos se publican al momento (no si ya vencieron) y su autor puede corregirlas. **Criterio único de «vigente»:** `Convocatoria::visibles()` (publicada/activa y no vencida) en docente, directivo y admin; `historial()` = cerradas o publicadas vencidas. **Nadie revisa convocatorias a mano:** el directivo revisa las **propuestas** para participar. **Tipos (desde 18-oct):** también se publican becas, premios, concursos, eventos y formación, marcados con `convocatorias.tipo` (FINANCIAMIENTO, BECA, PREMIO, CONCURSO, EVENTO, FORMACION; `NO_APLICA` interno = resultados, vacantes, no elegibles: nunca visible ni en historial) y `dirigido_a` (jsonb: INSTITUCION, DOCENTES, ESTUDIANTES); migración `2026_10_18_220000`, reglas en `extractor/app/tipo_convocatoria.py`, existentes con `extractor/scripts/clasificar_tipos.py`. **Historial** de cerradas en los tres paneles (`HistorialConvocatorias`); el detalle de una cerrada es solo de consulta. **Mis convocatorias:** guardar/quitar/favorita desde buscador y detalle (trait `GestionaMisConvocatorias`), pestaña Favoritas. **Subir desde un enlace:** trait `ImportaDesdeEnlace` → `POST /convocatorias/analizar-url` (extrae y valida sin guardar); si no cumple, el usuario elige «Publicar de todas maneras» o «No publicar»; enlaces repetidos se detectan por `url_hash` en el servidor (una pendiente interna se publica en su lugar).
- **Interruptor del motor:** `ajustes_sistema.motor_activo` ('1'/'0'; migración `2026_10_15_200000`). Lo cambia un administrador con permiso `ejecutar_scraping` desde el dashboard admin o Motor de extracción (`app/Services/InterruptorMotor.php`, trait `ControlaMotor`). Apagado: el ciclo automático no busca ni extrae (reporte con estado `APAGADO`) y `POST /scraping/ejecutar` responde 409. Si no se puede leer, el extractor lo considera encendido (`app/interruptor_motor.py`).
- Spider (`app/spiders/convocatorias.py`): para SECIHTI procesa primero la portada `/convocatorias` (las más recientes) y omite rutas nunca elegibles (becas, plazas de centros públicos, Investigadoras e Investigadores por México); después las subcategorías. Para cualquier otra fuente usa un listado genérico (enlaces del mismo sitio bajo la ruta del listado o con «convocatoria»). Se identifica con el User-Agent del proyecto (algunos sitios dan 406 al de Scrapy). Fuentes oficiales: SECIHTI (id 2) y Secretaría de Ciencia de Yucatán (id 4, `ciencia.yucatan.gob.mx/convocatorias/`; publica sobre todo premios, ferias y becas, que el validador bloquea).
- `convocatorias.url_hash` = sha256 de la URL normalizada (`extractor/app/urls.py` y `Convocatoria::hashUrl()` deben coincidir; si cambias la regla, corre `extractor/scripts/recalcular_url_hash.py`).
- No regenerar con `make:filament-resource`: los recursos de Convocatorias (admin, directivo, docente), Fuentes, Roles y Users tienen autorización y flujos propios (`can*`, `getEloquentQuery`, `handleRecordCreation/Update`).
- Entorno virtual Python: `extractor/.venv`.

Versiones registradas; confirmarlas antes de asumirlas (`php artisan --version`, `pip list`).

## Módulos clave del extractor

| Archivo | Función |
|---|---|
| `app/ejecutar_descubrimiento.py` | Punto de entrada del descubrimiento (Google News RSS, sitemaps, WordPress, HTML ligero, enlaces directos). Solo explora portales institucionales `.mx`; ya no rastrea sitios de noticias (solo aportaban notas descartadas). |
| `app/descubrimiento/clasificar_objetivo.py` | Clasificación por reglas (título + URL; palabras completas) → categoría + decisión `ACEPTAR` / `REVISION` / `DESCARTAR`. El texto de la nota (contexto) solo sube de `REVISION` a `ACEPTAR` si habla de financiar investigación/proyectos y no de becas o eventos. |
| `app/descubrimiento/elegibilidad.py` | ¿Puede participar ITSVA (Yucatán, TecNM)? No elegibles: otros estados (nombre, gentilicio, siglas del consejo estatal o dominio `*.gob.mx`), instituciones extranjeras sin apertura a México, vacantes/plazas/admisiones. Elegibles: Yucatán, sur-sureste, nacionales, TecNM. Ignora el sufijo " - Medio" de Google News. Ante la duda, elegible. |
| `app/evaluar_descubrimientos.py` | Puntaje; aprobación automática con ≥ 80 **y** condiciones obligatorias (incluye `elegible`); no elegible → `DESCARTADO`. Reevalúa `NUEVO`, `REVISION` y `APROBADO`. |
| `app/publicar_descubrimientos.py` | Extracción persistente, deduplicación (por `url_hash`), validación, publicación. Al iniciar: cierra `PUBLICADA` vencidas (y padres con todas sus hijas cerradas), recupera `EN_PROCESO` de más de 2 h, descarta `ERROR_TEMPORAL` no elegibles. No empieza un descubrimiento que no alcance a terminar antes del plazo del ciclo (3.300 s). Reintentos: espera 5 h (el timer corre cada 6 h), máx. 3 intentos (`MAX_INTENTOS`); al agotarse pasa a `FUENTE_INACCESIBLE`. Antes de reintentar, los `ERROR_TEMPORAL` no elegibles pasan a `DESCARTADO`. |
| `app/validar_convocatoria.py` | Validación final del contenido extraído (umbral 70). Desde el 18-oct **marca** el tipo y la audiencia (`tipo_convocatoria.py`) en vez de bloquear becas/premios/eventos; guarda `tipo`/`dirigido_a` al validar (no en las MANUAL). Bloqueos duros: resultados y vacantes/plazas (`NO_APLICA`), no elegible para ITSVA, programas que excluyen a mexicanos (`bloqueo_por_objetivo`; el seguimiento retira las publicadas así), fecha vencida o ausente. |
| `app/importar_desde_url.py` | «Subir desde un enlace»: descarga segura, enriquecimiento, clasificación y validación **sin guardar**. Manual: `python -m app.importar_desde_url <url>`. |
| `app/ejecutar_scraping.py`, `app/ejecutar_fuente.py` | Extractor. Flags: `--fuente-id --url --limite --guardar --modo --vigencia --fecha --analizar-pdf --buscar-pdf`. `--analizar-pdf` es incompatible con `--guardar`. |
| `app/ejecutar_ciclo_automatico.py` | Ciclo completo en 6 fases: descubrir, evaluar, extraer/publicar descubrimientos aprobados, **fuentes oficiales** (`fuentes` activas sin JavaScript cuya `frecuencia_scraping` ya pasó, sin las creadas automáticamente al publicar descubrimientos; `ejecutar_fuente --limite 10 --guardar`, lo nuevo entra como `PENDIENTE_REVISION`), **seguimiento de publicadas** (`app/seguimiento_convocatorias.py`: cada convocatoria vigente con enlace se reconsulta cada ~20 h, máx. 5 por ciclo; documentos nuevos se agregan, una ampliación de fecha se aplica, un adelanto solo se avisa, cambios de texto > 2 % cuentan; avisa a quien la sigue o tiene propuesta según la preferencia `convocatorias_revision`; estado en `convocatoria_seguimientos`), revalidar convocatorias pendientes de scraping (cierra vencidas; enriquece y valida vigentes o sin fecha, máx. 5 por ciclo, cada una cada 20 h; nunca las MANUAL), y al final mantenimiento (`app/mantenimiento.py`: borra descartados sin convocatoria y notificaciones leídas de más de 180 días, temporales de más de 24 h). Plazo 3.300 s. Reporte en `extractor/ciclo_automatico_ultimo.json`. Límites: `--cantidad-google 6 --max-dominios 5 --max-publicaciones 5`; para carga inicial también `--limite-fuentes` (def. 10) y `--max-revalidaciones` (def. 5). |
| `app/main.py` | API: `GET /health`, `GET /db-check`, `POST /scraping/ejecutar`, `POST /convocatorias/analizar-url` (409 si el motor está apagado, 429 si hay otro análisis, 422 si la URL no es pública). Laravel la usa vía `EXTRACTOR_API_URL=http://127.0.0.1:8001` (página admin `MotorExtraccion`). Sin autenticación: escuchar solo en 127.0.0.1. Unit: `convocatorias-itsva-api.service` (instalada; copia en `extractor/systemd/`). Al reservar, las ejecuciones con más de 60 min en `INICIADO`/`EJECUTANDO` se marcan `FALLIDO` y se registran en `bitacora_errores`. |

## Base de datos (PostgreSQL)

Tablas: `users`, `convocatorias`, `fuentes`, `categorias`, `usuario_convocatorias`, `descubrimientos_web`, `ejecuciones_scraping`, `ejecucion_fuentes`, `bitacora_errores`.

- Un **descubrimiento** (`descubrimientos_web`) es un candidato; una **convocatoria** ya pasó extracción y persistencia. No mezclarlos.
- Estados de convocatoria: `PENDIENTE_REVISION`, `PUBLICADA`, `CERRADA`.
- Estados de descubrimiento: `NUEVO`, `REVISION`, `APROBADO`, `EN_PROCESO`, `ERROR_TEMPORAL`, `ERROR`, `FUENTE_INACCESIBLE`, `REVISION_FINAL`, `PROCESADO`, `DESCARTADO`.
- `FUENTE_INACCESIBLE`: se agotaron los reintentos; buscar un canal oficial alterno. No devolverlo a `REVISION` (el evaluador lo volvería a aprobar y se repetiría el ciclo).
- Relación padre-hijas vía `convocatoria_padre_id` (ej. padre ID 20, hijas 21–23 cerradas). No publicar un padre como vigente si sus hijas están cerradas.
- Verificar columnas reales en el esquema antes de escribir consultas o migraciones.

## Reglas de trabajo (obligatorias)

1. **Diagnosticar antes de modificar.** Leer código, logs y reporte antes de cambiar nada.
2. **No bajar umbrales** (80 evaluador / 70 validador) para forzar publicaciones. Cero publicaciones puede ser un resultado correcto.
3. **No editar a mano estados ni contadores de intentos** en la BD para forzar un reintento o una publicación.
4. **No evadir protecciones de sitios** (ej. Akamai/403 en impulsorainnovacion.gob.mx). Registrar la fuente como inaccesible y buscar canales oficiales.
5. **Mantener las defensas SSRF** existentes del extractor (bloqueo de IPs privadas y redirecciones inseguras).
6. **Bajo consumo:** HTTP + selectolax antes que navegador; Playwright/Selenium solo si una fuente lo exige; sin OCR por defecto; sin modelos de IA pesados; ciclos secuenciales y limitados.
7. **No ejecutar sin confirmar:** `php artisan migrate`, `migrate:fresh`, regeneración de recursos Filament (sobrescribe protecciones y acciones personalizadas), `git checkout`/`reset`/`push`, cambios en units de systemd, borrados.
8. **Git:** confirmar rama actual antes de trabajar (se ha usado `dashboard-Vanessa-developer` y `main`). Commits pequeños y descriptivos en español.
9. **Permisos Laravel:** autorizar en el servidor (policies/`canAccess`), no solo ocultar botones.
10. Figma muestra el diseño, no prueba que una pantalla esté implementada. No afirmar que algo está conectado a la BD sin verificarlo.
11. Secretos solo en `.env`; nunca en el repositorio.

## Comandos de diagnóstico (solo lectura)

```bash
git branch --show-current && git status --short && git log -5 --oneline
systemctl status convocatorias-itsva.service convocatorias-itsva.timer --no-pager
systemctl list-timers --all | grep convocatorias-itsva
journalctl -u convocatorias-itsva.service -n 100 --no-pager
curl -s http://127.0.0.1:8001/health; curl -s http://127.0.0.1:8001/db-check
cd extractor && ./.venv/bin/python -m json.tool ciclo_automatico_ultimo.json
cd web && php artisan migrate:status && php artisan route:list --path=admin
```

## Problemas conocidos (al 14-oct-2026)

- Prueba integral positiva (14-oct): la convocatoria 4 (SECIHTI, revistas de acceso abierto, cierra 23-oct) se extrajo, se enriqueció (descripción, objetivo, organismo SECIHTI), el validador le dio 90/70 y quedó `PUBLICADA`; el docente la ve en buscador, dashboard, detalle y calendario. Falta verla pasar a `CERRADA` sola tras el 23-oct.
- Incidente (14-oct): la primera corrida de la fase 4 publicó la convocatoria 5 ("Apoyo Complementario SECIHTI-OEA", apoyo a estudiantes extranjeros bajo `/becas-nacionales/`). Se retiró a `PENDIENTE_REVISION` a mano y el validador ahora bloquea becas por la ruta. Lección: el validador no lee el objetivo; una beca sin la palabra en título ni URL aún podría pasar.
- Organismo SECIHTI (id 2) agregado al catálogo el 14-oct; el enriquecimiento asocia organismos por dominio de `organismos.sitio_web`. El catálogo de categorías solo tiene "investigación" (se asigna cuando el clasificador dice investigación).
- Instalado el 14-oct: unit de la API (`convocatorias-itsva-api.service`, activa y habilitada, `MemoryMax=512M`), `MemoryMax=1G` en el ciclo y la base `convocatorias_itsva_test` (dueño `convocatorias_app`; `php artisan test` 17/17). Si se cambian las units en `extractor/systemd/`, copiarlas a `/etc/systemd/system/` y `daemon-reload`.
- `estado_temporal` del descubrimiento sale del año en el título; la vigencia real la decide el validador y la web (`noVencidas()`), y el ciclo cierra las publicadas vencidas.
- La API (8001) y el ciclo usan bloqueos distintos; si coinciden, la asociación tras extraer usa primero `url_hash` y solo después la heurística de `max(id)`.
- Elegibilidad por título, URL y dominio: una convocatoria estatal publicada en un medio nacional sin mencionar el estado no se detecta.
- El descubrimiento ID 4 conserva el título "edición 54" (la mejora de títulos aplica a descubrimientos nuevos).
- Se consulta el estado `ACTIVA`, que nada produce (se dejó como alias inofensivo de `PUBLICADA`; quitarlo toca 26 archivos). La publicación manual permite convocatorias sin fecha de cierre (decisión del directivo). `APP_DEBUG=true` (solo desarrollo).
- OCR (tesseract) activo solo en la ruta de convocatorias marco (`leer_imagen_calendario`).
- Migraciones duplicadas de `convocatoria_padre_id` (`2026_10_09_204500` y `2026_10_09_205003`); ambas aplicadas, la segunda es idempotente. `convocatoria_padre_id` no tiene llave foránea.
- Respaldos fuera de git: `extractor/backups/legado_2026-10-14/` (scripts `probar_*`/`diagnosticar_*` y 75 `.bak`) y `extractor/backups/url_hash_antes_2026-10-14.csv`.

## Criterio de terminado

Demostrar con registros que el sistema: encuentra una convocatoria real, extrae sus datos, clasifica, determina vigencia, evita duplicados, valida, publica solo lo correcto, la muestra en el panel docente, registra errores y lo repite automáticamente. Más una prueba negativa: un evento o una convocatoria vencida no aparecen como abiertos.
