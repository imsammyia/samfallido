<?php

session_start();
include("conexion.php");
include("planes_helper.php");
include("gestion_helper.php");
include("invitaciones_mailer.php");

$redirigir = static function ($organizacionId, $estado, $ancla = '') {
    $destino = 'gestion.php';
    $parametros = [];

    if ($organizacionId) {
        $parametros['org'] = (int) $organizacionId;
    }
    if ($estado !== '') {
        $parametros['estado'] = $estado;
    }

    if ($parametros) {
        $destino .= '?' . http_build_query($parametros);
    }
    if ($ancla !== '') {
        $destino .= '#' . rawurlencode($ancla);
    }

    header('Location: ' . $destino);
    exit;
};

if (!isset($_SESSION['id'])) {
    header('Location: index.php?plan=login#precios');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Método no permitido.');
}

if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('La sesión del formulario venció. Recarga la página.');
}

$usuarioId = (int) $_SESSION['id'];
$accion = $_POST['action'] ?? '';
$organizacionId = filter_var(
    $_POST['organization_id'] ?? null,
    FILTER_VALIDATE_INT
);
$transaccionActiva = false;
$estadoError = 'dato_invalido';

try {
    if ($accion === 'create_organization') {
        $nombre = trim($_POST['name'] ?? '');
        $tipo = $_POST['organization_type'] ?? '';
        if ($nombre === '' || mb_strlen($nombre) > 140
            || !in_array($tipo, ['school', 'business', 'mixed'], true)) {
            $redirigir(null, 'dato_invalido');
        }

        $plan = obtenerPlanUsuario($conn, $usuarioId);
        $limite = limiteEspaciosPorPlan($plan['code']);
        $stmt = $conn->prepare(
            'SELECT COUNT(*) AS total FROM organizations WHERE owner_user_id = ?'
        );
        $stmt->bind_param('i', $usuarioId);
        $stmt->execute();
        $total = (int) $stmt->get_result()->fetch_assoc()['total'];
        $stmt->close();

        if ($limite !== null && $total >= $limite) {
            $redirigir(null, 'limite_espacios');
        }

        $conn->begin_transaction();
        $transaccionActiva = true;
        $stmt = $conn->prepare(
            'INSERT INTO organizations (owner_user_id, name, organization_type) VALUES (?, ?, ?)'
        );
        $stmt->bind_param('iss', $usuarioId, $nombre, $tipo);
        $stmt->execute();
        $organizacionId = (int) $conn->insert_id;
        $stmt->close();

        $stmt = $conn->prepare(
            "INSERT INTO organization_members (organization_id, user_id, role)
             VALUES (?, ?, 'owner')"
        );
        $stmt->bind_param('ii', $organizacionId, $usuarioId);
        $stmt->execute();
        $stmt->close();
        $conn->commit();
        $transaccionActiva = false;
        $redirigir($organizacionId, 'organizacion_creada');
    }

    if (!$organizacionId || !in_array($accion, [
        'enable_mixed_modules',
        'create_product',
        'stock_movement',
        'create_student',
        'mark_attendance',
        'invite_member'
    ], true)) {
        $redirigir(null, 'dato_invalido');
    }

    $membresia = obtenerMembresiaOrganizacion($conn, $organizacionId, $usuarioId);
    if (!$membresia) {
        http_response_code(403);
        exit('No tienes acceso a esta organización.');
    }

    if (in_array($accion, ['invite_member', 'enable_mixed_modules'], true)
        && !usuarioPuedeAdministrarOrganizacion($membresia)) {
        $redirigir($organizacionId, 'sin_permiso', 'equipo');
    }

    if ($accion === 'enable_mixed_modules') {
        $stmt = $conn->prepare(
            "UPDATE organizations SET organization_type = 'mixed' WHERE id = ?"
        );
        $stmt->bind_param('i', $organizacionId);
        $stmt->execute();
        $stmt->close();
        $redirigir($organizacionId, 'modulos_activados');
    }

    if ($accion === 'create_product') {
        if (!in_array($membresia['organization_type'], ['business', 'mixed'], true)) {
            $redirigir($organizacionId, 'sin_permiso');
        }

        $nombre = trim($_POST['name'] ?? '');
        $sku = trim($_POST['sku'] ?? '');
        $descripcion = trim($_POST['description'] ?? '');
        $unidad = trim($_POST['unit'] ?? 'unidad');
        $cantidadInicial = $_POST['initial_quantity'] ?? '';
        $minimo = $_POST['minimum_quantity'] ?? '';

        if ($nombre === '' || mb_strlen($nombre) > 140
            || mb_strlen($descripcion) > 500
            || $unidad === '' || mb_strlen($unidad) > 24
            || mb_strlen($sku) > 80
            || !is_numeric($cantidadInicial) || !is_numeric($minimo)
            || (float) $cantidadInicial < 0 || (float) $minimo < 0) {
            $redirigir($organizacionId, 'dato_invalido', 'inventario');
        }

        $cantidadInicial = (float) $cantidadInicial;
        $minimo = (float) $minimo;
        $sku = $sku === '' ? null : $sku;
        $conn->begin_transaction();
        $transaccionActiva = true;
        $stmt = $conn->prepare(
            "INSERT INTO inventory_products
                (organization_id, sku, name, description, unit, quantity, minimum_quantity)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'issssdd',
            $organizacionId,
            $sku,
            $nombre,
            $descripcion,
            $unidad,
            $cantidadInicial,
            $minimo
        );
        $stmt->execute();
        $productoId = (int) $conn->insert_id;
        $stmt->close();

        if ($cantidadInicial > 0) {
            $tipoMovimiento = 'in';
            $nota = 'Existencia inicial';
            $stmt = $conn->prepare(
                "INSERT INTO inventory_movements
                    (organization_id, product_id, user_id, movement_type, quantity, note)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param(
                'iiisds',
                $organizacionId,
                $productoId,
                $usuarioId,
                $tipoMovimiento,
                $cantidadInicial,
                $nota
            );
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $transaccionActiva = false;
        $redirigir($organizacionId, 'producto_creado', 'inventario');
    }

    if ($accion === 'stock_movement') {
        if (!in_array($membresia['organization_type'], ['business', 'mixed'], true)) {
            $redirigir($organizacionId, 'sin_permiso');
        }

        $productoId = filter_var($_POST['product_id'] ?? null, FILTER_VALIDATE_INT);
        $tipoMovimiento = $_POST['movement_type'] ?? '';
        $cantidad = $_POST['quantity'] ?? '';
        $nota = trim($_POST['note'] ?? '');
        if (!$productoId || !in_array($tipoMovimiento, ['in', 'out'], true)
            || !is_numeric($cantidad) || (float) $cantidad <= 0
            || (float) $cantidad > 9999999999.99 || mb_strlen($nota) > 300) {
            $redirigir($organizacionId, 'dato_invalido', 'inventario');
        }
        $cantidad = (float) $cantidad;

        $conn->begin_transaction();
        $transaccionActiva = true;
        $stmt = $conn->prepare(
            'SELECT quantity FROM inventory_products WHERE id = ? AND organization_id = ? FOR UPDATE'
        );
        $stmt->bind_param('ii', $productoId, $organizacionId);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$producto || ($tipoMovimiento === 'out' && (float) $producto['quantity'] < $cantidad)) {
            $conn->rollback();
            $transaccionActiva = false;
            $redirigir($organizacionId, 'stock_insuficiente', 'inventario');
        }

        if ($tipoMovimiento === 'in') {
            $stmt = $conn->prepare(
                'UPDATE inventory_products SET quantity = quantity + ? WHERE id = ? AND organization_id = ?'
            );
        } else {
            $stmt = $conn->prepare(
                'UPDATE inventory_products SET quantity = quantity - ? WHERE id = ? AND organization_id = ?'
            );
        }
        $stmt->bind_param('dii', $cantidad, $productoId, $organizacionId);
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare(
            "INSERT INTO inventory_movements
                (organization_id, product_id, user_id, movement_type, quantity, note)
             VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            'iiisds',
            $organizacionId,
            $productoId,
            $usuarioId,
            $tipoMovimiento,
            $cantidad,
            $nota
        );
        $stmt->execute();
        $stmt->close();
        $conn->commit();
        $transaccionActiva = false;
        $redirigir($organizacionId, 'movimiento_guardado', 'inventario');
    }

    if ($accion === 'create_student') {
        if (!in_array($membresia['organization_type'], ['school', 'mixed'], true)) {
            $redirigir($organizacionId, 'sin_permiso');
        }

        $codigo = trim($_POST['student_code'] ?? '');
        $nombre = trim($_POST['full_name'] ?? '');
        $grupo = trim($_POST['group_name'] ?? '');
        if ($codigo === '' || mb_strlen($codigo) > 64
            || $nombre === '' || mb_strlen($nombre) > 160
            || $grupo === '' || mb_strlen($grupo) > 100) {
            $redirigir($organizacionId, 'dato_invalido', 'asistencia');
        }

        $stmt = $conn->prepare(
            'INSERT INTO organization_students (organization_id, student_code, full_name, group_name)
             VALUES (?, ?, ?, ?)'
        );
        $stmt->bind_param('isss', $organizacionId, $codigo, $nombre, $grupo);
        $stmt->execute();
        $stmt->close();
        $redirigir($organizacionId, 'estudiante_creado', 'asistencia');
    }

    if ($accion === 'mark_attendance') {
        if (!in_array($membresia['organization_type'], ['school', 'mixed'], true)) {
            $redirigir($organizacionId, 'sin_permiso');
        }

        $estudianteId = filter_var($_POST['student_id'] ?? null, FILTER_VALIDATE_INT);
        $fecha = $_POST['attendance_date'] ?? '';
        $estadoAsistencia = $_POST['status'] ?? '';
        $nota = trim($_POST['note'] ?? '');
        $fechaValida = is_string($fecha)
            && preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)
            && checkdate((int) substr($fecha, 5, 2), (int) substr($fecha, 8, 2), (int) substr($fecha, 0, 4));

        if (!$estudianteId || !$fechaValida
            || !in_array($estadoAsistencia, ['present', 'absent', 'late', 'excused'], true)
            || mb_strlen($nota) > 300) {
            $redirigir($organizacionId, 'dato_invalido', 'asistencia');
        }

        $stmt = $conn->prepare(
            'SELECT id FROM organization_students
             WHERE id = ? AND organization_id = ? AND active = 1'
        );
        $stmt->bind_param('ii', $estudianteId, $organizacionId);
        $stmt->execute();
        $estudiante = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if (!$estudiante) {
            $redirigir($organizacionId, 'dato_invalido', 'asistencia');
        }

        $stmt = $conn->prepare(
            "INSERT INTO attendance_records
                (organization_id, student_id, marked_by, attendance_date, status, note)
             VALUES (?, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                marked_by = VALUES(marked_by),
                status = VALUES(status),
                note = VALUES(note),
                updated_at = NOW()"
        );
        $stmt->bind_param(
            'iiisss',
            $organizacionId,
            $estudianteId,
            $usuarioId,
            $fecha,
            $estadoAsistencia,
            $nota
        );
        $stmt->execute();
        $stmt->close();
        $redirigir($organizacionId, 'asistencia_guardada', 'asistencia');
    }

    if ($accion === 'invite_member') {
        $correo = strtolower(trim($_POST['email'] ?? ''));
        $rol = $_POST['role'] ?? 'staff';
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL) || mb_strlen($correo) > 150
            || !in_array($rol, ['admin', 'staff'], true)) {
            $redirigir($organizacionId, 'dato_invalido', 'equipo');
        }

        $stmt = $conn->prepare(
            "SELECT m.user_id
             FROM organization_members m
             INNER JOIN usuarios u ON u.id = m.user_id
             WHERE m.organization_id = ? AND u.correo = ?
             LIMIT 1"
        );
        $stmt->bind_param('is', $organizacionId, $correo);
        $stmt->execute();
        $yaEsMiembro = $stmt->get_result()->num_rows > 0;
        $stmt->close();
        if ($yaEsMiembro) {
            $redirigir($organizacionId, 'dato_invalido', 'equipo');
        }

        $stmt = $conn->prepare(
            "UPDATE organization_invitations
             SET status = 'cancelled'
             WHERE organization_id = ? AND invited_email = ? AND status = 'pending'"
        );
        $stmt->bind_param('is', $organizacionId, $correo);
        $stmt->execute();
        $stmt->close();

        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $stmt = $conn->prepare(
            "INSERT INTO organization_invitations
                (organization_id, invited_email, invited_role, token_hash, invited_by, expires_at)
             VALUES (?, ?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 48 HOUR))"
        );
        $stmt->bind_param('isssi', $organizacionId, $correo, $rol, $tokenHash, $usuarioId);
        $stmt->execute();
        $invitacionId = (int) $conn->insert_id;
        $stmt->close();

        try {
            enviarInvitacionOrganizacion($correo, $membresia['name'], $rol, $token);
        } catch (Throwable $error) {
            $stmt = $conn->prepare(
                "UPDATE organization_invitations SET status = 'cancelled' WHERE id = ?"
            );
            $stmt->bind_param('i', $invitacionId);
            $stmt->execute();
            $stmt->close();
            $estadoError = is_file(__DIR__ . '/mail_config.php')
                && is_file(__DIR__ . '/vendor/autoload.php')
                ? 'invitacion_fallida'
                : 'smtp_no_configurado';
            $redirigir($organizacionId, $estadoError, 'equipo');
        }

        $redirigir($organizacionId, 'invitacion_enviada', 'equipo');
    }
} catch (mysqli_sql_exception $error) {
    if ($transaccionActiva) {
        $conn->rollback();
    }
    $estadoError = (int) $error->getCode() === 1062
        ? 'dato_duplicado'
        : 'dato_invalido';
} catch (Throwable $error) {
    if ($transaccionActiva) {
        $conn->rollback();
    }
    $estadoError = 'dato_invalido';
}

$redirigir($organizacionId, $estadoError);
