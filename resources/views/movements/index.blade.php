<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Gastos - MoneyWay</title>
</head>
<body>
    <h2>Mi Historial Financiero</h2>
    
    <div style="margin-bottom: 20px;">
    <a href="/registrar" style="background-color: #117964; color: white; padding: 8px 16px; text-decoration: none; border-radius: 6px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.1); display: inline-block;">
        Registrar Gasto
    </a>
</div>


    <br><br>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Descripción</th>
                <th>Monto</th>
                <th>Origen</th>
            </tr>
        </thead>
        <tbody>
            <!-- El ciclo foreach SOLO repite las filas (tr) de la tabla -->
            @foreach($movements as $movement)
                <tr>
                    <td>{{ $movement->movement_date }}</td>
                    <td>{{ ucfirst($movement->type) }}</td>
                    <td>{{ $movement->description }}</td>
                    <td>${{ number_format($movement->amount, 0) }}</td>
                    <td>{{ ucfirst($movement->source) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <br>
    {{ $movements->links() }} 

</body>
</html>