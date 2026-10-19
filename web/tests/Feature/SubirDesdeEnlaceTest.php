<?php

namespace Tests\Feature;

use App\Filament\Docente\Resources\Convocatorias\Pages\CreateConvocatoria;
use App\Models\Convocatoria;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class SubirDesdeEnlaceTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'https://www.secihti.mx/convocatoria/ciencias/zz-prueba-2026';

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs($this->usuarioConRol('DOCENTE'));
        Filament::setCurrentPanel(Filament::getPanel('docente'));
    }

    private function respuesta(bool $publicable, ?array $existente = null): array
    {
        return [
            'url_final' => self::URL,
            'fuente' => 'SECIHTI',
            'existente' => $existente,
            'datos' => [
                'titulo' => 'ZZ-DESDE-ENLACE Convocatoria de proyectos 2026',
                'url_original' => self::URL,
                'descripcion' => 'Descripción extraída de la página oficial.',
                'objetivo' => 'Financiar proyectos de investigación.',
                'fecha_cierre' => today()->addDays(15)->toDateString(),
                'fecha_publicacion' => null,
                'fecha_inicio' => null,
                'monto_minimo' => null,
                'monto_maximo' => 500000,
                'moneda' => 'MXN',
                'organismo_id' => null,
                'categoria_id' => null,
                'fuente_id' => null,
                'tipo' => 'FINANCIAMIENTO',
                'dirigido_a' => ['INSTITUCION', 'DOCENTES'],
            ],
            'requisitos' => [],
            'validacion' => [
                'publicable' => $publicable,
                'score' => $publicable ? 90 : 55,
                'umbral' => 70,
                'razones' => [],
                'bloqueos' => $publicable ? [] : ['No existe una fecha de cierre verificable.'],
            ],
            'documentos' => [],
        ];
    }

    public function test_un_enlace_que_cumple_llena_el_formulario_y_se_publica(): void
    {
        Http::fake(['*/convocatorias/analizar-url' => Http::response($this->respuesta(true))]);

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->assertSee('Cumple los criterios')
            ->assertSet('data.titulo', 'ZZ-DESDE-ENLACE Convocatoria de proyectos 2026')
            ->call('create')
            ->assertHasNoFormErrors();

        $c = Convocatoria::where('url_original', self::URL)->firstOrFail();
        $this->assertSame('PUBLICADA', $c->estado);
        $this->assertSame('MANUAL', $c->origen);
        $this->assertSame(['INSTITUCION', 'DOCENTES'], $c->dirigido_a);
    }

    public function test_si_no_cumple_pide_confirmar_y_se_puede_publicar_de_todas_maneras(): void
    {
        Http::fake(['*/convocatorias/analizar-url' => Http::response($this->respuesta(false))]);

        $pagina = Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->assertSee('No cumple todos los criterios')
            ->assertSee('No existe una fecha de cierre verificable.')
            ->call('create')
            ->assertHasErrors(['enlace']);

        $this->assertFalse(Convocatoria::where('url_original', self::URL)->exists());

        $pagina->call('publicarDeTodasManeras')->call('create')->assertHasNoFormErrors();

        $this->assertSame('PUBLICADA', Convocatoria::where('url_original', self::URL)->value('estado'));
    }

    public function test_no_publicar_descarta_lo_analizado(): void
    {
        Http::fake(['*/convocatorias/analizar-url' => Http::response($this->respuesta(false))]);

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->call('descartarAnalisis')
            ->assertSet('analisis', null)
            ->assertSet('enlace', '')
            ->assertSet('data.titulo', null);
    }

    public function test_un_enlace_ya_publicado_no_se_duplica(): void
    {
        $existente = $this->convocatoria(['titulo' => 'ZZ-YA', 'url_original' => self::URL, 'fecha_cierre' => today()->addDays(5)]);
        Http::fake(['*/convocatorias/analizar-url' => Http::response(
            $this->respuesta(true, ['id' => $existente->id, 'titulo' => 'ZZ-YA', 'estado' => 'PUBLICADA'])
        )]);

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->assertSee('ya está en la plataforma')
            ->call('create')
            ->assertHasErrors(['data.url_original']);

        $this->assertSame(1, Convocatoria::where('url_hash', Convocatoria::hashUrl(self::URL))->count());
    }

    public function test_una_pendiente_interna_del_motor_se_publica_en_lugar_de_duplicarla(): void
    {
        $pendiente = $this->convocatoria(['titulo' => 'ZZ-PENDIENTE', 'estado' => 'PENDIENTE_REVISION', 'origen' => 'SCRAPING', 'url_original' => self::URL]);
        Http::fake(['*/convocatorias/analizar-url' => Http::response(
            $this->respuesta(true, ['id' => $pendiente->id, 'titulo' => 'ZZ-PENDIENTE', 'estado' => 'PENDIENTE_REVISION'])
        )]);

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->call('create')
            ->assertHasNoFormErrors();

        $pendiente->refresh();
        $this->assertSame('PUBLICADA', $pendiente->estado);
        $this->assertSame('ZZ-DESDE-ENLACE Convocatoria de proyectos 2026', $pendiente->titulo);
        $this->assertSame(1, Convocatoria::where('url_hash', Convocatoria::hashUrl(self::URL))->count());
    }

    public function test_motor_apagado_avisa_sin_romper_la_pagina(): void
    {
        Http::fake(['*/convocatorias/analizar-url' => Http::response(['detail' => 'El motor de extracción está apagado.'], 409)]);

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', self::URL)
            ->call('analizarEnlace')
            ->assertNotified('El motor de extracción está apagado')
            ->assertSet('analisis', null);
    }

    public function test_un_enlace_invalido_se_rechaza_sin_llamar_al_motor(): void
    {
        Http::fake();

        Livewire::test(CreateConvocatoria::class)
            ->set('enlace', 'file:///etc/passwd')
            ->call('analizarEnlace')
            ->assertHasErrors(['enlace']);

        Http::assertNothingSent();
    }
}
