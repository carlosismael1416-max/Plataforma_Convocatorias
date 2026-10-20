<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\EditorPropuesta;
use App\Models\Propuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EditorPropuestaTest extends TestCase
{
    use RefreshDatabase;

    private function propuesta(): Propuesta
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        return Propuesta::create([
            'user_id' => $docente->id,
            'convocatoria_id' => $this->convocatoria(['fecha_cierre' => today()->addDays(20)])->id,
            'titulo' => 'ZZ-PROPUESTA',
            'estado' => 'EDITANDO',
        ]);
    }

    public function test_no_continua_sin_los_datos_principales_pero_el_borrador_si_guarda(): void
    {
        $propuesta = $this->propuesta();

        Livewire::withQueryParams(['propuesta' => $propuesta->id])
            ->test(EditorPropuesta::class)
            ->set('resumen', 'Corto')
            ->call('continuarAObjetivos')
            ->assertHasErrors(['resumen', 'justificacion', 'metodologia', 'impactoEsperado'])
            ->assertNoRedirect()
            ->call('guardarBorrador')
            ->assertNotified('Borrador guardado');

        $this->assertSame('Corto', $propuesta->fresh()->resumen);
    }

    public function test_con_los_datos_completos_continua_a_objetivos(): void
    {
        $propuesta = $this->propuesta();
        $texto = str_repeat('Texto suficiente para la sección. ', 2);

        Livewire::withQueryParams(['propuesta' => $propuesta->id])
            ->test(EditorPropuesta::class)
            ->set('resumen', $texto)
            ->set('justificacion', $texto)
            ->set('metodologia', $texto)
            ->set('impactoEsperado', $texto)
            ->call('continuarAObjetivos')
            ->assertHasNoErrors()
            ->assertRedirectContains('objetivos-propuesta');
    }
}
