<?php
// Habilitar logs de error (Railway los guardará)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// PRIORIDAD 1: Usar MYSQL_URL si existe (Railway lo genera automáticamente)
$mysql_url = getenv('MYSQL_URL');

if ($mysql_url) {
    // Parsear la URL: mysql://user:pass@host:port/database
    $parts = parse_url($mysql_url);
    $host = $parts['host'] ?? 'localhost';
    $user = $parts['user'] ?? 'root';
    $pass = $parts['pass'] ?? '';
    $db   = ltrim($parts['path'] ?? '', '/') ?: 'railway';
    $port = $parts['port'] ?? 3306;
    
    error_log("📍 Usando MYSQL_URL: $host:$port/$db (user: $user)");
} else {
    // PRIORIDAD 2: Variables individuales (fallback)
    $host = getenv('MYSQLHOST') ?: 'mysql.railway.internal';
    $user = getenv('MYSQLUSER') ?: 'root';
    $pass = getenv('MYSQLPASSWORD') ?: '';
    $db   = getenv('MYSQLDATABASE') ?: 'railway';
    $port = getenv('MYSQLPORT') ?: 3306;
    
    error_log("📍 Usando variables individuales: $host:$port/$db (user: $user)");
}

// Intentar conexión
$conn = @new mysqli($host, $user, $pass, $db, $port);

// Si falla la conexión, loguear pero NO terminar aquí
if ($conn->connect_error) {
    error_log("❌ MySQL Connection Error: " . $conn->connect_error);
    error_log("   Host: $host | User: $user | DB: $db | Port: $port");
    $conn = null;
} else {
    // Conexión exitosa
    $conn->set_charset("utf8mb4");
    error_log("✅ MySQL Connected: $host:$port/$db");
}
?>