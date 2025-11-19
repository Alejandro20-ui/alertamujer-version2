<?php
// Reportar errores a logs
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Tomar valores EXACTOS que Railway sí envía
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE');   // <--- VARIABLE CORRECTA CONFIRMADA
$port = getenv('MYSQLPORT') ?: 3306;

// Inicializar conexión
$conn = null;

// Validar que llegaron todas
if (!$host || !$user || !$pass || !$db) {
    error_log("❌ ERROR: Variables incompletas:
HOST=$host
USER=$user
PASS=$pass
DB=$db
PORT=$port
");
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
