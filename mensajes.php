<?php

session_start();
include("conexion.php");
include("planes_helper.php");

header("Content-Type: application/json; charset=UTF-8");

if (!isset($_SESSION["id"])) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Debes iniciar sesión."
    ]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Método no permitido."
    ]);
    exit;
}

$usuario_id = $_SESSION["id"];
$planActual = obtenerPlanUsuario($conn, (int) $usuario_id);
if (!usuarioPuedeUsarChat($planActual['code'])) {
    http_response_code(403);
    echo json_encode([
        "ok" => false,
        "mensaje" => "El chat requiere un plan pagado."
    ]);
    exit;
}

$contenido = trim($_POST["contenido"] ?? "");
$tipo = $_POST["tipo"] ?? "";

if ($contenido === "") {
    echo json_encode([
        "ok" => false,
        "mensaje" => "El mensaje está vacío."
    ]);
    exit;
}

if (!in_array($tipo, ["usuario", "sammy", "user", "bot"], true)) {
    echo json_encode([
        "ok" => false,
        "mensaje" => "Tipo de mensaje inválido."
    ]);
    exit;
}

$tipoBaseDatos = $tipo === "usuario" || $tipo === "user"
    ? "user"
    : "bot";

/* Buscamos la conversación más reciente del peleao*/
$sql = "SELECT id
        FROM conversaciones
        WHERE usuario_id = ?
        ORDER BY id DESC
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows > 0) {

    $fila = $resultado->fetch_assoc();
    $conversacion_id = $fila["id"];

} else {

    /*Si no hay conversacion se crea una.*/
    $titulo = "Nueva conversación";

    $sqlCrear = "INSERT INTO conversaciones (usuario_id, titulo)
                 VALUES (?, ?)";

    $stmtCrear = $conn->prepare($sqlCrear);
    $stmtCrear->bind_param("is", $usuario_id, $titulo);
    $stmtCrear->execute();

    $conversacion_id = $conn->insert_id;

    $stmtCrear->close();
}

/*
 * Guardamos el mensaje.
 */
$sqlMensaje = "INSERT INTO mensajes
               (conversacion_id, tipo, contenido)
               VALUES (?, ?, ?)";

$stmtMensaje = $conn->prepare($sqlMensaje);
$stmtMensaje->bind_param(
    "iss",
    $conversacion_id,
    $tipoBaseDatos,
    $contenido
);

if ($stmtMensaje->execute()) {

    echo json_encode([
        "ok" => true,
        "conversacion_id" => $conversacion_id,
        "mensaje_id" => $conn->insert_id
    ]);

} else {

    echo json_encode([
        "ok" => false,
        "mensaje" => "No se pudo guardar el mensaje."
    ]);
}

$stmt->close();
$stmtMensaje->close();
$conn->close();

?>