<?php
session_start();

if (!isset($_SESSION['id'])) {
    header('Location: View/login.php');
    exit();
}

require_once 'Config/conexion.php';
require_once 'Model/Noticia.php';
require_once 'Model/Parametro.php';

$parametro       = new Parametro($conn);
$dias_expiracion = $parametro->obtenerPorClave('dias_expiracion');

$modeloNoticia = new Noticia($conn);
$modeloNoticia->verificarExpiracion($dias_expiracion);
$noticias      = $modeloNoticia->obtenerTodas();

$badges = [
    1 => 'badge-borrador',
    2 => 'badge-validacion',
    3 => 'badge-correccion',
    4 => 'badge-publicada',
    5 => 'badge-expirada',
    6 => 'badge-anulada'
];
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Noticias Institucionales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Source+Sans+3:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="View/css/index.css">
</head>
<body>

  <nav class="navbar navbar-expand-lg">
    <div class="container">
      <a class="navbar-brand" href="#">Noticias Institucionales</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navMenu">
        <ul class="navbar-nav ms-auto gap-2">
          <li class="nav-item"><a class="nav-link active" href="#">Inicio</a></li>
          <?php if (in_array(3, $_SESSION['roles'])) : ?>
            <li class="nav-item"><a class="nav-link" href="parametros.php">Parámetros</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="Controller/procesar_logout.php">Cerrar sesión</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container py-4">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <div class="page-title">Noticias</div>
        <div class="page-subtitle">Listado de noticias institucionales</div>
      </div>
      <?php if (in_array(1, $_SESSION['roles'])) : ?>
        <a href="View/crear_noticia.php" class="btn-verde">+ Nueva noticia</a>
      <?php endif; ?>
    </div>

    <div class="d-flex gap-2 flex-wrap mb-4">
      <span class="chip activo"    data-estado="todas">Todas</span>
      <span class="chip inactivo"  data-estado="1">Borrador</span>
      <span class="chip inactivo"  data-estado="2">Lista para validación</span>
      <span class="chip inactivo"  data-estado="3">Para corrección</span>
      <span class="chip inactivo"  data-estado="4">Publicadas</span>
      <span class="chip inactivo"  data-estado="5">Expiradas</span>
      <span class="chip inactivo"  data-estado="6">Anuladas</span>
    </div>

    <?php if (isset($_GET['exito']) && $_GET['exito'] === 'noticia_creada') : ?>
    <div class="alert alert-success" style="max-width: 100%; margin-bottom: 1rem;">
        Noticia creada correctamente.
    </div>
    <?php endif; ?>

    <div class="row g-4">

      <?php if (empty($noticias)) : ?>

        <div class="col-12">
          <p class="text-center" style="color: var(--texto-suave); padding: 2rem 0;">
            No hay noticias para mostrar.
          </p>
        </div>

      <?php else : ?>

        <?php foreach ($noticias as $noticia) : ?>

          <?php $clase = $badges[$noticia['noticia_estado']] ?? 'badge-borrador'; ?>

          <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 card-noticia" data-estado="<?php echo $noticia['noticia_estado']; ?>">

              <?php if ($noticia['noticia_imagen']) : ?>
                <img src="uploads/<?php echo htmlspecialchars($noticia['noticia_imagen']); ?>"
                     class="card-img-top" alt="imagen noticia">
              <?php else : ?>
                <div class="card-img-placeholder">Sin imagen</div>
              <?php endif; ?>

              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <span class="badge-estado <?php echo $clase; ?>">
                    <?php echo htmlspecialchars($noticia['estado_nombre']); ?>
                  </span>
                  <small class="text-muted">
                    <?php echo date('d/m/Y', strtotime($noticia['noticia_fechaCreado'])); ?>
                  </small>
                </div>
                <h5 class="card-title">
                  <?php echo htmlspecialchars($noticia['noticia_titulo']); ?>
                </h5>
                <p class="card-text">
                  <?php echo htmlspecialchars($noticia['noticia_descripcion']); ?>
                </p>
              </div>

              <div class="card-footer d-flex justify-content-between align-items-center">
                <span><?php echo htmlspecialchars($noticia['nombre_usuario']); ?></span>
                <a href="View/detalle_noticia.php?id=<?php echo $noticia['noticia_id']; ?>" class="btn-outline-verde">Ver más</a>
              </div>

            </div>
          </div>

        <?php endforeach; ?>

      <?php endif; ?>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // filtro noticias
  const chips = document.querySelectorAll('.chip');
  const cards = document.querySelectorAll('.card-noticia');

  chips.forEach(chip => {
    chip.addEventListener('click', () => {

      chips.forEach(c => {
        c.classList.remove('activo');
        c.classList.add('inactivo');
      });

      chip.classList.remove('inactivo');
      chip.classList.add('activo');

      const filtro = chip.dataset.estado;

      cards.forEach(card => {
        if (filtro === 'todas' || card.dataset.estado === filtro) {
          card.parentElement.style.display = 'block';
        } else {
          card.parentElement.style.display = 'none';
        }
      });

    });
  });
</script>
</body>
</html>