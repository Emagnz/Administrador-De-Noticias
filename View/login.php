<?php
session_start();

if (isset($_SESSION['id'])) {
    header('Location: ../index.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión — Noticias Institucionales</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Lora:wght@400;500;600&family=Source+Sans+3:wght@300;400;500&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/login.css">
</head>
<body>


  <nav class="navbar">
    <div class="container">
      <span class="navbar-brand">Administrador de noticias</span>
    </div>
  </nav>
  <div class="bienvenida text-center">
    <p>Bienvenido al sistema de gestión de noticias institucionales. Ingresá tus datos para continuar.</p>
  </div>


  <div class="login-wrapper">
    <div class="login-card">

      <!-- Formulario: iniciar sesión -->
      <div class="formulario activo" id="form-login">
        <div class="form-titulo">Bienvenido</div>
        <div class="form-subtitulo">Ingresá tus datos para continuar</div>

        <form action="../Controller/procesar_login.php" method="POST">
          <div class="mb-3">
            <label for="login-usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="login-usuario" name="usuario" placeholder="Tu nombre de usuario" required>
          </div>

          <div class="mb-4">
            <label for="login-password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="login-password" name="password" placeholder="••••••••" required>
          </div>
    <?php
    $error = $_GET['error'] ?? '';
    $exito = $_GET['exito'] ?? '';
    ?>

    <?php if ($error === 'campos_vacios'): ?>
      <div class="alert alert-danger text-center ">Completá todos los campos.</div>
    <?php elseif ($error === 'passwords_no_coinciden'): ?>
      <div class="alert alert-danger text-center">Las contraseñas no coinciden.</div>
    <?php elseif ($error === 'sin_rol'): ?>
      <div class="alert alert-danger text-center">Seleccioná al menos un rol.</div>
    <?php elseif ($error === 'usuario_existente'): ?>
      <div class="alert alert-danger text-center">Ese nombre de usuario ya existe.</div>
    <?php elseif ($error === 'credenciales_invalidas'): ?>
      <div class="alert alert-danger text-center ">Credenciales inválidas.</div>
    <?php endif; ?>

    <?php if ($exito === 'registro_ok'): ?>
      <div class="alert alert-success text-center">Cuenta creada correctamente. Podés iniciar sesión.</div>
    <?php endif; ?>
          <button type="submit" class="btn-naranja">Ingresar</button>
        </form>

        <hr class="divider">

        <button class="btn-cambiar" onclick="cambiar('form-registro')">
          ¿No tenés cuenta? Registrate
        </button>
      </div>

      <!-- Formulario: registrarse -->
      <div class="formulario" id="form-registro">
        <div class="form-titulo">Crear cuenta</div>
        <div class="form-subtitulo">Completá los datos para registrarte</div>

        <form action="../Controller/procesar_registro.php" method="POST">
          <div class="mb-3">
            <label for="reg-usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" id="reg-usuario" name="usuario" placeholder="Elegí un nombre de usuario" required>
          </div>

          <div class="mb-3">
            <label for="reg-password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" id="reg-password" name="password" placeholder="••••••••" required>
          </div>

          <div class="mb-4">
            <label for="reg-password2" class="form-label">Repetir contraseña</label>
            <input type="password" class="form-control" id="reg-password2" name="password_confirmacion" placeholder="••••••••" required>
          </div>

          <div class="mb-4">
            <label class="form-label">Rol</label>
            <div class="d-flex gap-3 mt-1">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rol-editor" name="rol[]" value="1">
                <label class="form-check-label" for="rol-editor">Editor</label>
              </div>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="rol-validador" name="rol[]" value="2">
                <label class="form-check-label" for="rol-validador">Validador</label>
              </div>
            </div>
          </div>
          
          <button type="submit" class="btn-naranja">Registrarse</button>
          
        </form>

        <hr class="divider">

        <button class="btn-cambiar" onclick="cambiar('form-login')">
          ¿Ya tenés cuenta? Iniciá sesión
        </button>
      </div>

    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    function seleccionarRol(valor, idOpcion) {
    document.querySelectorAll('.rol-opcion').forEach(o => o.classList.remove('seleccionado'));
    document.getElementById(idOpcion).classList.add('seleccionado');
    document.querySelector('input[name="rol"][value="' + valor + '"]').checked = true;
  }
    function cambiar(id) {
      document.querySelectorAll('.formulario').forEach(f => f.classList.remove('activo'));
      document.getElementById(id).classList.add('activo');
    }
  </script>
</body>
</html>