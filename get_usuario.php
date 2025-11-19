<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

if (!isset($_GET['id'])) {
    echo json_encode(["status" => "error", "message" => "Falta ID"]);
    exit;
}

$id = intval($_GET['id']);

$sql = $conn->prepare("SELECT id, nombre, apellidos, numero, correo FROM usuarios WHERE id = ?");
$sql->bind_param("i", $id);
$sql->execute();
$result = $sql->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    exit;
}

echo json_encode(["status" => "success", "data" => $result->fetch_assoc()]);
$conn->close();