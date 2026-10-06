<?php

function limiteEspaciosPorPlan($codigoPlan)
{
    $limites = [
        'free' => 1,
        'demo' => 2,
        'basic' => 6,
        'professional' => 10,
        'semi_enterprise' => null,
        'enterprise' => null
    ];

    return $limites[$codigoPlan] ?? 1;
}

function obtenerMembresiaOrganizacion($conn, $organizacionId, $usuarioId)
{
    $stmt = $conn->prepare(
        "SELECT o.id, o.owner_user_id, o.name, o.organization_type, m.role
         FROM organizations o
         INNER JOIN organization_members m ON m.organization_id = o.id
         WHERE o.id = ? AND m.user_id = ?
         LIMIT 1"
    );
    $stmt->bind_param('ii', $organizacionId, $usuarioId);
    $stmt->execute();
    $membresia = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $membresia ?: null;
}

function listarOrganizacionesUsuario($conn, $usuarioId)
{
    $stmt = $conn->prepare(
        "SELECT o.id, o.name, o.organization_type, o.owner_user_id, m.role
         FROM organizations o
         INNER JOIN organization_members m ON m.organization_id = o.id
         WHERE m.user_id = ?
         ORDER BY o.created_at DESC, o.id DESC"
    );
    $stmt->bind_param('i', $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();
    $organizaciones = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $organizaciones;
}

function usuarioPuedeAdministrarOrganizacion($membresia)
{
    return $membresia !== null
        && in_array($membresia['role'], ['owner', 'admin'], true);
}

function escaparGestion($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}
