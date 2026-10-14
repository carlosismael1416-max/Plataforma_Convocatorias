<?php
// Prueba negativa de permisos: cada rol recibe 403/404 fuera de lo suyo.
// Crea los datos necesarios en una transacción y la revierte.
//
// Uso (desde web/): php tests/Verificacion/permisos_por_rol.php
// Requiere usuarios activos: ADMINISTRADOR id 1, DOCENTE id 2, DIRECTIVO id 3.
//
// Cada caso corre en su propio proceso PHP (ver todas_las_paginas.php).

// [usuario, ruta, códigos aceptados, descripción]
$casos = [
    [2, "admin", [403], "docente entra al panel admin"],
    [2, "admin/convocatorias", [403], "docente abre la gestión de convocatorias"],
    [2, "directivo", [403], "docente entra al panel directivo"],
    [3, "admin/users", [403], "directivo abre usuarios del admin"],
    [1, "docente", [403], "admin entra al panel docente"],
    [2, "docente/convocatorias/{ajena}/edit", [403, 404], "docente edita convocatoria subida por otro"],
    [2, "docente/detalle-convocatoria/{pendiente}", [403, 404], "docente ve detalle de convocatoria no publicada"],
    [3, "directivo/convocatorias/{ajena}", [403, 404], "directivo ve convocatoria subida por otro directivo"],
];

if (! isset($argv[1])) {
    $codigo = 0;
    foreach (array_keys($casos) as $i) {
        passthru(escapeshellarg(PHP_BINARY) . " " . escapeshellarg(__FILE__) . " $i", $salida);
        $codigo = max($codigo, $salida);
    }
    exit($codigo);
}

[$usuarioId, $ruta, $aceptados, $descripcion] = $casos[(int) $argv[1]];

chdir(dirname(__DIR__, 2));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(["session.driver" => "array"]);

use Illuminate\Support\Facades\DB;

DB::beginTransaction();

// Convocatoria pendiente subida por el administrador (id 1): ajena para
// el docente y el directivo, y no publicada.
$ajena = DB::table("convocatorias")->insertGetId([
    "titulo" => "ZZ-PERMISOS-AJENA",
    "estado" => "PENDIENTE_REVISION",
    "origen" => "MANUAL",
    "subido_por_user_id" => 1,
    "url_hash" => hash("sha256", uniqid("", true)),
    "created_at" => now(),
    "updated_at" => now(),
]);

$url = str_replace(["{ajena}", "{pendiente}"], $ajena, $ruta);

$app["auth"]->guard("web")->setUser(App\Models\User::find($usuarioId));
$codigo = $kernel->handle(Illuminate\Http\Request::create("/$url", "GET"))->getStatusCode();

DB::rollBack();

$ok = in_array($codigo, $aceptados, true);
printf("%-4s HTTP %d  %s (%s)\n", $ok ? "OK" : "FALLA", $codigo, $descripcion, $url);

exit($ok ? 0 : 1);
