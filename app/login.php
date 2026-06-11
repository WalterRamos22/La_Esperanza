<?php

session_start();
require_once "config/db.php";

/*
    Recibir datos del formulario de index.php
*/
$rol = $_POST["rol"] ?? "";
$identificador = trim($_POST["identificador"] ?? "");
$password = trim($_POST["password"] ?? "");

/*
    Validar que no vengan campos vacíos
*/
if ($rol === "" || $identificador === "" || $password === "") {
    header("Location: index.php?error=campos");
    exit;
}

/*
    Buscar usuario activo por:
    - Teléfono o DPI
    - Rol seleccionado
    - Estado activo
*/
$sql = "
    SELECT *
    FROM usuarios
    WHERE (telefono = :identificador OR dpi = :identificador)
    AND rol = :rol
    AND estado = 'activo'
    LIMIT 1
";

$stmt = $pdo->prepare($sql);

$stmt->execute([
    "identificador" => $identificador,
    "rol" => $rol
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

/*
    Si no existe usuario con ese teléfono/DPI y rol
*/
if (!$usuario) {
    header("Location: index.php?error=usuario");
    exit;
}

/*
    Comparar contraseña usando SHA256
*/
$passwordHash = hash("sha256", $password);

if ($usuario["password"] !== $passwordHash) {
    header("Location: index.php?error=password");
    exit;
}

/*
    Crear sesión
*/
$_SESSION["usuario"] = [
    "id" => $usuario["id_usuario"],
    "nombre" => $usuario["nombre"],
    "telefono" => $usuario["telefono"],
    "dpi" => $usuario["dpi"] ?? "",
    "rol" => $usuario["rol"]
];

/*
    Enviar al dashboard
*/
header("Location: dashboard.php");
exit;