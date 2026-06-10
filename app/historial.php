<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$rol = $usuario["rol"];

/*
    HISTORIAL DE PRODUCTOS
*/
if ($rol === "administrador") {
    $stmtProductos = $pdo->query("
        SELECT 
            p.id_producto,
            p.nombre AS producto,
            p.precio,
            p.cantidad,
            p.unidad_medida,
            p.fecha_registro,
            u.nombre AS productor
        FROM productos p
        INNER JOIN usuarios u ON p.id_productor = u.id_usuario
        ORDER BY p.fecha_registro DESC
    ");
}

if ($rol === "productor") {
    $stmtProductos = $pdo->prepare("
        SELECT 
            p.id_producto,
            p.nombre AS producto,
            p.precio,
            p.cantidad,
            p.unidad_medida,
            p.fecha_registro,
            u.nombre AS productor
        FROM productos p
        INNER JOIN usuarios u ON p.id_productor = u.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY p.fecha_registro DESC
    ");

    $stmtProductos->execute([
        "id_productor" => $usuario["id"]
    ]);
}

if ($rol === "comprador") {
    $stmtProductos = $pdo->query("
        SELECT 
            p.id_producto,
            p.nombre AS producto,
            p.precio,
            p.cantidad,
            p.unidad_medida,
            p.fecha_registro,
            u.nombre AS productor
        FROM productos p
        INNER JOIN usuarios u ON p.id_productor = u.id_usuario
        ORDER BY p.fecha_registro DESC
    ");
}

$productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

/*
    HISTORIAL DE SOLICITUDES
*/
if ($rol === "administrador") {
    $stmtSolicitudes = $pdo->query("
        SELECT 
            s.id_solicitud,
            s.cantidad,
            s.estado,
            s.fecha_solicitud,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        ORDER BY s.fecha_solicitud DESC
    ");
}

if ($rol === "productor") {
    $stmtSolicitudes = $pdo->prepare("
        SELECT 
            s.id_solicitud,
            s.cantidad,
            s.estado,
            s.fecha_solicitud,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY s.fecha_solicitud DESC
    ");

    $stmtSolicitudes->execute([
        "id_productor" => $usuario["id"]
    ]);
}

if ($rol === "comprador") {
    $stmtSolicitudes = $pdo->prepare("
        SELECT 
            s.id_solicitud,
            s.cantidad,
            s.estado,
            s.fecha_solicitud,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE s.id_comprador = :id_comprador
        ORDER BY s.fecha_solicitud DESC
    ");

    $stmtSolicitudes->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

$solicitudes = $stmtSolicitudes->fetchAll(PDO::FETCH_ASSOC);

/*
    HISTORIAL DE ENTREGAS
*/
if ($rol === "administrador") {
    $stmtEntregas = $pdo->query("
        SELECT 
            e.id_entrega,
            e.fecha_entrega,
            e.lugar,
            e.observacion,
            e.fecha_registro,
            s.id_solicitud,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        ORDER BY e.fecha_registro DESC
    ");
}

if ($rol === "productor") {
    $stmtEntregas = $pdo->prepare("
        SELECT 
            e.id_entrega,
            e.fecha_entrega,
            e.lugar,
            e.observacion,
            e.fecha_registro,
            s.id_solicitud,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY e.fecha_registro DESC
    ");

    $stmtEntregas->execute([
        "id_productor" => $usuario["id"]
    ]);
}

if ($rol === "comprador") {
    $stmtEntregas = $pdo->prepare("
        SELECT 
            e.id_entrega,
            e.fecha_entrega,
            e.lugar,
            e.observacion,
            e.fecha_registro,
            s.id_solicitud,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON s.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE s.id_comprador = :id_comprador
        ORDER BY e.fecha_registro DESC
    ");

    $stmtEntregas->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

$entregas = $stmtEntregas->fetchAll(PDO::FETCH_ASSOC);

/*
    HISTORIAL DE CALIFICACIONES
*/
if ($rol === "administrador") {
    $stmtCalificaciones = $pdo->query("
        SELECT 
            c.id_calificacion,
            c.puntuacion,
            c.comentario,
            c.fecha_registro,
            s.id_solicitud,
            p.nombre AS producto,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON c.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        ORDER BY c.fecha_registro DESC
    ");
}

if ($rol === "productor") {
    $stmtCalificaciones = $pdo->prepare("
        SELECT 
            c.id_calificacion,
            c.puntuacion,
            c.comentario,
            c.fecha_registro,
            s.id_solicitud,
            p.nombre AS producto,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON c.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY c.fecha_registro DESC
    ");

    $stmtCalificaciones->execute([
        "id_productor" => $usuario["id"]
    ]);
}

if ($rol === "comprador") {
    $stmtCalificaciones = $pdo->prepare("
        SELECT 
            c.id_calificacion,
            c.puntuacion,
            c.comentario,
            c.fecha_registro,
            s.id_solicitud,
            p.nombre AS producto,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON c.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE c.id_comprador = :id_comprador
        ORDER BY c.fecha_registro DESC
    ");

    $stmtCalificaciones->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

$calificaciones = $stmtCalificaciones->fetchAll(PDO::FETCH_ASSOC);

function badgeEstado($estado) {
    if ($estado === "pendiente") {
        return "bg-warning text-dark";
    }

    if ($estado === "confirmada") {
        return "bg-primary";
    }

    if ($estado === "cancelada") {
        return "bg-danger";
    }

    if ($estado === "finalizada") {
        return "bg-success";
    }

    return "bg-secondary";
}

function estrellas($puntuacion) {
    $texto = "";

    for ($i = 1; $i <= 5; $i++) {
        if ($i <= $puntuacion) {
            $texto .= "★";
        } else {
            $texto .= "☆";
        }
    }

    return $texto;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial - La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

<div class="main-full">
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        Volver al dashboard
    </a>

    <div class="card-box">
        <h3>Historial del sistema</h3>

        <p class="text-muted">
            Consulta general de productos, solicitudes, entregas y calificaciones registradas.
        </p>

        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="stats bg-success">
                    <h5>Productos</h5>
                    <h2><?php echo count($productos); ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats bg-primary">
                    <h5>Solicitudes</h5>
                    <h2><?php echo count($solicitudes); ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats bg-danger">
                    <h5>Entregas</h5>
                    <h2><?php echo count($entregas); ?></h2>
                </div>
            </div>

            <div class="col-md-3">
                <div class="stats bg-warning text-dark">
                    <h5>Calificaciones</h5>
                    <h2><?php echo count($calificaciones); ?></h2>
                </div>
            </div>
        </div>

        <h5>Productos registrados</h5>

        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Productor</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Unidad</th>
                        <th>Fecha registro</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($productos as $p): ?>
                        <tr>
                            <td><?php echo $p["id_producto"]; ?></td>
                            <td><?php echo htmlspecialchars($p["producto"]); ?></td>
                            <td><?php echo htmlspecialchars($p["productor"]); ?></td>
                            <td>Q<?php echo number_format($p["precio"], 2); ?></td>
                            <td><?php echo $p["cantidad"]; ?></td>
                            <td><?php echo htmlspecialchars($p["unidad_medida"]); ?></td>
                            <td><?php echo $p["fecha_registro"]; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($productos) === 0): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No hay productos registrados.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h5>Solicitudes registradas</h5>

        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>
                        <th>Comprador</th>
                        <th>Productor</th>
                        <th>Cantidad</th>
                        <th>Total estimado</th>
                        <th>Estado</th>
                        <th>Fecha solicitud</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($solicitudes as $s): ?>
                        <tr>
                            <td><?php echo $s["id_solicitud"]; ?></td>
                            <td><?php echo htmlspecialchars($s["producto"]); ?></td>
                            <td><?php echo htmlspecialchars($s["comprador"]); ?></td>
                            <td><?php echo htmlspecialchars($s["productor"]); ?></td>
                            <td><?php echo $s["cantidad"]; ?></td>
                            <td>Q<?php echo number_format($s["cantidad"] * $s["precio"], 2); ?></td>
                            <td>
                                <span class="badge <?php echo badgeEstado($s["estado"]); ?>">
                                    <?php echo strtoupper($s["estado"]); ?>
                                </span>
                            </td>
                            <td><?php echo $s["fecha_solicitud"]; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($solicitudes) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay solicitudes registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h5>Entregas registradas</h5>

        <div class="table-responsive mb-4">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID Entrega</th>
                        <th>Solicitud</th>
                        <th>Producto</th>
                        <th>Comprador</th>
                        <th>Productor</th>
                        <th>Fecha entrega</th>
                        <th>Lugar</th>
                        <th>Observación</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($entregas as $e): ?>
                        <tr>
                            <td><?php echo $e["id_entrega"]; ?></td>
                            <td>#<?php echo $e["id_solicitud"]; ?></td>
                            <td><?php echo htmlspecialchars($e["producto"]); ?></td>
                            <td><?php echo htmlspecialchars($e["comprador"]); ?></td>
                            <td><?php echo htmlspecialchars($e["productor"]); ?></td>
                            <td><?php echo $e["fecha_entrega"]; ?></td>
                            <td><?php echo htmlspecialchars($e["lugar"]); ?></td>
                            <td><?php echo htmlspecialchars($e["observacion"] ?? ""); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($entregas) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay entregas registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <h5>Calificaciones registradas</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitud</th>
                        <th>Producto</th>
                        <th>Comprador</th>
                        <th>Productor</th>
                        <th>Puntuación</th>
                        <th>Comentario</th>
                        <th>Fecha</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($calificaciones as $c): ?>
                        <tr>
                            <td><?php echo $c["id_calificacion"]; ?></td>
                            <td>#<?php echo $c["id_solicitud"]; ?></td>
                            <td><?php echo htmlspecialchars($c["producto"]); ?></td>
                            <td><?php echo htmlspecialchars($c["comprador"]); ?></td>
                            <td><?php echo htmlspecialchars($c["productor"]); ?></td>
                            <td>
                                <span class="text-warning fs-5">
                                    <?php echo estrellas($c["puntuacion"]); ?>
                                </span>
                                <br>
                                <small><?php echo $c["puntuacion"]; ?>/5</small>
                            </td>
                            <td><?php echo htmlspecialchars($c["comentario"] ?? ""); ?></td>
                            <td><?php echo $c["fecha_registro"]; ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($calificaciones) === 0): ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">
                                No hay calificaciones registradas.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>