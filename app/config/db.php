<?php

$host = "db";
$dbname = "la_esperanza";
$user = "esperanza_user";
$password = "esperanza123";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user,
        $password
    );

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    die("Error de conexion a la base de datos");
}