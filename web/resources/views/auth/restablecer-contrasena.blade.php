<x-acceso titulo="Nueva contraseña" subtitulo="Escribe tu nueva contraseña">
    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <label for="email">Correo electrónico</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email', $email) }}"
            autocomplete="username"
            required
        >

        <label for="password">Nueva contraseña</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="new-password"
            required
            autofocus
        >
        <p class="ayuda">Mínimo 8 caracteres, con letras y números.</p>

        <label for="password_confirmation">Confirmar contraseña</label>
        <input
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            autocomplete="new-password"
            required
        >

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit">
            Guardar contraseña
        </button>
    </form>

    <a class="enlace" href="{{ route('login') }}">
        Volver a iniciar sesión
    </a>
</x-acceso>
