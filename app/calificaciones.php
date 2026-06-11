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
    REGISTRAR CALIFICACION
    Solo el comprador puede calificar solicitudes finalizadas.
*/
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar_calificacion"]) && $rol === "comprador") {
    $idSolicitud = $_POST["id_solicitud"] ?? "";
    $puntuacion = (int) ($_POST["puntuacion"] ?? 0);
    $comentario = $_POST["comentario"] ?? "";

    $stmtSolicitud = $pdo->prepare("
        SELECT s.*
        FROM solicitudes s
        WHERE s.id_solicitud = :id_solicitud
        AND s.id_comprador = :id_comprador
        AND s.estado = 'finalizada'
    ");

    $stmtSolicitud->execute([
        "id_solicitud" => $idSolicitud,
        "id_comprador" => $usuario["id"]
    ]);

    $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);

    $stmtExiste = $pdo->prepare("
        SELECT COUNT(*) 
        FROM calificaciones 
        WHERE id_solicitud = :id_solicitud
    ");

    $stmtExiste->execute([
        "id_solicitud" => $idSolicitud
    ]);

    $yaCalificada = $stmtExiste->fetchColumn();

    if (!$solicitud) {
        $mensaje = "La solicitud no existe, no pertenece al comprador o no está finalizada.";
    } elseif ($puntuacion < 1 || $puntuacion > 5) {
        $mensaje = "La puntuación debe estar entre 1 y 5.";
    } elseif ($yaCalificada > 0) {
        $mensaje = "Esta solicitud ya fue calificada.";
    } else {
        $sql = "
            INSERT INTO calificaciones
            (id_solicitud, id_comprador, puntuacion, comentario)
            VALUES
            (:id_solicitud, :id_comprador, :puntuacion, :comentario)
        ";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            "id_solicitud" => $idSolicitud,
            "id_comprador" => $usuario["id"],
            "puntuacion" => $puntuacion,
            "comentario" => $comentario
        ]);

        header("Location: calificaciones.php?ok=1");
        exit;
    }
}

/*
    SOLICITUDES FINALIZADAS SIN CALIFICAR
    Solo se muestran al comprador.
*/
$solicitudesFinalizadas = [];

if ($rol === "comprador") {
    $stmtFinalizadas = $pdo->prepare("
        SELECT 
            s.id_solicitud,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            pr.nombre AS productor
        FROM solicitudes s
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios pr ON p.id_productor = pr.id_usuario
        LEFT JOIN calificaciones c ON s.id_solicitud = c.id_solicitud
        WHERE s.id_comprador = :id_comprador
        AND s.estado = 'finalizada'
        AND c.id_calificacion IS NULL
        ORDER BY s.id_solicitud DESC
    ");

    $stmtFinalizadas->execute([
        "id_comprador" => $usuario["id"]
    ]);

    $solicitudesFinalizadas = $stmtFinalizadas->fetchAll(PDO::FETCH_ASSOC);
}

/*
    LISTADO DE CALIFICACIONES SEGUN ROL
*/
$stmtCalificaciones = null;

if ($rol === "administrador") {
    $stmtCalificaciones = $pdo->query("
        SELECT 
            c.*,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador,
            productor.nombre AS productor
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON c.id_comprador = comprador.id_usuario
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        ORDER BY c.id_calificacion DESC
    ");
}

if ($rol === "comprador") {
    $stmtCalificaciones = $pdo->prepare("
        SELECT 
            c.*,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            productor.nombre AS productor
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios productor ON p.id_productor = productor.id_usuario
        WHERE c.id_comprador = :id_comprador
        ORDER BY c.id_calificacion DESC
    ");

    $stmtCalificaciones->execute([
        "id_comprador" => $usuario["id"]
    ]);
}

if ($rol === "productor") {
    $stmtCalificaciones = $pdo->prepare("
        SELECT 
            c.*,
            s.cantidad,
            p.nombre AS producto,
            p.precio,
            comprador.nombre AS comprador
        FROM calificaciones c
        INNER JOIN solicitudes s ON c.id_solicitud = s.id_solicitud
        INNER JOIN productos p ON s.id_producto = p.id_producto
        INNER JOIN usuarios comprador ON c.id_comprador = comprador.id_usuario
        WHERE p.id_productor = :id_productor
        ORDER BY c.id_calificacion DESC
    ");

    $stmtCalificaciones->execute([
        "id_productor" => $usuario["id"]
    ]);
}

$calificaciones = [];

if ($stmtCalificaciones) {
    $calificaciones = $stmtCalificaciones->fetchAll(PDO::FETCH_ASSOC);
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
    <title>Calificaciones - La Esperanza</title>
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
        <h3>Calificaciones</h3>

        <p class="text-muted">
            Registro y consulta de calificaciones sobre transacciones finalizadas.
        </p>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert alert-success">
                Calificación registrada correctamente.
            </div>
        <?php endif; ?>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if ($rol === "comprador"): ?>
            <div class="card-box mb-4">
                <h5>Registrar calificación</h5>

                <?php if (count($solicitudesFinalizadas) === 0): ?>
                    <div class="alert alert-info">
                        No tiene solicitudes finalizadas pendientes de calificar.
                    </div>
                <?php endif; ?>

                <?php if (count($solicitudesFinalizadas) > 0): ?>
                    <form method="POST" class="row g-3">
                        <input type="hidden" name="registrar_calificacion" value="1">

                        <div class="col-md-4">
                            <label class="form-label">Solicitud finalizada</label>

                            <select name="id_solicitud" class="form-select" required>
                                <option value="">Seleccione una solicitud</option>

                                <?php foreach ($solicitudesFinalizadas as $s): ?>
                                    <option value="<?php echo $s["id_solicitud"]; ?>">
                                        #<?php echo $s["id_solicitud"]; ?>
                                        - <?php echo htmlspecialchars($s["producto"]); ?>
                                        - Productor: <?php echo htmlspecialchars($s["productor"]); ?>
                                        - Total: Q<?php echo number_format($s["cantidad"] * $s["precio"], 2); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Puntuación</label>

                            <select name="puntuacion" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="5">5 - Excelente</option>
                                <option value="4">4 - Muy bueno</option>
                                <option value="3">3 - Bueno</option>
                                <option value="2">2 - Regular</option>
                                <option value="1">1 - Malo</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Comentario</label>
                            <input type="text" name="comentario" class="form-control" placeholder="Comentario opcional">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100">
                                Calificar
                            </button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if ($rol === "administrador"): ?>
            <div class="alert alert-info">
                Vista general de calificaciones registradas en el sistema.
            </div>
        <?php endif; ?>

        <?php if ($rol === "productor"): ?>
            <div class="alert alert-info">
                Aquí puede consultar las calificaciones recibidas sobre sus productos.
            </div>
        <?php endif; ?>

        <h5>Calificaciones registradas</h5>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Solicitud</th>
                        <th>Producto</th>

                        <?php if ($rol === "administrador" || $rol === "productor"): ?>
                            <th>Comprador</th>
                        <?php endif; ?>

                        <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                            <th>Productor</th>
                        <?php endif; ?>

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

                            <?php if ($rol === "administrador" || $rol === "productor"): ?>
                                <td><?php echo htmlspecialchars($c["comprador"]); ?></td>
                            <?php endif; ?>

                            <?php if ($rol === "administrador" || $rol === "comprador"): ?>
                                <td><?php echo htmlspecialchars($c["productor"]); ?></td>
                            <?php endif; ?>

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