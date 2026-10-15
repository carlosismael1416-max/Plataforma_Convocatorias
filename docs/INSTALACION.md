# Instalación en una máquina nueva

Guía para dejar funcionando la Plataforma de Convocatorias ITSVA en otra computadora, desde cero. Probada sobre **Ubuntu 24.04** (servidor Linux o **WSL2** en Windows).

Tiempo estimado: 30–45 minutos.

> Convención: los comandos con `sudo` necesitan permisos de administrador. Sustituye `TU_USUARIO` por tu usuario de Linux y `/home/TU_USUARIO/proyectos/convocatorias-itsva` por la ruta donde clones el proyecto.

---

## 0. Requisitos

| Recurso | Mínimo recomendado |
|---|---|
| Sistema | Ubuntu 24.04 LTS (o WSL2 con Ubuntu 24.04) |
| Memoria | 4 GB (el ciclo está limitado a 1 GB y la API a 512 MB) |
| Disco | 5 GB libres |
| Red | Acceso a internet (el motor consulta Google News y sitios oficiales) |

Software que se instalará:

| Componente | Versión |
|---|---|
| PostgreSQL | 16 |
| PHP | 8.3 con extensiones `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `intl`, `gd`, `bcmath` |
| Composer | 2.7 o superior |
| Python | 3.12 con `venv` |
| Tesseract OCR | 5, con idioma español (`spa`) |
| Git | cualquiera reciente |

No hace falta Node.js: los estilos de Filament se publican con Composer.

---

## 1. (Solo WSL2) Activar systemd

El ciclo automático usa systemd. En WSL hay que activarlo una vez.

```bash
sudo tee /etc/wsl.conf > /dev/null <<'EOF'
[boot]
systemd=true

[user]
default=TU_USUARIO
EOF
```

Luego, en **PowerShell de Windows**: `wsl --shutdown`, y vuelve a abrir Ubuntu. Comprueba con `systemctl --version`.

> En WSL el ciclo solo corre mientras WSL está encendido. Si se apaga, `Persistent=true` ejecuta el ciclo pendiente al volver a encenderlo.

---

## 2. Paquetes del sistema

```bash
sudo apt update
sudo apt install -y git curl unzip \
  postgresql postgresql-contrib \
  php8.3-cli php8.3-pgsql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-intl php8.3-gd php8.3-bcmath composer \
  python3 python3-venv python3-dev \
  tesseract-ocr tesseract-ocr-spa poppler-utils
```

Verifica:

```bash
php -v            # 8.3.x
composer -V       # 2.7 o superior
psql --version    # 16.x
python3 --version # 3.12.x
tesseract --list-langs | grep spa
```

---

## 3. Obtener el código

```bash
mkdir -p ~/proyectos && cd ~/proyectos
git clone https://github.com/carlosismael1416-max/Plataforma_Convocatorias.git convocatorias-itsva
cd convocatorias-itsva
```

---

## 4. Base de datos (PostgreSQL)

Se crean un usuario y dos bases: la principal y la de pruebas (las pruebas la vacían; **nunca uses la principal para pruebas**).

```bash
sudo systemctl enable --now postgresql

sudo -u postgres psql <<'SQL'
CREATE USER convocatorias_app WITH PASSWORD 'CAMBIA_ESTA_CONTRASEÑA';
CREATE DATABASE convocatorias_itsva OWNER convocatorias_app ENCODING 'UTF8';
CREATE DATABASE convocatorias_itsva_test OWNER convocatorias_app ENCODING 'UTF8';
SQL
```

Elige una contraseña segura y guárdala: irá en los dos archivos `.env`.

> La base de pruebas **debe** terminar en `_test`: las pruebas se niegan a correr sobre otra base.

---

## 5. Aplicación web (Laravel + Filament)

```bash
cd ~/proyectos/convocatorias-itsva/web
composer install            # también publica los estilos de Filament
cp .env.example .env
php artisan key:generate
```

Edita `web/.env` (por ejemplo con `nano .env`) y ajusta:

```dotenv
APP_ENV=local               # production en un servidor
APP_DEBUG=true              # false en un servidor
APP_URL=http://127.0.0.1:8000
APP_LOCALE=es

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=convocatorias_itsva
DB_USERNAME=convocatorias_app
DB_PASSWORD=CAMBIA_ESTA_CONTRASEÑA

EXTRACTOR_API_URL=http://127.0.0.1:8001

# Correo: con «log» los correos (p. ej. recuperar contraseña) se escriben en
# storage/logs/laravel.log. Para enviarlos de verdad, configura SMTP:
MAIL_MAILER=log
# MAIL_MAILER=smtp
# MAIL_HOST=smtp.tu-servidor.mx
# MAIL_PORT=587
# MAIL_USERNAME=...
# MAIL_PASSWORD=...
# MAIL_FROM_ADDRESS="convocatorias@itsva.edu.mx"
```

Crea las tablas, carga los datos iniciales y el administrador:

```bash
php artisan migrate --pretend   # opcional: revisar el SQL
php artisan migrate
php artisan db:seed             # roles, permisos, categoría, organismo SECIHTI y fuentes oficiales
php artisan itsva:crear-admin admin@itsva.edu.mx --nombre="Administrador"
# Pide la contraseña (mínimo 8 caracteres, con letras y números).
```

Arranca el servidor de desarrollo y entra:

```bash
php artisan serve --host=127.0.0.1 --port=8000
```

Abre `http://127.0.0.1:8000/login` e inicia sesión con el administrador. Desde **Admin → Usuarios** crea las cuentas de docentes y directivos (asigna rol y departamento).

Permisos de carpetas (si ves errores de escritura):

```bash
chmod -R ug+rw storage bootstrap/cache
```

---

## 6. Motor de extracción (Python)

```bash
cd ~/proyectos/convocatorias-itsva/extractor
python3 -m venv .venv
./.venv/bin/pip install --upgrade pip
./.venv/bin/pip install -r requirements.txt
cp .env.example .env
chmod 600 .env
```

Edita `extractor/.env` con **la misma base que la web**:

```dotenv
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=convocatorias_itsva
DB_USERNAME=convocatorias_app
DB_PASSWORD=CAMBIA_ESTA_CONTRASEÑA
APP_ENV=local
APP_DEBUG=true
```

Comprueba la conexión arrancando la API un momento:

```bash
./.venv/bin/python -m uvicorn app.main:app --host 127.0.0.1 --port 8001
# En otra terminal:
curl -s http://127.0.0.1:8001/health   # {"status":"ok",...}
curl -s http://127.0.0.1:8001/db-check # conexión a PostgreSQL correcta
# Detén la API con Ctrl+C; en el paso 7 quedará como servicio.
```

Prueba una vista previa de la fuente SECIHTI (no guarda nada):

```bash
./.venv/bin/python -m app.ejecutar_scraping --fuente-id 1 --limite 3
```

> Los ID de las fuentes dependen del orden en que se cargaron; consúltalos en **Admin → Fuentes web**.

---

## 7. Servicios automáticos (systemd)

Las units de `extractor/systemd/` traen la ruta y el usuario de la máquina original. Se copian ajustando ambos:

```bash
cd ~/proyectos/convocatorias-itsva/extractor/systemd
RUTA=/home/TU_USUARIO/proyectos/convocatorias-itsva
USUARIO=TU_USUARIO

for f in convocatorias-itsva.service convocatorias-itsva.timer convocatorias-itsva-api.service; do
  sed -e "s#/home/carlos_ismael/proyectos/convocatorias-itsva#${RUTA}#g" \
      -e "s#^User=carlos_ismael#User=${USUARIO}#" \
      -e "s#^Group=carlos_ismael#Group=${USUARIO}#" \
      "$f" | sudo tee /etc/systemd/system/$f > /dev/null
done

sudo systemctl daemon-reload
sudo systemctl enable --now convocatorias-itsva-api.service   # API (127.0.0.1:8001)
sudo systemctl enable --now convocatorias-itsva.timer          # ciclo cada 6 h
```

Verifica:

```bash
systemctl status convocatorias-itsva-api.service --no-pager
systemctl list-timers --all | grep convocatorias     # próxima ejecución
curl -s http://127.0.0.1:8001/health
```

Para correr un ciclo completo de inmediato (opcional, tarda unos minutos):

```bash
sudo systemctl start convocatorias-itsva.service
journalctl -u convocatorias-itsva.service -f          # ver el avance (Ctrl+C para salir)
```

> La API **no tiene autenticación**: debe escuchar solo en `127.0.0.1` (ya viene así). No la expongas a la red.

---

## 8. Pruebas

```bash
# Web (usa convocatorias_itsva_test; necesita DB_PASSWORD correcto en web/.env)
cd ~/proyectos/convocatorias-itsva/web
php artisan test                 # 73 pruebas

# Extractor
cd ~/proyectos/convocatorias-itsva/extractor
./.venv/bin/python -m unittest discover -s tests -t .   # 90 pruebas
```

---

## 9. Lista de comprobación final

- [ ] `http://127.0.0.1:8000/login` abre y el administrador entra a `/admin`.
- [ ] En **Admin → Dashboard** el motor aparece *encendido* y «API del extractor: OK».
- [ ] **Admin → Fuentes web** muestra SECIHTI y la Secretaría de Ciencia de Yucatán.
- [ ] `systemctl list-timers` muestra el próximo ciclo.
- [ ] Tras un ciclo, `extractor/ciclo_automatico_ultimo.json` existe y el dashboard muestra «Último ciclo».
- [ ] Un docente de prueba entra a `/docente` y ve las convocatorias vigentes (si hay).
- [ ] `php artisan test` y las pruebas del extractor pasan.

---

## 10. (Opcional) Servidor de producción

`php artisan serve` es solo para desarrollo. En un servidor:

1. En `web/.env`: `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio`, y SMTP real.
2. Instala Nginx y PHP-FPM: `sudo apt install -y nginx php8.3-fpm`.
3. Configura un sitio de Nginx cuyo `root` sea `.../web/public` y que pase `*.php` a `php8.3-fpm`.
4. Da permisos al usuario del servidor web (`www-data`) sobre `web/storage` y `web/bootstrap/cache`.
5. Optimiza:
   ```bash
   cd web
   composer install --no-dev --optimize-autoloader
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Usa HTTPS (por ejemplo con Certbot).

---

## 11. Solución de problemas

| Síntoma | Causa probable y solución |
|---|---|
| `SQLSTATE[08006]` o «password authentication failed» | Revisa `DB_*` en `web/.env` y `extractor/.env`, y que PostgreSQL esté activo (`systemctl status postgresql`). |
| `php artisan test` aborta por el nombre de la base | La base de pruebas debe llamarse `convocatorias_itsva_test` (ver `web/phpunit.xml`). |
| Página sin estilos | Ejecuta `php artisan filament:upgrade` y `php artisan optimize:clear`. |
| «No existe el rol ADMINISTRADOR» al crear el admin | Ejecuta `php artisan db:seed` antes de `itsva:crear-admin`. |
| El dashboard dice «API del extractor: sin respuesta» | `sudo systemctl restart convocatorias-itsva-api.service` y revisa `journalctl -u convocatorias-itsva-api.service -n 50`. |
| El ciclo no corre | `systemctl list-timers`; en WSL verifica que systemd esté activo (paso 1). Revisa también que el motor no esté apagado en el admin. |
| `ModuleNotFoundError` en Python | Usa siempre `./.venv/bin/python` desde la carpeta `extractor/`. |
| Errores de OCR en convocatorias marco | Instala `tesseract-ocr-spa`. |
| No llegan correos | Con `MAIL_MAILER=log` se guardan en `web/storage/logs/laravel.log`; configura SMTP para enviarlos. |
| Cambié código de la API y no se nota | `sudo systemctl restart convocatorias-itsva-api.service` (el ciclo no lo necesita: carga el código en cada ejecución). |

---

## 12. Actualizar una instalación existente

```bash
cd ~/proyectos/convocatorias-itsva
git pull
cd web && composer install && php artisan migrate --pretend && php artisan migrate && php artisan optimize:clear
cd ../extractor && ./.venv/bin/pip install -r requirements.txt
sudo systemctl restart convocatorias-itsva-api.service
```

Si cambiaron las units en `extractor/systemd/`, vuelve a copiarlas (paso 7) y ejecuta `sudo systemctl daemon-reload`.

Para la documentación completa del funcionamiento, consulta [DOCUMENTACION.md](DOCUMENTACION.md).
