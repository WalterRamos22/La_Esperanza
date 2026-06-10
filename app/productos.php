<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$rol = $usuario["rol"];

if ($_SERVER["REQUEST_METHOD"] === "POST" && $rol === "productor") {
    $sql = "INSERT INTO productos
            (id_productor, nombre, descripcion, precio, cantidad, unidad_medida)
            VALUES
            (:id_productor, :nombre, :descripcion, :precio, :cantidad, :unidad_medida)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        "id_productor" => $usuario["id"],
        "nombre" => $_POST["nombre"],
        "descripcion" => $_POST["descripcion"],
        "precio" => $_POST["precio"],
        "cantidad" => $_POST["cantidad"],
        "unidad_medida" => $_POST["unidad_medida"]
    ]);

    header("Location: productos.php?ok=1");
    exit;
}

$productos = $pdo->query("
    SELECT p.*, u.nombre AS productor
    FROM productos p
    INNER JOIN usuarios u ON p.id_productor = u.id_usuario
    WHERE p.estado = 'activo'
    ORDER BY p.id_producto DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Productos - La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

<div class="main-full">
    <a href="dashboard.php" class="btn btn-secondary mb-3">Volver al dashboard</a>

    <div class="card-box">
        <h3>Productos agrícolas</h3>

        <p class="text-muted">
            Consulta y registro de productos disponibles dentro de la comunidad.
        </p>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert alert-success">Producto registrado correctamente.</div>
        <?php endif; ?>

        <?php if ($rol === "productor"): ?>
            <form method="POST" class="row g-3 mb-4">
                <div class="col-md-4">
                    <label class="form-label">Producto</label>
                    <input type="text" name="nombre" class="form-control" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Descripción</label>
                    <input type="text" name="descripcion" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Precio</label>
                    <input type="number" step="0.01" name="precio" class="form-control" required>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" class="form-control" required>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Unidad de medida</label>
                    <input type="text" name="unidad_medida" class="form-control" placeholder="libra, quintal, unidad" required>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button class="btn btn-success">Guardar producto</button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($rol !== "productor"): ?>
            <div class="alert alert-info">
                Solo los productores pueden registrar productos. Usted puede consultar los productos disponibles.
            </div>
        <?php endif; ?>

        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Productor</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Unidad</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($productos as $p): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($p["nombre"]); ?></td>
                        <td><?php echo htmlspecialchars($p["productor"]); ?></td>
                        <td><?php echo htmlspecialchars($p["descripcion"]); ?></td>
                        <td>Q<?php echo number_format($p["precio"], 2); ?></td>
                        <td><?php echo $p["cantidad"]; ?></td>
                        <td><?php echo htmlspecialchars($p["unidad_medida"]); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>