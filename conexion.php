<?php

$servidor = "localhost";
$usuario = "root";
$contraseña = "";
$base_datos = "mi_pagina";

$conn = new mysqli(
    $servidor,
    $usuario,
    $contraseña,
    $base_datos
);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>