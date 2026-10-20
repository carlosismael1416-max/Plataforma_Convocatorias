#!/usr/bin/env bash
# Prepara el recorrido de botones SIN tocar la base real:
#  1. copia la base real a convocatorias_itsva_test (la que vacían las pruebas);
#  2. crea datos de ejemplo y la lista de URLs (salida/urls.json);
#  3. levanta una copia de la web en 127.0.0.1:8010 apuntando a esa base.
# Después: node tests/Recorrido/recorrer.cjs docente|directivo|admin
# No correr `php artisan test` mientras dure el recorrido (vacía la base).
set -euo pipefail
cd "$(dirname "$0")/../.."
PRUEBA=convocatorias_itsva_test
REAL="$(grep '^DB_DATABASE=' .env | cut -d= -f2-)"
export PGPASSWORD="$(grep '^DB_PASSWORD=' .env | cut -d= -f2-)"
USUARIO="$(grep '^DB_USERNAME=' .env | cut -d= -f2-)"
[ "$REAL" != "$PRUEBA" ] || { echo "La base real no puede ser la de pruebas." >&2; exit 1; }
mkdir -p tests/Recorrido/salida
pg_dump -h 127.0.0.1 -U "$USUARIO" -Fc --no-owner "$REAL" -f tests/Recorrido/salida/copia.dump
pg_restore --clean --if-exists --no-owner -h 127.0.0.1 -U "$USUARIO" -d "$PRUEBA" tests/Recorrido/salida/copia.dump
rm -f tests/Recorrido/salida/copia.dump
DB_DATABASE=$PRUEBA php tests/Recorrido/urls.php > tests/Recorrido/salida/urls.json
PID=tests/Recorrido/salida/servidor.pid
[ -f "$PID" ] && kill "$(cat "$PID")" 2>/dev/null || true
DB_DATABASE=$PRUEBA APP_URL=http://127.0.0.1:8010 nohup php artisan serve --host=127.0.0.1 --port=8010 > tests/Recorrido/salida/servidor.log 2>&1 &
echo $! > "$PID"
curl -s -o /dev/null --retry 10 --retry-connrefused --retry-delay 1 http://127.0.0.1:8010/login
echo "Copia de prueba lista en http://127.0.0.1:8010 (base $PRUEBA)."
