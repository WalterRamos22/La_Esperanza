<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$rol = $usuario["rol"];
$mensaje = "";

/*
    REGISTRAR SOLICITUD
    Solo el comprador puede crear solicitudes.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_solicitud"]) && $rol === "comprador") {
    $idProducto = $_POST["id_producto"] ?? "";
    $cantidad = (int) ($_POST["cantidad"] ?? 0);
    $observacion = $_POST["observacion"] ?? "";

    $stmtProducto = $pdo->prepare("
        SELECT * 
        FROM productos 
        WHERE id_producto = :id_producto 
        AND estado = 'activo'
    ");

    $stmtProducto->execute([
        "id_producto" => $idProducto
    ]);

    $producto = $stmtProducto->fetch(PDO::FETCH_ASSOC);

    if (!$producto) {
        $mensaje = "El producto seleccionado no existe o no está disponible.";
    } elseif ($cantidad <= 0) {
        $mensaje = "La cantidad solicitada debe ser mayor que cero.";
    } elseif ($cantidad > $producto["cantidad"]) {
        $mensaje = "La cantidad solicitada supera la disponibilidad del producto.";
    } else {
        $sql = "
            INSERT INTO solicitudes
            (id_comprador, id_producto, cantidad, observacion, estado)
            VALUES
            (:id_comprador, :id_producto, :cantidad, :observacion, 'pendiente')
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "id_comprador" => $usuario["id"],
            "id_producto" => $idProducto,
            "cantidad" => $cantidad,
            "observacion" => $observacion
        ]);

        header("Location: solicitudes.php?ok=1");
        exit;
    }
}

/*
    CONFIRMAR SOLICITUD
    Solo el productor dueño del producto puede confirmar solicitudes pendientes.
*/
if (isset($_GET["confirmar"]) && $rol === "productor") {
    $idSolicitud = $_GET["confirmar"];

    $sql = "
        UPDATE solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        SET s.estado = 'confirmada'
        WHERE s.id_solicitud = :id_solicitud
        AND p.id_productor = :id_productor
        AND s.estado = 'pendiente'
    ";

    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        "id_solicitud" => $idSolicitud,
        "id_productor" => $usuario["id"]
    ]);

    header("Location: solicitudes.php?confirmada=1");
    exit;
}

/*
    CANCELAR SOLICITUD
    El comprador puede cancelar sus solicitudes.
    El productor puede cancelar solicitudes sobre sus productos.
*/
if (isset($_GET["cancelar"])) {
    $idSolicitud = $_GET["cancelar"];

    if ($rol === "comprador") {
        $sql = "
            UPDATE solicitudes
            SET estado = 'cancelada'
            WHERE id_solicitud = :id_solicitud
            AND id_comprador = :id_comprador
            AND estado IN ('pendiente','confirmada')
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "id_solicitud" => $idSolicitud,
            "id_comprador" => $usuario["id"]
        ]);
    }

    if ($rol === "productor") {
        $sql = "
            UPDATE solicitudes s
            INNER JOIN productos p ON s.id_producto = p.id_producto
            SET s.estado = 'cancelada'
            WHERE s.id_solicitud = :id_solicitud
            AND p.id_productor = :id_productor
            AND s.estado IN ('pendiente','confirmada')
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "id_solicitud" => $idSolicitud,
            "id_productor" => $usuario["id"]
        ]);
    }

    header("Location: solicitudes.php?cancelada=1");
    exit;
}

/*
    PRODUCTOS DISPONIBLES
    Solo se cargan para comprador, porque el comprador crea solicitudes.
*/
$productos = [];

if ($rol === "comprador") {
    $productos = $pdo->query("
        SELECT p.*, u.nombre AS productor
        FROM productos p
        INNER JOIN usuarios u ON p.id_productor = u.id_usuario
        WHERE p.estado = 'activo'
        AND p.cantidad > 0
        ORDER BY p.nombre ASC
    ")->fetchAll(PDO::FETCH_ASSOC);
}

/*
    LISTADO DE SOLICITUDES SEGUN ROL
*/
$stmtSolicitudes = null;

if ($rol === "administrador") {
    $sqlSolicitudes = "
        SELECT 
            s.*, 
            p.nombre AS producto, 
            p.precio,
            u.nombre AS comprador, 
            pr.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios u ON s.id_comprador = u.id_usuario
        INNER JOIN usuarios pr ON p.id_productor = pr.id_usuario
        ORDER BY s.id_solicitud DESC
    ";

    $stmtSolicitudes = $pdo->query($sqlSolicitudes);
}

if ($rol === "comprador") {
    $sqlSolicitudes = "
        SELECT 
            s.*, 
            p.nombre AS producto, 
            p.precio,
            u.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios u ON p.id_productor = u.id_usuario
        WHERE s.id_comprador = :id_comprador
        ORDER BY s.id_solicitud DESC
    ";

    $stmtSolicitudes = $pdo->prepare($sqlSolicitudes);

    $stmtSolicitudes->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

if ($rol === "productor") {
    $sqlSolicitudes = "
        SELECT 
            s.*, 
            p.nombre AS producto, 
            p.precio,
            u.nombre AS comprador
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios u ON s.id_comprador = u.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY s.id_solicitud DESC
    ";

    $stmtSolicitudes = $pdo->prepare($sqlSolicitudes);

    $stmtSolicitudes->execute([
        "id_productor" => $usuario["id"]
    ]);
}

$solicitudes = [];

if ($stmtSolicitudes) {
    $solicitudes = $stmtSolicitudes->fetchAll(PDO::FETCH_ASSOC);
}

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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes - La Esperanza</title>
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
        <h3>Solicitudes de compra</h3>

        <p class="text-muted">
            Gestión de solicitudes realizadas sobre productos agrícolas de la comunidad.
        </p>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert alert-success">
                Solicitud registrada correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["confirmada"])): ?>
            <div class="alert alert-success">
                Solicitud confirmada correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["cancelada"])): ?>
            <div class="alert alert-warning">
                Solicitud cancelada correctamente.
            </div>
        <?php endif; ?>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($rol === "comprador"): ?>
            <form method="POST" class="row g-3 mb-4">
                <input type="hidden" name="crear_solicitud" value="1">

                <div class="col-md-4">
                    <label class="form-label">Producto</label>

                    <select name="id_producto" class="form-select" required>
                        <option value="">Seleccione un producto</option>

                        <?php foreach ($productos as $p): ?>
                            <option value="<?php echo $p["id_producto"]; ?>">
                                <?php echo htmlspecialchars($p["nombre"]); ?>
                                - Q<?php echo number_format($p["precio"], 2); ?>
                                - Disponible: <?php echo $p["cantidad"]; ?>
                                <?php echo htmlspecialchars($p["unidad_medida"]); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Cantidad</label>
                    <input type="number" name="cantidad" class="form-control" min="1" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Observación</label>
                    <input type="text" name="observacion" class="form-control" placeholder="Opcional">
                </div>

                <div class="col-md-2 d-flex align-items-end">
                    <button class="btn btn-success w-100">
                        Solicitar
                    </button>
                </div>
            </form>
        <?php endif; ?>

        <?php if ($rol === "productor"): ?>
            <div class="alert alert-info">
                Aquí puede confirmar o cancelar solicitudes realizadas sobre sus productos.
            </div>
        <?php endif; ?>

        <?php if ($rol === "administrador"): ?>
            <div class="alert alert-info">
                Vista de seguimiento general para el administrador comunitario.
            </div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Producto</th>

                        <?php if ($rol === "administrador" || $rol === "productor"): ?>
                            <th>Comprador</th>
                        <?php endif; ?>

                        <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                            <th>Productor</th>
                        <?php endif; ?>

                        <th>Cantidad</th>
                        <th>Total estimado</th>
                        <th>Observación</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($solicitudes as $s): ?>
                        <tr>
                            <td>
                                <?php echo $s["id_solicitud"]; ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($s["producto"]); ?>
                            </td>

                            <?php if ($rol === "administrador" || $rol === "productor"): ?>
                                <td>
                                    <?php echo htmlspecialchars($s["comprador"]); ?>
                                </td>
                            <?php endif; ?>

                            <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                                <td>
                                    <?php echo htmlspecialchars($s["productor"]); ?>
                                </td>
                            <?php endif; ?>

                            <td>
                                <?php echo $s["cantidad"]; ?>
                            </td>

                            <td>
                                Q<?php echo number_format($s["cantidad"] * $s["precio"], 2); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($s["observacion"] ?? ""); ?>
                            </td>

                            <td>
                                <span class="badge <?php echo badgeEstado($s["estado"]); ?>">
                                    <?php echo strtoupper($s["estado"]); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo $s["fecha_solicitud"]; ?>
                            </td>

                            <td>
                                <?php if ($rol === "productor" && $s["estado"] === "pendiente"): ?>
                                    <a href="solicitudes.php?confirmar=<?php echo $s["id_solicitud"]; ?>" class="btn btn-sm btn-primary">
                                        Confirmar
                                    </a>
                                <?php endif; ?>

                                <?php if (($rol === "productor" || $rol === "comprador") && ($s["estado"] === "pendiente" || $s["estado"] === "confirmada")): ?>
                                    <a href="solicitudes.php?cancelar=<?php echo $s["id_solicitud"]; ?>" class="btn btn-sm btn-danger">
                                        Cancelar
                                    </a>
                                <?php endif; ?>

                                <?php if ($rol === "administrador"): ?>
                                    <span class="text-muted">
                                        Seguimiento
                                    </span>
                                <?php endif; ?>

                                <?php if ($s["estado"] === "cancelada" || $s["estado"] === "finalizada"): ?>
                                    <span class="text-muted">
                                        Sin acciones
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($solicitudes) === 0): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                No hay solicitudes registradas.
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