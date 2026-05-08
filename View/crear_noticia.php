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

$id       = $_GET['id'] ?? null;
$editando = $id !== null;
$noticia  = null;

if ($editando) {
    $modeloNoticia = new Noticia($conn);
    $noticia       = $modeloNoticia->obtenerPorId($id);

    if (!$noticia) {
        header('Location: ../index.php');
        exit();
    }

    // Solo se puede editar si está en Borrador o Para Corrección
    if (!in_array($noticia['noticia_estado'], [1, 3])) {
        header('Location: ../View/detalle_noticia.php?id=' . $id . '&error=sin_permiso');
        exit();
    }
}

$errores = [
    'campos_vacios'        => 'Completá todos los campos obligatorios.',
    'titulo_invalido'      => 'El título debe tener entre 10 y 100 caracteres.',
    'descripcion_invalida' => 'La descripción debe tener al menos 50 caracteres.',
    'imagen_formato'       => 'La imagen debe ser JPG o PNG.',
    'imagen_tamano'        => 'La imagen no puede superar los 2MB.',
    'imagen_error'         => 'Ocurrió un error al subir la imagen.',
    'error_guardar'        => 'Ocurrió un error al guardar la noticia.'
];

$error_msg = isset($_GET['error']) ? ($errores[$_GET['error']] ?? 'Ocurrió un error.') : null;

// Valores del formulario: primero los de la URL (si hubo error), sino los de la BD
$titulo      = $_GET['titulo']      ?? $noticia['noticia_titulo']      ?? '';
$descripcion = $_GET['descripcion'] ?? $noticia['noticia_descripcion'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo $editando ? 'Editar noticia' : 'Nueva noticia'; ?> — Noticias Institucionales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Source+Sans+3:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/crear_noticia.css">
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
      <div class="page-title">
        <?php echo $editando ? 'Editar noticia' : 'Nueva noticia'; ?>
      </div>
      <div class="page-subtitle">
        <?php echo $editando ? 'Modificá los campos de la noticia' : 'Completá los campos para crear una noticia'; ?>
      </div>
    </div>

    <div class="form-card">

      <?php if ($error_msg) : ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo $error_msg; ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form action="../Controller/<?php echo $editando ? 'procesar_editar_noticia.php' : 'procesar_crear_noticia.php'; ?>"
            method="POST" enctype="multipart/form-data">

        <?php if ($editando) : ?>
          <input type="hidden" name="noticia_id" value="<?php echo $noticia['noticia_id']; ?>">
          <input type="hidden" name="estado_actual" value="<?php echo $noticia['noticia_estado']; ?>">
        <?php endif; ?>

        <div class="mb-3">
          <label for="titulo" class="form-label">
            Título <span class="obligatorio">*</span>
          </label>
          <input type="text" class="form-control" id="titulo" name="titulo"
                 minlength="10" maxlength="100" required
                 placeholder="Entre 10 y 100 caracteres"
                 value="<?php echo htmlspecialchars($titulo); ?>">
          <div class="form-hint">Mínimo 10 caracteres, máximo 100.</div>
        </div>

        <div class="mb-3">
          <label for="descripcion" class="form-label">
            Descripción <span class="obligatorio">*</span>
          </label>
          <textarea class="form-control" id="descripcion" name="descripcion"
                    rows="5" minlength="50" required
                    placeholder="Mínimo 50 caracteres"><?php echo htmlspecialchars($descripcion); ?></textarea>
          <div class="form-hint">Mínimo 50 caracteres.</div>
        </div>

        <div class="mb-4">
          <label for="imagen" class="form-label">
            Imagen <span class="opcional">(opcional)</span>
          </label>
          <?php if ($editando && $noticia['noticia_imagen']) : ?>
            <div class="mb-2">
              <img src="../uploads/<?php echo htmlspecialchars($noticia['noticia_imagen']); ?>"
                   style="height: 100px; border-radius: 6px; object-fit: cover;">
              <div class="form-hint">Imagen actual. Subí una nueva para reemplazarla.</div>
            </div>
          <?php endif; ?>
          <input type="file" class="form-control" id="imagen" name="imagen"
                 accept=".jpg,.jpeg,.png">
          <div class="form-hint">Formatos permitidos: JPG, PNG. Tamaño máximo: 2MB.</div>
        </div>

        <?php if ($editando && $noticia['noticia_estado'] == 3) : ?>
          <div class="mb-4">
            <label class="form-label">Guardar como <span class="obligatorio">*</span></label>
            <div class="d-flex gap-3 mt-1">
              <div class="form-check">
                <input class="form-check-input" type="radio" name="estado_nuevo" value="1" id="estado-borrador" required>
                <label class="form-check-label" for="estado-borrador">Borrador</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="radio" name="estado_nuevo" value="2" id="estado-validacion">
                <label class="form-check-label" for="estado-validacion">Lista para validación</label>
              </div>
            </div>
            <div class="form-hint">La noticia está en corrección. Elegí a qué estado pasa al guardar.</div>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-3">
          <button type="submit" class="btn-verde">
            <?php echo $editando ? 'Guardar cambios' : 'Guardar como borrador'; ?>
          </button>
          <?php if ($editando) : ?>
            <a href="detalle_noticia.php?id=<?php echo $noticia['noticia_id']; ?>" class="btn-cancelar">Cancelar</a>
          <?php else : ?>
            <a href="../index.php" class="btn-cancelar">Cancelar</a>
          <?php endif; ?>
        </div>

      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>