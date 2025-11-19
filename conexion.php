<?php
// Reportar errores a logs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// USAR EL HOST PÚBLICO DE RAILWAY
$host = getenv('MYSQLHOST') ?: 'maglev.proxy.rlwy.net';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE') ?: 'railway';
$port = getenv('MYSQLPORT') ?: 50204;

// Inicializar conexión
$conn = null;

if (!$host || !$user || !$pass || !$db) {
    error_log("❌ ERROR: Variables incompletas");
    return;
}

try {
    $conn = @new mysqli($host, $user, $pass, $db, $port);

    if ($conn->connect_error) {
        error_log("❌ ERROR CONECTANDO MySQL: " . $conn->connect_error);
        $conn = null;
    } else {
        $conn->set_charset("utf8mb4");
    }
} catch (Exception $e) {
    error_log("❌ EXCEPCIÓN EN MYSQL: " . $e->getMessage());
    $conn = null;
}
