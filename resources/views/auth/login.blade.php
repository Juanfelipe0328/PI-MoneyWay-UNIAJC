<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - MoneyWay</title>
</head>
<body>
    <h2>Bienvenido a MoneyWay</h2>
    
    <form action="{{ route('login.post') }}" method="POST">
        @csrf

        @if ($errors->any())
            <div style="color: red; margin-bottom: 10px;">
                {{ $errors->first() }}
            </div>
        @endif
        
        <div>
            <label for="email">Correo Electrónico:</label>
            <input type="email" name="email" id="email" required>
        </div>
        <br>
        
        <div>
            <label for="password">Contraseña:</label>
            <input type="password" name="password" id="password" required>
        </div>
        <br>
        
        <button type="submit">Entrar</button>
    </form>
</body>
</html>