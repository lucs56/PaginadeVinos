<?php
include 'db.php';
session_start();

// Obtener los vinos disponibles
$sql = "SELECT * FROM vinos WHERE stock > 0";
$stmt = $pdo->query($sql);
$vinos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener las categorías disponibles
$sql_categorias = "SELECT * FROM categorias";
$stmt_categorias = $pdo->query($sql_categorias);
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Filtrar por categoría
$categoria_id = $_GET['categoria_id'] ?? '';

// Modificar la consulta SQL para aplicar el filtro
$sql = "SELECT * FROM vinos WHERE stock > 0";
$params = [];

if ($categoria_id) {
    $sql .= " AND categoria_id = :categoria_id";
    $params['categoria_id'] = $categoria_id;
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$vinos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Vinos</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <h1>Catálogo de Vinos</h1>

    <!-- Filtro por categoría -->
    <form method="GET" action="" class="container">
        <label for="categoria_id">Categoría:</label>
        <select id="categoria_id" name="categoria_id">
            <option value="">Todas</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?php echo $categoria['id']; ?>" <?php if ($categoria_id == $categoria['id']) echo 'selected'; ?>>
                    <?php echo $categoria['nombre']; ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit">Filtrar</button>
    </form>

    <div class="catalogo">
    <?php foreach ($vinos as $vino): ?>
        <div class="vino">
        <img src="uploads/<?php echo $vino['imagen']; ?>" alt="<?php echo $vino['nombre']; ?>">
            <h2><?php echo $vino['nombre']; ?></h2>
            <p><?php echo $vino['descripcion']; ?></p>
            <p><strong>Precio:</strong> $<?php echo $vino['precio']; ?></p>
            <p><strong>Variedad:</strong> <?php echo $vino['variedad']; ?></p>
            <p><strong>Stock:</strong> <?php echo $vino['stock']; ?> unidades</p>
            <form method="POST" action="/vinoteca/procesar_compra.php">
                <input type="hidden" name="vino_id" value="<?php echo $vino['id']; ?>">
                <label for="cantidad">Cantidad:</label>
                <input type="number" id="cantidad" name="cantidad" min="1" max="<?php echo $vino['stock']; ?>" value="1">
                <button type="submit">Agregar al carrito</button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
