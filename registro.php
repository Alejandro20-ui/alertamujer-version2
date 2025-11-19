<?php
// ¡IMPORTANTE! Asegúrate de que esta sea la línea 1 sin espacios en blanco encima.

// --- CONFIGURACIÓN DE ENCABEZADOS ---
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS"); // Incluir OPTIONS
header("Access-Control-Allow-Headers: Content-Type");

// Configuración de errores para que no impriman mensajes HTML al cliente
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Manejo de preflight OPTIONS (CORS)
if ($_SERVER["REQUEST_METHOD"] === "OPTIONS") {
    http_response_code(200);
    exit(0);
}

// 1. Verificar Método HTTP
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["status" => "invalid_method", "message" => "Método no permitido. Solo POST."]);
    exit();
}

// 2. Incluir el archivo de conexión
include "conexion.php";

// 3. Verificar si la conexión falló (desde conexion.php)
if (!$conn) {
    error_log("❌ Fallo de conexión en registro.php (Ver logs de conexion.php)");
    http_response_code(503); // Service Unavailable (problema de BD)
    echo json_encode(["status" => "database_error", "message" => "No se pudo conectar a la base de datos."]);
    exit();
}

// 4. Leer datos del cuerpo de la petición (Mejora la compatibilidad)
// Intenta leer el cuerpo como JSON, luego usa $_POST como fallback.
$data = json_decode(file_get_contents("php://input"), true);

if (empty($data)) {
    $data = $_POST; // Tu código original funciona aquí si el cuerpo es form-urlencoded
}

// 5. Validación básica de campos
$required = ['nombre', 'apellidos', 'numero', 'correo'];
foreach ($required as $field) {
    // Usamos $data en lugar de $_POST
    if (!isset($data[$field]) || trim($data[$field]) === '') {
        http_response_code(400); // Bad Request
        echo json_encode(["status" => "missing_field", "field" => $field, "message" => "Falta el campo requerido: $field."]);
        $conn->close();
        exit();
    }
}

// 6. Asignación de variables
$nombre = trim($data["nombre"]);
$apellidos = trim($data["apellidos"]);
$numero = trim($data["numero"]);
$correo = trim($data["correo"]);

// Validación simple de correo
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); // Bad Request
    echo json_encode(["status" => "invalid_email", "message" => "Formato de correo inválido."]);
    $conn->close();
    exit();
}

// 7. Verificar si el correo ya existe
$check = $conn->prepare("SELECT id FROM usuarios WHERE correo = ? LIMIT 1");
$check->bind_param("s", $correo);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    http_response_code(409); // Conflict
    echo json_encode(["status" => "exists", "idUsuario" => (int)$row["id"], "message" => "El correo ya está registrado."]);
} else {
    // 8. Insertar nuevo usuario
    $stmt = $conn->prepare("INSERT INTO usuarios (nombre, apellidos, numero, correo) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nombre, $apellidos, $numero, $correo);

    if ($stmt->execute()) {
        http_response_code(201); // Created
        echo json_encode([
            "status" => "success",
            "idUsuario" => (int)$stmt->insert_id,
            "message" => "Usuario registrado exitosamente."
        ]);
    } else {
        error_log("Registro fallido (SQL Error): " . $stmt->error);
        http_response_code(500); // Internal Server Error
        echo json_encode(["status" => "insert_error", "message" => "Fallo al ejecutar la inserción en la base de datos."]);
    }
    $stmt->close();
}

$check->close();
$conn->close();

// ¡IMPORTANTE! NO CIERRES LA ETIQUETA PHP (?>) AQUÍ.