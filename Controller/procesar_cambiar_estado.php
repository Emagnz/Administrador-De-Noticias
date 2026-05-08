<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/login.php');
    exit();
}

require_once '../Config/conexion.php';
require_once '../Model/Noticia.php';

$noticia_id   = $_POST['noticia_id']   ?? null;
$estado_nuevo = $_POST['estado_nuevo'] ?? null;

if (!$noticia_id || !$estado_nuevo) {
    header('Location: ../index.php');
    exit();
}

$modeloNoticia = new Noticia($conn);
$noticia       = $modeloNoticia->obtenerPorId($noticia_id);
$estado_actual = $noticia['noticia_estado'];

$es_autor     = $_SESSION['id'] == $noticia['noticia_autor'];
$es_editor    = in_array(1, $_SESSION['roles']);
$es_validador = in_array(2, $_SESSION['roles']);


// Verificar permisos
$permitido = false;

// Borrador → Lista para validación o Anulada (solo autor editor)
if ($estado_actual == 1 && $es_editor && $es_autor && in_array($estado_nuevo, [2, 6])) {
    $permitido = true;
}

// Lista para validación → Publicada o Para corrección (validador que no sea autor)
if ($estado_actual == 2 && $es_validador && !$es_autor && in_array($estado_nuevo, [3, 4])) {
    $permitido = true;
}

// Para corrección → Borrador o Lista para validación (solo autor editor)
if ($estado_actual == 3 && $es_editor && $es_autor && in_array($estado_nuevo, [1, 2])) {
    $permitido = true;
}

if (!$permitido) {
    header('Location: ../View/detalle_noticia.php?id=' . $noticia_id . '&error=sin_permiso');
    exit();
}


// Cambiar estado
$resultado = $modeloNoticia->cambiarEstado($noticia_id, $estado_nuevo, $_SESSION['id']);

if ($resultado) {
    header('Location: ../View/detalle_noticia.php?id=' . $noticia_id . '&exito=estado_cambiado');
} else {
    header('Location: ../View/detalle_noticia.php?id=' . $noticia_id . '&error=error_estado');
}

exit();