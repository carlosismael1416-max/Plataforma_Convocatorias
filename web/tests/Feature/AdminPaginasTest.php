<?php

namespace Tests\Feature;

use App\Models\DescubrimientoWeb;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPaginasTest extends TestCase
{
    use RefreshDatabase;

    public function test_roles_muestra_conteos_y_permisos_reales(): void
    {
        $admin = $this->usuarioConRol('ADMINISTRADOR');
        $this->usuarioConRol('DOCENTE');

        $this->actingAs($admin)
            ->get('/admin/roles')
            ->assertOk()
            ->assertSee('Usuarios asignados')
            ->assertDontSee('se conectará en la fase de funcionalidad')
            ->assertSee('Editar permisos del rol');
    }

    public function test_el_motor_no_muestra_textos_de_fases_pendientes(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'))
            ->get('/admin/motor-extraccion')
            ->assertOk()
            ->assertSee('API del extractor')
            ->assertDontSee('Fase 3')
            ->assertDontSee('PostgreSQL');
    }

    public function test_titulo_legible_de_descubrimientos(): void
    {
        $roto = new DescubrimientoWeb(['titulo' => 'AmÃ©rico Villarreal inaugura']);
        $sinTitulo = new DescubrimientoWeb([
            'titulo' => '',
            'url_original' => 'https://ejemplo.mx/nacional/2026/10/13/convocatoria-de-investigacion-907537.html',
        ]);

        $this->assertSame('Américo Villarreal inaugura', $roto->tituloLegible());
        $this->assertSame('(sin título) Convocatoria de investigacion', $sinTitulo->tituloLegible());
    }

    public function test_sin_migas_de_pan_en_los_paneles(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'))
            ->get('/admin/users')
            ->assertOk()
            ->assertDontSee('fi-breadcrumbs', false);
    }
}
