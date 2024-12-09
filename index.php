<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Vinoteca</title>
    <link rel="stylesheet" href="/vinoteca/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container">
        <header>
            <h1>Bienvenidos a Nuestra Vinoteca</h1>
            <p class="lead">Encuentra los mejores vinos de la región al mejor precio.</p>
        </header>
        <div class="text-center">
            <a class="btn" href="/vinoteca/views/catalogo.php">Explorar Catálogo</a>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>
</body>
</html>
