<?php

function enviarInvitacionOrganizacion($correo, $nombreOrganizacion, $rol, $token)
{
    $autoload = __DIR__ . '/vendor/autoload.php';
    $configPath = __DIR__ . '/mail_config.php';

    if (!is_file($autoload) || !is_file($configPath)) {
        throw new RuntimeException('Falta instalar PHPMailer o configurar mail_config.php.');
    }

    require_once $autoload;
    $config = require $configPath;

    $camposNecesarios = [
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_email',
        'from_name',
        'base_url'
    ];

    foreach ($camposNecesarios as $campo) {
        if (empty($config[$campo])) {
            throw new RuntimeException('La configuración SMTP está incompleta.');
        }
    }

    if (strpos($config['password'], 'PEGA_AQUI_') === 0) {
        throw new RuntimeException('Configura una contraseña de aplicación de Gmail.');
    }

    $baseUrl = rtrim($config['base_url'], '/');
    if (!filter_var($baseUrl, FILTER_VALIDATE_URL)) {
        throw new RuntimeException('La URL pública del sitio no es válida.');
    }

    $url = $baseUrl . '/aceptar_invitacion.php?token=' . rawurlencode($token);
    $rolVisible = $rol === 'admin' ? 'administrador' : 'personal';
    $organizacionHtml = htmlspecialchars($nombreOrganizacion, ENT_QUOTES, 'UTF-8');
    $urlHtml = htmlspecialchars($url, ENT_QUOTES, 'UTF-8');

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->CharSet = PHPMailer\PHPMailer\PHPMailer::CHARSET_UTF8;
    $mail->isSMTP();
    $mail->Host = $config['host'];
    $mail->SMTPAuth = true;
    $mail->Username = $config['username'];
    $mail->Password = $config['password'];
    $mail->Port = (int) $config['port'];
    $mail->SMTPSecure = strtolower($config['encryption']) === 'ssl'
        ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
        : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mail->setFrom($config['from_email'], $config['from_name']);
    $mail->addAddress($correo);
    $mail->isHTML(true);
    $mail->Subject = 'Invitación a ' . $nombreOrganizacion . ' | Sam Software';
    $mail->Body = '<p>Te invitaron a colaborar en <strong>'
        . $organizacionHtml . '</strong> como ' . $rolVisible . '.</p>'
        . '<p><a href="' . $urlHtml . '">Aceptar invitación</a></p>'
        . '<p>Este enlace vence en 48 horas y solo puede usarse una vez.</p>';
    $mail->AltBody = "Te invitaron a colaborar en {$nombreOrganizacion} como {$rolVisible}.\n"
        . "Acepta la invitación: {$url}\n"
        . 'El enlace vence en 48 horas y solo puede usarse una vez.';
    $mail->send();
}
