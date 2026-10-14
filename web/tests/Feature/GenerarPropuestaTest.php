<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\GenerarPropuesta;
use App\Models\Organismo;
use App\Models\Propuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GenerarPropuestaTest extends TestCase
{
    use RefreshDatabase;

    private function convocatoriaCompleta(array $atributos = [])
    {
        $organismo = Organismo::query()->forceCreate([
            'nombre' => 'SECIHTI',
            'tipo_organismo' => 'FEDERAL',
            'estado' => true,
        ]);

        return $this->convocatoria($atributos + [
            'titulo' => 'Convocatoria de revistas de acceso abierto 2026',
            'descripcion' => 'La Secretaría, con fundamento en los artículos 3° y 134...',
            'objetivo' => 'Impulsar revistas nacionales de acceso abierto.',
            'fecha_cierre' => today()->addDays(10),
            'organismo_id' => $organismo->id,
        ]);
    }

    public function test_desde_el_detalle_la_convocatoria_llega_preseleccionada(): void
    {
        $convocatoria = $this->convocatoriaCompleta();

        $html = $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get("/docente/generar-propuesta?convocatoria={$convocatoria->id}")
            ->assertOk()
            ->assertSee('value="Propuesta - ' . $convocatoria->titulo . '"', false)
            ->getContent();

        $this->assertMatchesRegularExpression(
            '/<option\s+value="' . $convocatoria->id . '"\s+selected/',
            $html
        );
    }

    public function test_si_ya_hay_propuesta_va_directo_a_ella(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $convocatoria = $this->convocatoriaCompleta();
        $propuesta = Propuesta::query()->forceCreate([
            'user_id' => $docente->id,
            'convocatoria_id' => $convocatoria->id,
            'titulo' => 'Mi propuesta',
        ]);

        $this->actingAs($docente)
            ->get("/docente/generar-propuesta?convocatoria={$convocatoria->id}")
            ->assertRedirect(route('filament.docente.pages.editor-propuesta', ['propuesta' => $propuesta->id]));
    }

    public function test_al_crear_se_rellena_con_datos_de_la_convocatoria(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $convocatoria = $this->convocatoriaCompleta();

        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        Livewire::withQueryParams(['convocatoria' => $convocatoria->id])
            ->test(GenerarPropuesta::class)
            ->assertSet('convocatoriaId', (string) $convocatoria->id)
            ->call('crearOContinuar')
            ->assertRedirect();

        $propuesta = Propuesta::query()
            ->where('user_id', $docente->id)
            ->where('convocatoria_id', $convocatoria->id)
            ->firstOrFail();

        $this->assertSame('Propuesta - ' . $convocatoria->titulo, $propuesta->titulo);
        $this->assertSame($convocatoria->objetivo, $propuesta->resumen);
        $this->assertStringContainsString($convocatoria->titulo, $propuesta->justificacion);
        $this->assertStringContainsString('SECIHTI', $propuesta->justificacion);
        $this->assertStringContainsString($convocatoria->fecha_cierre->format('d/m/Y'), $propuesta->justificacion);
        $this->assertStringContainsString($convocatoria->objetivo, $propuesta->justificacion);
    }

    public function test_no_preselecciona_una_convocatoria_no_publicada(): void
    {
        $pendiente = $this->convocatoriaCompleta(['estado' => 'PENDIENTE_REVISION']);

        $this->actingAs($this->usuarioConRol('DOCENTE'));
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        Livewire::withQueryParams(['convocatoria' => $pendiente->id])
            ->test(GenerarPropuesta::class)
            ->assertSet('convocatoriaId', '');
    }
}
