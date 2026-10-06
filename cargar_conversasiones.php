<?php

session_start();

include("conexion.php");
include("planes_helper.php");

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION['id'])) {

    echo json_encode([
        "success" => false,
        "mensaje" => "Debes iniciar sesión"
    ]);

    exit;
}

$usuario_id = $_SESSION['id'];

$planActual = obtenerPlanUsuario($conn, (int) $usuario_id);
if (!usuarioPuedeUsarChat($planActual['code'])) {
    http_response_code(403);
    echo json_encode([
        "success" => false,
        "mensaje" => "El chat requiere un plan pagado."
    ]);
    exit;
}

if (!isset($_GET['conversacion_id'])) {

    echo json_encode([
        "success" => false,
        "mensaje" => "No se indicó la conversación"
    ]);

    exit;
}

$conversacion_id = intval($_GET['conversacion_id']);


/*pa probar que la conversación pertenece al usuario*/

$sql = "SELECT id, titulo, fecha_creacion
        FROM conversaciones
        WHERE id = ? AND usuario_id = ?";

$stmt = $conn->prepare($sql);

$stmt->bind_param("ii", $conversacion_id, $usuario_id);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    echo json_encode([
        "success" => false,
        "mensaje" => "Conversación no encontrada"
    ]);

    $stmt->close();
    $conn->close();

    exit;
}

$conversacion = $resultado->fetch_assoc();

$stmt->close();


/*buscamos todos los mensajes*/

$sql = "SELECT id, tipo, contenido, fecha
        FROM mensajes
        WHERE conversacion_id = ?
        ORDER BY id ASC";

$stmt = $conn->prepare($sql);

$stmt->bind_param("i", $conversacion_id);

$stmt->execute();

$resultado = $stmt->get_result();

$mensajes = [];

while ($mensaje = $resultado->fetch_assoc()) {
    $mensaje['tipo'] = $mensaje['tipo'] === 'user'
        ? 'usuario'
        : 'sammy';
    $mensajes[] = $mensaje;
}

$stmt->close();
$conn->close();


/*regresa la conversacion con los mensajes*/

echo json_encode([
    "success" => true,
    "conversacion" => $conversacion,
    "mensajes" => $mensajes
]);

?>