<x-acceso titulo="Nueva contraseña" subtitulo="Escribe y confirma tu nueva contraseña.">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="campo">
            <label for="email">Correo institucional</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email', $email) }}"
                autocomplete="username"
                required
            >
        </div>

        <div class="campo">
            <label for="password">Nueva contraseña</label>
            <div class="con-ojo">
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="new-password"
                    required
                    autofocus
                >
                <button type="button" class="ojo" data-ojo="password" aria-label="Mostrar contraseña">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
            <p class="ayuda">Mínimo 8 caracteres, con letras y números.</p>
        </div>

        <div class="campo">
            <label for="password_confirmation">Confirmar contraseña</label>
            <input
                id="password_confirmation"
                name="password_confirmation"
                type="password"
                autocomplete="new-password"
                required
            >
        </div>

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="boton">Guardar contraseña</button>
    </form>

    <a class="enlace volver" href="{{ route('login') }}">← Volver a iniciar sesión</a>
</x-acceso>
