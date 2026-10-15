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
                'Dashboard', 'Convocatorias por revisar', 'Buscar convocatorias',
                'Subir convocatoria', 'Estadísticas', 'Reportes', 'Notificaciones',
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
}
