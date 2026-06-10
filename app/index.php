<?php
session_start();

if (isset($_SESSION["usuario"])) {
    header("Location: dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comunidad La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body class="login-body">

<div class="login-card">
    <h3 class="text-center mb-3">Comunidad La Esperanza</h3>

    <p class="text-center text-muted mb-4">
        Sistema de Gestión y Comercialización Agrícola
    </p>

    <?php if (isset($_GET["error"])): ?>
        <div class="alert alert-danger">
            Teléfono o contraseña incorrectos.
        </div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <div class="mb-3">
            <label class="form-label">Número de teléfono</label>
            <input type="text" name="telefono" class="form-control" placeholder="Ingrese su teléfono" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Contraseña</label>
            <input type="password" name="password" class="form-control" placeholder="Ingrese su contraseña" required>
        </div>

        <button class="btn btn-success w-100">
            Ingresar al sistema
        </button>
    </form>

    <div class="mt-4 small text-muted">
        <strong>Usuarios de prueba:</strong><br>
        Admin: 55550001 / admin123<br>
        Productor: 55550002 / prod123<br>
        Comprador: 55550003 / comp123
    </div>
</div>

</body>
</html>