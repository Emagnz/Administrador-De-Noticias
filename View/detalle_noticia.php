<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: ../View/login.php');
    exit();
}

require_once '../Config/conexion.php';
require_once '../Model/Noticia.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    header('Location: ../index.php');
    exit();
}

$modeloNoticia = new Noticia($conn);
$noticia       = $modeloNoticia->obtenerPorId($id);

if (!$noticia) {
    header('Location: ../index.php');
    exit();
}

$estado_id   = $noticia['noticia_estado'];
$es_autor     = $_SESSION['id'] == $noticia['noticia_autor'];
$es_editor    = in_array(1, $_SESSION['roles']);
$es_validador = in_array(2, $_SESSION['roles']);
$historial = $modeloNoticia->obtenerHistorial($id);

$badges = [
    1 => 'badge-borrador',
    2 => 'badge-validacion',
    3 => 'badge-correccion',
    4 => 'badge-publicada',
    5 => 'badge-expirada',
    6 => 'badge-anulada'
];
$clase_badge = $badges[$estado_id] ?? 'badge-borrador';

$exito_msg = null;
$error_msg = null;

$mensajes_exito = [
    'estado_cambiado' => 'El estado de la noticia fue actualizado correctamente.'
];

$mensajes_error = [
    'sin_permiso'  => 'No tenés permiso para realizar esa acción.',
    'error_estado' => 'Ocurrió un error al cambiar el estado.'
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
  <title><?php echo htmlspecialchars($noticia['noticia_titulo']); ?> — Noticias</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Source+Sans+3:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/detalle_noticia.css">
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
      <a href="../index.php" class="btn-volver">← Volver al listado</a>
    </div>

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

    <div class="detalle-card">

      <?php if ($noticia['noticia_imagen']) : ?>
        <img src="../uploads/<?php echo htmlspecialchars($noticia['noticia_imagen']); ?>"
             class="detalle-imagen" alt="imagen noticia">
      <?php endif; ?>

      <div class="detalle-header">
        <div>
          <span class="badge-estado <?php echo $clase_badge; ?>">
            <?php echo htmlspecialchars($noticia['estado_nombre']); ?>
          </span>
        </div>
        <h1 class="detalle-titulo">
          <?php echo htmlspecialchars($noticia['noticia_titulo']); ?>
        </h1>
        <div class="detalle-meta">
          <span>Autor: <?php echo htmlspecialchars($noticia['nombre_usuario']); ?></span>
          <span>Creado: <?php echo date('d/m/Y', strtotime($noticia['noticia_fechaCreado'])); ?></span>
          <?php if ($noticia['noticia_fechaPublicado']) : ?>
            <span>Publicado: <?php echo date('d/m/Y', strtotime($noticia['noticia_fechaPublicado'])); ?></span>
          <?php endif; ?>
        </div>
      </div>

      <div class="detalle-descripcion">
        <?php echo nl2br(htmlspecialchars($noticia['noticia_descripcion'])); ?>
      </div>

      <!-- Acciones segun estado -->
      <div class="detalle-acciones">

        <?php if ($estado_id == 1 && $es_editor && $es_autor) : ?>
          <!-- Borrador: solo el autor editor puede actuar -->
          <form action="../Controller/procesar_cambiar_estado.php" method="POST">
            <input type="hidden" name="noticia_id" value="<?php echo $noticia['noticia_id']; ?>">
            <input type="hidden" name="estado_nuevo" value="2">
            <button type="submit" class="btn-accion btn-verde">Enviar a validación</button>
          </form>
          <form action="../Controller/procesar_cambiar_estado.php" method="POST">
            <input type="hidden" name="noticia_id" value="<?php echo $noticia['noticia_id']; ?>">
            <input type="hidden" name="estado_nuevo" value="6">
            <button type="submit" class="btn-accion btn-peligro">Anular</button>
          </form>
          <a href="crear_noticia.php?id=<?php echo $noticia['noticia_id']; ?>" class="btn-accion btn-advertencia">
            Editar
          </a>

        <?php elseif ($estado_id == 2) : ?>
          <!-- Lista para Validación: validador diferente al autor -->
          <?php if ($es_validador && !$es_autor) : ?>
            <form action="../Controller/procesar_cambiar_estado.php" method="POST">
              <input type="hidden" name="noticia_id" value="<?php echo $noticia['noticia_id']; ?>">
              <input type="hidden" name="estado_nuevo" value="4">
              <button type="submit" class="btn-accion btn-verde">Publicar</button>
            </form>
            <form action="../Controller/procesar_cambiar_estado.php" method="POST">
              <input type="hidden" name="noticia_id" value="<?php echo $noticia['noticia_id']; ?>">
              <input type="hidden" name="estado_nuevo" value="3">
              <button type="submit" class="btn-accion btn-advertencia">Enviar a corrección</button>
            </form>
          <?php elseif ($es_autor) : ?>
            <p class="texto-info">Esta noticia está esperando validación. No podés validar tu propia noticia.</p>
          <?php else : ?>
            <p class="texto-info">Esta noticia está esperando validación por un Validador.</p>
          <?php endif; ?>

        <?php elseif ($estado_id == 3 && $es_editor && $es_autor) : ?>
          <!-- Para Corrección: solo el autor editor puede modificar -->
          <a href="crear_noticia.php?id=<?php echo $noticia['noticia_id']; ?>" class="btn-accion btn-verde">
            Editar noticia
          </a>

        <?php elseif ($estado_id == 4) : ?>
          <p class="texto-info">Esta noticia está publicada y no puede modificarse.</p>

        <?php elseif ($estado_id == 5) : ?>
          <p class="texto-info">Esta noticia ha expirado.</p>

        <?php elseif ($estado_id == 6) : ?>
          <p class="texto-info">Esta noticia fue anulada.</p>

        <?php endif; ?>

      </div>

    </div>

    <!-- Historial -->
    <div class="detalle-historial">
        <div class="historial-titulo">Historial de cambios</div>

        <?php if (empty($historial)) : ?>
            <p class="texto-info">Esta noticia no tiene historial de cambios.</p>

        <?php else : ?>
            <div class="historial-lista">
            <?php foreach ($historial as $entrada) : ?>
                <div class="historial-item">
                <div class="historial-fecha">
                    <?php echo date('d/m/Y H:i', strtotime($entrada['historial_fecha'])); ?>
                </div>
                <div class="historial-contenido">
                    <span class="historial-usuario">
                    <?php echo htmlspecialchars($entrada['nombre_usuario']); ?>
                    </span>
                    <?php if ($entrada['estado_anterior']) : ?>
                    cambió el estado de
                    <span class="historial-estado">
                        <?php echo htmlspecialchars($entrada['estado_anterior']); ?>
                    </span>
                    a
                    <span class="historial-estado">
                        <?php echo htmlspecialchars($entrada['estado_posterior']); ?>
                    </span>
                    <?php else : ?>
                    creó la noticia en estado
                    <span class="historial-estado">
                        <?php echo htmlspecialchars($entrada['estado_posterior']); ?>
                    </span>
                    <?php endif; ?>
                    <?php if ($entrada['historial_observacion']) : ?>
                    <div class="historial-observacion">
                        "<?php echo htmlspecialchars($entrada['historial_observacion']); ?>"
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
        </div>

    <?php endif; ?>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>