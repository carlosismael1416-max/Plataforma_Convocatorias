<?php

namespace Tests\Feature;

use App\Filament\Docente\Dashboard;
use App\Models\UsuarioConvocatoria;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DashboardDocenteTest extends TestCase
{
    use RefreshDatabase;

    private function comoDocente()
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        return $docente;
    }

    public function test_muestra_datos_reales_del_docente(): void
    {
        $docente = $this->comoDocente();
        $this->convocatoria(['titulo' => 'ZZ-RECOMENDADA', 'fecha_cierre' => today()->addDays(5)]);

        $this->get('/docente')
            ->assertOk()
            ->assertSee($docente->name)
            ->assertSee('ZZ-RECOMENDADA')
            ->assertSee('POR CERRAR')
            ->assertSee('Aún no tienes propuestas.');
    }

    public function test_guardar_y_marcar_favorita(): void
    {
        $docente = $this->comoDocente();
        $convocatoria = $this->convocatoria(['fecha_cierre' => today()->addDays(20)]);

        $pagina = Livewire::test(Dashboard::class)->call('guardar', $convocatoria->id);

        $seguimiento = UsuarioConvocatoria::query()
            ->where('user_id', $docente->id)
            ->where('convocatoria_id', $convocatoria->id)
            ->firstOrFail();

        $this->assertSame('GUARDADA', $seguimiento->estado_seguimiento);
        $this->assertFalse((bool) $seguimiento->es_favorita);

        $pagina->call('alternarFavorita', $convocatoria->id);
        $this->assertTrue((bool) $seguimiento->fresh()->es_favorita);

        $pagina->call('alternarFavorita', $convocatoria->id);
        $this->assertFalse((bool) $seguimiento->fresh()->es_favorita);
    }

    public function test_no_guarda_convocatorias_vencidas_ni_no_publicadas(): void
    {
        $docente = $this->comoDocente();
        $vencida = $this->convocatoria(['fecha_cierre' => today()->subDay()]);
        $pendiente = $this->convocatoria(['estado' => 'PENDIENTE_REVISION']);

        Livewire::test(Dashboard::class)
            ->call('guardar', $vencida->id)
            ->call('alternarFavorita', $pendiente->id);

        $this->assertSame(0, UsuarioConvocatoria::query()->where('user_id', $docente->id)->count());
    }
}
