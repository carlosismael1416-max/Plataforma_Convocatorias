<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\VistaPreviaPropuesta;
use App\Models\Propuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExportarPropuestaTest extends TestCase
{
    use RefreshDatabase;

    private function propuesta(): Propuesta
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        return Propuesta::create([
            'user_id' => $docente->id,
            'convocatoria_id' => $this->convocatoria(['titulo' => 'ZZ-Convocatoria con acentos: ñandú', 'fecha_cierre' => today()->addDays(9)])->id,
            'titulo' => 'ZZ Propuesta de investigación',
            'resumen' => 'Resumen con acentos y eñes: investigación aplicada.',
            'estado' => 'EDITANDO',
        ]);
    }

    public function test_exportar_pdf_descarga_el_archivo(): void
    {
        $propuesta = $this->propuesta();

        Livewire::withQueryParams(['propuesta' => $propuesta->id])
            ->test(VistaPreviaPropuesta::class)
            ->call('exportarPdf')
            ->assertFileDownloaded("propuesta_{$propuesta->id}_v1.pdf");
    }

    public function test_exportar_excel_descarga_el_archivo(): void
    {
        $propuesta = $this->propuesta();

        Livewire::withQueryParams(['propuesta' => $propuesta->id])
            ->test(VistaPreviaPropuesta::class)
            ->call('exportarExcel')
            ->assertFileDownloaded("propuesta_{$propuesta->id}_v1.xlsx");
    }
}
