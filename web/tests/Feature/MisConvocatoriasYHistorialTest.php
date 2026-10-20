<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\BuscarConvocatorias;
use App\Filament\Docente\Pages\DetalleConvocatoria;
use App\Filament\Docente\Pages\HistorialConvocatorias;
use App\Filament\Docente\Pages\MisConvocatorias;
use App\Models\Propuesta;
use App\Models\UsuarioConvocatoria;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class MisConvocatoriasYHistorialTest extends TestCase
{
    use RefreshDatabase;

    private function comoDocente(): \App\Models\User
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        return $docente;
    }

    public function test_guardar_marcar_favorita_y_quitar_desde_el_detalle(): void
    {
        $docente = $this->comoDocente();
        $c = $this->convocatoria(['fecha_cierre' => today()->addDays(10)]);

        $pagina = Livewire::test(DetalleConvocatoria::class, ['record' => $c->id])
            ->assertSet('guardada', false)
            ->call('alternarFavorita', $c->id)
            ->assertSet('guardada', true)
            ->assertSet('favorita', true)
            ->assertSee('Favorita · Quitar');

        $this->assertTrue(UsuarioConvocatoria::where('user_id', $docente->id)->where('es_favorita', true)->exists());

        $pagina->call('quitarDeMis', $c->id)
            ->assertSet('guardada', false)
            ->assertSet('favorita', false);

        $this->assertFalse(UsuarioConvocatoria::where('user_id', $docente->id)->exists());
    }

    public function test_desde_el_buscador_se_guarda_sin_marcar_favorita(): void
    {
        $docente = $this->comoDocente();
        $c = $this->convocatoria(['fecha_cierre' => today()->addDays(10)]);

        Livewire::test(BuscarConvocatorias::class)->call('guardarEnMis', $c->id);

        $this->assertFalse((bool) UsuarioConvocatoria::where('user_id', $docente->id)->value('es_favorita'));
    }

    public function test_no_se_guarda_una_convocatoria_cerrada(): void
    {
        $docente = $this->comoDocente();
        $c = $this->convocatoria(['estado' => 'CERRADA', 'fecha_cierre' => today()->subDays(3)]);

        Livewire::test(BuscarConvocatorias::class)->call('alternarFavorita', $c->id);

        $this->assertFalse(UsuarioConvocatoria::where('user_id', $docente->id)->exists());
    }

    public function test_la_pestana_favoritas_solo_muestra_favoritas(): void
    {
        $docente = $this->comoDocente();
        $fav = $this->convocatoria(['titulo' => 'ZZ-FAVORITA', 'fecha_cierre' => today()->addDays(9)]);
        $otra = $this->convocatoria(['titulo' => 'ZZ-NORMAL', 'fecha_cierre' => today()->addDays(9)]);

        foreach ([[$fav, true], [$otra, false]] as [$c, $esFav]) {
            UsuarioConvocatoria::create([
                'user_id' => $docente->id, 'convocatoria_id' => $c->id,
                'estado_seguimiento' => 'GUARDADA', 'prioridad' => 'MEDIA', 'es_favorita' => $esFav,
            ]);
        }

        Livewire::test(MisConvocatorias::class)
            ->assertSee('ZZ-NORMAL')
            ->call('cambiarVista', 'favoritas')
            ->assertSee('ZZ-FAVORITA')
            ->assertDontSee('ZZ-NORMAL');
    }

    public function test_el_historial_muestra_cerradas_y_su_detalle_es_de_consulta(): void
    {
        $this->comoDocente();
        $cerrada = $this->convocatoria(['titulo' => 'ZZ-CERRADA', 'estado' => 'CERRADA', 'fecha_cierre' => today()->subDays(20)]);
        $this->convocatoria(['titulo' => 'ZZ-VIGENTE', 'fecha_cierre' => today()->addDays(5)]);

        Livewire::test(HistorialConvocatorias::class)
            ->assertSee('ZZ-CERRADA')
            ->assertDontSee('ZZ-VIGENTE');

        Livewire::test(DetalleConvocatoria::class, ['record' => $cerrada->id])
            ->assertSet('disponible', false)
            ->assertSee('solo de consulta')
            ->assertDontSee('Generar propuesta');

        foreach (['/docente', '/directivo'] as $i => $ruta) {
            $rol = ['DOCENTE', 'DIRECTIVO'][$i];
            $this->actingAs($this->usuarioConRol($rol))
                ->get($ruta . '/historial-convocatorias')
                ->assertOk()
                ->assertSee('ZZ-CERRADA');
        }
    }

    public function test_el_historial_es_una_pestana_de_buscar_convocatorias(): void
    {
        foreach (['DOCENTE' => '/docente', 'DIRECTIVO' => '/directivo'] as $rol => $panel) {
            $this->actingAs($this->usuarioConRol($rol))
                ->get($panel . '/buscar-convocatorias')
                ->assertOk()
                ->assertSee('Historial (cerradas)')
                ->assertSee($panel . '/historial-convocatorias', false)
                ->assertSee('Tipo')
                ->assertSee('Dirigida a');
        }
    }

    public function test_mis_propuestas_muestra_el_titulo_de_la_convocatoria_y_no_json(): void
    {
        $docente = $this->comoDocente();
        $c = $this->convocatoria(['titulo' => 'ZZ-CONVOCATORIA', 'fecha_cierre' => today()->addDays(5)]);
        Propuesta::create(['user_id' => $docente->id, 'convocatoria_id' => $c->id, 'titulo' => 'ZZ-PROPUESTA', 'estado' => 'EDITANDO']);

        $this->get('/docente/propuestas')
            ->assertOk()
            ->assertSee('ZZ-CONVOCATORIA')
            ->assertSee('ZZ-PROPUESTA')
            ->assertDontSee('"titulo":', false)
            ->assertSee('Cierra en 5 días');
    }
}
