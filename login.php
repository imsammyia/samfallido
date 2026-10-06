
<?php

session_start();

include("conexion.php");

if (isset($_POST['iniciar'])) {

    $correo = $_POST['correo'];
    $contraseña = $_POST['contraseña'];

    $sql = "SELECT * FROM usuarios WHERE correo = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $correo);
    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 1) {

        $usuario = $resultado->fetch_assoc();

        if (password_verify($contraseña, $usuario['contraseña'])) {

            $_SESSION['id'] = $usuario['id'];
            $_SESSION['nombre'] = $usuario['nombre'];
            $_SESSION['correo'] = $usuario['correo'];

            $stmt->close();
            $conn->close();

            if (!empty($_SESSION['pending_invitation_token'])) {
                $token = rawurlencode($_SESSION['pending_invitation_token']);
                unset($_SESSION['pending_invitation_token']);
                header('Location: aceptar_invitacion.php?token=' . $token);
                exit();
            }

            header("Location: index.php?login=exitoso");
            exit();

        } else {

            $stmt->close();
            $conn->close();

            header("Location: index.php?error=contraseña");
            exit();
        }

    } else {

        $stmt->close();
        $conn->close();

        header("Location: index.php?error=usuario");
        exit();
    }
}

?>

