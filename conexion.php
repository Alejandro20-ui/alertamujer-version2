<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', '/tmp/php-error.log'); // Railway puede escribir aquí

$host = getenv('MYSQLHOST') ?: 'localhost';
$user = getenv('MYSQLUSER') ?: 'root';
$pass = getenv('MYSQLPASSWORD') ?: '';
$db   = getenv('MYSQLDATABASE') ?: 'alertamujer';
$port = getenv('MYSQLPORT') ?: 3306;

// Importante: Railway usa MYSQL_URL también
$mysql_url = getenv('MYSQL_URL');
if ($mysql_url) {
    $parts = parse_url($mysql_url);
    $host = $parts['host'] ?? $host;
    $user = $parts['user'] ?? $user;
    $pass = $parts['pass'] ?? $pass;
    $db   = ltrim($parts['path'] ?? '', '/') ?: $db;
    $port = $parts['port'] ?? $port;
}

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    error_log("Error de conexión MySQL: " . $conn->connect_error);
    http_response_code(500);
    header("Content-Type: application/json; charset=UTF-8");
    echo json_encode([
        "status" => "error", 
        "message" => "Error de conexión a la base de datos"
    ]);
    exit();
}

$conn->set_charset("utf8mb4");
?>