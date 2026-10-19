<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\BuscarConvocatorias;
use App\Filament\Docente\Pages\HistorialConvocatorias;
use App\Models\Convocatoria;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TiposConvocatoriaTest extends TestCase
{
    use RefreshDatabase;

    private function comoDocente(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'));
        Filament::setCurrentPanel(Filament::getPanel('docente'));
    }

    public function test_el_buscador_muestra_las_marcas_y_filtra_por_tipo_y_audiencia(): void
    {
        $this->comoDocente();
        $this->convocatoria(['titulo' => 'ZZ-BECA', 'tipo' => 'BECA', 'dirigido_a' => ['ESTUDIANTES'], 'fecha_cierre' => today()->addDays(9)]);
        $this->convocatoria(['titulo' => 'ZZ-PROYECTOS', 'tipo' => null, 'fecha_cierre' => today()->addDays(9)]);

        Livewire::test(BuscarConvocatorias::class)
            ->assertSee('ZZ-BECA')
            ->assertSee('Beca')
            ->assertSee('Estudiantes')
            ->set('tipo', 'BECA')
            ->assertSee('ZZ-BECA')
            ->assertDontSee('ZZ-PROYECTOS')
            ->set('tipo', 'FINANCIAMIENTO')
            ->assertSee('ZZ-PROYECTOS')
            ->assertDontSee('ZZ-BECA')
            ->set('tipo', '')
            ->set('dirigido', 'ESTUDIANTES')
            ->assertSee('ZZ-BECA')
            ->assertDontSee('ZZ-PROYECTOS');
    }

    public function test_no_aplica_nunca_se_ve_ni_en_vigentes_ni_en_historial(): void
    {
        $this->comoDocente();
        $this->convocatoria(['titulo' => 'ZZ-VACANTE', 'tipo' => 'NO_APLICA', 'fecha_cierre' => today()->addDays(9)]);
        $this->convocatoria(['titulo' => 'ZZ-VACANTE-CERRADA', 'tipo' => 'NO_APLICA', 'estado' => 'CERRADA', 'fecha_cierre' => today()->subDays(9)]);
        $this->convocatoria(['titulo' => 'ZZ-PREMIO-CERRADO', 'tipo' => 'PREMIO', 'estado' => 'CERRADA', 'fecha_cierre' => today()->subDays(9)]);

        $this->assertFalse(Convocatoria::visibles()->where('titulo', 'ZZ-VACANTE')->exists());

        Livewire::test(HistorialConvocatorias::class)
            ->assertSee('ZZ-PREMIO-CERRADO')
            ->assertDontSee('ZZ-VACANTE-CERRADA');
    }

    public function test_al_subir_a_mano_se_guarda_el_tipo_y_a_quien_va_dirigida(): void
    {
        $this->comoDocente();

        Livewire::test(\App\Filament\Docente\Resources\Convocatorias\Pages\CreateConvocatoria::class)
            ->fillForm([
                'titulo' => 'ZZ-CONGRESO',
                'tipo' => 'EVENTO',
                'dirigido_a' => ['DOCENTES', 'ESTUDIANTES'],
                'descripcion' => 'Congreso regional',
                'moneda' => 'MXN',
                'fecha_cierre' => today()->addDays(10)->toDateString(),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $c = Convocatoria::where('titulo', 'ZZ-CONGRESO')->firstOrFail();
        $this->assertSame('EVENTO', $c->tipo);
        $this->assertSame(['DOCENTES', 'ESTUDIANTES'], $c->dirigido_a);
    }
}
