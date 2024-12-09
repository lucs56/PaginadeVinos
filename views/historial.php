<?php
session_start();
include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener el historial de pedidos para el usuario
$sql = "SELECT pedidos.*, vinos.nombre, vinos.precio 
        FROM pedidos 
        INNER JOIN vinos ON pedidos.vino_id = vinos.id 
        WHERE pedidos.usuario_id = :usuario_id 
        ORDER BY pedidos.fecha DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute(['usuario_id' => $usuario_id]);
$historial_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Pedidos</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <h1>Historial de Pedidos</h1>
    <?php if (count($historial_items) > 0): ?>
        <table>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Fecha</th>
                <th>Total</th>
            </tr>
            <?php foreach ($historial_items as $item): ?>
                <tr>
                    <td><?php echo $item['nombre']; ?></td>
                    <td>$<?php echo number_format($item['precio'], 2); ?></td>
                    <td><?php echo $item['cantidad']; ?></td>
                    <td><?php echo $item['fecha']; ?></td>
                    <td>$<?php echo number_format($item['precio'] * $item['cantidad'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php else: ?>
        <p>No tienes pedidos en tu historial.</p>
    <?php endif; ?>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
