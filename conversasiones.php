<?php

session_start();
include("conexion.php");
include("planes_helper.php");

header("Content-Type: application/json");

if (!isset($_SESSION["id"])) {
    echo json_encode([
        "success" => false,
        "message" => "No has iniciado sesión."
    ]);
    exit();
}

$usuario_id = $_SESSION["id"];

$planActual = obtenerPlanUsuario($conn, (int) $usuario_id);
if (!usuarioPuedeUsarChat($planActual['code'])) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "message" => "El chat requiere un plan pagado."
    ]);
    exit;
}

$titulo = "Nueva conversación";

$sql = "INSERT INTO conversaciones (usuario_id, titulo) VALUES (?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $usuario_id, $titulo);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "id" => $stmt->insert_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "No se pudo crear la conversación."
    ]);
}

$stmt->close();
$conn->close();

?>