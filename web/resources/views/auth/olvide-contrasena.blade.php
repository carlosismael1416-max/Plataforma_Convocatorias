<x-acceso titulo="Recuperar contraseña" subtitulo="Escribe tu correo institucional y te enviaremos un enlace para crear una nueva contraseña.">
    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="campo">
            <label for="email">Correo institucional</label>
            <input
                id="email"
                name="email"
                type="email"
                value="{{ old('email') }}"
                placeholder="usuario@itsva.edu.mx"
                autocomplete="username"
                required
                autofocus
            >
        </div>

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="boton">Enviar enlace</button>
    </form>

    <a class="enlace volver" href="{{ route('login') }}">← Volver a iniciar sesión</a>
</x-acceso>
