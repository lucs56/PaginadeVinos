<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuarios</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Registro de Usuarios</h1>
        <form method="POST" action="registro.php">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required>

            <button type="submit">Registrarse</button>
        </form>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Recibir datos del formulario
    $nombre = htmlspecialchars($_POST['nombre']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $tipo_usuario = 'cliente'; // Por defecto todos los nuevos registros son clientes

    // Verificar si el email ya está registrado
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $usuario_existente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario_existente) {
        echo "Este email ya está registrado. Por favor, inicia sesión.";
    } else {
        // Insertar en la base de datos
        $sql = "INSERT INTO usuarios (nombre, email, password, tipo_usuario) VALUES (:nombre, :email, :password, :tipo_usuario)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['nombre' => $nombre, 'email' => $email, 'password' => $password, 'tipo_usuario' => $tipo_usuario]);

        echo "Registro exitoso. Por favor, inicia sesión.";
        header("Location: login_cliente.php");
        exit;
    }
}
?>
