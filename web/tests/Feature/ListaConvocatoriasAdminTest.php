<?php

namespace Tests\Feature;

use App\Filament\Resources\Convocatorias\Pages\ListConvocatorias;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListaConvocatoriasAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_tabla_incluye_todos_los_estados_y_las_tarjetas_filtran(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $vigente = $this->convocatoria(['titulo' => 'ZZ-VIGENTE', 'fecha_cierre' => today()->addDays(5)]);
        $interna = $this->convocatoria(['titulo' => 'ZZ-INTERNA', 'estado' => 'PENDIENTE_REVISION']);
        $cerrada = $this->convocatoria(['titulo' => 'ZZ-CERRADA', 'estado' => 'CERRADA', 'fecha_cierre' => today()->subDays(5)]);

        Livewire::test(ListConvocatorias::class)
            ->assertCanSeeTableRecords([$vigente, $interna, $cerrada])
            ->set('activeTab', 'vigentes')
            ->assertCanSeeTableRecords([$vigente])
            ->assertCanNotSeeTableRecords([$interna, $cerrada])
            ->set('activeTab', 'cerradas')
            ->assertCanSeeTableRecords([$cerrada])
            ->assertCanNotSeeTableRecords([$vigente, $interna])
            ->set('activeTab', 'no_publicadas')
            ->assertCanSeeTableRecords([$interna])
            ->assertCanNotSeeTableRecords([$vigente, $cerrada]);

        $this->get('/admin/historial-convocatorias')->assertNotFound();
    }
}
