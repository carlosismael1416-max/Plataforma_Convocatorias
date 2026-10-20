# Plataforma de Convocatorias ITSVA — Web

Aplicación Laravel + Filament (paneles docente, directivo y administrador)
que muestra las convocatorias publicadas por el motor (`../extractor`) y
gestiona las propuestas.

## Puesta en marcha

```bash
composer install
cp .env.example .env && php artisan key:generate   # datos de PostgreSQL en .env
php artisan migrate --seed
php artisan itsva:crear-admin correo@itsva.edu.mx
php artisan serve --host=127.0.0.1 --port=8000
```

El motor de extracción debe estar corriendo (API en `EXTRACTOR_API_URL`).
