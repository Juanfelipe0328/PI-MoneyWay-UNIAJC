<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar sesión - MoneyWay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="w-100" style="max-width: 380px;">

        <div class="text-center mb-4">
            <div class="fs-3 fw-bold">Money<span class="text-success">Way</span></div>
            <p class="text-secondary mb-0">Inicia sesión para continuar</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form action="{{ route('login.post') }}" method="POST" id="login-form">
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            value="{{ old('email') }}"
                            class="form-control @error('email') is-invalid @enderror"
                            autocomplete="email"
                            autofocus
                            required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label">Contraseña</label>
                        <div class="input-group has-validation">
                            <input
                                type="password"
                                name="password"
                                id="password"
                                class="form-control @error('password') is-invalid @enderror"
                                autocomplete="current-password"
                                required>
                            <button class="btn btn-outline-secondary" type="button" id="toggle-pass" aria-controls="password">
                                Mostrar
                            </button>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="submit-btn">Entrar</button>
                </form>

                <p class="text-center text-secondary small mt-3 mb-0">
                    ¿No tienes cuenta? <a href="{{ route('register') }}" class="link-success">Crea una</a>
                </p>
            </div>
        </div>

    </div>
</main>

<script>
    const pass = document.getElementById('password');
    const toggle = document.getElementById('toggle-pass');

    toggle.addEventListener('click', () => {
        const show = pass.type === 'password';
        pass.type = show ? 'text' : 'password';
        toggle.textContent = show ? 'Ocultar' : 'Mostrar';
    });

    document.getElementById('login-form').addEventListener('submit', (e) => {
        if (!e.target.checkValidity()) return;
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Ingresando...';
    });
</script>
</body>
</html>