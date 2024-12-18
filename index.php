<?php
session_start();  // Asegúrate de iniciar la sesión

// Generar un token CSRF y guardarlo en la sesión si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32)); // Generar un token aleatorio
}
$csrf_token = $_SESSION['csrf_token'];  // Asignar el token a una variable para usar en el formulario

include 'db.php';

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
    <link rel="stylesheet" href="/css/style.css"> <!-- Usar ruta absoluta -->
    <style>
        .catalogo {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }
        .vino {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 10px;
            width: calc(33% - 20px);
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .vino img {
            max-width: 100%;
            height: auto;
            border-radius: 5px;
        }
        .vino h2 {
            font-size: 1.5em;
            margin: 10px 0;
        }
        .vino p {
            margin: 5px 0;
        }
        .comentarios {
            margin-top: 15px;
        }
        .comentarios ul {
            list-style-type: none;
            padding: 0;
        }
        .comentarios li {
            background-color: #fff;
            padding: 5px;
            margin-bottom: 5px;
            border-radius: 5px;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.1);
        }
        .form-comentario {
            margin-top: 15px;
            padding-top: 10px;
            border-top: 1px solid #ddd;
        }
    </style>
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
                <!-- Imagen -->
                <img src="uploads/<?php echo $vino['imagen']; ?>" alt="<?php echo $vino['nombre']; ?>">

                <!-- Información del producto -->
                <h2><?php echo $vino['nombre']; ?></h2>
                <p><?php echo $vino['descripcion']; ?></p>
                <p><strong>Precio:</strong> $<?php echo $vino['precio']; ?></p>
                <p><strong>Variedad:</strong> <?php echo $vino['variedad']; ?></p>
                <p><strong>Stock:</strong> <?php echo $vino['stock']; ?> unidades</p>

                <!-- Formulario para agregar al carrito -->
                <form method="POST" action="procesar_compra.php">
                    <input type="hidden" name="vino_id" value="<?php echo $vino['id']; ?>">
                    <label class="cantidad-label" for="cantidad"><strong>Cantidad:</strong></label>
                    <input type="number" id="cantidad" name="cantidad" min="1" max="<?php echo $vino['stock']; ?>" value="1">
                    <button type="submit">Agregar al carrito</button>
                </form>

                <!-- Mostrar comentarios -->
                <div class="comentarios">
                    <h3>Comentarios:</h3>
                    <?php
                    $sql_comentarios = "SELECT * FROM comentarios WHERE producto_id = :producto_id";
                    $stmt_comentarios = $pdo->prepare($sql_comentarios);
                    $stmt_comentarios->execute(['producto_id' => $vino['id']]);
                    $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
                    ?>

                    <ul>
                        <?php foreach ($comentarios as $comentario): ?>
                            <li><?php echo htmlspecialchars($comentario['comentario'], ENT_QUOTES, 'UTF-8'); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <!-- Formulario de comentarios -->
                <div class="form-comentario">
                    <form method="POST" action="procesar_comentario.php">
                        <input type="hidden" name="producto_id" value="<?php echo $vino['id']; ?>">
                        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>"> <!-- Token CSRF -->
                        <textarea name="comentario" rows="2" placeholder="Deja un comentario..." style="width: 100%;"></textarea>
                        <button type="submit" class="comentario-button">Enviar comentario</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>