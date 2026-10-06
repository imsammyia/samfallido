<?php

session_start();

header('Content-Type: application/json; charset=utf-8');

include("conexion.php");
include("planes_helper.php");

if (!isset($_SESSION['id'])) {
    echo json_encode([
        "success" => false,
        "error" => "No hay sesión iniciada"
    ]);
    exit;
}

$usuario_id = $_SESSION['id'];

$planActual = obtenerPlanUsuario($conn, (int) $usuario_id);
if (!usuarioPuedeUsarChat($planActual['code'])) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "error" => "El chat requiere un plan pagado.",
        "requires_plan" => true
    ]);
    exit;
}

$contenido = $_POST['contenido'] ?? '';
$tipo = $_POST['tipo'] ?? 'usuario';
$conversacion_id = $_POST['conversacion_id'] ?? 0;

$contenido = trim($contenido);

if ($contenido === '') {
    echo json_encode([
        "success" => false,
        "error" => "El mensaje está vacío"
    ]);
    exit;
}

if (!in_array($tipo, ['usuario', 'sammy', 'user', 'bot'], true)) {
    echo json_encode([
        'success' => false,
        'error' => 'Tipo de mensaje inválido'
    ]);
    exit;
}

$tipoBaseDatos = $tipo === 'usuario' || $tipo === 'user'
    ? 'user'
    : 'bot';

if (empty($conversacion_id)) {

    $titulo = mb_substr($contenido, 0, 50);

    $sql = "INSERT INTO conversaciones (usuario_id, titulo)
            VALUES (?, ?)";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "success" => false,
            "error" => $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("is", $usuario_id, $titulo);

    if (!$stmt->execute()) {
        echo json_encode([
            "success" => false,
            "error" => $stmt->error
        ]);
        exit;
    }

    $conversacion_id = $conn->insert_id;

    $stmt->close();
}

$sql = "INSERT INTO mensajes
        (conversacion_id, tipo, contenido)
        VALUES (?, ?, ?)";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        "success" => false,
        "error" => $conn->error
    ]);
    exit;
}

$stmt->bind_param(
    "iss",
    $conversacion_id,
    $tipoBaseDatos,
    $contenido
);

if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "mensaje_id" => $conn->insert_id,
        "conversacion_id" => $conversacion_id
    ]);

} else {

    echo json_encode([
        "success" => false,
        "error" => $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>