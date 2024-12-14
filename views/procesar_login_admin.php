<?php
include '../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = htmlspecialchars($_POST['email']);
    $password = htmlspecialchars($_POST['password']);

    // Mensajes de depuración
    echo "Email ingresado: " . $email . "<br>";
    echo "Password ingresada: " . $password . "<br>";

    $sql = "SELECT * FROM usuarios WHERE email = :email AND tipo_usuario = 'admin'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Más depuración
    if ($user) {
        echo "Usuario encontrado: " . $user['email'] . "<br>";
        echo "Password hash en la BD: " . $user['password'] . "<br>";
        echo "Tipo de usuario en la BD: " . $user['tipo_usuario'] . "<br>";
    } else {
        echo "No se encontró usuario con ese email o no es administrador.<br>";
    }

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Email o contraseña incorrectos.";
    }
}
?>
