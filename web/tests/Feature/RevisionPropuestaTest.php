<?php

namespace Tests\Feature;

use App\Filament\Directivo\Pages\RevisarPropuesta;
use App\Filament\Docente\Pages\VistaPreviaPropuesta;
use App\Models\CronogramaActividad;
use App\Models\Notificacion;
use App\Models\PresupuestoItem;
use App\Models\Propuesta;
use App\Models\PropuestaEntregable;
use App\Models\PropuestaObjetivo;
use App\Models\PropuestaRevision;
use App\Models\User;
use App\Services\RevisionPropuesta;
use Filament\Facades\Filament;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Livewire\Livewire;
use Tests\TestCase;

class RevisionPropuestaTest extends TestCase
{
    use RefreshDatabase;

    private function propuesta(User $autor, bool $completa = true, string $estado = 'EDITANDO'): Propuesta
    {
        $propuesta = Propuesta::create([
            'user_id' => $autor->id,
            'convocatoria_id' => $this->convocatoria(['fecha_cierre' => today()->addDays(20)])->id,
            'titulo' => 'ZZ-PROPUESTA',
            'resumen' => 'Resumen',
            'justificacion' => 'Justificación',
            'metodologia' => $completa ? 'Metodología' : null,
            'impacto_esperado' => 'Impacto',
            'estado' => $estado,
        ]);

        if ($completa) {
            PropuestaObjetivo::create(['propuesta_id' => $propuesta->id, 'tipo' => 'GENERAL', 'descripcion' => 'General', 'orden' => 1]);
            PropuestaObjetivo::create(['propuesta_id' => $propuesta->id, 'tipo' => 'ESPECIFICO', 'descripcion' => 'Específico', 'orden' => 2]);
            PresupuestoItem::create(['propuesta_id' => $propuesta->id, 'concepto' => 'Equipo', 'cantidad' => 1, 'precio_unitario' => 1000, 'moneda' => 'MXN']);
            CronogramaActividad::create(['propuesta_id' => $propuesta->id, 'actividad' => 'Inicio', 'fecha_inicio' => today(), 'fecha_fin' => today()->addMonth(), 'orden' => 1]);
            PropuestaEntregable::create(['propuesta_id' => $propuesta->id, 'nombre' => 'Informe']);
        }

        return $propuesta;
    }

    public function test_el_directivo_tiene_las_paginas_de_trabajo_del_docente(): void
    {
        $this->actingAs($this->usuarioConRol('DIRECTIVO'));

        foreach (['mis-convocatorias', 'calendario', 'propuestas', 'generar-propuesta', 'propuestas-por-revisar'] as $pagina) {
            $this->get("/directivo/{$pagina}")->assertOk();
        }
    }

    public function test_cada_rol_solo_entra_a_su_panel(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get('/directivo/propuestas')
            ->assertForbidden();

        $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/docente/propuestas')
            ->assertForbidden();
    }

    public function test_el_autor_envia_su_propuesta_completa_y_se_avisa_a_los_directivos(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $propuesta = $this->propuesta($docente);

        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        Livewire::withQueryParams(['propuesta' => $propuesta->id])
            ->test(VistaPreviaPropuesta::class)
            ->assertSee('Enviar a revisión')
            ->call('enviarARevision');

        $this->assertSame('ENVIADA', $propuesta->fresh()->estado);
        $this->assertNotNull($propuesta->fresh()->fecha_envio);
        $this->assertTrue(
            Notificacion::query()
                ->where('user_id', $directivo->id)
                ->where('propuesta_id', $propuesta->id)
                ->where('tipo', 'PROPUESTA_ENVIADA')
                ->exists()
        );
    }

    public function test_no_se_envia_incompleta_ni_ajena(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $otro = $this->usuarioConRol('DOCENTE');

        try {
            RevisionPropuesta::enviar($this->propuesta($docente, completa: false), $docente);
            $this->fail('Se envió una propuesta incompleta.');
        } catch (ValidationException) {
        }

        $this->expectException(AuthorizationException::class);
        RevisionPropuesta::enviar($this->propuesta($docente), $otro);
    }

    public function test_el_directivo_aprueba_devuelve_o_rechaza_y_el_autor_recibe_las_observaciones(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');

        $aprobada = $this->propuesta($docente, estado: 'ENVIADA');
        RevisionPropuesta::dictaminar($aprobada, $directivo, 'APROBAR');
        $this->assertSame('APROBADA', $aprobada->fresh()->estado);

        $devuelta = $this->propuesta($docente, estado: 'ENVIADA');
        RevisionPropuesta::dictaminar($devuelta, $directivo, 'DEVOLVER', 'Falta detallar el presupuesto.');
        $this->assertSame('EDITANDO', $devuelta->fresh()->estado);
        $this->assertStringContainsString(
            'Falta detallar el presupuesto.',
            Notificacion::query()->where('propuesta_id', $devuelta->id)->value('mensaje')
        );

        $rechazada = $this->propuesta($docente, estado: 'ENVIADA');
        $this->expectException(ValidationException::class);
        RevisionPropuesta::dictaminar($rechazada, $directivo, 'RECHAZAR');
    }

    public function test_solo_un_directivo_dictamina_y_solo_propuestas_enviadas(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');

        try {
            RevisionPropuesta::dictaminar($this->propuesta($docente, estado: 'ENVIADA'), $docente, 'APROBAR');
            $this->fail('Un docente dictaminó.');
        } catch (AuthorizationException) {
        }

        $this->expectException(ValidationException::class);
        RevisionPropuesta::dictaminar($this->propuesta($docente, estado: 'EDITANDO'), $directivo, 'APROBAR');
    }

    public function test_el_directivo_revisa_tambien_sus_propias_propuestas(): void
    {
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $propia = $this->propuesta($directivo);

        RevisionPropuesta::enviar($propia, $directivo);

        $this->actingAs($directivo)
            ->get('/directivo/propuestas-por-revisar')
            ->assertOk()
            ->assertSee('ZZ-PROPUESTA')
            ->assertSee('Tuya');

        Filament::setCurrentPanel(Filament::getPanel('directivo'));

        Livewire::withQueryParams(['propuesta' => $propia->id])
            ->test(RevisarPropuesta::class)
            ->assertSee('Dictamen')
            ->call('aprobar');

        $this->assertSame('APROBADA', $propia->fresh()->estado);
    }

    public function test_la_revision_no_abre_borradores_ajenos(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $borrador = $this->propuesta($docente, estado: 'EDITANDO');

        $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/directivo/revisar-propuesta?propuesta=' . $borrador->id)
            ->assertNotFound();

        $this->actingAs($docente)
            ->get('/directivo/revisar-propuesta?propuesta=' . $borrador->id)
            ->assertForbidden();
    }

    public function test_el_historial_guarda_envio_y_dictamen_con_observaciones(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $propuesta = $this->propuesta($docente);

        RevisionPropuesta::enviar($propuesta, $docente);
        RevisionPropuesta::dictaminar($propuesta, $directivo, 'DEVOLVER', 'Ajusta el cronograma.');

        $historial = PropuestaRevision::query()
            ->where('propuesta_id', $propuesta->id)
            ->orderBy('id')
            ->get(['user_id', 'accion', 'estado_anterior', 'estado_nuevo', 'observaciones'])
            ->toArray();

        $this->assertSame([
            ['user_id' => $docente->id, 'accion' => 'ENVIAR', 'estado_anterior' => 'EDITANDO', 'estado_nuevo' => 'ENVIADA', 'observaciones' => null],
            ['user_id' => $directivo->id, 'accion' => 'DEVOLVER', 'estado_anterior' => 'ENVIADA', 'estado_nuevo' => 'EDITANDO', 'observaciones' => 'Ajusta el cronograma.'],
        ], $historial);

        // El autor ve las observaciones al abrir el editor.
        $this->actingAs($docente)
            ->get('/docente/editor-propuesta?propuesta=' . $propuesta->id)
            ->assertOk()
            ->assertSee('Propuesta devuelta con observaciones')
            ->assertSee('Ajusta el cronograma.');
    }

    public function test_un_dictamen_fallido_no_deja_historial(): void
    {
        $directivo = $this->usuarioConRol('DIRECTIVO');
        $propuesta = $this->propuesta($this->usuarioConRol('DOCENTE'), estado: 'ENVIADA');

        try {
            RevisionPropuesta::dictaminar($propuesta, $directivo, 'RECHAZAR', '');
        } catch (ValidationException) {
        }

        $this->assertSame(0, PropuestaRevision::query()->count());
        $this->assertSame('ENVIADA', $propuesta->fresh()->estado);
    }

    public function test_las_estadisticas_del_dashboard_directivo_son_enlaces(): void
    {
        $respuesta = $this->actingAs($this->usuarioConRol('DIRECTIVO'))
            ->get('/directivo')
            ->assertOk();

        foreach (['convocatorias-por-revisar', 'propuestas-por-revisar', 'buscar-convocatorias'] as $seccion) {
            $respuesta->assertSee('href="' . url("/directivo/{$seccion}") . '" class="dd-card dd-stat"', false);
        }
    }
}
