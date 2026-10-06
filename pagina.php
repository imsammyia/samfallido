<?php

session_start();

if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Página principal</title>
</head>

<body>

<h1>Bienvenido, <?php echo $_SESSION['nombre']; ?></h1>

<p>Has iniciado sesión correctamente.</p>

<a href="logout.php">Cerrar sesión</a>

</body>
</html>