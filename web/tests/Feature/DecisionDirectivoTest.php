<?php

namespace Tests\Feature;

use App\Filament\Directivo\Pages\RevisarConvocatoria;
use App\Models\Notificacion;
use App\Services\DecisionDirectivoConvocatoria;
use Database\Seeders\PermisoSeeder;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DecisionDirectivoTest extends TestCase
{
    use RefreshDatabase;

    private function directivo()
    {
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $this->seed(PermisoSeeder::class);

        return $directivo->fresh();
    }

    private function pendiente(array $extra = [])
    {
        return $this->convocatoria($extra + [
            'estado' => 'PENDIENTE_REVISION',
            'fecha_cierre' => today()->addDays(20),
        ]);
    }

    public function test_el_directivo_aprueba_desde_la_pagina_y_se_avisa_al_autor(): void
    {
        $directivo = $this->directivo();
        $docente = $this->usuarioConRol('DOCENTE');
        $convocatoria = $this->pendiente(['subido_por_user_id' => $docente->id]);

        $this->actingAs($directivo);
        Filament::setCurrentPanel(Filament::getPanel('directivo'));

        Livewire::test(RevisarConvocatoria::class, ['record' => $convocatoria->id])
            ->call('aprobar');

        $this->assertSame('PUBLICADA', $convocatoria->fresh()->estado);
        $this->assertTrue(
            DB::table('convocatoria_revisiones_administrativas')
                ->where('convocatoria_id', $convocatoria->id)
                ->where('accion', 'APROBAR')
                ->where('revisor_id', $directivo->id)
                ->exists()
        );
        $this->assertTrue(
            Notificacion::query()->where('user_id', $docente->id)->where('tipo', 'CONVOCATORIA_DICTAMEN')->exists()
        );
    }

    public function test_correccion_y_rechazo_exigen_observaciones(): void
    {
        $directivo = $this->directivo();

        $correccion = $this->pendiente();
        DecisionDirectivoConvocatoria::decidir($correccion, $directivo, 'SOLICITAR_CORRECCIONES', 'Falta el monto.');
        $this->assertSame('REQUIERE_CORRECCIONES', $correccion->fresh()->estado);

        $rechazo = $this->pendiente();
        DecisionDirectivoConvocatoria::decidir($rechazo, $directivo, 'RECHAZAR', 'Es una beca, no investigación.');
        $this->assertSame('DESCARTADA', $rechazo->fresh()->estado);

        $sinObservaciones = $this->pendiente();
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        DecisionDirectivoConvocatoria::decidir($sinObservaciones, $directivo, 'RECHAZAR', '');
    }

    public function test_no_publica_vencidas_ni_decide_dos_veces(): void
    {
        $directivo = $this->directivo();

        $vencida = $this->pendiente(['fecha_cierre' => today()->subDay()]);

        try {
            DecisionDirectivoConvocatoria::decidir($vencida, $directivo, 'APROBAR');
            $this->fail('Publicó una convocatoria vencida.');
        } catch (\Illuminate\Validation\ValidationException) {
        }

        $this->assertSame('PENDIENTE_REVISION', $vencida->fresh()->estado);

        $publicada = $this->convocatoria(['estado' => 'PUBLICADA']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        DecisionDirectivoConvocatoria::decidir($publicada, $directivo, 'APROBAR');
    }

    public function test_ni_admin_ni_docente_deciden(): void
    {
        $admin = $this->usuarioConRol('ADMINISTRADOR');
        $this->seed(PermisoSeeder::class);
        $admin = $admin->fresh();

        $this->expectException(AuthorizationException::class);
        DecisionDirectivoConvocatoria::decidir($this->pendiente(), $admin, 'APROBAR');
    }
}
