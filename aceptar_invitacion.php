<?php

session_start();
include("conexion.php");
include("planes_helper.php");

$token = $_GET['token'] ?? $_POST['token'] ?? '';
if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/', $token)) {
    http_response_code(400);
    exit('El enlace de invitación no es válido.');
}

if (!isset($_SESSION['id'])) {
    $_SESSION['pending_invitation_token'] = $token;
    header('Location: index.php?gestion=login');
    exit;
}

$tokenHash = hash('sha256', $token);
$stmt = $conn->prepare(
    "SELECT i.id, i.organization_id, i.invited_email, i.invited_role,
            i.expires_at, o.name AS organization_name
     FROM organization_invitations i
     INNER JOIN organizations o ON o.id = i.organization_id
     WHERE i.token_hash = ? AND i.status = 'pending' AND i.expires_at > NOW()
     LIMIT 1"
);
$stmt->bind_param('s', $tokenHash);
$stmt->execute();
$invitacion = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$invitacion) {
    http_response_code(410);
    exit('La invitación venció, ya se usó o fue cancelada.');
}

$correoSesion = strtolower((string) ($_SESSION['correo'] ?? ''));
$correoInvitado = strtolower($invitacion['invited_email']);
if ($correoSesion !== $correoInvitado) {
    http_response_code(403);
    exit('Inicia sesión con el correo invitado para aceptar. Cierra sesión y vuelve a abrir el enlace recibido por correo.');
}

$rolesVisibles = [
    'admin' => 'Administrador',
    'staff' => 'Personal'
];
$csrfToken = obtenerTokenCsrf();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!tokenCsrfValido($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('La sesión del formulario venció. Recarga la página.');
    }

    $usuarioId = (int) $_SESSION['id'];
    try {
        $conn->begin_transaction();
        $stmt = $conn->prepare(
            "SELECT id, organization_id, invited_email, invited_role
             FROM organization_invitations
             WHERE token_hash = ? AND status = 'pending' AND expires_at > NOW()
             FOR UPDATE"
        );
        $stmt->bind_param('s', $tokenHash);
        $stmt->execute();
        $invitacionBloqueada = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$invitacionBloqueada
            || strtolower($invitacionBloqueada['invited_email']) !== $correoSesion) {
            $conn->rollback();
            http_response_code(410);
            exit('La invitación ya no está disponible para esta cuenta.');
        }

        $organizacionId = (int) $invitacionBloqueada['organization_id'];
        $rol = $invitacionBloqueada['invited_role'];
        $stmt = $conn->prepare(
            "INSERT INTO organization_members (organization_id, user_id, role)
             VALUES (?, ?, ?)
             ON DUPLICATE KEY UPDATE organization_id = VALUES(organization_id)"
        );
        $stmt->bind_param('iis', $organizacionId, $usuarioId, $rol);
        $stmt->execute();
        $stmt->close();

        $invitacionId = (int) $invitacionBloqueada['id'];
        $stmt = $conn->prepare(
            "UPDATE organization_invitations
             SET status = 'accepted', accepted_at = NOW()
             WHERE id = ? AND status = 'pending'"
        );
        $stmt->bind_param('i', $invitacionId);
        $stmt->execute();
        $stmt->close();
        $conn->commit();

        unset($_SESSION['pending_invitation_token']);
        header('Location: gestion.php?org=' . $organizacionId . '&estado=miembro');
        exit;
    } catch (Throwable $error) {
        $conn->rollback();
        http_response_code(500);
        exit('No se pudo aceptar la invitación. Inténtalo de nuevo.');
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aceptar invitación | Sam Software</title>
    <link rel="stylesheet" href="style.css?v=gestion-panel-2">
</head>
<body class="management-body">
    <main class="invite-page">
        <section class="invite-panel">
            <p class="management-eyebrow">Invitación de equipo</p>
            <h1><?= htmlspecialchars($invitacion['organization_name'], ENT_QUOTES, 'UTF-8') ?></h1>
            <p>Te invitaron como <?= htmlspecialchars($rolesVisibles[$invitacion['invited_role']] ?? 'Personal', ENT_QUOTES, 'UTF-8') ?>.</p>
            <p class="invite-email"><?= htmlspecialchars($invitacion['invited_email'], ENT_QUOTES, 'UTF-8') ?></p>
            <form method="post">
                <input type="hidden" name="token" value="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken, ENT_QUOTES, 'UTF-8') ?>">
                <button type="submit">Aceptar invitación</button>
            </form>
            <a href="index.php">Volver al inicio</a>
        </section>
    </main>
    <script src="appearance.js?v=gestion-panel"></script>
</body>
</html>
