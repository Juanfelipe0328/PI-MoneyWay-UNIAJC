<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | MoneyWay</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Manrope:wght@600;700;800&display=swap"
        rel="stylesheet"
    >

    <style>
        :root {
            --ink: #1b2927;
            --muted: #71817d;
            --line: #e6efec;
            --paper: #f8fbfa;
            --teal: #3a9b91;
            --deep-teal: #277d74;
            --mint: #e7f5f2;
            --coral: #e77a70;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: var(--ink);
            background: var(--paper);
            font-family: "DM Sans", sans-serif;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .layout {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 235px;
            min-height: 100vh;
            padding: 28px 16px;
            background: white;
            border-right: 1px solid var(--line);
            display: flex;
            flex-direction: column;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 0 10px 35px;
            font-family: "Manrope", sans-serif;
            font-size: 22px;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .brand-icon {
            width: 38px;
            height: 38px;
            display: grid;
            place-items: center;
            border-radius: 13px 13px 13px 4px;
            color: white;
            background: linear-gradient(135deg, #66bcb2, #398d84);
            box-shadow: 0 7px 15px #3a9b9126;
        }

        .brand-icon svg {
            width: 23px;
        }

        .nav-title {
            margin: 0 0 10px;
            padding-left: 13px;
            color: #9ba9a5;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 5px;
            padding: 12px 13px;
            border-radius: 12px;
            color: #687873;
            font-size: 14px;
            font-weight: 600;
        }

        .nav-link:hover {
            color: var(--deep-teal);
            background: #f1f8f6;
        }

        .nav-link.active {
            color: var(--deep-teal);
            background: var(--mint);
        }

        .user-box {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: auto;
            padding: 18px 10px 0;
            border-top: 1px solid var(--line);
        }

        .avatar {
            width: 36px;
            height: 36px;
            display: grid;
            place-items: center;
            border-radius: 50%;
            color: var(--deep-teal);
            background: #d8f3ed;
            font-size: 14px;
            font-weight: 800;
        }

        .user-box p {
            margin: 0;
            color: var(--muted);
            font-size: 12px;
        }

        .user-box strong {
            display: block;
            color: var(--ink);
            font-size: 13px;
        }

        .logout {
            margin: 12px 0 0 47px;
            border: 0;
            padding: 0;
            color: #8c9996;
            background: transparent;
            cursor: pointer;
            font-size: 12px;
            font-weight: 600;
        }

        .logout:hover {
            color: var(--coral);
        }

        .main {
            width: 100%;
            max-width: 1450px;
            margin: 0 auto;
            padding: 32px clamp(22px, 4vw, 58px);
        }

        .header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .month {
            margin: 0 0 5px;
            color: var(--teal);
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 1.2px;
            text-transform: uppercase;
        }

        h1 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: clamp(25px, 3vw, 32px);
            letter-spacing: -1.2px;
        }

        .subtitle {
            margin: 7px 0 0;
            color: var(--muted);
            font-size: 14px;
        }

        .new-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            border-radius: 13px;
            padding: 12px 16px;
            color: white;
            background: var(--teal);
            box-shadow: 0 8px 18px #3a9b912d;
            font-size: 13px;
            font-weight: 700;
        }

        .new-button:hover {
            background: var(--deep-teal);
        }

        .balance {
            position: relative;
            overflow: hidden;
            border-radius: 25px;
            padding: 31px;
            color: white;
            background: linear-gradient(130deg, #59afa5, #35867d);
            box-shadow: 0 13px 28px #277d741f;
        }

        .balance::after {
            content: "";
            position: absolute;
            right: -120px;
            bottom: -180px;
            width: 330px;
            height: 330px;
            border: 1px solid #d1fff64f;
            border-radius: 50%;
        }

        .balance-content {
            position: relative;
            z-index: 1;
        }

        .balance-label {
            margin: 0;
            color: #d8fffa;
            font-size: 14px;
        }

        .balance h2 {
            margin: 12px 0 7px;
            font-family: "Manrope", sans-serif;
            font-size: clamp(38px, 4vw, 50px);
            letter-spacing: -2px;
        }

        .balance-description {
            margin: 0;
            color: #d8fffa;
            font-size: 13px;
        }

        .balance-details {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 13px;
            max-width: 520px;
            margin-top: 28px;
        }

        .balance-details div {
            padding: 14px 16px;
            border: 1px solid #d9fff744;
            border-radius: 15px;
            background: #ffffff16;
        }

        .balance-details small {
            display: block;
            color: #d0fff8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .balance-details strong {
            display: block;
            margin-top: 7px;
            font-size: 18px;
        }

        .summary-cards {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 16px;
            margin: 20px 0 28px;
        }

        .card {
            padding: 20px;
            border: 1px solid var(--line);
            border-radius: 19px;
            background: white;
        }

        .card-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .card-title {
            color: var(--muted);
            font-size: 13px;
            font-weight: 600;
        }

        .card-icon {
            width: 35px;
            height: 35px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            font-size: 18px;
        }

        .card-icon.income {
            color: #168e82;
            background: #e0f7f2;
        }

        .card-icon.expense {
            color: #dd7168;
            background: #fff0ed;
        }

        .card-icon.balance-icon {
            color: #537fd9;
            background: #edf2ff;
        }

        .card-value {
            margin: 18px 0 5px;
            font-family: "Manrope", sans-serif;
            font-size: 23px;
            font-weight: 800;
            letter-spacing: -0.8px;
        }

        .card p {
            margin: 0;
            color: #95a39f;
            font-size: 12px;
        }

        .section {
            padding: 24px;
            border: 1px solid var(--line);
            border-radius: 21px;
            background: white;
        }

        .section-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 17px;
        }

        .section h3 {
            margin: 0;
            font-family: "Manrope", sans-serif;
            font-size: 18px;
            letter-spacing: -0.5px;
        }

        .section-header p {
            margin: 5px 0 0;
            color: #92a09d;
            font-size: 12px;
        }

        .history-link {
            color: var(--deep-teal);
            font-size: 12px;
            font-weight: 700;
        }

        .history-link:hover {
            text-decoration: underline;
        }

        .movement {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #edf2f0;
        }

        .movement:last-child {
            border-bottom: 0;
        }

        .movement-icon {
            width: 39px;
            height: 39px;
            display: grid;
            place-items: center;
            border-radius: 12px;
            font-size: 18px;
        }

        .movement-icon.income {
            color: #168e82;
            background: #e0f7f2;
        }

        .movement-icon.expense {
            color: #dd7168;
            background: #fff0ed;
        }

        .movement-info {
            min-width: 0;
            flex: 1;
        }

        .movement-info strong {
            display: block;
            overflow: hidden;
            font-size: 13px;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .movement-info small {
            display: block;
            margin-top: 3px;
            color: #95a19e;
            font-size: 11px;
        }

        .movement-value {
            font-size: 13px;
            font-weight: 800;
        }

        .movement-value.income {
            color: #0d998b;
        }

        .movement-value.expense {
            color: #e06a60;
        }

        .empty {
            padding: 30px 10px;
            color: #92a09d;
            text-align: center;
            font-size: 13px;
        }
                        
        .category-highlight {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin: 0 0 28px;
            padding: 20px 23px;
            border: 1px solid #dcece8;
            border-radius: 19px;
            background: #ffffff;
        }

        .category-highlight small {   
            display: block;
            color: #74837f;
            font-size: 12px;
            font-weight: 600;
        }

        .category-highlight h3 {
            margin: 6px 0 0;
            color: #277d74;
            font-family: "Manrope", sans-serif;
            font-size: 20px;
        }
        
        .category-total {
            color: #277d74;
            font-family: "Manrope", sans-serif;
            font-size: 20px;
            font-weight: 800;
        }      

        @media (max-width: 800px) {
            .sidebar {
                display: none;
            }

            .main {
                padding: 24px 17px;
            }

            .header {
                align-items: flex-start;
            }

            .new-button span {
                display: none;
            }

            .summary-cards {
                grid-template-columns: 1fr 1fr;
            }

            .summary-cards .card:last-child {
                grid-column: span 2;
            }
        }

        @media (max-width: 470px) {
            .balance {
                padding: 23px;
            }

            .balance h2 {
                font-size: 36px;
            }

            .balance-details {
                gap: 9px;
            }

            .balance-details div {
                padding: 12px;
            }

            .summary-cards {
                gap: 10px;
            }

            .card {
                padding: 16px;
            }

            .card-value {
                font-size: 19px;
            }

            .section {
                padding: 18px;
            }
        }
    </style>
</head>

<body>
    @php
        $currency = fn ($value) => '$ ' . number_format((float) $value, 0, ',', '.');
        $firstName = explode(' ', trim(auth()->user()->name))[0];
        $initial = strtoupper(mb_substr(auth()->user()->name, 0, 1));
    @endphp

    <div class="layout">
        <aside class="sidebar">
            <a class="brand" href="{{ route('dashboard') }}">
                <span class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="8"></circle>
                        <path d="M15 9.5c-.6-.55-1.65-.9-3-.9-1.7 0-3 .75-3 1.95 0 2.9 6 1.1 6 4 0 1.1-1.2 1.9-3 1.9-1.4 0-2.6-.5-3.3-1.2"></path>
                        <path d="M12 7.2v10"></path>
                    </svg>
                </span>

                MoneyWay
            </a>

            <p class="nav-title">Mi dinero</p>

            <a class="nav-link active" href="{{ route('dashboard') }}">
                ⌂ Resumen
            </a>

            <a class="nav-link" href="{{ route('movements.index') }}">
                ☷ Movimientos
            </a>

            <div class="user-box">
                <span class="avatar">{{ $initial }}</span>

                <p>
                    <strong>{{ auth()->user()->name }}</strong>
                    Cuenta personal
                </p>
            </div>

            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button class="logout" type="submit">Cerrar sesión</button>
            </form>
        </aside>

        <main class="main">
            <header class="header">
                <div>
                    <p class="month">{{ $monthLabel }}</p>
                    <h1>Hola, {{ $firstName }} 👋</h1>
                    <p class="subtitle">Este es el resumen de tus finanzas.</p>
                </div>

                <a class="new-button" href="{{ route('movements.create') }}">
                    ＋ <span>Nuevo movimiento</span>
                </a>
            </header>

            <section class="balance">
                <div class="balance-content">
                    <p class="balance-label">Balance disponible</p>
                    <h2>{{ $currency($balance) }}</h2>
                    <p class="balance-description">
                        Resultado de tus ingresos y gastos de {{ $monthLabel }}.
                    </p>

                    <div class="balance-details">
                        <div>
                            <small>Ingresos</small>
                            <strong>+ {{ $currency($income) }}</strong>
                        </div>

                        <div>
                            <small>Gastos</small>
                            <strong>- {{ $currency($expenses) }}</strong>
                        </div>
                    </div>
                </div>
            </section>

            <section class="summary-cards">
                <article class="card">
                    <div class="card-top">
                        <span class="card-title">Ingresos del mes</span>
                        <span class="card-icon income">↑</span>
                    </div>

                    <h3 class="card-value">{{ $currency($income) }}</h3>
                    <p>Dinero que entró este mes</p>
                </article>

                <article class="card">
                    <div class="card-top">
                        <span class="card-title">Gastos del mes</span>
                        <span class="card-icon expense">↓</span>
                    </div>

                    <h3 class="card-value">{{ $currency($expenses) }}</h3>
                    <p>Movimientos de salida</p>
                </article>

                <article class="card">
                    <div class="card-top">
                        <span class="card-title">Saldo actual</span>
                        <span class="card-icon balance-icon">$</span>
                    </div>

                    <h3 class="card-value">{{ $currency($balance) }}</h3>
                    <p>Con los datos registrados</p>
                </article>
            </section>

            <section class="category-highlight">
                 @if($topExpenseCategory)
                    <div>
                        <small>Categoría con mayor gasto este mes</small>
                        <h3>{{ $topExpenseCategory->name }}</h3>
                    </div>
                    
                    <span class="category-total">
                         {{ $currency($topExpenseCategory->total) }}
                        
                        </span>
                        
                        @else
                        <div>
                            <small>Categoría con mayor gasto este mes</small>
                            <h3>Aún no hay gastos registrados</h3>
                        </div>
                        <span class="category-total">$ 0</span>
                        
                        @endif
                    </section>

            <section class="section">
                <div class="section-header">
                    <div>
                        <h3>Movimientos recientes</h3>
                        <p>Tu actividad más reciente</p>
                    </div>

                    <a class="history-link" href="{{ route('movements.index') }}">
                        Ver historial
                    </a>
                </div>

                @if($recentMovements->isEmpty())
                    <div class="empty">
                        Aún no tienes movimientos.
                        <br>
                        Registra el primero para comenzar.
                    </div>
                @else
                    @foreach($recentMovements as $movement)
                        @php
                            $isIncome = in_array(
                                mb_strtolower($movement->type),
                                ['ingreso', 'income']
                            );
                        @endphp

                        <div class="movement">
                            <span class="movement-icon {{ $isIncome ? 'income' : 'expense' }}">
                                {{ $isIncome ? '↑' : '↓' }}
                            </span>

                            <div class="movement-info">
                                <strong>
                                    {{ $movement->description ?: ($movement->category?->name ?? 'Movimiento') }}
                                </strong>

                                <small>
                                    {{ $movement->category?->name ?? 'Sin categoría' }}
                                    ·
                                    {{ \Carbon\Carbon::parse($movement->movement_date)->locale('es')->translatedFormat('d M') }}
                                </small>
                            </div>

                            <span class="movement-value {{ $isIncome ? 'income' : 'expense' }}">
                                {{ $isIncome ? '+' : '-' }}
                                {{ $currency($movement->amount) }}
                            </span>
                        </div>
                    @endforeach
                @endif
            </section>
        </main>
    </div>
</body>
</html>