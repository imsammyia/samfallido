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
$userId = (int) $_SESSION['id'];
if (!$orderId) {
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

try {
    $conn->begin_transaction();

    $stmt = $conn->prepare(
        "SELECT plan_code, amount, currency, status, provider
         FROM ordenes_plan
         WHERE id = ? AND user_id = ?
         FOR UPDATE"
    );
    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $catalogo = catalogoPlanes();
    if (!$order || $order['status'] !== 'pending'
        || $order['provider'] !== 'test'
        || $order['currency'] !== 'USD'
        || !isset($catalogo[$order['plan_code']])
        || $order['plan_code'] === 'free'
        || number_format((float) $order['amount'], 2, '.', '')
            !== number_format((float) $catalogo[$order['plan_code']]['amount'], 2, '.', '')) {
        $conn->rollback();
        header('Location: index.php?plan=no_disponible#precios');
        exit;
    }

    $stmt = $conn->prepare(
        "UPDATE ordenes_plan
         SET status = 'paid', paid_at = NOW()
         WHERE id = ? AND user_id = ? AND status = 'pending'"
    );
    $stmt->bind_param('ii', $orderId, $userId);
    $stmt->execute();
    $actualizadas = $stmt->affected_rows;
    $stmt->close();

    if ($actualizadas !== 1) {
        $conn->rollback();
        header('Location: index.php?plan=no_disponible#precios');
        exit;
    }

    $planCode = $order['plan_code'];
    $stmt = $conn->prepare(
        "INSERT INTO plan_usuario
            (user_id, plan_code, status, started_at, expires_at, order_id)
         VALUES (?, ?, 'active', NOW(), DATE_ADD(NOW(), INTERVAL 30 DAY), ?)
         ON DUPLICATE KEY UPDATE
            plan_code = VALUES(plan_code),
            status = 'active',
            started_at = NOW(),
            expires_at = DATE_ADD(NOW(), INTERVAL 30 DAY),
            order_id = VALUES(order_id)"
    );
    $stmt->bind_param('isi', $userId, $planCode, $orderId);
    $stmt->execute();
    $stmt->close();

    $conn->commit();
} catch (Throwable $error) {
    $conn->rollback();
    $conn->close();
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

$conn->close();
header('Location: index.php?plan=activo#precios');
exit;
