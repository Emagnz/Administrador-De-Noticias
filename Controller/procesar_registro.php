<?php
require_once '../Config/conexion.php';
require_once '../Model/Usuario.php';

$nombre   = $_POST['usuario']  ?? '';
$password = $_POST['password'] ?? '';
$password2 = $_POST['password_confirmacion'] ?? '';
$roles     = $_POST['rol']     ?? [];

// Validar que los campos no estén vacíos
if (empty($nombre) || empty($password) || empty($password2)) {
    header('Location: ../view/login.php?error=campos_vacios');
    exit();
}

// Validar que las contraseñas coincidan
if ($password !== $password2) {
    header('Location: ../view/login.php?error=passwords_no_coinciden');
    exit();
}

// Validar que se seleccionó al menos un rol
if (empty($roles)) {
    header('Location: ../view/login.php?error=sin_rol');
    exit();
}

$usuario = new Usuario($conn);
$resultado = $usuario->registrar($nombre, $password, $roles);

if ($resultado) {
    header('Location: ../view/login.php?exito=registro_ok');
} else {
    header('Location: ../view/login.php?error=usuario_existente');
}

exit();