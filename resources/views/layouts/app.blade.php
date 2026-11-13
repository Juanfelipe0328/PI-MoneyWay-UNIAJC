<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MoneyWay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">

<nav class="navbar bg-white border-bottom">
    <div class="container">
        <a class="navbar-brand fw-bold" href="{{ route('movements.index') }}">Money<span class="text-success">Way</span></a>

        @auth
            <div class="d-flex align-items-center gap-3">
                <span class="text-secondary small d-none d-sm-inline">{{ auth()->user()->name }}</span>
                <form action="{{ route('logout') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Cerrar sesión</button>
                </form>
            </div>
        @endauth
    </div>
</nav>

<main class="container py-4">
    @yield('content')
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
