<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vino_id = $_POST['vino_id'];

    if (!isset($_SESSION['usuario_id'])) {
        header("Location: views/login_cliente.php");
        exit;
    }

    $usuario_id = $_SESSION['usuario_id'];

    // Verificar si el vino ya está en el carrito
    $sql = "SELECT * FROM carrito WHERE vino_id = :vino_id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['vino_id' => $vino_id, 'usuario_id' => $usuario_id]);
    $carrito_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($carrito_item) {
        // Incrementar la cantidad
        $sql = "UPDATE carrito SET cantidad = cantidad + 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $carrito_item['id']]);
    } else {
        // Agregar al carrito
        $sql = "INSERT INTO carrito (usuario_id, vino_id, cantidad) VALUES (:usuario_id, :vino_id, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id, 'vino_id' => $vino_id]);
    }

    header("Location: /vinoteca/views/carrito.php");
    exit;
}
?>
