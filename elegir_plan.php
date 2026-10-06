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

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('La sesión del formulario venció. Recarga la página e inténtalo de nuevo.');
}

$catalogo = catalogoPlanes();
$planCode = $_POST['plan_code'] ?? '';

if (!isset($catalogo[$planCode])) {
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

$usuarioId = (int) $_SESSION['id'];
$planActual = obtenerPlanUsuario($conn, $usuarioId);

if ($planActual['code'] === $planCode) {
    header('Location: index.php#precios');
    exit;
}

if ($planCode === 'free') {
    if ($planActual['active'] && $planActual['code'] !== 'free') {
        header('Location: index.php?plan=no_degradar#precios');
        exit;
    }

    $stmt = $conn->prepare(
        "INSERT INTO plan_usuario
            (user_id, plan_code, status, started_at, expires_at, order_id)
         VALUES (?, 'free', 'active', NOW(), NULL, NULL)
         ON DUPLICATE KEY UPDATE
            plan_code = 'free',
            status = 'active',
            started_at = NOW(),
            expires_at = NULL,
            order_id = NULL"
    );
    $stmt->bind_param('i', $usuarioId);

    if (!$stmt->execute()) {
        $stmt->close();
        header('Location: index.php?plan=no_disponible#precios');
        exit;
    }

    $stmt->close();
    header('Location: index.php?plan=activo#precios');
    exit;
}

if (!esEntornoPruebasLocal()) {
    http_response_code(503);
    exit('El checkout de prueba solo está disponible en localhost.');
}

$amount = (float) $catalogo[$planCode]['amount'];
$stmt = $conn->prepare(
    "INSERT INTO ordenes_plan (user_id, plan_code, amount, currency, provider)
     VALUES (?, ?, ?, 'USD', 'test')"
);
$stmt->bind_param('isd', $usuarioId, $planCode, $amount);

if (!$stmt->execute()) {
    $stmt->close();
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

$orderId = $conn->insert_id;
$stmt->close();
$conn->close();

header('Location: checkout_prueba.php?id=' . $orderId);
exit;
