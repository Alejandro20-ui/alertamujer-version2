<?php
include 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['id'])) {
    echo json_encode(["status" => "error", "message" => "Falta ID"]);
    exit;
}

$id = intval($data['id']);
$nombre = $data["nombre"];
$apellidos = $data["apellidos"];
$numero = $data["numero"];
$correo = $data["correo"];

$sql = $conn->prepare("UPDATE usuarios SET nombre=?, apellidos=?, numero=?, correo=? WHERE id=?");
$sql->bind_param("ssssi", $nombre, $apellidos, $numero, $correo, $id);

if ($sql->execute()) {
    echo json_encode(["status" => "success", "message" => "Usuario actualizado"]);
} else {
    echo json_encode(["status" => "error", "message" => "Error al actualizar"]);
}

$conn->close();
