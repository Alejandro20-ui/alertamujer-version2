<?php
// **********************************************
// PASO 1: Desactivar display_errors en PRODUCCIÓN
// Esto evita que cualquier Warning contamine la respuesta
ini_set('display_errors', 0); // Lo cambiamos de 1 a 0
error_reporting(E_ALL);
// **********************************************

// PASO 2: Simplificar la lectura de variables y evitar el fallback inseguro
// Usamos los valores configurados en el dashboard de Railway.
$host = getenv('MYSQLHOST');
$user = getenv('MYSQLUSER');
$pass = getenv('MYSQLPASSWORD');
$db = getenv('MYSQLDATABASE');
$port = getenv('MYSQLPORT');

// **********************************************
// PASO 3: Validación crucial antes de conectar
// Si alguna variable principal no se cargó, forzamos un error de configuración
if (!$host || !$user || !$pass || !$db) {
    die(json_encode(["status" => "error", "message" => "Error de configuración: Faltan variables de entorno de la BD (MYSQLHOST, etc.)."]));
}
// **********************************************

$conn = new mysqli($host, $user, $pass, $db, $port);

if ($conn->connect_error) {
    // Si falla, el error real ahora se puede ver en los Logs de Railway.
    // Solo le decimos al cliente que hay un error de conexión, sin exponer detalles.
    die(json_encode(["status" => "error", "message" => "Error de conexión a la base de datos."]));
}

$conn->set_charset("utf8mb4");

// OMITIR la etiqueta de cierre "? >" para evitar espacios en blanco/saltos de línea.