<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Inicializar mensaje de notificación y productos
$mensaje = "";
$productos = [];

// Crear un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "crear") {
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $variedad = htmlspecialchars($_POST['variedad']);
    $stock = (int)$_POST['stock'];
    $imagen = htmlspecialchars($_POST['imagen']);

    $sql = "INSERT INTO vinos (nombre, descripcion, precio, categoria_id, variedad, stock, imagen) 
            VALUES (:nombre, :descripcion, :precio, :categoria_id, :variedad, :stock, :imagen)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'precio' => $precio,
        'categoria_id' => $categoria_id,
        'variedad' => $variedad,
        'stock' => $stock,
        'imagen' => $imagen
    ]);
    $mensaje = "Producto agregado correctamente.";
}

// Leer productos existentes
$sql = "SELECT vinos.*, categorias.nombre AS categoria_nombre 
        FROM vinos 
        LEFT JOIN categorias ON vinos.categoria_id = categorias.id";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Si se selecciona un producto para editar
$producto_a_editar = null;
if (isset($_GET['editar_id'])) {
    $editar_id = (int)$_GET['editar_id'];
    $sql = "SELECT * FROM vinos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $editar_id]);
    $producto_a_editar = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Actualizar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "editar") {
    $id = (int)$_POST['id'];
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $variedad = htmlspecialchars($_POST['variedad']);
    $stock = (int)$_POST['stock'];
    $imagen = htmlspecialchars($_POST['imagen']);

    $sql = "UPDATE vinos 
            SET nombre = :nombre, descripcion = :descripcion, precio = :precio, categoria_id = :categoria_id, variedad = :variedad, stock = :stock, imagen = :imagen 
            WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'nombre' => $nombre,
        'descripcion' => $descripcion,
        'precio' => $precio,
        'categoria_id' => $categoria_id,
        'variedad' => $variedad,
        'stock' => $stock,
        'imagen' => $imagen,
        'id' => $id
    ]);
    $mensaje = "Producto actualizado correctamente.";
    header("Location: admin_productos.php");
    exit;
}

// Eliminar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "eliminar") {
    $id = (int)$_POST['id'];
    $sql = "DELETE FROM vinos WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $id]);
    $mensaje = "Producto eliminado correctamente.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Productos</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <div class="container">
        <h1>Administrar Productos</h1>
        <?php if ($mensaje): ?>
            <div class="mensaje"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <button class="btn" onclick="mostrarFormulario('crear')">Agregar Producto</button>

        <!-- Tabla de productos -->
        <h2>Listado de Productos</h2>
        <table>
            <tr>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio</th>
                <th>Categoría</th>
                <th>Variedad</th>
                <th>Stock</th>
                <th>Imagen</th>
                <th>Acciones</th>
            </tr>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto['nombre']; ?></td>
                    <td><?php echo $producto['descripcion']; ?></td>
                    <td>$<?php echo number_format($producto['precio'], 2); ?></td>
                    <td><?php echo $producto['categoria_nombre'] ?? 'Sin categoría'; ?></td>
                    <td><?php echo $producto['variedad']; ?></td>
                    <td><?php echo $producto['stock']; ?></td>
                    <td><img src="../path_to_images/<?php echo $producto['imagen']; ?>" alt="<?php echo $producto['nombre']; ?>" style="width: 50px;"></td>
                    <td>
                        <button type="button" onclick="mostrarFormularioEditar(<?php echo $producto['id']; ?>)">Editar</button>
                        <form method="POST" action="" style="display:inline;">
                            <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                            <input type="hidden" name="accion" value="eliminar">
                            <button type="submit">Eliminar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </table>

        <!-- Formulario para agregar/editar productos -->
        <div id="formularioProducto" style="display: none;">
            <h2 id="tituloFormulario">Agregar Producto</h2>
            <form id="formProducto" method="POST" action="">
                <input type="hidden" name="accion" value="crear" id="accion">
                <input type="hidden" name="id" id="productoId">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required>
                <br>
                <label for="descripcion">Descripción:</label>
                <textarea id="descripcion" name="descripcion" required></textarea>
                <br>
                <label for="precio">Precio:</label>
                <input type="number" id="precio" name="precio" step="0.01" required>
                <br>
                <label for="categoria_id">Categoría:</label>
                <select id="categoria_id" name="categoria_id" required>
                    <?php
                    $categorias = $pdo->query("SELECT * FROM categorias")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categorias as $categoria) {
                        echo "<option value='{$categoria['id']}'>{$categoria['nombre']}</option>";
                    }
                    ?>
                </select>
                <br>
                <label for="variedad">Variedad:</label>
                <input type="text" id="variedad" name="variedad" required>
                <br>
                <label for="stock">Stock:</label>
                <input type="number" id="stock" name="stock" required>
                <br>
                <label for="imagen">Imagen (ruta):</label>
                <input type="text" id="imagen" name="imagen" required>
                <br>
                <button type="submit" id="botonGuardar">Guardar Producto</button>
            </form>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
    // Mostrar formulario para agregar o editar productos
    function mostrarFormulario(accion) {
        var form = document.getElementById('formularioProducto');
        var titulo = document.getElementById('tituloFormulario');
        var accionInput = document.getElementById('accion');
        var botonGuardar = document.getElementById('botonGuardar');
        
        form.style.display = 'block'; // Mostrar el formulario

        if (accion === 'crear') {
            titulo.innerText = 'Agregar Producto';
            botonGuardar.innerText = 'Guardar Producto';
            accionInput.value = 'crear'; // Cambiar la acción
            form.reset(); // Limpiar formulario
        }
    }

    // Mostrar formulario pre-rellenado para editar un producto
    function mostrarFormularioEditar(id) {
        var productos = <?php echo json_encode($productos); ?>;
        var producto = productos.find(producto => producto.id == id);

        if (producto) {
            var form = document.getElementById('formularioProducto');
            var titulo = document.getElementById('tituloFormulario');
            var accionInput = document.getElementById('accion');
            var botonGuardar = document.getElementById('botonGuardar');
            
            // Mostrar el formulario
            form.style.display = 'block';
            titulo.innerText = 'Editar Producto';
            botonGuardar.innerText = 'Actualizar Producto';
            accionInput.value = 'editar'; // Cambiar la acción

            // Rellenar el formulario con los datos del producto
            document.getElementById('productoId').value = producto.id;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('descripcion').value = producto.descripcion;
            document.getElementById('precio').value = producto.precio;
            document.getElementById('categoria_id').value = producto.categoria_id;
            document.getElementById('variedad').value = producto.variedad;
            document.getElementById('stock').value = producto.stock;
            document.getElementById('imagen').value = producto.imagen;
        } else {
            alert("Producto no encontrado.");
        }
    }
</script> 