<x-acceso titulo="Recuperar contraseña" subtitulo="Te enviaremos un enlace para crear una nueva contraseña">
    <form method="POST" action="{{ route('password.email') }}">
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

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit">
            Enviar enlace
        </button>
    </form>

    <a class="enlace" href="{{ route('login') }}">
        Volver a iniciar sesión
    </a>
</x-acceso>
