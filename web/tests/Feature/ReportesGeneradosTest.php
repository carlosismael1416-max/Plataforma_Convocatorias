<?php

namespace Tests\Feature;

use App\Filament\Directivo\Pages\Reportes;
use App\Models\ReporteGenerado;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ReportesGeneradosTest extends TestCase
{
    use RefreshDatabase;

    public function test_cada_reporte_queda_en_el_historial_y_se_puede_repetir(): void
    {
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $this->actingAs($directivo);
        Filament::setCurrentPanel(Filament::getPanel('directivo'));
        $this->convocatoria(['titulo' => 'ZZ-REPORTE', 'fecha_cierre' => today()->addDays(9)]);

        $pagina = Livewire::test(Reportes::class)
            ->assertDontSee('No se muestran reportes recientes')
            ->set('formato', 'csv')
            ->call('generarReporte');

        $primero = ReporteGenerado::sole();
        $this->assertSame($directivo->id, $primero->user_id);
        $this->assertSame('csv', $primero->formato);
        $this->assertSame(1, $primero->total);

        $pagina->set('formato', 'pdf')
            ->call('repetirReporte', $primero->id)
            ->assertSee('Reportes recientes');

        $this->assertSame(2, ReporteGenerado::count());
        $this->assertSame('csv', ReporteGenerado::latest('id')->first()->formato);
    }

    public function test_no_se_repite_el_reporte_de_otro_usuario(): void
    {
        $otro = ReporteGenerado::create([
            'user_id' => $this->usuarioConRol('DIRECTIVO')->id,
            'tipo' => 'convocatorias', 'formato' => 'csv', 'filtros' => [], 'total' => 0,
        ]);

        $this->actingAs($this->usuarioConRol('DIRECTIVO'));
        Filament::setCurrentPanel(Filament::getPanel('directivo'));

        Livewire::test(Reportes::class)->call('repetirReporte', $otro->id)->assertNotFound();
    }
}
