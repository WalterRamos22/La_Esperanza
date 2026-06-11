<?php
session_start();

if (isset($_SESSION["usuario"])) {
    header("Location: dashboard.php");
    exit;
}

$mensajeError = "";

if (isset($_GET["error"])) {
    if ($_GET["error"] === "campos") {
        $mensajeError = "Debe completar tipo de usuario, teléfono o DPI y contraseña.";
    } elseif ($_GET["error"] === "usuario") {
        $mensajeError = "No existe un usuario activo con ese tipo de usuario y teléfono o DPI.";
    } elseif ($_GET["error"] === "password") {
        $mensajeError = "La contraseña ingresada es incorrecta.";
    } else {
        $mensajeError = "Credenciales incorrectas.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comunidad La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css?v=3" rel="stylesheet">
</head>

<body class="login-body">

    <main class="login-container">

        <section class="login-title">
            <div class="login-icon">🌱</div>

            <h1>Comunidad La Esperanza</h1>

            <p>
                Sistema de Gestión y Comercialización Agrícola
            </p>
        </section>

        <section class="login-card">

            <div class="login-card-header">
                <h2>Iniciar sesión</h2>

                <p>
                    Seleccione su tipo de usuario e ingrese sus datos de acceso.
                </p>
            </div>

            <?php if ($mensajeError !== ""): ?>
                <div class="alert alert-danger custom-alert">
                    <?php echo htmlspecialchars($mensajeError); ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">

                <div class="mb-3">
                    <label class="form-label">Tipo de usuario</label>

                    <select name="rol" class="form-select role-select" required>
                        <option value="">Seleccione una opción</option>
                        <option value="administrador">Administrador comunitario</option>
                        <option value="productor">Productor</option>
                        <option value="comprador">Comprador</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label">Teléfono o DPI</label>

                    <input 
                        type="text" 
                        name="identificador" 
                        class="form-control" 
                        placeholder="Ingrese su teléfono o DPI" 
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>

                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Ingrese su contraseña" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-login w-100">
                    Ingresar al sistema
                </button>

            </form>

        </section>

        <footer class="login-footer">
            <span>Universidad Mariano Gálvez</span>
            <span>Proyecto Final</span>
            <span>v1.0.0</span>
        </footer>

    </main>

</body>
</html>