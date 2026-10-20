<x-acceso titulo="Contraseña actualizada" subtitulo="Tu nueva contraseña quedó guardada.">
    <div style="display: flex; flex-direction: column; align-items: center; gap: 14px; text-align: center">
        <div style="width: 64px; height: 64px; border-radius: 50%; background: #D1FAE5; color: #047857; display: flex; align-items: center; justify-content: center" aria-hidden="true">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
        </div>
        <p class="ayuda" style="font-size: 14px; line-height: 1.5">
            Ya puedes iniciar sesión con tu correo institucional y la contraseña nueva.
            Por seguridad, las sesiones que tenías abiertas con «Recordarme» se cerraron.
        </p>
        <a href="{{ route('login') }}" class="boton" style="display: block; text-align: center; text-decoration: none">Ir a iniciar sesión</a>
    </div>
</x-acceso>
