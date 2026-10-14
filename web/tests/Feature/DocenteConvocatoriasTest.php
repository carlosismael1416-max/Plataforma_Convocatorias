<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteConvocatoriasTest extends TestCase
{
    use RefreshDatabase;

    private function crearCasos(): void
    {
        $this->convocatoria(['titulo' => 'ZZ-VENCIDA', 'fecha_cierre' => today()->subDay()]);
        $this->convocatoria(['titulo' => 'ZZ-VIGENTE', 'fecha_cierre' => today()->addDays(20)]);
        $this->convocatoria(['titulo' => 'ZZ-SIN-FECHA', 'fecha_cierre' => null]);
        $this->convocatoria([
            'titulo' => 'ZZ-PENDIENTE',
            'estado' => 'PENDIENTE_REVISION',
            'fecha_cierre' => today()->addDays(20),
        ]);
    }

    public function test_el_buscador_oculta_vencidas_y_no_publicadas(): void
    {
        $this->crearCasos();

        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get('/docente/buscar-convocatorias')
            ->assertOk()
            ->assertSee('ZZ-VIGENTE')
            ->assertSee('ZZ-SIN-FECHA')
            ->assertDontSee('ZZ-VENCIDA')
            ->assertDontSee('ZZ-PENDIENTE');
    }

    public function test_el_dashboard_oculta_vencidas(): void
    {
        $this->crearCasos();

        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get('/docente')
            ->assertOk()
            ->assertSee('ZZ-VIGENTE')
            ->assertDontSee('ZZ-VENCIDA')
            ->assertDontSee('ZZ-PENDIENTE');
    }
}
