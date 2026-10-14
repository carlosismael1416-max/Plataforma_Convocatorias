<?php
// Verifica ConvocatoriaResource::tieneDependencias(): una convocatoria
// sin datos relacionados se puede borrar; con propuesta o con evento de
// calendario, o un padre con hijas, no. Todo ocurre en una transacción revertida.
// Uso (desde web/): php tests/Verificacion/convocatoria_dependencias.php
chdir(dirname(__DIR__, 2));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use Illuminate\Support\Facades\DB;

DB::beginTransaction();
$nueva = fn (string $t) => Convocatoria::find(DB::table("convocatorias")->insertGetId([
    "titulo" => $t, "estado" => "PENDIENTE_REVISION", "origen" => "MANUAL",
    "url_hash" => hash("sha256", $t), "created_at" => now(), "updated_at" => now(),
]));
$sola = $nueva("ZZ-DEP-SOLA");
$conPropuesta = $nueva("ZZ-DEP-PROPUESTA");
$conEvento = $nueva("ZZ-DEP-EVENTO");
$padre = $nueva("ZZ-DEP-PADRE");
DB::table("convocatorias")->where("id", $nueva("ZZ-DEP-HIJA")->id)->update(["convocatoria_padre_id" => $padre->id]);
DB::table("propuestas")->insert(["user_id" => 2, "convocatoria_id" => $conPropuesta->id, "titulo" => "x", "created_at" => now(), "updated_at" => now()]);
$columnas = DB::getSchemaBuilder()->getColumnListing("eventos_calendario");
$evento = ["user_id" => 2, "convocatoria_id" => $conEvento->id, "titulo" => "x", "created_at" => now(), "updated_at" => now()];
foreach (["fecha" => today(), "fecha_inicio" => now(), "tipo" => "PERSONAL"] as $c => $v) if (in_array($c, $columnas)) $evento[$c] = $v;
DB::table("eventos_calendario")->insert($evento);

$fallos = 0;
foreach ([[$sola, false], [$conPropuesta, true], [$conEvento, true], [$padre, true]] as [$c, $esperado]) {
    $obtenido = ConvocatoriaResource::tieneDependencias($c);
    printf("%-18s dependencias=%-5s %s\n", $c->titulo, $obtenido ? "sí" : "no", $obtenido === $esperado ? "OK" : "FALLA");
    $fallos += $obtenido === $esperado ? 0 : 1;
}
DB::rollBack();
exit($fallos ? 1 : 0);
