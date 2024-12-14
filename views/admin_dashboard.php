<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_id']) || $_SESSION['tipo_usuario'] !== 'admin') {
    header("Location: login_admin.php");
    exit;
}

// Contenido del dashboard del administrador
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administrador</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Bienvenido, Administrador</h1>
        <p>Desde aquí puedes gestionar los productos, usuarios y más.</p>
        <!-- Opciones del administrador -->
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
