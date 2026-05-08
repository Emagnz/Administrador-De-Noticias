<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/login.php');
    exit();
}

require_once '../Config/conexion.php';
require_once '../Model/Parametro.php';
require_once '../Model/Noticia.php';

$dias_expiracion = $_POST['dias_expiracion'] ?? '';
$imagen_max_mb   = $_POST['imagen_max_mb']   ?? '';

// Validar que los campos no estén vacíos
if (empty($dias_expiracion) || empty($imagen_max_mb)) {
    header('Location: ../View/parametros.php?error=campos_vacios');
    exit();
}

// Validar que sean números mayores a 0
if (!is_numeric($dias_expiracion) || !is_numeric($imagen_max_mb) ||
    $dias_expiracion <=0 || $imagen_max_mb <= 0) {
    header('Location: ../View/parametros.php?error=valores_invalidos');
    exit();
}

// =====================
// Guardar parametros
// =====================

$parametro  = new Parametro($conn);
$resultado1 = $parametro->actualizar('dias_expiracion', $dias_expiracion);
$resultado2 = $parametro->actualizar('imagen_max_mb',   $imagen_max_mb);

if (!$resultado1 || !$resultado2) {
    header('Location: ../View/parametros.php?error=error_guardar');
    exit();
}

// =====================
// Verificar expiracion con los nuevos dias
// =====================

$modeloNoticia = new Noticia($conn);
$modeloNoticia->verificarExpiracion($dias_expiracion);

header('Location: ../View/parametros.php?exito=parametros_guardados');
exit();