<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AparienciaTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Etiquetas del menú lateral, en orden, según el diseño de Figma.
     */
    public static function menus(): array
    {
        return [
            'docente' => ['DOCENTE', '/docente', [
                'Dashboard', 'Buscar convocatorias', 'Mis convocatorias',
                'Subir convocatoria', 'Calendario', 'Propuestas', 'Notificaciones',
            ]],
            'directivo' => ['DIRECTIVO', '/directivo', [
                'Dashboard', 'Propuestas por revisar', 'Buscar convocatorias', 'Mis convocatorias', 'Subir convocatoria',
                'Calendario', 'Propuestas', 'Estadísticas', 'Reportes', 'Notificaciones',
            ]],
            'admin' => ['ADMINISTRADOR', '/admin', [
                'Dashboard', 'Usuarios', 'Roles y permisos', 'Fuentes web',
                'Motor de Extracción', 'Descubrimientos', 'Bitácora de Errores', 'Convocatorias',
            ]],
        ];
    }

    #[DataProvider('menus')]
    public function test_menu_lateral_en_el_orden_del_diseno(string $rol, string $panel, array $esperado): void
    {
        $html = $this->actingAs($this->usuarioConRol($rol))
            ->get($panel)
            ->assertOk()
            ->getContent();

        preg_match_all('/<span[^>]*class="[^"]*fi-sidebar-item-label[^"]*"[^>]*>\s*(.*?)\s*<\/span>/s', $html, $m);

        $this->assertSame($esperado, array_map(fn ($t) => html_entity_decode(trim(strip_tags($t))), $m[1]));
    }

    public function test_el_marco_sigue_el_diseno_y_esta_en_espanol(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');

        $html = $this->actingAs($docente)->get('/docente')->assertOk()->getContent();

        $this->assertStringContainsString('class="itsva-logo"', $html);
        $this->assertStringContainsString('class="itsva-header"', $html);
        $this->assertStringContainsString('Buscar convocatorias...', $html);
        $this->assertStringContainsString('Mi perfil', $html);
        $this->assertStringContainsString('Cerrar sesión', $html);
        $this->assertStringContainsString($docente->email, $html);
        // El avatar se genera localmente: el nombre no sale a ui-avatars.com.
        $this->assertStringNotContainsString('ui-avatars.com', $html);
        $this->assertStringContainsString('data:image/svg+xml;base64,', $html);
    }

    public function test_el_perfil_no_esta_en_la_sidebar_pero_abre_desde_el_menu(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'));

        $this->get('/docente/perfil')->assertOk();
    }

    public function test_cerrar_sesion_desde_el_header(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->post(filament()->getPanel('docente')->getLogoutUrl())
            ->assertRedirect('/login');

        $this->assertGuest();
    }

    public function test_paginas_fuera_del_menu_muestran_su_titulo_una_sola_vez(): void
    {
        $convocatoria = $this->convocatoria(['fecha_cierre' => today()->addDays(10)]);
        $this->actingAs($this->usuarioConRol('DOCENTE'));

        $html = $this->get("/docente/detalle-convocatoria/{$convocatoria->id}")->assertOk()->getContent();

        $this->assertStringContainsString('<h1 class="itsva-header-titulo">Detalle de convocatoria</h1>', $html);
        $this->assertDoesNotMatchRegularExpression('/<h1[^>]*fi-header-heading/', $html);
    }

    public function test_subir_convocatoria_agrupa_el_formulario_y_explica_la_publicacion(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'));

        $this->get('/docente/convocatorias/create')
            ->assertOk()
            ->assertSeeInOrder(['Información básica', 'Descripción y objetivo', 'Fechas y apoyo económico'])
            ->assertSee('Publicar convocatoria')
            ->assertSee('Se publica al momento');
    }

    public function test_la_paginacion_esta_en_espanol_y_con_el_estilo_propio(): void
    {
        foreach (range(1, 12) as $i) {
            $this->convocatoria(['titulo' => "ZZ-PAGINA-{$i}", 'fecha_cierre' => today()->addDays(10)]);
        }

        $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/directivo/buscar-convocatorias')
            ->assertOk()
            ->assertSee('itsva-paginacion', false)
            ->assertSee('Siguiente ›')
            ->assertDontSee('Showing');
    }

    public function test_reportes_no_muestra_el_boton_de_prueba(): void
    {
        $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/directivo/reportes')
            ->assertOk()
            ->assertDontSee('Probar interacción')
            ->assertDontSee('PostgreSQL');
    }

    public function test_el_directivo_solo_ve_convocatorias_publicadas_o_cerradas(): void
    {
        $this->convocatoria(['titulo' => 'ZZ-VISIBLE', 'fecha_cierre' => today()->addDays(10)]);
        $interna = $this->convocatoria(['titulo' => 'ZZ-INTERNA', 'estado' => 'PENDIENTE_REVISION']);

        $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/directivo/buscar-convocatorias')
            ->assertOk()
            ->assertSee('ZZ-VISIBLE')
            ->assertDontSee('ZZ-INTERNA');

        $this->get("/directivo/detalle-convocatoria/{$interna->id}")->assertNotFound();
    }
}
