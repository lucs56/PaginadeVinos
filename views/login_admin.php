<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/style.css">
    <title>Iniciar Sesión Administrador</title>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Iniciar Sesión Administrador</h1>
        <form method="post" action="procesar_login_admin.php">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
