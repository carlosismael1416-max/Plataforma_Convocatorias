<?php

namespace Tests\Feature;

use App\Filament\Docente\Pages\VistaPreviaPropuesta;
use App\Models\Propuesta;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;
use Tests\TestCase;

class PropuestaBloqueadaTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_navegador_no_puede_cambiar_propuesta_id(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');
        $propia = Propuesta::query()->forceCreate([
            'user_id' => $docente->id,
            'convocatoria_id' => $this->convocatoria()->id,
            'titulo' => 'Propuesta propia',
        ]);

        $this->actingAs($docente);
        Filament::setCurrentPanel(Filament::getPanel('docente'));

        $this->expectException(CannotUpdateLockedPropertyException::class);

        Livewire::withQueryParams(['propuesta' => $propia->id])
            ->test(VistaPreviaPropuesta::class)
            ->set('propuestaId', $propia->id + 1);
    }

    public function test_no_se_monta_la_propuesta_de_otro_docente(): void
    {
        $otro = $this->usuarioConRol('DOCENTE');
        $ajena = Propuesta::query()->forceCreate([
            'user_id' => $otro->id,
            'convocatoria_id' => $this->convocatoria()->id,
            'titulo' => 'Propuesta ajena',
        ]);

        $this->actingAs($this->usuarioConRol('DOCENTE'))
            ->get("/docente/vista-previa-propuesta?propuesta={$ajena->id}")
            ->assertNotFound();
    }
}
