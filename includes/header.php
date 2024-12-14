<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/vinoteca/css/style.css">
    <title>Vinoteca</title>
</head>
<body>
    <header>
        <nav>
            <a href="/vinoteca/index.php">Inicio</a>
            
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <a href="/vinoteca/views/carrito.php">Carrito</a>
                <a href="/vinoteca/views/historial.php">Historial</a>
                <a href="/vinoteca/views/perfil.php">Perfil</a>
                <?php if ($_SESSION['tipo_usuario'] === 'admin'): ?>
                    <a href="/vinoteca/views/admin_productos.php">Administrar Productos</a>
                <?php endif; ?>
                <a href="/vinoteca/views/logout.php">Cerrar Sesión</a>
            <?php else: ?>
                <a href="/vinoteca/views/login_cliente.php">Iniciar Sesión Cliente</a>
                <a href="/vinoteca/views/login_admin.php">Iniciar Sesión Admin</a>
                <a href="/vinoteca/views/registro.php">Registrarse</a>
            <?php endif; ?>
        </nav>
    </header>
</body>
</html>
