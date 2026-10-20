<?php
// Crea datos de ejemplo en la base de pruebas e imprime (JSON) las URLs GET
// de cada panel con el usuario que las recorre.
// Uso: DB_DATABASE=convocatorias_itsva_test php tests/Recorrido/urls.php > tests/Recorrido/salida/urls.json
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

if (! str_ends_with((string) config('database.connections.pgsql.database'), '_test')) {
    fwrite(STDERR, "Solo con una base que termine en _test.\n");
    exit(1);
}

$conv = fn (array $c) => DB::table('convocatorias')->insertGetId($c + ['descripcion' => 'Recorrido de botones', 'origen' => 'MANUAL', 'url_hash' => hash('sha256', uniqid('', true)), 'created_at' => now(), 'updated_at' => now()]);
$buscar = fn (string $titulo) => DB::table('convocatorias')->where('titulo', $titulo)->value('id');

$publicada = $buscar('ZZ-RECORRIDO-PUBLICADA') ?? $conv(['titulo' => 'ZZ-RECORRIDO-PUBLICADA', 'estado' => 'PUBLICADA', 'tipo' => 'FINANCIAMIENTO', 'fecha_cierre' => today()->addDays(30)]);
$delDocente = $buscar('ZZ-RECORRIDO-DOCENTE') ?? $conv(['titulo' => 'ZZ-RECORRIDO-DOCENTE', 'estado' => 'PUBLICADA', 'subido_por_user_id' => 2, 'fecha_cierre' => today()->addDays(20)]);
$delDirectivo = $buscar('ZZ-RECORRIDO-DIRECTIVO') ?? $conv(['titulo' => 'ZZ-RECORRIDO-DIRECTIVO', 'estado' => 'PUBLICADA', 'subido_por_user_id' => 3, 'fecha_cierre' => today()->addDays(20)]);
$propuesta = DB::table('propuestas')->where('user_id', 2)->whereIn('estado', ['BORRADOR', 'GENERADA', 'EDITANDO'])->orderBy('id')->value('id')
    ?? DB::table('propuestas')->insertGetId(['user_id' => 2, 'convocatoria_id' => $publicada, 'titulo' => 'ZZ-RECORRIDO-PROPUESTA', 'estado' => 'EDITANDO', 'created_at' => now(), 'updated_at' => now()]);
$propuestaDirectivo = DB::table('propuestas')->where('titulo', 'ZZ-RECORRIDO-PROP-DIR')->value('id')
    ?? DB::table('propuestas')->insertGetId(['user_id' => 3, 'convocatoria_id' => $publicada, 'titulo' => 'ZZ-RECORRIDO-PROP-DIR', 'estado' => 'ENVIADA', 'fecha_envio' => now(), 'created_at' => now(), 'updated_at' => now()]);

$primero = fn (string $tabla) => DB::table($tabla)->min('id');
$registros = [
    'admin/categorias' => $primero('categorias'), 'admin/convocatorias' => $publicada, 'admin/departamentos' => $primero('departamentos'),
    'admin/descubrimiento-webs' => $primero('descubrimientos_web'), 'admin/detalle-ejecucion' => $primero('ejecuciones_scraping'),
    'admin/detalle-error' => $primero('bitacora_errores'), 'admin/fuentes' => $primero('fuentes'), 'admin/gestionar-fuente' => $primero('fuentes'),
    'admin/organismos' => $primero('organismos'), 'admin/propuestas' => $propuesta, 'admin/roles' => $primero('roles'), 'admin/users' => 2,
    'directivo/convocatorias' => $delDirectivo, 'directivo/detalle-convocatoria' => $publicada, 'directivo/revisar-propuesta' => $propuestaDirectivo,
    'docente/convocatorias' => $delDocente, 'docente/detalle-convocatoria' => $publicada,
];

$salida = [];

foreach (['docente' => 2, 'directivo' => 3, 'admin' => 1] as $panel => $usuario) {
    $urls = [];

    foreach (app('router')->getRoutes() as $ruta) {
        $uri = $ruta->uri();

        if (! in_array('GET', $ruta->methods()) || ! preg_match("#^$panel(/|$)#", $uri) || str_ends_with($uri, '/login') || str_contains($uri, 'logout') || preg_match('#\{(?!record\})#', $uri)) {
            continue;
        }

        $url = $uri;

        if (str_contains($uri, '{record}')) {
            $id = $registros[preg_replace('#/\{record\}.*#', '', $uri)] ?? null;

            if ($id === null) {
                continue;
            }

            $url = str_replace('{record}', $id, $uri);
        }

        if (preg_match('#-propuesta$#', $uri) && ! str_contains($uri, 'generar')) {
            $url .= '?propuesta=' . ($panel === 'directivo' ? $propuestaDirectivo : $propuesta);
        }

        $urls[] = '/' . $url;
    }

    $salida[$panel] = ['usuario' => $usuario, 'urls' => array_values(array_unique($urls))];
}

echo json_encode($salida, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES), "\n";
