<?php
include 'config.php';
include 'helpers.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Todos los campos son obligatorios.';
    } else {
        $stmt = $conexion->prepare('SELECT id, password FROM usuarios WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($id, $hash);
        if ($stmt->fetch()) {
            if (password_verify($password, $hash)) {
                $_SESSION['user_id'] = $id;
                header('Location: index.php');
                exit;
            } else {
                $errors[] = 'Credenciales incorrectas.';
            }
        } else {
            $errors[] = 'Credenciales incorrectas.';
        }
        $stmt->close();
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar</title><link rel="stylesheet" href="css/style.css"></head>
<body class="auth">
<div class="auth-box">
  <h2>Entrar</h2>
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post">
    <input name="email" type="email" placeholder="Correo" required value="<?= esc($_POST['email'] ?? '') ?>">
    <input name="password" type="password" placeholder="Contraseña" required>
    <button>Entrar</button>
  </form>
  <p>¿No tienes cuenta? <a href="register.php">Registrarse</a></p>
</div>
</body></html>
