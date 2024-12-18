<?php
session_start(); // Iniciar la sesión

// Incluir el archivo de conexión a la base de datos
include 'db.php'; // Asegúrate de que esta ruta sea correcta

// Verificar que la sesión esté iniciada y el usuario esté autenticado
if (!isset($_SESSION['usuario_id'])) {
    die("Usuario no autenticado.");
}

// Verificar que el token CSRF sea válido
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("CSRF token mismatch.");
}

// Definir las variables a partir de $_POST y sanitizarlas
$usuario_id = $_SESSION['usuario_id']; 
$producto_id = $_POST['producto_id'] ?? null;  
$comentario = htmlspecialchars($_POST['comentario'] ?? '', ENT_QUOTES, 'UTF-8');

// Asegurarse de que las variables no estén vacías
if (empty($producto_id) || empty($comentario)) {
    die("Faltan datos importantes.");
}

try {
    // Insertar comentario en la base de datos
    $sql = "INSERT INTO comentarios (usuario_id, producto_id, comentario) VALUES (:usuario_id, :producto_id, :comentario)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id, 'producto_id' => $producto_id, 'comentario' => $comentario]);

    // Redirigir después de la inserción
    header("Location: index.php"); // Asegúrate de que index.php exista en la misma carpeta
    exit;
} catch (PDOException $e) {
    // Manejar errores en la base de datos
    echo "Error al guardar el comentario: " . $e->getMessage();
}
?>