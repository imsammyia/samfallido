<?php

function catalogoPlanes()
{
    return [
        'free' => [
            'name' => 'Plan gratuito',
            'amount' => 0.00,
            'chat_level' => 'sin acceso al chat'
        ],
        'demo' => [
            'name' => 'Plan demo',
            'amount' => 5.99,
            'chat_level' => 'básico'
        ],
        'basic' => [
            'name' => 'Básico',
            'amount' => 15.99,
            'chat_level' => 'medio'
        ],
        'professional' => [
            'name' => 'Profesional',
            'amount' => 27.99,
            'chat_level' => 'avanzado'
        ],
        'semi_enterprise' => [
            'name' => 'Semi Empresarial',
            'amount' => 45.99,
            'chat_level' => 'ilimitado'
        ],
        'enterprise' => [
            'name' => 'Empresarial',
            'amount' => 55.99,
            'chat_level' => 'inteligente'
        ]
    ];
}

function obtenerPlanUsuario($conn, $usuarioId)
{
    $catalogo = catalogoPlanes();
    $planGratis = $catalogo['free'];
    $planGratis['code'] = 'free';
    $planGratis['active'] = false;
    $planGratis['expires_at'] = null;

    $stmt = $conn->prepare(
        "SELECT plan_code, expires_at
         FROM plan_usuario
         WHERE user_id = ?
           AND status = 'active'
           AND (expires_at IS NULL OR expires_at > NOW())
         LIMIT 1"
    );

    if (!$stmt) {
        return $planGratis;
    }

    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || !isset($catalogo[$row['plan_code']])) {
        return $planGratis;
    }

    $plan = $catalogo[$row['plan_code']];
    $plan['code'] = $row['plan_code'];
    $plan['active'] = true;
    $plan['expires_at'] = $row['expires_at'];

    return $plan;
}

function usuarioPuedeUsarChat($planCode)
{
    $catalogo = catalogoPlanes();

    return isset($catalogo[$planCode]) && $planCode !== 'free';
}

function usuarioPuedeUsarInterfazSammy($planCode)
{
    return in_array($planCode, [
        'professional',
        'semi_enterprise',
        'enterprise'
    ], true);
}

function obtenerTokenCsrf()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function tokenCsrfValido($token)
{
    return isset($_SESSION['csrf_token'])
        && is_string($token)
        && hash_equals($_SESSION['csrf_token'], $token);
}

function esEntornoPruebasLocal()
{
    $host = parse_url(
        'http://' . ($_SERVER['HTTP_HOST'] ?? ''),
        PHP_URL_HOST
    );

    $direccionCliente = $_SERVER['REMOTE_ADDR'] ?? '';

    return in_array(strtolower((string) $host), [
            'localhost',
            '127.0.0.1',
            '::1'
        ], true)
        && in_array($direccionCliente, ['127.0.0.1', '::1'], true);
}

function formularioPlan($codigoPlan, $csrfToken, $codigoPlanActual)
{
    $esActual = $codigoPlanActual === $codigoPlan;
    $textoBoton = $esActual ? 'Plan activo' : 'Elegir';
    $disabled = $esActual ? ' disabled aria-pressed="true"' : '';

    return '<form class="plan-order-form" action="elegir_plan.php" method="post">'
        . '<input type="hidden" name="csrf_token" value="'
        . htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') . '">'
        . '<input type="hidden" name="plan_code" value="'
        . htmlspecialchars($codigoPlan, ENT_QUOTES, 'UTF-8') . '">'
        . '<button type="submit"' . $disabled . '>'
        . htmlspecialchars($textoBoton, ENT_QUOTES, 'UTF-8')
        . '</button></form>';
}