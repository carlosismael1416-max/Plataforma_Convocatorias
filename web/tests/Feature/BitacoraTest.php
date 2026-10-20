<?php

namespace Tests\Feature;

use App\Services\Bitacora;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class BitacoraTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_error_repetido_no_se_duplica(): void
    {
        Bitacora::registrar('WEB', 'Fallo de prueba', url: 'http://x/a');
        Bitacora::registrar('WEB', 'Fallo de prueba', url: 'http://x/a');
        Bitacora::registrar('WEB', 'Otro fallo', url: 'http://x/a');

        $this->assertSame(2, DB::table('bitacora_errores')->count());
        $this->assertStringContainsString('Se repitió', (string) DB::table('bitacora_errores')->where('mensaje', 'Fallo de prueba')->value('detalle'));
    }

    public function test_los_fallos_de_la_web_quedan_en_la_bitacora(): void
    {
        report(new RuntimeException('ZZ fallo del servidor'));

        $fila = DB::table('bitacora_errores')->where('tipo_error', 'WEB')->first();
        $this->assertNotNull($fila);
        $this->assertStringContainsString('ZZ fallo del servidor', $fila->mensaje);
    }

    public function test_la_pagina_muestra_las_incidencias_con_su_origen(): void
    {
        Bitacora::registrar('REVALIDACION', 'ReadTimeout: ciencia.yucatan.gob.mx', url: 'https://ciencia.yucatan.gob.mx/x');

        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'))
            ->get('/admin/bitacora-errores')
            ->assertOk()
            ->assertSee('Motor · Revalidación')
            ->assertSee('ReadTimeout: ciencia.yucatan.gob.mx')
            ->assertDontSee('No se generan incidencias ficticias');
    }
}
