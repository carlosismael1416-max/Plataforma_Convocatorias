<?php

namespace Tests\Feature;

use App\Filament\Resources\Convocatorias\ConvocatoriaResource;
use App\Models\Convocatoria;
use App\Models\Propuesta;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ConvocatoriaDependenciasTest extends TestCase
{
    use RefreshDatabase;

    public function test_detecta_datos_relacionados(): void
    {
        $docente = $this->usuarioConRol('DOCENTE');

        $sola = $this->convocatoria();
        $conPropuesta = $this->convocatoria();
        $conEvento = $this->convocatoria();
        $padre = $this->convocatoria();

        Propuesta::query()->forceCreate([
            'user_id' => $docente->id,
            'convocatoria_id' => $conPropuesta->id,
            'titulo' => 'x',
        ]);

        DB::table('eventos_calendario')->insert([
            'user_id' => $docente->id,
            'convocatoria_id' => $conEvento->id,
            'titulo' => 'x',
            'created_at' => now(),
            'updated_at' => now(),
        ] + array_intersect_key(
            ['fecha' => today(), 'fecha_inicio' => now(), 'tipo' => 'PERSONAL'],
            array_flip(DB::getSchemaBuilder()->getColumnListing('eventos_calendario'))
        ));

        $this->convocatoria(['convocatoria_padre_id' => $padre->id]);

        $this->assertFalse(ConvocatoriaResource::tieneDependencias($sola));
        $this->assertTrue(ConvocatoriaResource::tieneDependencias($conPropuesta));
        $this->assertTrue(ConvocatoriaResource::tieneDependencias($conEvento));
        $this->assertTrue(ConvocatoriaResource::tieneDependencias($padre));
    }

    public function test_el_hash_de_url_coincide_con_el_extractor(): void
    {
        // Valor calculado con extractor/app/urls.py::hash_url.
        $esperado = hash('sha256', 'https://secihti.mx/convocatoria/x');

        foreach ([
            'https://www.secihti.mx/convocatoria/x/',
            'HTTPS://SECIHTI.MX/convocatoria/x',
            'https://secihti.mx/convocatoria/x#bases',
        ] as $variante) {
            $this->assertSame($esperado, Convocatoria::hashUrl($variante));
        }

        $guardada = $this->convocatoria(['url_original' => 'https://www.secihti.mx/convocatoria/x/']);
        $this->assertSame($esperado, $guardada->url_hash);
    }
}
