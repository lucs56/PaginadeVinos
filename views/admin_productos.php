<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include '../db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$mensaje = "";
$productos = [];

// Función para manejar la subida de imágenes
function subirImagen($archivo) {
    $target_dir = __DIR__ . "/../uploads/"; // Carpeta de destino
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true); // Crear carpeta si no existe
    }

    $imagen = basename($archivo['name']);
    $imagen = preg_replace('/[^A-Za-z0-9._-]/', '', $imagen); // Normalizar nombre de archivo
    $target_file = $target_dir . $imagen;

    // Validar el tipo de archivo
    $tipoArchivo = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    if (!in_array($tipoArchivo, ['jpg', 'jpeg', 'png', 'gif'])) {
        return false; // Tipo no permitido
    }

    if (move_uploaded_file($archivo['tmp_name'], $target_file)) {
        return $imagen; // Devolver nombre del archivo
    } else {
        return false;
    }
}

// Crear un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "crear") {
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $variedad = htmlspecialchars($_POST['variedad']);
    $stock = (int)$_POST['stock'];

    // Subir imagen
    $imagen = subirImagen($_FILES['imagen']);
    if ($imagen) {
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
    } else {
        $mensaje = "Error al subir la imagen. Solo se permiten archivos JPG, JPEG, PNG y GIF.";
    }
}

// Editar un producto
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['accion']) && $_POST['accion'] == "editar") {
    $id = (int)$_POST['id'];
    $nombre = htmlspecialchars($_POST['nombre']);
    $descripcion = htmlspecialchars($_POST['descripcion']);
    $precio = (float)$_POST['precio'];
    $categoria_id = (int)$_POST['categoria_id'];
    $variedad = htmlspecialchars($_POST['variedad']);
    $stock = (int)$_POST['stock'];

    // Comprobar si se subió una nueva imagen
    if (!empty($_FILES['imagen']['name'])) {
        $imagen = subirImagen($_FILES['imagen']);
        if ($imagen) {
            $sql = "UPDATE vinos 
                    SET nombre = :nombre, descripcion = :descripcion, precio = :precio, 
                        categoria_id = :categoria_id, variedad = :variedad, stock = :stock, imagen = :imagen 
                    WHERE id = :id";
            $params = [
                'nombre' => $nombre,
                'descripcion' => $descripcion,
                'precio' => $precio,
                'categoria_id' => $categoria_id,
                'variedad' => $variedad,
                'stock' => $stock,
                'imagen' => $imagen,
                'id' => $id
            ];
        } else {
            $mensaje = "Error al subir la imagen.";
        }
    } else {
        $sql = "UPDATE vinos 
                SET nombre = :nombre, descripcion = :descripcion, precio = :precio, 
                    categoria_id = :categoria_id, variedad = :variedad, stock = :stock
                WHERE id = :id";
        $params = [
            'nombre' => $nombre,
            'descripcion' => $descripcion,
            'precio' => $precio,
            'categoria_id' => $categoria_id,
            'variedad' => $variedad,
            'stock' => $stock,
            'id' => $id
        ];
    }

    // Ejecutar la consulta
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
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

// Leer productos existentes
$sql = "SELECT vinos.*, categorias.nombre AS categoria_nombre 
        FROM vinos 
        LEFT JOIN categorias ON vinos.categoria_id = categorias.id";
$stmt = $pdo->query($sql);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                    <td><img src="../uploads/<?php echo $producto['imagen']; ?>" style="width: 50px;"></td>
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

        <div id="formularioProducto" style="display: none;">
            <h2 id="tituloFormulario">Agregar Producto</h2>
            <form id="formProducto" method="POST" enctype="multipart/form-data">
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
                <label for="imagen">Imagen:</label>
                <input type="file" id="imagen" name="imagen" required>
                <br>
                <button type="submit" id="botonGuardar">Guardar Producto</button>
            </form>
        </div>
    </div>
    <?php include '../includes/footer.php'; ?>

    <script>
    function mostrarFormulario(accion) {
        var form = document.getElementById('formularioProducto');
        var titulo = document.getElementById('tituloFormulario');
        var accionInput = document.getElementById('accion');
        var botonGuardar = document.getElementById('botonGuardar');
        
        form.style.display = 'block';

        if (accion === 'crear') {
            titulo.innerText = 'Agregar Producto';
            botonGuardar.innerText = 'Guardar Producto';
            accionInput.value = 'crear';
            form.reset();
        }
    }

    function mostrarFormularioEditar(id) {
        var productos = <?php echo json_encode($productos); ?>;
        var producto = productos.find(producto => producto.id == id);

        if (producto) {
            var form = document.getElementById('formularioProducto');
            var titulo = document.getElementById('tituloFormulario');
            var accionInput = document.getElementById('accion');
            var botonGuardar = document.getElementById('botonGuardar');
            
            form.style.display = 'block';
            titulo.innerText = 'Editar Producto';
            botonGuardar.innerText = 'Actualizar Producto';
            accionInput.value = 'editar';

            document.getElementById('productoId').value = producto.id;
            document.getElementById('nombre').value = producto.nombre;
            document.getElementById('descripcion').value = producto.descripcion;
            document.getElementById('precio').value = producto.precio;
            document.getElementById('categoria_id').value = producto.categoria_id;
            document.getElementById('variedad').value = producto.variedad;
            document.getElementById('stock').value = producto.stock;
        } else {
            alert("Producto no encontrado.");
        }
    }
    </script>
</body>
</html>
