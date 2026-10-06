
<?php

include("conexion.php");

if (isset($_POST['registrarse'])) {

    $nombre = $_POST['nombre'];
    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    
    $contraseña_segura = password_hash(
        $contraseña,
        PASSWORD_DEFAULT
    );

    
    $sql = "SELECT id FROM usuarios WHERE correo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {

        $stmt->close();
        $conn->close();

        header("Location: index.php?error=correo");
        exit();

    }

    $stmt->close();

    
    $sql = "INSERT INTO usuarios (nombre, correo, contraseña)
            VALUES (?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "sss",
        $nombre,
        $correo,
        $contraseña_segura
    );

    if ($stmt->execute()) {

        $stmt->close();
        $conn->close();

        header("Location: index.php?registro=exitoso");
        exit();

    } else {

        $stmt->close();
        $conn->close();

        header("Location: index.php?error=registro");
        exit();

    }

}

?>

