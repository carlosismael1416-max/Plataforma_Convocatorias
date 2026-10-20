<?php

namespace Tests\Feature;

use App\Filament\Resources\Departamentos\Pages\CreateDepartamento;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Departamento;
use App\Models\Role;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DepartamentosTest extends TestCase
{
    use RefreshDatabase;

    private function comoAdmin(): void
    {
        $this->actingAs($this->usuarioConRol('ADMINISTRADOR'));
        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_solo_el_administrador_entra_a_departamentos(): void
    {
        $this->actingAs($this->usuarioConRol('DOCENTE'))->get('/admin/departamentos')->assertForbidden();

        $this->comoAdmin();
        $this->get('/admin/departamentos')->assertOk()->assertSee('Departamentos')->assertSee('Usuarios');
    }

    public function test_se_crea_un_departamento_y_se_asigna_a_un_usuario(): void
    {
        $this->comoAdmin();

        Livewire::test(CreateDepartamento::class)
            ->fillForm(['nombre' => 'ZZ Investigación y Posgrado'])
            ->call('create')
            ->assertHasNoFormErrors();

        $departamento = Departamento::where('nombre', 'ZZ Investigación y Posgrado')->firstOrFail();
        $this->assertTrue((bool) $departamento->estado);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'ZZ Nueva',
                'email' => 'zz.nueva@itsva.edu.mx',
                'password' => 'secreta123',
                'password_confirmation' => 'secreta123',
                'role_id' => Role::where('nombre', 'DOCENTE')->value('id'),
                'departamento_id' => $departamento->id,
                'estado' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame($departamento->id, User::where('email', 'zz.nueva@itsva.edu.mx')->value('departamento_id'));
    }
}
