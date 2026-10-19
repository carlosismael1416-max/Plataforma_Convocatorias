#!/usr/bin/env bash
# Despliegue de producción: los servicios (ciclo, API, web y respaldos) corren
# desde una copia aparte que solo cambia con este script, siempre desde una
# rama fija. Así, cambiar de rama o editar archivos en la carpeta de
# desarrollo no tumba el motor.
#
# Uso:
#   scripts/desplegar.sh                    # actualizar producción desde «main»
#   scripts/desplegar.sh --migrar           # además aplicar migraciones pendientes
#   scripts/desplegar.sh --instalar-units   # (re)instalar las units de systemd (sudo)
#   RAMA=main PROD_DIR=~/produccion/convocatorias-itsva scripts/desplegar.sh
set -euo pipefail

DEV_DIR="$(cd "$(dirname "$0")/.." && pwd)"
PROD_DIR="${PROD_DIR:-$HOME/produccion/convocatorias-itsva}"
RAMA="${RAMA:-main}"
USUARIO="$(id -un)"
MIGRAR=0
INSTALAR_UNITS=0

for arg in "$@"; do
  case "$arg" in
    --migrar) MIGRAR=1 ;;
    --instalar-units) INSTALAR_UNITS=1 ;;
    *) echo "Opción desconocida: $arg" >&2; exit 2 ;;
  esac
done

paso() { printf '\n==> %s\n' "$*"; }

# 1. Código: clon local de la carpeta de desarrollo, solo lo que ya tiene commit.
if [ ! -d "$PROD_DIR/.git" ]; then
  paso "Creando la copia de producción en $PROD_DIR"
  mkdir -p "$(dirname "$PROD_DIR")"
  git clone --branch "$RAMA" "$DEV_DIR" "$PROD_DIR"
fi

paso "Actualizando el código a la rama $RAMA"
git -C "$PROD_DIR" fetch --quiet origin "$RAMA"
git -C "$PROD_DIR" checkout --quiet -B "$RAMA" "origin/$RAMA"
git -C "$PROD_DIR" reset --quiet --hard "origin/$RAMA"
git -C "$PROD_DIR" clean -fdq -e .env -e .venv -e vendor -e storage -e ciclo_automatico_ultimo.json -e google_news_url_cache.json
echo "Versión: $(git -C "$PROD_DIR" log -1 --oneline)"

# 2. Archivos .env: la primera vez se copian de desarrollo (luego son propios).
for parte in web extractor; do
  if [ ! -f "$PROD_DIR/$parte/.env" ]; then
    cp "$DEV_DIR/$parte/.env" "$PROD_DIR/$parte/.env"
    chmod 600 "$PROD_DIR/$parte/.env"
    echo "Copiado $parte/.env desde desarrollo"
  fi
done

# 3. Extractor: entorno virtual y dependencias (solo si cambiaron).
paso "Dependencias del extractor"
cd "$PROD_DIR/extractor"
[ -x .venv/bin/python ] || python3 -m venv .venv
HUELLA="$(sha256sum requirements.txt | cut -d' ' -f1)"
if [ "$(cat .venv/.requirements 2>/dev/null)" != "$HUELLA" ]; then
  ./.venv/bin/pip install --quiet --upgrade pip
  ./.venv/bin/pip install --quiet -r requirements.txt
  echo "$HUELLA" > .venv/.requirements
fi
./.venv/bin/python -c "import app.ejecutar_ciclo_automatico, app.main" \
  || { echo "El extractor no importa: NO se reinician los servicios." >&2; exit 1; }

# 4. Web: dependencias, migraciones (solo con --migrar) y cachés.
paso "Web"
cd "$PROD_DIR/web"
composer install --quiet --no-dev --optimize-autoloader --no-interaction
PENDIENTES="$(php artisan migrate:status --pending 2>/dev/null | grep -c Pending || true)"
if [ "$PENDIENTES" -gt 0 ]; then
  php artisan migrate:status --pending
  if [ "$MIGRAR" = 1 ]; then
    php artisan migrate --force
  else
    echo "Hay $PENDIENTES migración(es) pendiente(s). Revísalas y vuelve a correr con --migrar." >&2
  fi
fi
[ -e public/storage ] || php artisan storage:link >/dev/null
php artisan optimize:clear >/dev/null
php artisan config:cache >/dev/null
php artisan route:cache >/dev/null
php artisan view:cache >/dev/null

# 5. Units de systemd apuntando a la copia de producción.
UNITS=(convocatorias-itsva.service convocatorias-itsva.timer convocatorias-itsva-api.service
       convocatorias-itsva-web.service convocatorias-itsva-respaldo.service convocatorias-itsva-respaldo.timer)
if [ "$INSTALAR_UNITS" = 1 ]; then
  paso "Instalando las units de systemd (pide sudo)"
  for unit in "${UNITS[@]}"; do
    sed -e "s#/home/carlos_ismael/proyectos/convocatorias-itsva#${PROD_DIR}#g" \
        -e "s#^User=carlos_ismael#User=${USUARIO}#" \
        -e "s#^Group=carlos_ismael#Group=${USUARIO}#" \
        "$PROD_DIR/extractor/systemd/$unit" | sudo tee "/etc/systemd/system/$unit" > /dev/null
  done
  sudo systemctl daemon-reload
  sudo systemctl enable --now convocatorias-itsva-api.service convocatorias-itsva-web.service \
    convocatorias-itsva.timer convocatorias-itsva-respaldo.timer
fi

# 6. Reinicio de los servicios que mantienen código en memoria.
paso "Reiniciando la API y la web"
sudo systemctl restart convocatorias-itsva-api.service convocatorias-itsva-web.service
sleep 2
curl -fsS http://127.0.0.1:8001/health >/dev/null && echo "API: OK" || echo "API: sin respuesta" >&2
curl -fsS -o /dev/null http://127.0.0.1:8000/login && echo "Web: OK" || echo "Web: sin respuesta" >&2

paso "Despliegue terminado"
