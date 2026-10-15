<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\Notificaciones;
use App\Models\Notificacion;
use App\Models\NotificacionPreferencia;
use App\Models\Propuesta;
use App\Models\UsuarioConvocatoria;
use App\Services\Avisos;
use App\Services\RevisionPropuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AvisosTest extends TestCase
{
    use RefreshDatabase;

    private function guardar(int $usuarioId, int $convocatoriaId): void
    {
        UsuarioConvocatoria::create([
            'user_id' => $usuarioId,
            'convocatoria_id' => $convocatoriaId,
            'estado_seguimiento' => 'GUARDADA',
            'prioridad' => 'MEDIA',
            'es_favorita' => false,
        ]);
    }

    public function test_avisa_una_sola_vez_del_cierre_de_convocatorias_guardadas(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $cerca = $this->convocatoria(['titulo' => 'ZZ-CERCA', 'fecha_cierre' => today()->addDays(3)]);
        $lejos = $this->convocatoria(['titulo' => 'ZZ-LEJOS', 'fecha_cierre' => today()->addDays(40)]);
        $this->guardar($docente->id, $cerca->id);
        $this->guardar($docente->id, $lejos->id);

        Avisos::generarAvisosDeCierre($docente);
        Avisos::generarAvisosDeCierre($docente);

        $avisos = Notificacion::query()->where('user_id', $docente->id)->where('tipo', 'CONVOCATORIA_POR_CERRAR');
        $this->assertSame(1, $avisos->count());
        $this->assertSame($cerca->id, $avisos->value('convocatoria_id'));
    }

    public function test_avisa_de_propuestas_sin_enviar_cuya_convocatoria_cierra(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $propuesta = Propuesta::create([
            'user_id' => $docente->id,
            'convocatoria_id' => $this->convocatoria(['fecha_cierre' => today()->addDays(2)])->id,
            'titulo' => 'ZZ-SIN-ENVIAR',
            'estado' => 'EDITANDO',
        ]);

        Avisos::generarAvisosDeCierre($docente);

        $this->assertTrue(
            Notificacion::query()
                ->where('user_id', $docente->id)
                ->where('tipo', 'PROPUESTA_POR_VENCER')
                ->where('propuesta_id', $propuesta->id)
                ->exists()
        );
    }

    public function test_respeta_la_preferencia_de_fechas_y_de_propuestas(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');
        NotificacionPreferencia::create([
            'user_id' => $docente->id,
            'fechas_proximas' => false,
            'nuevas_propuestas' => false,
        ]);

        $c = $this->convocatoria(['fecha_cierre' => today()->addDays(3)]);
        $this->guardar($docente->id, $c->id);
        Avisos::generarAvisosDeCierre($docente);

        $propuesta = Propuesta::create([
            'user_id' => $docente->id,
            'convocatoria_id' => $c->id,
            'titulo' => 'ZZ-SIN-AVISO',
            'estado' => 'ENVIADA',
        ]);
        RevisionPropuesta::dictaminar($propuesta, $directivo, 'APROBAR');

        $this->assertSame(0, Notificacion::query()->where('user_id', $docente->id)->count());
        // El dictamen queda en el historial aunque no se notifique.
        $this->assertSame(1, $propuesta->revisiones()->count());
    }

    public function test_el_docente_cambia_sus_preferencias(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        Livewire::test(Notificaciones::class)
            ->assertSee('Fechas de cierre')
            ->assertDontSee('Avisos por correo')
            ->call('alternarPreferencia', 'fechas_proximas')
            ->call('alternarPreferencia', 'avisos_sistema');

        $this->assertFalse(Avisos::quiere($docente->id, 'fechas_proximas'));
        $this->assertTrue(Avisos::quiere($docente->id, 'nuevas_propuestas'));
        $this->assertFalse(Avisos::quiere($docente->id, 'avisos_sistema'));
    }

    public function test_subir_una_convocatoria_avisa_a_los_directivos(): void
    {
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $docente = $this->usuarioConRol('DOCENTE');

        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        Livewire::test(\App\Filament\Docente\Resources\Convocatorias\Pages\CreateConvocatoria::class)
            ->fillForm([
                'titulo' => 'ZZ-SUBIDA',
                'descripcion' => 'Descripción',
                'moneda' => 'MXN',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertTrue(
            Notificacion::query()
                ->where('user_id', $directivo->id)
                ->where('tipo', 'CONVOCATORIA_REVISION')
                ->exists()
        );
    }
}
