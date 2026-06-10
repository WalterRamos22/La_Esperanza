<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$rol = $usuario["rol"];

$totalProductos = $pdo->query("SELECT COUNT(*) FROM productos")->fetchColumn();
$totalSolicitudes = $pdo->query("SELECT COUNT(*) FROM solicitudes")->fetchColumn();
$totalEntregas = $pdo->query("SELECT COUNT(*) FROM entregas")->fetchColumn();
$totalUsuarios = $pdo->query("SELECT COUNT(*) FROM usuarios")->fetchColumn();

function nombreRol($rol) {
    if ($rol === "administrador") {
        return "Administrador comunitario";
    }

    if ($rol === "productor") {
        return "Productor";
    }

    if ($rol === "comprador") {
        return "Comprador";
    }

    return "Usuario";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Comunidad La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

<div class="sidebar">
    <h4 class="mb-4">La Esperanza</h4>

    <a href="dashboard.php">Dashboard</a>
    <a href="productos.php">Productos</a>

    <?php if ($rol === "comprador"): ?>
        <a href="solicitudes.php">Mis solicitudes</a>
        <a href="entregas.php">Mis entregas</a>
        <a href="calificaciones.php">Calificaciones</a>
        <a href="historial.php">Historial</a>
    <?php endif; ?>

    <?php if ($rol === "productor"): ?>
        <a href="solicitudes.php">Solicitudes recibidas</a>
        <a href="entregas.php">Entregas</a>
        <a href="calificaciones.php">Calificaciones recibidas</a>
        <a href="historial.php">Historial</a>
    <?php endif; ?>

    <?php if ($rol === "administrador"): ?>
        <a href="#">Usuarios</a>
        <a href="solicitudes.php">Seguimiento</a>
        <a href="entregas.php">Consultar entregas</a>
        <a href="calificaciones.php">Consultar calificaciones</a>
        <a href="historial.php">Historial</a>
    <?php endif; ?>

    <a href="logout.php">Cerrar sesión</a>
</div>

<div class="main">
    <div class="topbar">
        <div>
            <h3 class="mb-0">Sistema Comunitario</h3>
            <small>Gestión agrícola comunitaria</small>
        </div>

        <span class="badge bg-success p-2">
            <?php echo strtoupper(nombreRol($rol)); ?>
        </span>
    </div>

    <div class="row g-4">
        <div class="col-md-3">
            <div class="stats bg-success">
                <h5>Productos</h5>
                <h2><?php echo $totalProductos; ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats bg-primary">
                <h5>Solicitudes</h5>
                <h2><?php echo $totalSolicitudes; ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats bg-danger">
                <h5>Entregas</h5>
                <h2><?php echo $totalEntregas; ?></h2>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stats bg-warning text-dark">
                <h5>Usuarios</h5>
                <h2><?php echo $totalUsuarios; ?></h2>
            </div>
        </div>
    </div>

    <div class="card-box mt-4">
        <h4>
            Bienvenido, <?php echo htmlspecialchars($usuario["nombre"]); ?>
        </h4>

        <p>
            Sistema de Gestión y Comercialización para la Comunidad Agrícola Rural “La Esperanza”.
        </p>

        <p class="mb-0">
            Rol actual:
            <strong><?php echo htmlspecialchars(nombreRol($rol)); ?></strong>
        </p>
    </div>

    <div class="card-box mt-4">
        <h5>Opciones disponibles</h5>

        <div class="row g-3 mt-2">
            <div class="col-md-4">
                <a href="productos.php" class="btn btn-outline-success w-100">
                    Ver productos agrícolas
                </a>
            </div>

            <?php if ($rol === "comprador"): ?>
                <div class="col-md-4">
                    <a href="solicitudes.php" class="btn btn-outline-primary w-100">
                        Crear o consultar solicitudes
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="entregas.php" class="btn btn-outline-danger w-100">
                        Consultar mis entregas
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="calificaciones.php" class="btn btn-outline-warning w-100">
                        Registrar calificación
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="historial.php" class="btn btn-outline-dark w-100">
                        Ver mi historial
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === "productor"): ?>
                <div class="col-md-4">
                    <a href="productos.php" class="btn btn-outline-success w-100">
                        Registrar producto
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="solicitudes.php" class="btn btn-outline-primary w-100">
                        Revisar solicitudes recibidas
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="entregas.php" class="btn btn-outline-danger w-100">
                        Registrar entrega
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="calificaciones.php" class="btn btn-outline-warning w-100">
                        Ver calificaciones recibidas
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="historial.php" class="btn btn-outline-dark w-100">
                        Ver historial
                    </a>
                </div>
            <?php endif; ?>

            <?php if ($rol === "administrador"): ?>
                <div class="col-md-4">
                    <a href="productos.php" class="btn btn-outline-success w-100">
                        Consultar productos
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="solicitudes.php" class="btn btn-outline-primary w-100">
                        Seguimiento de solicitudes
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="entregas.php" class="btn btn-outline-danger w-100">
                        Consultar entregas
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="calificaciones.php" class="btn btn-outline-warning w-100">
                        Consultar calificaciones
                    </a>
                </div>

                <div class="col-md-4">
                    <a href="historial.php" class="btn btn-outline-dark w-100">
                        Historial del sistema
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card-box mt-4">
        <h5>Estado actual del sistema</h5>

        <div class="table-responsive mt-3">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td>Login</td>
                        <td>Ingreso con teléfono, contraseña y rol desde base de datos.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>

                    <tr>
                        <td>Productos</td>
                        <td>Registro y consulta de productos agrícolas.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>

                    <tr>
                        <td>Solicitudes</td>
                        <td>Creación, confirmación y cancelación de solicitudes.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>

                    <tr>
                        <td>Entregas</td>
                        <td>Registro de entregas para solicitudes confirmadas.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>

                    <tr>
                        <td>Calificaciones</td>
                        <td>Registro y consulta de calificaciones por transacciones finalizadas.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>

                    <tr>
                        <td>Historial</td>
                        <td>Consulta general de productos, solicitudes, entregas y calificaciones.</td>
                        <td><span class="badge bg-success">Funcional</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>