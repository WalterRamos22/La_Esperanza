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
    REGISTRAR ENTREGA
    Solo el productor puede registrar entregas de solicitudes confirmadas.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar_entrega"]) && $rol === "productor") {
    $idSolicitud = $_POST["id_solicitud"] ?? "";
    $fechaEntrega = $_POST["fecha_entrega"] ?? "";
    $lugar = $_POST["lugar"] ?? "";
    $observacion = $_POST["observacion"] ?? "";

    $stmtSolicitud = $pdo->prepare("
        SELECT s.*, p.id_productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        WHERE s.id_solicitud = :id_solicitud
        AND p.id_productor = :id_productor
        AND s.estado = 'confirmada'
    ");

    $stmtSolicitud->execute([
        "id_solicitud" => $idSolicitud,
        "id_productor" => $usuario["id"]
    ]);

    $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);

    if (!$solicitud) {
        $mensaje = "La solicitud no existe, no pertenece al productor o no está confirmada.";
    } elseif ($fechaEntrega === "" || $lugar === "") {
        $mensaje = "Debe ingresar fecha y lugar de entrega.";
    } else {
        $pdo->beginTransaction();

        try {
            $sqlEntrega = "
                INSERT INTO entregas
                (id_solicitud, fecha_entrega, lugar, observacion)
                VALUES
                (:id_solicitud, :fecha_entrega, :lugar, :observacion)
            ";

            $stmtEntrega = $pdo->prepare($sqlEntrega);

            $stmtEntrega->execute([
                "id_solicitud" => $idSolicitud,
                "fecha_entrega" => $fechaEntrega,
                "lugar" => $lugar,
                "observacion" => $observacion
            ]);

            $sqlActualizar = "
                UPDATE solicitudes
                SET estado = 'finalizada'
                WHERE id_solicitud = :id_solicitud
            ";

            $stmtActualizar = $pdo->prepare($sqlActualizar);

            $stmtActualizar->execute([
                "id_solicitud" => $idSolicitud
            ]);

            $pdo->commit();

            header("Location: entregas.php?ok=1");
            exit;

        } catch (Exception $e) {
            $pdo->rollBack();
            $mensaje = "No se pudo registrar la entrega.";
        }
    }
}

/*
    SOLICITUDES CONFIRMADAS DEL PRODUCTOR
*/
$solicitudesConfirmadas = [];

if ($rol === "productor") {
    $stmtConfirmadas = $pdo->prepare("
        SELECT 
            s.id_solicitud,
            s.cantidad,
            s.fecha_solicitud,
            p.nombre AS producto,
            p.precio,
            u.nombre AS comprador
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios u ON s.id_comprador = u.id_usuario
        WHERE p.id_productor = :id_productor
        AND s.estado = 'confirmada'
        ORDER BY s.id_solicitud DESC
    ");

    $stmtConfirmadas->execute([
        "id_productor" => $usuario["id"]
    ]);

    $solicitudesConfirmadas = $stmtConfirmadas->fetchAll(PDO::FETCH_ASSOC);
}

/*
    LISTADO DE ENTREGAS SEGUN ROL
*/
$stmtEntregas = null;

if ($rol === "administrador") {
    $stmtEntregas = $pdo->query("
        SELECT 
            e.*,
            s.cantidad,
            s.estado,
            p.nombre AS producto,
            p.precio,
            c.nombre AS comprador,
            pr.nombre AS productor
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios c ON s.id_comprador = c.id_usuario
        INNER JOIN usuarios pr ON p.id_productor = pr.id_usuario
        ORDER BY e.id_entrega DESC
    ");
}

if ($rol === "productor") {
    $stmtEntregas = $pdo->prepare("
        SELECT 
            e.*,
            s.cantidad,
            s.estado,
            p.nombre AS producto,
            p.precio,
            c.nombre AS comprador
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios c ON s.id_comprador = c.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY e.id_entrega DESC
    ");

    $stmtEntregas->execute([
        "id_productor" => $usuario["id"]
    ]);
}

if ($rol === "comprador") {
    $stmtEntregas = $pdo->prepare("
        SELECT 
            e.*,
            s.cantidad,
            s.estado,
            p.nombre AS producto,
            p.precio,
            pr.nombre AS productor
        FROM entregas e
        INNER JOIN solicitudes s ON e.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios pr ON p.id_productor = pr.id_usuario
        WHERE s.id_comprador = :id_comprador
        ORDER BY e.id_entrega DESC
    ");

    $stmtEntregas->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

$entregas = [];

if ($stmtEntregas) {
    $entregas = $stmtEntregas->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entregas - La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css?v=3" rel="stylesheet">
</head>

<body>

<div class="main-full">
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        Volver al dashboard
    </a>

    <div class="card-box">
        <h3>Entregas</h3>

        <p class="text-muted">
            Registro y consulta de entregas realizadas en la comunidad.
        </p>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert alert-success">
                Entrega registrada correctamente. La solicitud fue finalizada.
            </div>
        <?php endif; ?>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($rol === "productor"): ?>
            <div class="card-box mb-4">
                <h5>Registrar entrega</h5>

                <?php if (count($solicitudesConfirmadas) === 0): ?>
                    <div class="alert alert-info">
                        No tiene solicitudes confirmadas pendientes de entrega.
                    </div>
                <?php endif; ?>

                <?php if (count($solicitudesConfirmadas) > 0): ?>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="registrar_entrega" value="1">

                        <div class="col-md-4">
                            <label class="form-label">Solicitud confirmada</label>

                            <select name="id_solicitud" class="form-select" required>
                                <option value="">Seleccione una solicitud</option>

                                <?php foreach ($solicitudesConfirmadas as $s): ?>
                                    <option value="<?php echo $s["id_solicitud"]; ?>">
                                        #<?php echo $s["id_solicitud"]; ?>
                                        - <?php echo htmlspecialchars($s["producto"]); ?>
                                        - Comprador: <?php echo htmlspecialchars($s["comprador"]); ?>
                                        - Cantidad: <?php echo $s["cantidad"]; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Fecha de entrega</label>
                            <input type="date" name="fecha_entrega" class="form-control" required>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Lugar de entrega</label>
                            <input type="text" name="lugar" class="form-control" required>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Observación</label>
                            <input type="text" name="observacion" class="form-control">
                        </div>

                        <div class="col-md-3">
                            <button class="btn btn-success">
                                Registrar entrega
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($rol === "administrador"): ?>
            <div class="alert alert-info">
                Vista general de entregas registradas en el sistema.
            </div>
        <?php endif; ?>

        <?php if ($rol === "comprador"): ?>
            <div class="alert alert-info">
                Aquí puede consultar las entregas asociadas a sus solicitudes.
            </div>
        <?php endif; ?>

        <h5>Entregas registradas</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID Entrega</th>
                        <th>Solicitud</th>
                        <th>Producto</th>

                        <?php if ($rol === "administrador" || $rol === "productor"): ?>
                            <th>Comprador</th>
                        <?php endif; ?>

                        <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                            <th>Productor</th>
                        <?php endif; ?>

                        <th>Cantidad</th>
                        <th>Total estimado</th>
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

                            <?php if ($rol === "administrador" || $rol === "productor"): ?>
                                <td><?php echo htmlspecialchars($e["comprador"]); ?></td>
                            <?php endif; ?>

                            <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                                <td><?php echo htmlspecialchars($e["productor"]); ?></td>
                            <?php endif; ?>

                            <td><?php echo $e["cantidad"]; ?></td>
                            <td>Q<?php echo number_format($e["cantidad"] * $e["precio"], 2); ?></td>
                            <td><?php echo $e["fecha_entrega"]; ?></td>
                            <td><?php echo htmlspecialchars($e["lugar"]); ?></td>
                            <td><?php echo htmlspecialchars($e["observacion"] ?? ""); ?></td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($entregas) === 0): ?>
                        <tr>
                            <td colspan="10" class="text-center text-muted">
                                No hay entregas registradas.
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