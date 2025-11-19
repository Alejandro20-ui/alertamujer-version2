<?php
// Reportar errores para que aparezcan en los logs de Railway
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// --- 1. Obtener Credenciales desde Variables de Entorno de Railway ---
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db   = getenv('MYSQLDATABASE'); // Debe ser 'alertamujer'
$port = getenv('MYSQLPORT');

// Inicializar la variable de conexión como null
$conn = null;

// **Verificación de seguridad:** Si faltan variables, no intentar conectar.
if (!$host || !$user || !$pass || !$db) {
    error_log("❌ ERROR: Faltan variables de entorno esenciales para MySQL.");
    return; // Termina aquí, $conn sigue siendo null
}

// --- 2. Intentar Conexión usando MySQLi ---
try {
    // El '@' suprime los warnings de PHP en caso de fallo, para que el TRY/CATCH sea más limpio
    $conn = @new mysqli($host, $user, $pass, $db, $port); 

    if ($conn->connect_error) {
        // La conexión falló, loguear el error y establecer $conn a null
        error_log("❌ MySQL Connection Failed: " . $conn->connect_error);
        $conn = null;
    } else {
        // Conexión exitosa
        $conn->set_charset("utf8mb4");
    }
} catch (Exception $e) {
    // Capturar cualquier excepción inesperada (ej. clase mysqli no encontrada)
    error_log("❌ Excepción al intentar conectar MySQL: " . $e->getMessage());
    $conn = null;
}
// Al finalizar, $conn es o el objeto mysqli, o null.
?>