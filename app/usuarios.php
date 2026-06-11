<?php
session_start();
require_once "config/db.php";

if (!isset($_SESSION["usuario"])) {
    header("Location: index.php");
    exit;
}

$usuario = $_SESSION["usuario"];
$rol = $usuario["rol"];

if ($rol !== "administrador") {
    header("Location: dashboard.php");
    exit;
}

$mensaje = "";
$tipoMensaje = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre = trim($_POST["nombre"] ?? "");
    $telefono = trim($_POST["telefono"] ?? "");
    $dpi = trim($_POST["dpi"] ?? "");
    $direccion = trim($_POST["direccion"] ?? "");
    $password = trim($_POST["password"] ?? "");
    $rolNuevo = $_POST["rol"] ?? "";

    if ($nombre === "" || $telefono === "" || $dpi === "" || $direccion === "" || $password === "" || $rolNuevo === "") {
        $mensaje = "Todos los campos son obligatorios.";
        $tipoMensaje = "danger";
    } else {
        $stmtExiste = $pdo->prepare("
            SELECT COUNT(*) 
            FROM usuarios 
            WHERE telefono = :telefono OR dpi = :dpi
        ");

        $stmtExiste->execute([
            "telefono" => $telefono,
            "dpi" => $dpi
        ]);

        $existe = $stmtExiste->fetchColumn();

        if ($existe > 0) {
            $mensaje = "Ya existe un usuario con ese teléfono o DPI.";
            $tipoMensaje = "warning";
        } else {
            $sql = "
                INSERT INTO usuarios
                (nombre, telefono, dpi, direccion, password, rol, estado)
                VALUES
                (:nombre, :telefono, :dpi, :direccion, SHA2(:password, 256), :rol, 'activo')
            ";

            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                "nombre" => $nombre,
                "telefono" => $telefono,
                "dpi" => $dpi,
                "direccion" => $direccion,
                "password" => $password,
                "rol" => $rolNuevo
            ]);

            header("Location: usuarios.php?ok=1");
            exit;
        }
    }
}

if (isset($_GET["activar"])) {
    $idUsuario = $_GET["activar"];

    $stmt = $pdo->prepare("
        UPDATE usuarios
        SET estado = 'activo'
        WHERE id_usuario = :id_usuario
    ");

    $stmt->execute([
        "id_usuario" => $idUsuario
    ]);

    header("Location: usuarios.php?activado=1");
    exit;
}

if (isset($_GET["desactivar"])) {
    $idUsuario = $_GET["desactivar"];

    if ($idUsuario != $usuario["id"]) {
        $stmt = $pdo->prepare("
            UPDATE usuarios
            SET estado = 'inactivo'
            WHERE id_usuario = :id_usuario
        ");

        $stmt->execute([
            "id_usuario" => $idUsuario
        ]);

        header("Location: usuarios.php?desactivado=1");
        exit;
    } else {
        $mensaje = "No puede desactivar su propio usuario.";
        $tipoMensaje = "danger";
    }
}

$usuarios = $pdo->query("
    SELECT *
    FROM usuarios
    ORDER BY id_usuario DESC
")->fetchAll(PDO::FETCH_ASSOC);

function nombreRolUsuario($rol) {
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

function badgeEstadoUsuario($estado) {
    if ($estado === "activo") {
        return "bg-success";
    }

    if ($estado === "inactivo") {
        return "bg-danger";
    }

    return "bg-secondary";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios - Comunidad La Esperanza</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css?v=3" rel="stylesheet">
</head>

<body>

<div class="main-full">
    <a href="dashboard.php" class="btn btn-secondary mb-3">
        Volver al dashboard
    </a>

    <div class="card-box mb-4">
        <h3>Gestión de usuarios</h3>

        <p class="text-muted">
            Registro y administración de usuarios del sistema comunitario.
        </p>

        <?php if (isset($_GET["ok"])): ?>
            <div class="alert alert-success">
                Usuario registrado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["activado"])): ?>
            <div class="alert alert-success">
                Usuario activado correctamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET["desactivado"])): ?>
            <div class="alert alert-warning">
                Usuario desactivado correctamente.
            </div>
        <?php endif; ?>

        <?php if ($mensaje !== ""): ?>
            <div class="alert alert-<?php echo $tipoMensaje; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Nombre completo</label>
                <input 
                    type="text" 
                    name="nombre" 
                    class="form-control" 
                    placeholder="Ej. Juan Pérez"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">Teléfono</label>
                <input 
                    type="text" 
                    name="telefono" 
                    class="form-control" 
                    placeholder="Ej. 55550004"
                    required
                >
            </div>

            <div class="col-md-4">
                <label class="form-label">DPI</label>
                <input 
                    type="text" 
                    name="dpi" 
                    class="form-control" 
                    placeholder="Ej. 3000000000004"
                    required
                >
            </div>

            <div class="col-md-6">
                <label class="form-label">Dirección</label>
                <input 
                    type="text" 
                    name="direccion" 
                    class="form-control" 
                    placeholder="Dirección del usuario"
                    required
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control" 
                    placeholder="Contraseña inicial"
                    required
                >
            </div>

            <div class="col-md-3">
                <label class="form-label">Rol</label>
                <select name="rol" class="form-select" required>
                    <option value="">Seleccione rol</option>
                    <option value="productor">Productor</option>
                    <option value="comprador">Comprador</option>
                    <option value="administrador">Administrador comunitario</option>
                </select>
            </div>

            <div class="col-md-3">
                <button class="btn btn-success">
                    Registrar usuario
                </button>
            </div>
        </form>
    </div>

    <div class="card-box">
        <h4>Usuarios registrados</h4>

        <div class="table-responsive mt-3">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>DPI</th>
                        <th>Dirección</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td><?php echo $u["id_usuario"]; ?></td>

                            <td>
                                <?php echo htmlspecialchars($u["nombre"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($u["telefono"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($u["dpi"] ?? ""); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars($u["direccion"]); ?>
                            </td>

                            <td>
                                <?php echo htmlspecialchars(nombreRolUsuario($u["rol"])); ?>
                            </td>

                            <td>
                                <span class="badge <?php echo badgeEstadoUsuario($u["estado"]); ?>">
                                    <?php echo strtoupper($u["estado"]); ?>
                                </span>
                            </td>

                            <td>
                                <?php echo $u["fecha_registro"]; ?>
                            </td>

                            <td>
                                <?php if ($u["estado"] === "activo"): ?>
                                    <?php if ($u["id_usuario"] != $usuario["id"]): ?>
                                        <a href="usuarios.php?desactivar=<?php echo $u["id_usuario"]; ?>" class="btn btn-sm btn-danger">
                                            Desactivar
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">Usuario actual</span>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="usuarios.php?activar=<?php echo $u["id_usuario"]; ?>" class="btn btn-sm btn-success">
                                        Activar
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if (count($usuarios) === 0): ?>
                        <tr>
                            <td colspan="9" class="text-center text-muted">
                                No hay usuarios registrados.
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