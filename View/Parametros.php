<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/login.php');
    exit();
}
if (!in_array(3, $_SESSION['roles'])) {
    header('Location: ../index.php?error=sin_permiso');
    exit();
}

require_once '../Config/conexion.php';
require_once '../Model/Parametro.php';

$parametro       = new Parametro($conn);
$dias_expiracion = $parametro->obtenerPorClave('dias_expiracion');
$imagen_max_mb   = $parametro->obtenerPorClave('imagen_max_mb');

$exito_msg = null;
$error_msg = null;

$mensajes_exito = [
    'parametros_guardados' => 'Los parámetros fueron actualizados correctamente.'
];
$mensajes_error = [
    'campos_vacios'   => 'Completá todos los campos.',
    'valores_invalidos' => 'Los valores deben ser números mayores a 0.',
    'error_guardar'   => 'Ocurrió un error al guardar los parámetros.'
];

if (isset($_GET['exito'])) {
    $exito_msg = $mensajes_exito[$_GET['exito']] ?? null;
}
if (isset($_GET['error'])) {
    $error_msg = $mensajes_error[$_GET['error']] ?? null;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Parámetros — Noticias Institucionales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Source+Sans+3:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/parametros.css">
</head>
<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="../index.php">Noticias Institucionales</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto gap-2">
          <li class="nav-item"><a class="nav-link" href="../index.php">Inicio</a></li>
          <?php if (in_array(3, $_SESSION['roles'])) : ?>
            <li class="nav-item"><a class="nav-link" href="parametros.php">Parámetros</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="../Controller/procesar_logout.php">Cerrar sesión</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-4">

    <div class="mb-4">
      <div class="page-title">Parámetros del sistema</div>
      <div class="page-subtitle">Configuración general de la aplicación</div>
    </div>

    <div class="form-card">

      <?php if ($exito_msg) : ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo $exito_msg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <?php if ($error_msg) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $error_msg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form action="../Controller/procesar_parametros.php" method="POST">

        <div class="mb-4">
          <label for="dias_expiracion" class="form-label">
            Días hasta expiración <span class="obligatorio">*</span>
          </label>
          <div class="input-grupo">
            <input type="number" class="form-control" id="dias_expiracion"
                   name="dias_expiracion" min="1" required
                   value="<?php echo htmlspecialchars($dias_expiracion); ?>">
            <span class="input-sufijo">días</span>
          </div>
          <div class="form-hint">Cantidad de días que una noticia permanece publicada antes de expirar.</div>
        </div>

        <div class="mb-4">
          <label for="imagen_max_mb" class="form-label">
            Tamaño máximo de imagen <span class="obligatorio">*</span>
          </label>
          <div class="input-grupo">
            <input type="number" class="form-control" id="imagen_max_mb"
                   name="imagen_max_mb" min="1" required
                   value="<?php echo htmlspecialchars($imagen_max_mb); ?>">
            <span class="input-sufijo">MB</span>
          </div>
          <div class="form-hint">Tamaño máximo permitido para las imágenes de las noticias.</div>
        </div>

        <div class="d-flex gap-3">
          <button type="submit" class="btn-verde">Guardar cambios</button>
          <a href="../index.php" class="btn-cancelar">Cancelar</a>
        </div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>