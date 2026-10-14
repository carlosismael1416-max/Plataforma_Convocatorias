<?php
// Recorre todas las rutas GET de los paneles admin, directivo y docente
// con un usuario real de cada rol y reporta las que no respondan 200.
// Crea los datos que algunas páginas necesitan en una transacción y la
// revierte al final.
//
// Uso (desde web/): php tests/Verificacion/todas_las_paginas.php
// Requiere usuarios activos: ADMINISTRADOR id 1, DOCENTE id 2, DIRECTIVO id 3.
//
// Cada panel se prueba en un proceso PHP propio: dentro de un mismo
// proceso, la primera petición tras cambiar de panel pierde al usuario
// autenticado (efecto del arnés; en producción cada petición es nueva).

$usuarios = ["admin" => 1, "directivo" => 3, "docente" => 2];

if (! isset($argv[1])) {
    $codigo = 0;
    foreach (array_keys($usuarios) as $panel) {
        passthru(escapeshellarg(PHP_BINARY) . " " . escapeshellarg(__FILE__) . " " . escapeshellarg($panel), $salida);
        $codigo = max($codigo, $salida);
    }
    exit($codigo);
}

$panel = $argv[1];

chdir(dirname(__DIR__, 2));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(["session.driver" => "array"]);

use Illuminate\Support\Facades\DB;

DB::beginTransaction();

$conv = fn (array $c) => DB::table("convocatorias")->insertGetId($c + [
    "descripcion" => "Verificación de páginas",
    "origen" => "MANUAL",
    "url_hash" => hash("sha256", uniqid("", true)),
    "created_at" => now(),
    "updated_at" => now(),
]);
$publicada = $conv(["titulo" => "ZZ-PAGINAS-PUBLICADA", "estado" => "PUBLICADA", "fecha_cierre" => today()->addDays(30)]);
$delDocente = $conv(["titulo" => "ZZ-PAGINAS-DOCENTE", "estado" => "PENDIENTE_REVISION", "subido_por_user_id" => 2]);
$delDirectivo = $conv(["titulo" => "ZZ-PAGINAS-DIRECTIVO", "estado" => "PENDIENTE_REVISION", "subido_por_user_id" => 3]);
$propuesta = DB::table("propuestas")->insertGetId(["user_id" => 2, "convocatoria_id" => $publicada, "titulo" => "ZZ-PAGINAS-PROPUESTA", "created_at" => now(), "updated_at" => now()]);
DB::table("departamentos")->insert(["nombre" => "ZZ-PAGINAS-DEPTO", "created_at" => now(), "updated_at" => now()]);
DB::table("bitacora_errores")->insert(["mensaje" => "ZZ-PAGINAS-ERROR", "tipo_error" => "PRUEBA", "resuelto" => false, "created_at" => now(), "updated_at" => now()]);

$primero = fn (string $tabla) => DB::table($tabla)->min("id");
$registros = [
    "admin/categorias" => $primero("categorias"),
    "admin/convocatorias" => $publicada,
    "admin/departamentos" => $primero("departamentos"),
    "admin/descubrimiento-webs" => $primero("descubrimientos_web"),
    "admin/detalle-ejecucion" => $primero("ejecuciones_scraping"),
    "admin/detalle-error" => $primero("bitacora_errores"),
    "admin/fuentes" => $primero("fuentes"),
    "admin/gestionar-fuente" => $primero("fuentes"),
    "admin/organismos" => $primero("organismos"),
    "admin/propuestas" => $propuesta,
    "admin/roles" => $primero("roles"),
    "admin/users" => 2,
    "directivo/convocatorias" => $delDirectivo,
    "directivo/detalle-convocatoria" => $publicada,
    "directivo/revisar-convocatoria" => $delDocente,
    "docente/convocatorias" => $delDocente,
    "docente/detalle-convocatoria" => $publicada,
];

$rutas = [];
foreach (app("router")->getRoutes() as $ruta) {
    $uri = $ruta->uri();
    // Un usuario autenticado que pide /login es redirigido a su panel.
    if (in_array("GET", $ruta->methods()) && preg_match("#^$panel(/|$)#", $uri) && ! str_ends_with($uri, "/login")) {
        $rutas[] = $uri;
    }
}
$rutas = array_unique($rutas);
sort($rutas);

$app["auth"]->guard("web")->setUser(App\Models\User::find($usuarios[$panel]));

$fallos = [];
$total = 0;
foreach ($rutas as $uri) {
    $url = $uri;
    if (str_contains($uri, "{record}")) {
        $id = $registros[preg_replace("#/\{record\}.*#", "", $uri)] ?? null;
        if ($id === null) {
            $fallos[] = "$uri: sin registro de prueba";
            continue;
        }
        $url = str_replace("{record}", $id, $uri);
    }
    if (preg_match("#-propuesta$#", $uri) && ! str_contains($uri, "generar")) {
        $url .= "?propuesta=$propuesta";
    }

    $respuesta = $kernel->handle(Illuminate\Http\Request::create("/$url", "GET"));
    $total++;
    $codigo = $respuesta->getStatusCode();

    // docente/convocatorias redirige a "Subir convocatoria" (create).
    if ($codigo === 200 || ($codigo === 302 && $uri === "docente/convocatorias")) {
        continue;
    }

    $error = $respuesta->exception ?? null;
    $fallos[] = sprintf(
        "%s -> HTTP %d%s",
        $url,
        $codigo,
        $error ? ": " . class_basename($error) . ": " . mb_substr($error->getMessage(), 0, 160) : ""
    );
}

DB::rollBack();

printf("[%s] rutas probadas: %d, con fallo: %d\n", $panel, $total, count($fallos));
foreach ($fallos as $fallo) {
    echo "  $fallo\n";
}
$restantes = DB::table("convocatorias")->where("titulo", "like", "ZZ-PAGINAS-%")->count();
if ($restantes) {
    echo "  ¡Quedaron $restantes filas de prueba!\n";
}

exit($fallos || $restantes ? 1 : 0);
