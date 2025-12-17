<?php
include 'config.php';
include 'helpers.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($nombre === '' || $email === '' || $password === '') {
        $errors[] = 'Todos los campos son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Email inválido.';
    } else {
        // Verificar si email ya existe
        $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'El correo ya está registrado.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conexion->prepare('INSERT INTO usuarios (nombre,email,password) VALUES (?, ?, ?)');
        $stmt->bind_param('sss', $nombre, $email, $hash);
        $stmt->execute();
        $stmt->close();
        header('Location: login.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registro</title><link rel="stylesheet" href="css/style.css"></head>
<body class="auth">
<div class="auth-box">
  <h2>Crear cuenta</h2>
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post">
    <input name="nombre" placeholder="Nombre" required value="<?= esc($_POST['nombre'] ?? '') ?>">
    <input name="email" type="email" placeholder="Correo" required value="<?= esc($_POST['email'] ?? '') ?>">
    <input name="password" type="password" placeholder="Contraseña" required>
    <button>Registrarse</button>
  </form>
  <p>¿Ya tienes cuenta? <a href="login.php">Entrar</a></p>
</div>
</body></html>
