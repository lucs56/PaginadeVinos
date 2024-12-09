<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Obtener los vinos disponibles
$sql = "SELECT * FROM vinos WHERE stock > 0";
$stmt = $pdo->query($sql);
$vinos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$vinos) {
    $vinos = [];
}

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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vino_id'])) {
    $vino_id = $_POST['vino_id'];
    $usuario_id = $_SESSION['usuario_id'];

    // Verificar si el vino ya está en el carrito
    $sql = "SELECT * FROM carrito WHERE vino_id = :vino_id AND usuario_id = :usuario_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['vino_id' => $vino_id, 'usuario_id' => $usuario_id]);
    $carrito_item = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($carrito_item) {
        // Si el vino ya está en el carrito, incrementar la cantidad
        $sql = "UPDATE carrito SET cantidad = cantidad + 1 WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $carrito_item['id']]);
    } else {
        // Si el vino no está en el carrito, agregarlo
        $sql = "INSERT INTO carrito (usuario_id, vino_id, cantidad) VALUES (:usuario_id, :vino_id, 1)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['usuario_id' => $usuario_id, 'vino_id' => $vino_id]);
    }

    header("Location: catalogo.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catálogo de Vinos</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
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
                <img src="../path_to_images/<?php echo $vino['imagen']; ?>" alt="<?php echo $vino['nombre']; ?>">
                <h2><?php echo $vino['nombre']; ?></h2>
                <p><?php echo $vino['descripcion']; ?></p>
                <p><strong>Precio:</strong> $<?php echo $vino['precio']; ?></p>
                <p><strong>Variedad:</strong> <?php echo $vino['variedad']; ?></p>
                <p><strong>Stock:</strong> <?php echo $vino['stock']; ?> unidades</p>
                <form method="POST" action="">
                    <input type="hidden" name="vino_id" value="<?php echo $vino['id']; ?>">
                    <button type="submit">Agregar al carrito</button>
                </form>

                <!-- Mostrar comentarios -->
                <?php
                $sql_comentarios = "SELECT comentarios.*, usuarios.nombre 
                                    FROM comentarios 
                                    INNER JOIN usuarios ON comentarios.usuario_id = usuarios.id 
                                    WHERE comentarios.producto_id = :producto_id 
                                    ORDER BY comentarios.fecha DESC";
                $stmt_comentarios = $pdo->prepare($sql_comentarios);
                $stmt_comentarios->execute(['producto_id' => $vino['id']]);
                $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
                ?>
                <div class="comentarios">
                    <h3>Comentarios</h3>
                    <?php foreach ($comentarios as $comentario): ?>
                        <p><strong><?php echo $comentario['nombre']; ?>:</strong> <?php echo $comentario['comentario']; ?></p>
                        <p><em><?php echo $comentario['fecha']; ?></em></p>
                    <?php endforeach; ?>
                </div>

                <?php if (isset($_SESSION['usuario_id'])): ?>
                    <form method="POST" action="procesar_comentario.php">
                        <input type="hidden" name="producto_id" value="<?php echo $vino['id']; ?>">
                        <textarea name="comentario" placeholder="Escribe tu comentario..." required></textarea>
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <button type="submit">Comentar</button>
                    </form>
                <?php else: ?>
                    <p><a href="login.php">Inicia sesión</a> para comentar.</p>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>
</html>
