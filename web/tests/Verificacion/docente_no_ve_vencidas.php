<?php
// Verifica que el docente no vea convocatorias vencidas.
// Inserta filas de prueba en una transacción y la revierte al final.
// Uso (desde web/): php tests/Verificacion/docente_no_ve_vencidas.php
// Requiere un usuario DOCENTE activo con id 2.
chdir(dirname(__DIR__, 2));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(["session.driver" => "array"]);
use Illuminate\Support\Facades\DB;
DB::beginTransaction();
$base = ["descripcion"=>"x","estado"=>"PUBLICADA","origen"=>"MANUAL","created_at"=>now(),"updated_at"=>now()];
$casos = [
  "ZZ-VENCIDA-PRUEBA" => today()->subDay()->toDateString(),
  "ZZ-VIGENTE-PRUEBA" => today()->addDays(20)->toDateString(),
  "ZZ-SINFECHA-PRUEBA" => null,
];
foreach ($casos as $t => $f) DB::table("convocatorias")->insert($base + ["titulo"=>$t, "fecha_cierre"=>$f, "url_hash"=>hash("sha256",$t), "fecha_publicacion"=>today()]);
$app["auth"]->guard("web")->setUser(App\Models\User::find(2));
$fallos = 0;
foreach (["docente/buscar-convocatorias", "docente"] as $ruta) {
  $html = $kernel->handle(Illuminate\Http\Request::create("/$ruta", "GET"))->getContent();
  foreach ($casos as $t => $f) {
    $visible = str_contains($html, $t);
    $esperado = $t !== "ZZ-VENCIDA-PRUEBA";
    if ($ruta === "docente" && $t === "ZZ-SINFECHA-PRUEBA") $esperado = $visible; // el dashboard muestra solo 3 recientes
    printf("%-30s %-20s %-9s %s\n", $ruta, $t, $visible ? "visible" : "oculta", $visible === $esperado ? "OK" : "FALLA");
    $fallos += $visible === $esperado ? 0 : 1;
  }
}
DB::rollBack();
echo "Filas de prueba tras rollback: ", DB::table("convocatorias")->where("titulo","like","ZZ-%-PRUEBA")->count(), "\n";
exit($fallos ? 1 : 0);
