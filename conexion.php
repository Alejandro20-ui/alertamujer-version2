<?php
// Reportar errores para logs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Identificar correctamente todas las variantes de variables que Railway usa
$host = getenv('MYSQLHOST') ?: getenv('MYSQL_HOST') ?: getenv('HOST');
$user = getenv('MYSQLUSER') ?: getenv('MYSQL_USER') ?: getenv('USER');
$pass = getenv('MYSQLPASSWORD') ?: getenv('MYSQL_PASSWORD') ?: getenv('PASSWORD');
$db   = getenv('MYSQLDATABASE') ?: getenv('MYSQL_DATABASE') ?: getenv('DATABASE');
$port = getenv('MYSQLPORT') ?: getenv('MYSQL_PORT') ?: 3306;

// Inicializar conexión
$conn = null;

// Si falta algo, registrar error
if (!$host || !$user || !$pass || !$db) {
    error_log("❌ ERROR MySQL: Faltan variables de entorno. 
HOST=$host USER=$user PASS=$pass DB=$db");
    return;
}

try {
    $conn = @new mysqli($host, $user, $pass, $db, $port);

    if ($conn->connect_error) {
        error_log("❌ ERROR CONEXIÓN: " . $conn->connect_error);
        $conn = null;
    } else {
        $conn->set_charset("utf8mb4");
    }
} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN MySQL: " . $e->getMessage());
    $conn = null;
}
