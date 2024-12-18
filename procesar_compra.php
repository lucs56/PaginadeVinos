<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];
$vino_id = $_POST['vino_id'];
$cantidad = $_POST['cantidad'];

// Verificar que la cantidad sea válida
if ($cantidad < 1) {
    die("Cantidad no válida.");
}

try {
    // Verificar si el producto ya está en el carrito
    $sql = "SELECT * FROM carrito WHERE usuario_id = :usuario_id AND vino_id = :vino_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['usuario_id' => $usuario_id, 'vino_id' => $vino_id]);
    $item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($item) {
        // Si ya está en el carrito, actualizar la cantidad
        $nueva_cantidad = $item['cantidad'] + $cantidad;
        $sql = "UPDATE carrito SET cantidad = :cantidad WHERE usuario_id = :usuario_id AND vino_id = :vino_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['cantidad' => $nueva_cantidad, 'usuario_id' => $usuario_id, 'vino_id' => $vino_id]);
    } else {
        // Si no está en el carrito, insertarlo
        $sql = "INSERT INTO carrito (usuario_id, vino_id, cantidad) VALUES (:usuario_id, :vino_id, :cantidad)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id, 'vino_id' => $vino_id, 'cantidad' => $cantidad]);
    }

    // Redirigir de nuevo al catálogo o a una página de confirmación
    header("Location: index.php");
    exit;
} catch (PDOException $e) {
    echo "Error procesando la compra: " . $e->getMessage();
}
?>