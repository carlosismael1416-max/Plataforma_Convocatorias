#!/usr/bin/env bash
# Respaldo diario de la base de la plataforma (lo ejecuta el timer
# convocatorias-itsva-respaldo.timer). Formato custom de pg_dump (comprimido).
# Conserva 7 respaldos diarios y 4 semanales (los domingos).
#
# Restaurar un respaldo en una base vacía:
#   pg_restore --clean --if-exists --no-owner -d convocatorias_itsva ARCHIVO.dump
set -euo pipefail

BASE_DIR="$(cd "$(dirname "$0")/.." && pwd)"
DESTINO="${RESPALDOS_DIR:-$HOME/respaldos/convocatorias-itsva}"

# Credenciales desde extractor/.env (sin imprimirlas).
leer() { grep -E "^$1=" "$BASE_DIR/.env" | head -1 | cut -d= -f2- | sed -e 's/^"//' -e 's/"$//'; }

export PGHOST="$(leer DB_HOST)" PGPORT="$(leer DB_PORT)" PGUSER="$(leer DB_USERNAME)" PGPASSWORD="$(leer DB_PASSWORD)"
BASE="$(leer DB_DATABASE)"

mkdir -p "$DESTINO/diario" "$DESTINO/semanal"
chmod 700 "$DESTINO"

ARCHIVO="$DESTINO/diario/${BASE}_$(date +%F).dump"
pg_dump --format=custom --compress=6 --no-owner --file="$ARCHIVO.tmp" "$BASE"
mv "$ARCHIVO.tmp" "$ARCHIVO"

# Domingo: copia semanal.
if [ "$(date +%u)" = "7" ]; then
  cp "$ARCHIVO" "$DESTINO/semanal/"
fi

# Retención: 7 diarios y 4 semanales.
ls -1t "$DESTINO"/diario/*.dump 2>/dev/null | tail -n +8 | xargs -r rm -f
ls -1t "$DESTINO"/semanal/*.dump 2>/dev/null | tail -n +5 | xargs -r rm -f

echo "Respaldo listo: $ARCHIVO ($(du -h "$ARCHIVO" | cut -f1))"
