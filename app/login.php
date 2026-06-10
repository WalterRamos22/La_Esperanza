<?php

session_start();
require_once "config/db.php";

$telefono = $_POST["telefono"] ?? "";
$password = $_POST["password"] ?? "";

$sql = "SELECT * FROM usuarios 
        WHERE telefono = :telefono 
        AND password = SHA2(:password, 256)
        AND estado = 'activo'
        LIMIT 1";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    "telefono" => $telefono,
    "password" => $password
]);

$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

if ($usuario) {
    $_SESSION["usuario"] = [
        "id" => $usuario["id_usuario"],
        "nombre" => $usuario["nombre"],
        "telefono" => $usuario["telefono"],
        "rol" => $usuario["rol"]
    ];

    header("Location: dashboard.php");
    exit;
}

header("Location: index.php?error=1");
exit;