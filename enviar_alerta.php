<?php
header("Content-Type: application/json; charset=utf8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// 1. Validar método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// 2. Leer datos JSON
$input = json_decode(file_get_contents("php://input"), true);
$usuario_id = intval($input['usuario_id'] ?? 0);
$motivo = trim($input['motivo'] ?? "Alerta de emergencia");
$timestamp = date("Y-m-d H:i:s");

if ($usuario_id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "usuario_id inválido"]);
    exit;
}

// 3. Conexión a la base de datos (Railway)
try {
    $host = $_ENV['DB_HOST'] ?? 'localhost';
    $dbname = $_ENV['DB_NAME'] ?? 'railway';
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASSWORD'] ?? '';

    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    error_log("DB CONN ERROR: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Error interno"]);
    exit;
}

// 4. Obtener contactos del usuario (máximo 3, como en tu app)
$stmt = $pdo->prepare("
    SELECT id, nombre_contacto, numero_contacto 
    FROM contactos 
    WHERE usuario_id = ? AND activo = 1 
    LIMIT 3
");
$stmt->execute([$usuario_id]);
$contactos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($contactos)) {
    // Registrar alerta igual (por si el usuario no tiene contactos aún)
    $stmt = $pdo->prepare("
        INSERT INTO alertas_emergencia (usuario_id, motivo, destinatarios, fecha)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $motivo, '[]', $timestamp]);

    echo json_encode([
        "success" => true,
        "mensaje" => "Alerta registrada, pero no hay contactos configurados",
        "contactos_notificados" => 0
    ]);
    exit;
}

// 5. Registrar alerta en la base de datos
$destinatarios_json = json_encode($contactos);
$stmt = $pdo->prepare("
    INSERT INTO alertas_emergencia (usuario_id, motivo, destinatarios, fecha)
    VALUES (?, ?, ?, ?)
");
$stmt->execute([$usuario_id, $motivo, $destinatarios_json, $timestamp]);

// 6. "Enviar" alerta a cada contacto (simulado con log)
$enviados = 0;
foreach ($contactos as $c) {
    $nombre = htmlspecialchars($c['nombre_contacto']);
    $numero = $c['numero_contacto'];

    // === Simulación: solo registrar en log ===
    error_log("📱 ALERTA SMS A: $numero | Nombre: $nombre | Motivo: $motivo");
    $enviados++;
}

// 7. Respuesta exitosa
echo json_encode([
    "success" => true,
    "mensaje" => "Alerta procesada y registrada",
    "motivo" => $motivo,
    "contactos_notificados" => $enviados,
    "timestamp" => $timestamp
]);
?>