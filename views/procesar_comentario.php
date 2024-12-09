<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token mismatch.");
}

$usuario_id = $_SESSION['usuario_id'];
$producto_id = $_POST['producto_id'];
$comentario = htmlspecialchars($_POST['comentario'], ENT_QUOTES, 'UTF-8');

// Insertar comentario en la base de datos
$sql = "INSERT INTO comentarios (usuario_id, producto_id, comentario) VALUES (:usuario_id, :producto_id, :comentario)";
$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id, 'producto_id' => $producto_id, 'comentario' => $comentario]);

header("Location: catalogo.php");
exit;
?>
