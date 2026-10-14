# Plataforma de Gestión de Convocatorias ITSVA

Plataforma del Instituto Tecnológico Superior de Valladolid que descubre, extrae, clasifica, valida y publica convocatorias de financiamiento para investigación.

**Prioridad del proyecto:** un ciclo completo, verificable y de bajo consumo:
Descubrir → extraer → clasificar → evaluar → validar → guardar → publicar → consultar en la web.
No agregar funciones nuevas hasta que ese ciclo esté demostrado con registros reales.

## Estructura

- `web/` — Laravel 13 + Filament 5 + Livewire 4, PHP 8.3. Paneles: docente, directivo, admin. (`web/CLAUDE.md` contiene las guías de Laravel Boost.)
- `extractor/` — Python: Scrapy, FastAPI/Uvicorn (puerto 8001), selectolax, googlenewsdecoder.
- `database/schema.sql` — esquema PostgreSQL.
- Automatización: systemd `convocatorias-itsva.service` + `convocatorias-itsva.timer` (00:00, 06:00, 12:00, 18:00; `Persistent=true`). En WSL solo corre mientras WSL está encendido.
- Entorno virtual Python: `extractor/.venv`.

Versiones registradas; confirmarlas antes de asumirlas (`php artisan --version`, `pip list`).

## Módulos clave del extractor

| Archivo | Función |
|---|---|
| `app/descubrimiento/ejecutar_descubrimiento.py` | Punto de entrada del descubrimiento (Google News RSS, sitemaps, WordPress, HTML ligero, enlaces directos). |
| `app/descubrimiento/clasificar_objetivo.py` | Clasificación por reglas → categoría + decisión `ACEPTAR` / `REVISION` / `DESCARTAR`. |
| `app/evaluar_descubrimientos.py` | Puntaje; aprobación automática con ≥ 80 **y** condiciones obligatorias. Reevalúa `NUEVO`, `REVISION` y `APROBADO`. |
| `app/publicar_descubrimientos.py` | Extracción persistente, deduplicación, validación, publicación. Reintentos: espera 5 h (el timer corre cada 6 h), máx. 3 intentos (`MAX_INTENTOS`); al agotarse pasa a `FUENTE_INACCESIBLE`. |
| `app/validar_convocatoria.py` | Validación final del contenido extraído (umbral 70, bloqueos: resultados, premios, eventos, becas no aplicables). |
| `app/ejecutar_scraping.py`, `app/ejecutar_fuente.py` | Extractor. Flags: `--fuente-id --url --limite --guardar --modo --vigencia --fecha --analizar-pdf --buscar-pdf`. `--analizar-pdf` es incompatible con `--guardar`. |
| `app/ejecutar_ciclo_automatico.py` | Ciclo completo. Reporte en `extractor/ciclo_automatico_ultimo.json`. Límites: `--cantidad-google 6 --max-dominios 5 --max-publicaciones 5`. |
| `app/main.py` | API: `GET /health`, `GET /db-check`, `POST /scraping/ejecutar`. Laravel la usa vía `EXTRACTOR_API_URL=http://127.0.0.1:8001`. No tiene unit de systemd; hay que arrancarla aparte. |

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

## Problemas conocidos (al 13-oct-2026)

- Último ciclo (13-oct 21:04): 0 aprobados, 0 publicados, 4 en revisión. Revisado: dos son becas (CALAS, BEXT JICA), así que es correcto no publicarlas; FECTI Chihuahua salió con `categoria_valida=False`, probable fallo de clasificación.
- Descubrimiento ID 2 (COQHCYT): 3/3 intentos agotados; `cgc.qroo.gob.mx` no responde (timeout, incluso `robots.txt`). El siguiente ciclo lo pasará a `FUENTE_INACCESIBLE`; buscar un canal oficial alterno.
- La API del puerto 8001 no está corriendo; Laravel la necesita para `POST /scraping/ejecutar`.
- Migraciones duplicadas de `convocatoria_padre_id` (`2026_10_09_204500` y `2026_10_09_205003`); ambas aplicadas, la segunda es idempotente.
- PDF de SECIHTI con título "edición 54": mejorar extracción de título (metadatos, encabezados, página que lo enlaza); si sigue ambiguo → revisión.
- Clasificación por palabras clave confunde becas, eventos, portales y financiamiento. Necesita normalización de acentos, uso del contenido y pruebas.
- Pruebas automáticas insuficientes.

## Criterio de terminado

Demostrar con registros que el sistema: encuentra una convocatoria real, extrae sus datos, clasifica, determina vigencia, evita duplicados, valida, publica solo lo correcto, la muestra en el panel docente, registra errores y lo repite automáticamente. Más una prueba negativa: un evento o una convocatoria vencida no aparecen como abiertos.
