<?php

session_start();
include("conexion.php");
include("planes_helper.php");
include("gestion_helper.php");

if (!isset($_SESSION['id'])) {
    header('Location: index.php?gestion=login#inicio');
    exit;
}

$usuarioId = (int) $_SESSION['id'];
$csrfToken = obtenerTokenCsrf();
$planActual = obtenerPlanUsuario($conn, $usuarioId);
$limiteEspacios = limiteEspaciosPorPlan($planActual['code']);
$organizaciones = listarOrganizacionesUsuario($conn, $usuarioId);
$organizacionId = filter_input(INPUT_GET, 'org', FILTER_VALIDATE_INT);
$organizacion = null;

if ($organizacionId) {
    $organizacion = obtenerMembresiaOrganizacion($conn, $organizacionId, $usuarioId);
    if (!$organizacion) {
        http_response_code(403);
        exit('No tienes acceso a esta organización.');
    }
} elseif ($organizaciones) {
    $organizacion = $organizaciones[0];
    $organizacionId = (int) $organizacion['id'];
}

$mensajes = [
    'organizacion_creada' => 'Espacio de trabajo creado.',
    'producto_creado' => 'Producto guardado en el inventario.',
    'movimiento_guardado' => 'Movimiento de stock registrado.',
    'estudiante_creado' => 'Estudiante agregado.',
    'asistencia_guardada' => 'Asistencia actualizada.',
    'modulos_activados' => 'Los módulos de inventario y asistencia están disponibles en este espacio.',
    'invitacion_enviada' => 'Invitación enviada por correo.',
    'miembro' => 'Ya formas parte del equipo.',
    'limite_espacios' => 'Alcanzaste el límite de espacios de tu plan.',
    'smtp_no_configurado' => 'Configura Gmail en mail_config.php para enviar invitaciones.',
    'invitacion_fallida' => 'No se pudo enviar la invitación. Revisa la configuración SMTP.',
    'dato_duplicado' => 'Ese código o SKU ya existe en este espacio.',
    'stock_insuficiente' => 'No hay suficiente stock para registrar esa salida.',
    'dato_invalido' => 'Revisa los datos e inténtalo de nuevo.',
    'sin_permiso' => 'Tu rol no permite realizar esa acción.'
];
$estado = $_GET['estado'] ?? '';
$mensaje = is_string($estado) ? ($mensajes[$estado] ?? '') : '';
$fechaAsistencia = $_GET['fecha'] ?? date('Y-m-d');
if (!is_string($fechaAsistencia)
    || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaAsistencia)
    || !checkdate(
        (int) substr($fechaAsistencia, 5, 2),
        (int) substr($fechaAsistencia, 8, 2),
        (int) substr($fechaAsistencia, 0, 4)
    )) {
    $fechaAsistencia = date('Y-m-d');
}

$productos = [];
$estudiantes = [];
$miembros = [];
$invitaciones = [];
$movimientos = [];

if ($organizacion) {
    $stmt = $conn->prepare(
        "SELECT id, sku, name, description, unit, quantity, minimum_quantity
         FROM inventory_products
         WHERE organization_id = ?
         ORDER BY name"
    );
    $stmt->bind_param('i', $organizacionId);
    $stmt->execute();
    $productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT s.id, s.student_code, s.full_name, s.group_name,
                a.status AS attendance_status, a.note AS attendance_note
         FROM organization_students s
         LEFT JOIN attendance_records a
           ON a.student_id = s.id AND a.attendance_date = ?
         WHERE s.organization_id = ? AND s.active = 1
         ORDER BY s.group_name, s.full_name"
    );
    $stmt->bind_param('si', $fechaAsistencia, $organizacionId);
    $stmt->execute();
    $estudiantes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $stmt = $conn->prepare(
        "SELECT u.nombre, u.correo, m.role
         FROM organization_members m
         INNER JOIN usuarios u ON u.id = m.user_id
         WHERE m.organization_id = ?
         ORDER BY FIELD(m.role, 'owner', 'admin', 'staff'), u.nombre"
    );
    $stmt->bind_param('i', $organizacionId);
    $stmt->execute();
    $miembros = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (usuarioPuedeAdministrarOrganizacion($organizacion)) {
        $stmt = $conn->prepare(
            "SELECT invited_email, invited_role, expires_at
             FROM organization_invitations
             WHERE organization_id = ? AND status = 'pending' AND expires_at > NOW()
             ORDER BY created_at DESC"
        );
        $stmt->bind_param('i', $organizacionId);
        $stmt->execute();
        $invitaciones = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }

    $stmt = $conn->prepare(
        "SELECT p.name AS product_name, m.movement_type, m.quantity, m.note,
                m.created_at, u.nombre AS user_name
         FROM inventory_movements m
         INNER JOIN inventory_products p ON p.id = m.product_id
         INNER JOIN usuarios u ON u.id = m.user_id
         WHERE m.organization_id = ?
         ORDER BY m.id DESC
         LIMIT 12"
    );
    $stmt->bind_param('i', $organizacionId);
    $stmt->execute();
    $movimientos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$espaciosPropios = 0;
$stmt = $conn->prepare('SELECT COUNT(*) AS total FROM organizations WHERE owner_user_id = ?');
$stmt->bind_param('i', $usuarioId);
$stmt->execute();
$espaciosPropios = (int) $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$tiposOrganizacion = [
    'school' => 'Institución educativa',
    'business' => 'Empresa o negocio',
    'mixed' => 'Institución y negocio'
];
$roles = [
    'owner' => 'Propietario',
    'admin' => 'Administrador',
    'staff' => 'Personal'
];

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión | Sam Software</title>
    <link rel="stylesheet" href="style.css?v=gestion-panel-2">
</head>
<body class="management-body">
    <header class="management-header">
        <a class="management-brand" href="index.php">
            <img src="img/logo.png" alt="Sam Software">
            <span>Gestión</span>
        </a>
        <div class="management-user">
            <span><?= escaparGestion($_SESSION['nombre'] ?? 'Usuario') ?></span>
            <a href="index.php">Inicio</a>
            <a href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="management-main">
        <div class="management-heading">
            <div>
                <p class="management-eyebrow">Panel de operaciones</p>
                <h1><?= $organizacion ? escaparGestion($organizacion['name']) : 'Tus espacios de trabajo' ?></h1>
                <?php if ($organizacion): ?>
                    <p><?= escaparGestion($tiposOrganizacion[$organizacion['organization_type']] ?? 'Organización') ?> · <?= escaparGestion($roles[$organizacion['role']] ?? 'Personal') ?></p>
                <?php else: ?>
                    <p>Inventario comercial y asistencia educativa en un solo panel.</p>
                <?php endif; ?>
            </div>
            <div class="management-plan">
                <span>Plan <?= escaparGestion($planActual['name']) ?></span>
                <strong><?= $limiteEspacios === null ? 'Espacios ilimitados' : $espaciosPropios . ' / ' . $limiteEspacios . ' espacios' ?></strong>
            </div>
        </div>

        <?php if ($mensaje !== ''): ?>
            <p class="management-notice" role="status"><?= escaparGestion($mensaje) ?></p>
        <?php endif; ?>

        <?php if (count($organizaciones) > 1): ?>
            <nav class="organization-switcher" aria-label="Espacios de trabajo">
                <?php foreach ($organizaciones as $espacio): ?>
                    <a class="<?= (int) $espacio['id'] === (int) $organizacionId ? 'is-current' : '' ?>" href="gestion.php?org=<?= (int) $espacio['id'] ?>">
                        <?= escaparGestion($espacio['name']) ?>
                    </a>
                <?php endforeach; ?>
            </nav>
        <?php endif; ?>

        <?php if ($organizacion
            && (int) $organizacion['owner_user_id'] === $usuarioId): ?>
            <details class="workspace-add">
                <summary>Crear otro espacio</summary>
                <?php if ($limiteEspacios === null || $espaciosPropios < $limiteEspacios): ?>
                    <form action="guardar_gestion.php" method="post" class="management-form management-form-inline">
                        <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                        <input type="hidden" name="action" value="create_organization">
                        <label>Nombre del espacio<input name="name" maxlength="140" required></label>
                        <label>Tipo
                            <select name="organization_type" required>
                                <?php foreach ($tiposOrganizacion as $tipo => $nombreTipo): ?>
                                    <option value="<?= escaparGestion($tipo) ?>"><?= escaparGestion($nombreTipo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button type="submit">Crear espacio</button>
                    </form>
                <?php else: ?>
                    <p class="management-limit">Tu plan alcanzó el máximo de espacios. Consulta <a href="index.php#precios">los planes</a> para ampliarlo.</p>
                <?php endif; ?>
            </details>
        <?php endif; ?>

        <?php if ($organizacion
            && $organizacion['organization_type'] !== 'mixed'
            && usuarioPuedeAdministrarOrganizacion($organizacion)): ?>
            <form action="guardar_gestion.php" method="post" class="module-enable-form">
                <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                <input type="hidden" name="action" value="enable_mixed_modules">
                <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                <span>¿Necesitas las dos herramientas en este espacio?</span>
                <button type="submit"><?= $organizacion['organization_type'] === 'school' ? 'Agregar inventario' : 'Agregar asistencia' ?></button>
            </form>
        <?php endif; ?>

        <?php if (!$organizacion): ?>
            <section class="management-section workspace-create">
                <h2>Crear espacio de trabajo</h2>
                <p>Un espacio representa una institución, empresa o negocio. Los datos quedan separados por organización.</p>
                <?php if ($limiteEspacios === null || $espaciosPropios < $limiteEspacios): ?>
                    <form action="guardar_gestion.php" method="post" class="management-form management-form-inline">
                        <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                        <input type="hidden" name="action" value="create_organization">
                        <label>Nombre del espacio<input name="name" maxlength="140" required></label>
                        <label>Tipo
                            <select name="organization_type" required>
                                <?php foreach ($tiposOrganizacion as $tipo => $nombreTipo): ?>
                                    <option value="<?= escaparGestion($tipo) ?>"><?= escaparGestion($nombreTipo) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </label>
                        <button type="submit">Crear espacio</button>
                    </form>
                <?php else: ?>
                    <p class="management-limit">Tu plan alcanzó el máximo de espacios. Consulta <a href="index.php#precios">los planes</a> para ampliar el límite.</p>
                <?php endif; ?>
            </section>
        <?php else: ?>
            <nav class="management-tabs" aria-label="Herramientas de gestión">
                <?php if (in_array($organizacion['organization_type'], ['business', 'mixed'], true)): ?>
                    <a href="#inventario">Inventario</a>
                <?php endif; ?>
                <?php if (in_array($organizacion['organization_type'], ['school', 'mixed'], true)): ?>
                    <a href="#asistencia">Asistencia</a>
                <?php endif; ?>
                <a href="#equipo">Equipo</a>
            </nav>

            <?php if (in_array($organizacion['organization_type'], ['business', 'mixed'], true)): ?>
                <section class="management-section" id="inventario">
                    <div class="management-section-heading">
                        <div>
                            <p class="management-eyebrow">Control de existencias</p>
                            <h2>Inventario</h2>
                        </div>
                        <span class="management-count"><?= count($productos) ?> productos</span>
                    </div>

                    <form action="guardar_gestion.php" method="post" class="management-form product-create-form">
                        <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                        <input type="hidden" name="action" value="create_product">
                        <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                        <label>Producto<input name="name" maxlength="140" required></label>
                        <label>SKU / código<input name="sku" maxlength="80"></label>
                        <label>Unidad<input name="unit" maxlength="24" value="unidad" required></label>
                        <label>Existencia inicial<input name="initial_quantity" type="number" min="0" step="0.01" value="0" required></label>
                        <label>Alerta bajo<input name="minimum_quantity" type="number" min="0" step="0.01" value="0" required></label>
                        <label class="product-description">Descripción<input name="description" maxlength="500"></label>
                        <button type="submit">Agregar producto</button>
                    </form>

                    <div class="management-table-wrap">
                        <table class="management-table">
                            <thead><tr><th>Producto</th><th>SKU</th><th>Existencia</th><th>Movimiento</th></tr></thead>
                            <tbody>
                                <?php foreach ($productos as $producto): ?>
                                    <tr>
                                        <td data-label="Producto">
                                            <strong><?= escaparGestion($producto['name']) ?></strong>
                                            <?php if ((float) $producto['quantity'] <= (float) $producto['minimum_quantity']): ?>
                                                <span class="stock-warning">Stock bajo</span>
                                            <?php endif; ?>
                                        </td>
                                        <td data-label="SKU"><?= escaparGestion($producto['sku'] ?: '—') ?></td>
                                        <td data-label="Existencia"><strong><?= number_format((float) $producto['quantity'], 2) ?></strong> <?= escaparGestion($producto['unit']) ?></td>
                                        <td data-label="Movimiento">
                                            <form action="guardar_gestion.php" method="post" class="stock-movement-form">
                                                <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                                                <input type="hidden" name="action" value="stock_movement">
                                                <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                                                <input type="hidden" name="product_id" value="<?= (int) $producto['id'] ?>">
                                                <select name="movement_type" aria-label="Tipo de movimiento">
                                                    <option value="in">Entrada</option>
                                                    <option value="out">Salida</option>
                                                </select>
                                                <input name="quantity" type="number" min="0.01" step="0.01" placeholder="Cantidad" required>
                                                <input name="note" maxlength="300" placeholder="Nota">
                                                <button type="submit">Registrar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$productos): ?>
                                    <tr><td colspan="4" class="empty-state">Todavía no hay productos registrados.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="management-subsection">
                        <h3>Movimientos recientes</h3>
                        <?php if ($movimientos): ?>
                            <ul class="movement-list">
                                <?php foreach ($movimientos as $movimiento): ?>
                                    <li>
                                        <span class="movement-kind movement-<?= escaparGestion($movimiento['movement_type']) ?>"><?= $movimiento['movement_type'] === 'in' ? 'Entrada' : 'Salida' ?></span>
                                        <span><?= escaparGestion($movimiento['product_name']) ?></span>
                                        <strong><?= number_format((float) $movimiento['quantity'], 2) ?></strong>
                                        <small><?= escaparGestion($movimiento['user_name']) ?> · <?= escaparGestion($movimiento['created_at']) ?></small>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="empty-state">Los movimientos aparecerán aquí.</p>
                        <?php endif; ?>
                    </div>
                </section>
            <?php endif; ?>

            <?php if (in_array($organizacion['organization_type'], ['school', 'mixed'], true)): ?>
                <section class="management-section" id="asistencia">
                    <div class="management-section-heading">
                        <div>
                            <p class="management-eyebrow">Registro diario</p>
                            <h2>Asistencia</h2>
                        </div>
                        <form method="get" class="attendance-date-form">
                            <input type="hidden" name="org" value="<?= (int) $organizacionId ?>">
                            <label>Fecha<input type="date" name="fecha" value="<?= escaparGestion($fechaAsistencia) ?>" required></label>
                            <button type="submit">Ver fecha</button>
                        </form>
                    </div>

                    <form action="guardar_gestion.php" method="post" class="management-form student-create-form">
                        <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                        <input type="hidden" name="action" value="create_student">
                        <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                        <label>Código<input name="student_code" maxlength="64" required></label>
                        <label>Nombre completo<input name="full_name" maxlength="160" required></label>
                        <label>Grupo / curso<input name="group_name" maxlength="100" required></label>
                        <button type="submit">Agregar estudiante</button>
                    </form>
                    <p class="privacy-note">Guarda solo los datos necesarios para el control de asistencia.</p>

                    <div class="management-table-wrap">
                        <table class="management-table attendance-table">
                            <thead><tr><th>Estudiante</th><th>Grupo</th><th>Estado</th></tr></thead>
                            <tbody>
                                <?php foreach ($estudiantes as $estudiante): ?>
                                    <tr>
                                        <td data-label="Estudiante"><strong><?= escaparGestion($estudiante['full_name']) ?></strong><small><?= escaparGestion($estudiante['student_code']) ?></small></td>
                                        <td data-label="Grupo"><?= escaparGestion($estudiante['group_name']) ?></td>
                                        <td data-label="Estado">
                                            <form action="guardar_gestion.php" method="post" class="attendance-mark-form">
                                                <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                                                <input type="hidden" name="action" value="mark_attendance">
                                                <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                                                <input type="hidden" name="student_id" value="<?= (int) $estudiante['id'] ?>">
                                                <input type="hidden" name="attendance_date" value="<?= escaparGestion($fechaAsistencia) ?>">
                                                <select name="status" aria-label="Asistencia de <?= escaparGestion($estudiante['full_name']) ?>">
                                                    <?php foreach (['present' => 'Presente', 'absent' => 'Ausente', 'late' => 'Tarde', 'excused' => 'Justificado'] as $valor => $etiqueta): ?>
                                                        <option value="<?= escaparGestion($valor) ?>" <?= $estudiante['attendance_status'] === $valor ? 'selected' : '' ?>><?= escaparGestion($etiqueta) ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input name="note" maxlength="300" value="<?= escaparGestion($estudiante['attendance_note'] ?? '') ?>" placeholder="Nota opcional">
                                                <button type="submit">Guardar</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                                <?php if (!$estudiantes): ?>
                                    <tr><td colspan="3" class="empty-state">Agrega estudiantes para comenzar el registro diario.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php endif; ?>

            <section class="management-section" id="equipo">
                <div class="management-section-heading">
                    <div>
                        <p class="management-eyebrow">Acceso compartido</p>
                        <h2>Equipo</h2>
                    </div>
                    <span class="management-count"><?= count($miembros) ?> miembros</span>
                </div>

                <div class="team-list">
                    <?php foreach ($miembros as $miembro): ?>
                        <div class="team-row">
                            <strong><?= escaparGestion($miembro['nombre']) ?></strong>
                            <span><?= escaparGestion($miembro['correo']) ?></span>
                            <small><?= escaparGestion($roles[$miembro['role']] ?? 'Personal') ?></small>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (usuarioPuedeAdministrarOrganizacion($organizacion)): ?>
                    <form action="guardar_gestion.php" method="post" class="management-form invite-form">
                        <input type="hidden" name="csrf_token" value="<?= escaparGestion($csrfToken) ?>">
                        <input type="hidden" name="action" value="invite_member">
                        <input type="hidden" name="organization_id" value="<?= (int) $organizacionId ?>">
                        <label>Correo de invitación<input type="email" name="email" maxlength="150" required></label>
                        <label>Rol
                            <select name="role"><option value="staff">Personal</option><option value="admin">Administrador</option></select>
                        </label>
                        <button type="submit">Enviar invitación</button>
                    </form>
                    <?php if ($invitaciones): ?>
                        <h3 class="management-subheading">Invitaciones pendientes</h3>
                        <ul class="pending-invitations">
                            <?php foreach ($invitaciones as $invitacion): ?>
                                <li><?= escaparGestion($invitacion['invited_email']) ?> · vence <?= escaparGestion($invitacion['expires_at']) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        <?php endif; ?>
    </main>
    <script src="appearance.js?v=gestion-panel"></script>
</body>
</html>
