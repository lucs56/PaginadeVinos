<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'cliente') {
    header("Location: login_cliente.php");
    exit;
}

// Contenido del dashboard del cliente
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Panel de Cliente</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Bienvenido, Cliente</h1>
        <p>Desde aquí puedes ver y gestionar tus compras.</p>
        <!-- Opciones del cliente -->
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
