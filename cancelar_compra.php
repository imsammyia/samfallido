<?php

session_start();
include("conexion.php");
include("planes_helper.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

if (!isset($_SESSION['id'])) {
    header('Location: index.php?plan=login#precios');
    exit;
}

if (!esEntornoPruebasLocal()) {
    http_response_code(404);
    exit('No encontrado.');
}

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('La sesión del formulario venció.');
}

$orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
if ($orderId) {
    $stmt = $conn->prepare(
        "UPDATE ordenes_plan
         SET status = 'cancelled'
         WHERE id = ? AND user_id = ? AND provider = 'test' AND status = 'pending'"
    );
    $userId = (int) $_SESSION['id'];
    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();
    $stmt->close();
}

$conn->close();
header('Location: index.php#precios');
exit;
