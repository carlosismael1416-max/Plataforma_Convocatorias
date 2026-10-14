<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PermisosTest extends TestCase
{
    use RefreshDatabase;

    public static function panelesAjenos(): array
    {
        return [
            'docente en admin' => ['DOCENTE', '/admin'],
            'docente en gestión de convocatorias' => ['DOCENTE', '/admin/convocatorias'],
            'docente en directivo' => ['DOCENTE', '/directivo'],
            'directivo en usuarios del admin' => ['DIRECTIVO', '/admin/users'],
            'admin en docente' => ['ADMINISTRADOR', '/docente'],
        ];
    }

    #[DataProvider('panelesAjenos')]
    public function test_cada_rol_recibe_403_fuera_de_su_panel(string $rol, string $ruta): void
    {
        $this->actingAs($this->usuarioConRol($rol))
            ->get($ruta)
            ->assertForbidden();
    }

    public function test_un_usuario_inactivo_no_entra_a_su_panel(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE', activo: false))
            ->get('/docente')
            ->assertForbidden();
    }

    public function test_cada_rol_entra_a_su_panel(): void
    {
        foreach (['ADMINISTRADOR' => '/admin', 'DIRECTIVO' => '/directivo', 'DOCENTE' => '/docente'] as $rol => $ruta) {
            $this->refreshApplication();
            $this->actingAs($this->usuarioConRol($rol))->get($ruta)->assertOk();
        }
    }

    public function test_docente_no_edita_convocatoria_subida_por_otro(): void
    {
        $otro = $this->usuarioConRol('DOCENTE');
        $ajena = $this->convocatoria([
            'estado' => 'PENDIENTE_REVISION',
            'subido_por_user_id' => $otro->id,
        ]);

        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get("/docente/convocatorias/{$ajena->id}/edit")
            ->assertNotFound();
    }

    public function test_docente_no_ve_detalle_de_convocatoria_no_publicada(): void
    {
        $pendiente = $this->convocatoria(['estado' => 'PENDIENTE_REVISION']);

        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get("/docente/detalle-convocatoria/{$pendiente->id}")
            ->assertNotFound();
    }

    public function test_descubrimientos_son_de_solo_lectura(): void
    {
        $admin = $this->usuarioConRol('ADMINISTRADOR');

        $this->actingAs($admin)->get('/admin/descubrimiento-webs')->assertOk();
        $this->actingAs($admin)->get('/admin/descubrimiento-webs/create')->assertNotFound();
    }

    public function test_un_id_no_numerico_da_404_y_no_500(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'))
            ->get('/admin/convocatorias/abc')
            ->assertNotFound();
    }
}
