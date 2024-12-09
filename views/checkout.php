<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Procesar la compra cuando se envíe el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Preparar la inserción en pedidos desde el carrito
        $sql = "INSERT INTO pedidos (usuario_id, vino_id, cantidad, fecha) 
                SELECT carrito.usuario_id, carrito.vino_id, carrito.cantidad, NOW() 
                FROM carrito 
                WHERE carrito.usuario_id = :usuario_id";

        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        // Vaciar el carrito
        $sql = "DELETE FROM carrito WHERE usuario_id = :usuario_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':usuario_id', $usuario_id, PDO::PARAM_INT);
        $stmt->execute();

        // Redirigir a la página de confirmación
        header("Location: confirmacion.php");
        exit;

    } catch (PDOException $e) {
        echo "Error en la base de datos: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h1>Confirmar Compra</h1>
    <form action="" method="POST">
        <p>¿Deseas confirmar tu compra?</p>
        <button type="submit" class="btn-comprar">Finalizar Compra</button>
    </form>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
