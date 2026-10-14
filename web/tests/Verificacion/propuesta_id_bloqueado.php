<?php
// Verifica que el navegador no pueda cambiar propuestaId en las
// páginas de propuesta del docente (#[Locked]).
// Crea una propuesta de prueba en una transacción y la revierte.
// Uso (desde web/): php tests/Verificacion/propuesta_id_bloqueado.php
// Requiere un usuario DOCENTE activo con id 2 y una convocatoria.
chdir(dirname(__DIR__, 2));
require "vendor/autoload.php";
$app = require "bootstrap/app.php";
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(["session.driver" => "array"]);

use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

DB::beginTransaction();
$docente = App\Models\User::find(2);
$propia = DB::table("propuestas")->insertGetId([
    "user_id" => $docente->id,
    "convocatoria_id" => DB::table("convocatorias")->min("id"),
    "titulo" => "ZZ-PROPUESTA-PRUEBA",
    "created_at" => now(),
    "updated_at" => now(),
]);
auth()->guard("web")->setUser($docente);
Filament\Facades\Filament::setCurrentPanel(Filament\Facades\Filament::getPanel("docente"));

$fallos = 0;
foreach (["Editor", "Objetivos", "Presupuesto", "Cotizaciones", "Cronograma", "Entregables", "Requisitos", "VistaPrevia"] as $nombre) {
    $clase = "App\\Filament\\Docente\\Pages\\{$nombre}Propuesta";
    try {
        $prueba = Livewire::withQueryParams(["propuesta" => $propia])->test($clase);
    } catch (Throwable $e) {
        printf("%-24s FALLA al montar: %s\n", $nombre, $e->getMessage());
        $fallos++;
        continue;
    }
    try {
        $prueba->set("propuestaId", $propia + 1000);
        printf("%-24s FALLA: el cliente pudo cambiar propuestaId\n", $nombre);
        $fallos++;
    } catch (\Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException) {
        printf("%-24s OK: propuestaId bloqueado\n", $nombre);
    }
}
DB::rollBack();
echo "Propuestas de prueba tras rollback: ", DB::table("propuestas")->where("titulo", "ZZ-PROPUESTA-PRUEBA")->count(), "\n";
exit($fallos ? 1 : 0);
