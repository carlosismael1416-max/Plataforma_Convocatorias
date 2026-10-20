<?php
// Imprime "cookie=valor" de una sesión iniciada para el usuario indicado.
// Uso: DB_DATABASE=convocatorias_itsva_test php tests/Recorrido/sesion.php <user_id>
require __DIR__ . '/../../vendor/autoload.php';
$app = require __DIR__ . '/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

if (! str_ends_with((string) config('database.connections.pgsql.database'), '_test')) {
    fwrite(STDERR, "Solo con una base que termine en _test.\n");
    exit(1);
}

$sesion = app('session')->driver();
$sesion->start();
Illuminate\Support\Facades\Auth::guard('web')->login(App\Models\User::findOrFail((int) $argv[1]));
$sesion->save();

$nombre = config('session.cookie');
echo $nombre, '=', app('encrypter')->encrypt(
    Illuminate\Cookie\CookieValuePrefix::create($nombre, app('encrypter')->getKey()) . $sesion->getId(),
    false
), "\n";
