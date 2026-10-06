<?php

session_start();
include("conexion.php");
include("planes_helper.php");

if (!isset($_SESSION['id'])) {
    header('Location: index.php?plan=login#precios');
    exit;
}

if (!esEntornoPruebasLocal()) {
    http_response_code(404);
    exit('No encontrado.');
}

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$orderId) {
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

$stmt = $conn->prepare(
    "SELECT plan_code, amount, currency, status, provider
    FROM ordenes_plan
     WHERE id = ? AND user_id = ?
     LIMIT 1"
);
$userId = (int) $_SESSION['id'];
$stmt->bind_param('ii', $orderId, $userId);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

$catalogo = catalogoPlanes();
if (!$order || $order['status'] !== 'pending'
    || $order['provider'] !== 'test'
    || !isset($catalogo[$order['plan_code']])) {
    header('Location: index.php?plan=no_disponible#precios');
    exit;
}

$plan = $catalogo[$order['plan_code']];
$csrfToken = obtenerTokenCsrf();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout de prueba - Sam Software</title>
    <link rel="stylesheet" href="style.css?v=gestion-panel-2">
</head>
<body class="checkout-body">
    <main class="checkout-page">
        <section class="checkout-panel" aria-labelledby="checkout-title">
            <a class="checkout-back" href="index.php#precios">Volver a los planes</a>
            <p class="checkout-kicker">Entorno de pruebas</p>
            <h1 id="checkout-title">Confirmar <?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p class="checkout-order">Orden #<?= (int) $orderId ?></p>

            <div class="checkout-summary">
                <span><?= htmlspecialchars($plan['name'], ENT_QUOTES, 'UTF-8') ?> · 30 días</span>
                <strong>$<?= number_format((float) $order['amount'], 2) ?> <?= htmlspecialchars($order['currency'], ENT_QUOTES, 'UTF-8') ?></strong>
            </div>

            <p class="checkout-warning">
                Esta es una simulación local. No se procesará ningún pago ni se solicitarán datos de tarjeta.
            </p>

            <form action="confirmar_pago_prueba.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="order_id" value="<?= (int) $orderId ?>">
                <button class="checkout-confirm" type="submit">Simular pago y activar plan</button>
            </form>

            <form action="cancelar_compra.php" method="post">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="order_id" value="<?= (int) $orderId ?>">
                <button class="checkout-cancel" type="submit">Cancelar orden</button>
            </form>
        </section>
    </main>
    <script src="appearance.js?v=gestion-panel"></script>
</body>
</html>
