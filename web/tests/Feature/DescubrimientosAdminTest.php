<?php

namespace Tests\Feature;

use App\Filament\Resources\DescubrimientoWebs\Pages\ListDescubrimientoWebs;
use App\Models\DescubrimientoWeb;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Tests\TestCase;

class DescubrimientosAdminTest extends TestCase
{
    use RefreshDatabase;

    private function descubrimiento(string $estado, ?int $convocatoriaId = null): DescubrimientoWeb
    {
        $url = 'https://ejemplo.gob.mx/' . uniqid();

        $id = DB::table('descubrimientos_web')->insertGetId([
            'titulo' => "ZZ-{$estado}",
            'url_original' => $url,
            'url_normalizada' => $url,
            'url_hash' => hash('sha256', uniqid('', true)),
            'estado' => $estado,
            'convocatoria_id' => $convocatoriaId,
            'descubierto_en' => now(),
            'ultimo_visto_en' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return DescubrimientoWeb::findOrFail($id);
    }

    public function test_es_una_pestana_del_motor_y_las_tarjetas_filtran(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $convertido = $this->descubrimiento('PROCESADO', $this->convocatoria(['fecha_cierre' => today()->addDays(5)])->id);
        $descartado = $this->descubrimiento('DESCARTADO');
        $espera = $this->descubrimiento('REVISION');

        Livewire::test(ListDescubrimientoWebs::class)
            ->assertCanSeeTableRecords([$convertido, $descartado, $espera])
            ->set('activeTab', 'convertidos')
            ->assertCanSeeTableRecords([$convertido])
            ->assertCanNotSeeTableRecords([$descartado, $espera])
            ->set('activeTab', 'en_espera')
            ->assertCanSeeTableRecords([$espera])
            ->assertCanNotSeeTableRecords([$convertido, $descartado]);

        $this->get('/admin/motor-extraccion')->assertOk()->assertSee('Descubrimientos');
        $this->get('/admin/descubrimiento-webs')->assertOk()->assertSee('Motor y ejecuciones');
    }
}
