<x-acceso titulo="Iniciar sesión" subtitulo="Consulta, gestiona y desarrolla propuestas para oportunidades de financiamiento.">
    <form method="POST" action="{{ route('login.submit') }}">
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

        <div class="campo">
            <label for="password">Contraseña</label>
            <div class="con-ojo">
                <input
                    id="password"
                    name="password"
                    type="password"
                    autocomplete="current-password"
                    required
                >
                <button type="button" class="ojo" data-ojo="password" aria-label="Mostrar contraseña">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.88 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.88 0"/><circle cx="12" cy="12" r="3"/></svg>
                </button>
            </div>
        </div>

        <div class="fila">
            <label class="recordar">
                <input type="checkbox" name="remember" value="1" @checked(old('remember'))>
                Recordarme
            </label>

            <a class="enlace" href="{{ route('password.request') }}">¿Olvidaste tu contraseña?</a>
        </div>

        @if ($errors->any())
            <div class="error" role="alert">
                {{ $errors->first() }}
            </div>
        @endif

        <button type="submit" class="boton">Iniciar sesión</button>
    </form>
</x-acceso>
