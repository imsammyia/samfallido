# Configurar gestión e invitaciones

## Base de datos

La aplicación usa la base `mi_pagina` definida en `conexion.php`. En una instalación nueva, importa `planes.sql` y después `gestion.sql` en phpMyAdmin con esa base seleccionada. Sammy también necesita las tablas de conversaciones y mensajes: importa `sammy.sql` si aún no existen. En esta instalación local las tablas ya están creadas.

## Dependencia SMTP

Instala las dependencias del proyecto desde la carpeta de la aplicación:

```powershell
composer install
```

Esto instala PHPMailer usando `composer.lock`.

## Gmail

1. Activa la verificación en dos pasos de la cuenta de Google que enviará las invitaciones.
2. Crea una contraseña de aplicación para correo.
3. Copia `mail_config.example.php` como `mail_config.php`.
4. En `mail_config.php`, configura `username`, `from_email`, `password` y `base_url`. Pega la contraseña de aplicación sin espacios. No compartas ni publiques ese archivo; `.gitignore` lo excluye.
5. En producción, usa la URL HTTPS pública del sitio en `base_url`, sin terminar en `/`.

Las invitaciones vencen a las 48 horas, se aceptan con la cuenta cuyo correo recibió la invitación y solo pueden usarse una vez.

## Límites

Los espacios de trabajo cuentan para el propietario: Gratis 1, Demo 2, Básico 6, Profesional 10; Semi Empresarial y Empresarial no tienen límite. Productos y estudiantes no tienen tope interno en esta versión.
