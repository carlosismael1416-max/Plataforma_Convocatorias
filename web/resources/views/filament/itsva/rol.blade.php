@php
    $rol = $rol ?? 'docente';
    $etiqueta = ['docente' => 'Docente', 'directivo' => 'Directivo', 'admin' => 'Administrador'][$rol] ?? 'Docente';
@endphp

<div class="itsva-rol itsva-rol-{{ $rol }}">
    <span class="itsva-rol-punto"></span>
    {{ $etiqueta }}
</div>
