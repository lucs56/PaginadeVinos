<?php
include '../db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT * FROM usuarios WHERE email = :email AND tipo_usuario = 'cliente'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['usuario_id'] = $user['id'];
        $_SESSION['tipo_usuario'] = $user['tipo_usuario'];
        header("Location: cliente_dashboard.php");
    } else {
        echo "Email o contraseña incorrectos.";
    }
}
?>
