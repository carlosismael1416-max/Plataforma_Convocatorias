{{--
    Botones para subir, cambiar o quitar la foto de perfil (trait
    GestionaFotoPerfil) y estilo para mostrarla dentro del círculo .avatar.
    Uso: @include('filament.partials.foto-perfil', ['usuario' => $usuario, 'alinear' => 'center'])
--}}
@php $tieneFoto = filled($usuario->foto); $alinear ??= 'center'; @endphp

<style>
    .avatar { overflow: hidden; }
    .avatar img.avatar-foto { width: 100%; height: 100%; object-fit: cover; display: block; border-radius: inherit; }
    .fp { display: flex; flex-direction: column; align-items: {{ $alinear === 'start' ? 'flex-start' : 'center' }}; gap: 6px; margin: 10px 0 14px; }
    .fp-botones { display: flex; flex-wrap: wrap; gap: 6px; }
    .fp-btn { display: inline-flex; align-items: center; gap: 5px; border: 1px solid #DDE3EE; border-radius: 8px; background: #fff; color: #1E293B; padding: 6px 10px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .fp-btn:hover { background: #F8FAFC; }
    .fp-btn-quitar { color: #B91C1C; }
    .fp-ayuda { color: #94A3B8; font-size: 11px; }
    .fp-error { color: #B91C1C; font-size: 12px; }
</style>

<div class="fp">
    <div class="fp-botones">
        <label class="fp-btn">
            <span wire:loading.remove wire:target="fotoNueva">📷 {{ $tieneFoto ? 'Cambiar foto' : 'Subir foto' }}</span>
            <span wire:loading wire:target="fotoNueva">Subiendo…</span>
            <input type="file" accept="image/jpeg,image/png,image/webp" wire:model="fotoNueva" style="display: none">
        </label>
        @if ($tieneFoto)
            <button type="button" class="fp-btn fp-btn-quitar" wire:click="quitarFoto" wire:confirm="¿Quitar tu foto de perfil?">Quitar</button>
        @endif
    </div>
    <div class="fp-ayuda">JPG, PNG o WebP, hasta 3 MB.</div>
    @error('fotoNueva')
        <div class="fp-error">{{ $message }}</div>
    @enderror
</div>
