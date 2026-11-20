<!DOCTYPE html>
<html lang="es" data-bs-theme="@yield('theme', 'light')">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'MoneyWay')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @stack('styles')
</head>
<body class="bg-body-tertiary">

<nav class="navbar bg-body border-bottom">
    <div class="container flex-wrap gap-2">
        <a class="navbar-brand fw-bold me-0 order-1" href="{{ route('dashboard') }}">Money<span class="text-success">Way</span></a>

        @auth
            {{-- En celular las pestañas bajan a una segunda fila --}}
            <ul class="nav nav-pills col-12 col-md-auto order-3 order-md-2 me-md-auto ms-md-3" style="--bs-nav-pills-link-active-bg: var(--bs-success);">
                <li class="nav-item">
                    <a class="nav-link py-1 {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link py-1 {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">Movimientos</a>
                </li>
            </ul>

            <div class="d-flex align-items-center gap-3 order-2 order-md-3">
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
@stack('scripts')
</body>
</html>
