<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/login.php');
    exit();
}

if (!in_array(1, $_SESSION['roles'])) {
    header('Location: ../index.php?error=sin_permiso');
    exit();
}

require_once '../Config/conexion.php';
require_once '../Model/Noticia.php';
require_once '../Model/Parametro.php';

$titulo      = $_POST['titulo']      ?? '';
$descripcion = $_POST['descripcion'] ?? '';

$url_error = '../View/crear_noticia.php?error=';
$url_datos = '&titulo=' . urlencode($titulo) . '&descripcion=' . urlencode($descripcion);



// Validaciones de texto

if (empty($titulo) || empty($descripcion)) {
    header('Location: ' . $url_error . 'campos_vacios' . $url_datos);
    exit();
}

if (strlen($titulo) < 10 || strlen($titulo) > 100) {
    header('Location: ' . $url_error . 'titulo_invalido' . $url_datos);
    exit();
}

if (strlen($descripcion) < 50) {
    header('Location: ' . $url_error . 'descripcion_invalida' . $url_datos);
    exit();
}


// Validacion de titulo duplicado

$modeloNoticia = new Noticia($conn);

if ($modeloNoticia->existeTitulo($titulo)) {
    header('Location: ' . $url_error . 'titulo_duplicado' . $url_datos);
    exit();
}


// Validacion y procesamiento de imagen

$parametro     = new Parametro($conn);
$max_mb        = $parametro->obtenerPorClave('imagen_max_mb');
$tamano_maximo = $max_mb * 1024 * 1024;
$nombre_imagen = null;

if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {

    $extension = strtolower(pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION));
    $tamano    = $_FILES['imagen']['size'];

    if (!in_array($extension, ['jpg', 'jpeg', 'png'])) {
        header('Location: ' . $url_error . 'imagen_formato' . $url_datos);
        exit();
    }

    if ($tamano > $tamano_maximo) {
        header('Location: ' . $url_error . 'imagen_tamano' . $url_datos);
        exit();
    }

    $nombre_imagen = uniqid() . '.' . $extension;
    $destino       = '../uploads/' . $nombre_imagen;

    if (!move_uploaded_file($_FILES['imagen']['tmp_name'], $destino)) {
        header('Location: ' . $url_error . 'imagen_error' . $url_datos);
        exit();
    }
}


// Crear noticia

$resultado = $modeloNoticia->crear($titulo, $descripcion, $_SESSION['id'], $nombre_imagen);

if ($resultado) {
    header('Location: ../index.php?exito=noticia_creada');
} else {
    header('Location: ' . $url_error . 'error_guardar' . $url_datos);
}

exit();