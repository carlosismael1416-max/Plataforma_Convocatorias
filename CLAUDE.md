# Plataforma de Gestión de Convocatorias ITSVA

Plataforma del Instituto Tecnológico Superior de Valladolid que descubre, extrae, clasifica, valida y publica convocatorias de financiamiento para investigación.

**Prioridad del proyecto:** un ciclo completo, verificable y de bajo consumo:
Descubrir → extraer → clasificar → evaluar → validar → guardar → publicar → consultar en la web.
No agregar funciones nuevas hasta que ese ciclo esté demostrado con registros reales.

## Estructura

- `web/` — Laravel 13 + Filament 5 + Livewire 4, PHP 8.3. Paneles: docente, directivo, admin. (`web/CLAUDE.md` contiene las guías de Laravel Boost.)
- `extractor/` — Python: Scrapy, FastAPI/Uvicorn (puerto 8001), selectolax, googlenewsdecoder.
- `database/schema.sql` — esquema PostgreSQL.
- Automatización: systemd `convocatorias-itsva.service` + `convocatorias-itsva.timer` (00:00, 06:00, 12:00, 18:00; `Persistent=true`). En WSL solo corre mientras WSL está encendido. Copias versionadas de las units en `extractor/systemd/` (la instalada en `/etc/systemd/system/` es la que rige).
- Pruebas del extractor: `cd extractor && ./.venv/bin/python -m unittest discover -s tests -t .` (38; `test_publicacion_bd.py` usa PostgreSQL en transacción revertida y se omite sin conexión).
- Verificaciones de la web (PHPUnit no corre: `phpunit.xml` usa SQLite y varias migraciones tienen SQL de PostgreSQL): `cd web && php tests/Verificacion/<script>.php` — `todas_las_paginas.php` (77 rutas GET), `permisos_por_rol.php`, `docente_no_ve_vencidas.php`, `propuesta_id_bloqueado.php`, `convocatoria_dependencias.php`. Usan usuarios id 1 (admin), 2 (docente), 3 (directivo) y revierten sus datos.
- No regenerar con `make:filament-resource`: los recursos de Convocatorias (admin, directivo, docente), Fuentes, Roles y Users tienen autorización y flujos propios (`can*`, `getEloquentQuery`, `handleRecordCreation/Update`).
- Entorno virtual Python: `extractor/.venv`.

Versiones registradas; confirmarlas antes de asumirlas (`php artisan --version`, `pip list`).

## Módulos clave del extractor

| Archivo | Función |
|---|---|
| `app/ejecutar_descubrimiento.py` | Punto de entrada del descubrimiento (Google News RSS, sitemaps, WordPress, HTML ligero, enlaces directos). |
| `app/descubrimiento/clasificar_objetivo.py` | Clasificación por reglas → categoría + decisión `ACEPTAR` / `REVISION` / `DESCARTAR`. |
| `app/descubrimiento/elegibilidad.py` | ¿Puede participar ITSVA (Yucatán, TecNM)? No elegibles: otros estados (nombre, gentilicio, siglas del consejo estatal o dominio `*.gob.mx`), instituciones extranjeras sin apertura a México, vacantes/plazas/admisiones. Elegibles: Yucatán, sur-sureste, nacionales, TecNM. Ignora el sufijo " - Medio" de Google News. Ante la duda, elegible. |
| `app/evaluar_descubrimientos.py` | Puntaje; aprobación automática con ≥ 80 **y** condiciones obligatorias (incluye `elegible`); no elegible → `DESCARTADO`. Reevalúa `NUEVO`, `REVISION` y `APROBADO`. |
| `app/publicar_descubrimientos.py` | Extracción persistente, deduplicación, validación, publicación. Reintentos: espera 5 h (el timer corre cada 6 h), máx. 3 intentos (`MAX_INTENTOS`); al agotarse pasa a `FUENTE_INACCESIBLE`. Antes de reintentar, los `ERROR_TEMPORAL` no elegibles pasan a `DESCARTADO`. |
| `app/validar_convocatoria.py` | Validación final del contenido extraído (umbral 70, bloqueos: resultados, premios, eventos, becas no aplicables, no elegible para ITSVA). |
| `app/ejecutar_scraping.py`, `app/ejecutar_fuente.py` | Extractor. Flags: `--fuente-id --url --limite --guardar --modo --vigencia --fecha --analizar-pdf --buscar-pdf`. `--analizar-pdf` es incompatible con `--guardar`. |
| `app/ejecutar_ciclo_automatico.py` | Ciclo completo. Reporte en `extractor/ciclo_automatico_ultimo.json`. Límites: `--cantidad-google 6 --max-dominios 5 --max-publicaciones 5`. |
| `app/main.py` | API: `GET /health`, `GET /db-check`, `POST /scraping/ejecutar`. Laravel la usa vía `EXTRACTOR_API_URL=http://127.0.0.1:8001` (página admin `MotorExtraccion`). Sin autenticación: escuchar solo en 127.0.0.1. Unit: `extractor/systemd/convocatorias-itsva-api.service`. Al reservar, las ejecuciones con más de 60 min en `INICIADO`/`EJECUTANDO` se marcan `FALLIDO` y se registran en `bitacora_errores`. |

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

- Ciclo del 14-oct 00:00: 0 aprobados y 0 publicados, decisión correcta. FECTI, COQHCYT y CALAS quedaron `DESCARTADO` por elegibilidad; solo siguen en revisión dos becas federales (ID 4 y 6).
- Ninguna convocatoria real supera hoy el validador (70). La única vigente (ID 4, revistas de acceso abierto, cierra el 23-oct) obtiene 60 por falta de organismo, montos y objetivo. No se demostró aún una publicación automática real de principio a fin.
- `estado_temporal` del descubrimiento sale del año en el título, no de la fecha de cierre; la vigencia real la decide `validar_convocatoria.py` (bloquea fecha pasada o ausente) y el docente solo ve `noVencidas()`.
- Nada pasa a `CERRADA` una convocatoria `PUBLICADA` cuando vence (solo las hijas de un marco); la web la oculta, pero la BD no refleja el cierre. Pendiente de decisión.
- `url_hash` se calcula distinto en `guardar_resultado_scrapy.py` (sin normalizar `www.`/mayúsculas) y en `resolver_convocatoria_marco.py`; la misma URL con y sin `www.` evade el índice único. `buscar_convocatoria_exacta` compensa leyendo toda la tabla. Unificar requiere recalcular hashes existentes.
- La API (8001) y el ciclo usan bloqueos distintos; si coinciden, la asociación por `max(id)` del publicador podría tomar una fila ajena (mitigado por fuente y similitud de título).
- El peor caso del ciclo (~7.500 s) supera `TimeoutStartSec=3600`; los `EN_PROCESO` abandonados se recuperan a las 2 h, pero el ciclo queda cortado.
- La API del puerto 8001 no está corriendo: falta instalar su unit (requiere sudo).
- Elegibilidad y clasificación usan solo título, URL y dominio; no el contenido del artículo (que ya se descarga). Siglas de fondos sin estado en el título no se reconocen.
- El descubrimiento ID 4 conserva el título "edición 54" (la mejora de títulos aplica a descubrimientos nuevos).
- `ConvocatoriaResource::tieneDependencias` no considera hijas (`convocatoria_padre_id`): un padre con hijas se puede borrar.
- Código sin uso: 32 módulos del extractor (6.863 líneas, `probar_*`, `diagnosticar_*`, flujo ECOS), 28 `.bak` en `web/app/Filament`, Vite/npm sin uso; Playwright instalado sin uso (658 MB de navegadores).
- OCR (tesseract) activo solo en la ruta de convocatorias marco (`leer_imagen_calendario`).
- Migraciones duplicadas de `convocatoria_padre_id` (`2026_10_09_204500` y `2026_10_09_205003`); ambas aplicadas, la segunda es idempotente.
- `APP_DEBUG=true` en `web/.env` (aceptable solo en desarrollo). Se consulta el estado `ACTIVA`, que no existe en el flujo.

## Criterio de terminado

Demostrar con registros que el sistema: encuentra una convocatoria real, extrae sus datos, clasifica, determina vigencia, evita duplicados, valida, publica solo lo correcto, la muestra en el panel docente, registra errores y lo repite automáticamente. Más una prueba negativa: un evento o una convocatoria vencida no aparecen como abiertos.
