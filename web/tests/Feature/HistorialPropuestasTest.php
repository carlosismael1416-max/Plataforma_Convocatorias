<?php

namespace Tests\Feature;

use App\Filament\Directivo\Pages\PropuestasPorRevisar;
use App\Models\Propuesta;
use App\Services\RevisionPropuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class HistorialPropuestasTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_historial_muestra_envios_y_dictamenes_con_filtros(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $c1 = $this->convocatoria(['titulo' => 'ZZ-CONV-UNO', 'fecha_cierre' => today()->addDays(9)]);
        $c2 = $this->convocatoria(['titulo' => 'ZZ-CONV-DOS', 'fecha_cierre' => today()->addDays(9)]);

        $p1 = Propuesta::create(['user_id' => $docente->id, 'convocatoria_id' => $c1->id, 'titulo' => 'ZZ-PROP-UNO', 'estado' => 'ENVIADA']);
        $p2 = Propuesta::create(['user_id' => $docente->id, 'convocatoria_id' => $c2->id, 'titulo' => 'ZZ-PROP-DOS', 'estado' => 'ENVIADA']);
        RevisionPropuesta::dictaminar($p1, $directivo, 'APROBAR');
        RevisionPropuesta::dictaminar($p2, $directivo, 'DEVOLVER', 'Falta el presupuesto detallado');

        $this->actingAs($directivo);
        Filament::setCurrentPanel(Filament::getPanel('directivo'));

        Livewire::test(PropuestasPorRevisar::class)
            ->call('cambiarSeccion', 'historial')
            ->assertSee('ZZ-PROP-UNO')
            ->assertSee('ZZ-PROP-DOS')
            ->assertSee('Falta el presupuesto detallado')
            ->set('convocatoria', (string) $c1->id)
            ->assertSee('ZZ-PROP-UNO')
            ->assertDontSee('ZZ-PROP-DOS')
            ->set('convocatoria', '')
            ->set('accion', 'DEVOLVER')
            ->assertSee('ZZ-PROP-DOS')
            ->assertDontSee('ZZ-PROP-UNO');
    }
}
