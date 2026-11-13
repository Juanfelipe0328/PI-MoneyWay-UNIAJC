<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear cuenta - MoneyWay</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<main class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="w-100" style="max-width: 400px;">

        <div class="text-center mb-4">
            <div class="fs-3 fw-bold">Money<span class="text-success">Way</span></div>
            <p class="text-secondary mb-0">Crea tu cuenta para empezar</p>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">

                <form action="{{ route('register.post') }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label for="name" class="form-label">Nombre</label>
                        <input type="text" name="name" id="name" value="{{ old('name') }}"
                               class="form-control @error('name') is-invalid @enderror"
                               autocomplete="name" autofocus required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Correo electrónico</label>
                        <input type="email" name="email" id="email" value="{{ old('email') }}"
                               class="form-control @error('email') is-invalid @enderror"
                               autocomplete="email" required>
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Contraseña</label>
                        <input type="password" name="password" id="password"
                               class="form-control @error('password') is-invalid @enderror"
                               autocomplete="new-password" minlength="8" required>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @else
                            <div class="form-text">Mínimo 8 caracteres.</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                        <input type="password" name="password_confirmation" id="password_confirmation"
                               class="form-control" autocomplete="new-password" required>
                    </div>

                    <div class="form-check mb-4">
                        <input type="checkbox" name="terms" id="terms" value="1"
                               class="form-check-input @error('terms') is-invalid @enderror"
                               {{ old('terms') ? 'checked' : '' }} required>
                        <label for="terms" class="form-check-label small">
                            Acepto la politica de mis <a href="https://sedeelectronica.sic.gov.co/politica-de-tratamiento-de-datos-personales"target="_blank">datos</a> personales.
                        </label>
                        @error('terms') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <button type="submit" class="btn btn-success w-100" id="submit-btn">Crear cuenta</button>
                </form>

                <p class="text-center text-secondary small mt-3 mb-0">
                    ¿Ya tienes cuenta? <a href="{{ route('login') }}" class="link-success">Inicia sesión</a>
                </p>
            </div>
        </div>

    </div>
</main>

<script>
    document.querySelector('form').addEventListener('submit', (e) => {
        if (!e.target.checkValidity()) return;
        const btn = document.getElementById('submit-btn');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" aria-hidden="true"></span>Creando cuenta...';
    });
</script>
</body>
</html>
