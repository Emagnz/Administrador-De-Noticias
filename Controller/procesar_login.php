<?php
session_start();

require_once '../Config/conexion.php';
require_once '../Model/Usuario.php';

$nombre   = $_POST['usuario']  ?? '';
$password = $_POST['password'] ?? '';

if (empty($nombre) || empty($password)) {
    header('Location: ../View/login.php?error=campos_vacios');
    exit();
}

$usuario   = new Usuario($conn);
$resultado = $usuario->login($nombre, $password);

if ($resultado) {
    $_SESSION['id']    = $resultado['id_usuario'];
    $_SESSION['nombre'] = $resultado['nombre_usuario'];
    $_SESSION['roles'] = $usuario->obtenerRoles($resultado['id_usuario']);
    header('Location: ../index.php');
} else {
    header('Location: ../View/login.php?error=credenciales_invalidas');
}

exit();