<x-acceso titulo="Iniciar sesión" subtitulo="Inicia sesión con tu cuenta institucional">
    <form method="POST" action="{{ route('login.submit') }}">
        @csrf

        <label for="email">Correo electrónico</label>
        <input
            id="email"
            name="email"
            type="email"
            value="{{ old('email') }}"
            autocomplete="username"
            required
            autofocus
        >

        <label for="password">Contraseña</label>
        <input
            id="password"
            name="password"
            type="password"
            autocomplete="current-password"
            required
        >

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit">
            Iniciar sesión
        </button>
    </form>

    <a class="enlace" href="{{ route('password.request') }}">
        ¿Olvidaste tu contraseña?
    </a>
</x-acceso>
