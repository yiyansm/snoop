<?php
include 'config.php';
include 'helpers.php';

if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo'] ?? '');

    if (!isset($_FILES['imagen']) || $_FILES['imagen']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error subiendo la imagen.';
    } else {
        $f = $_FILES['imagen'];
        // Validar tipo simple
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        if (!in_array($f['type'], $allowed)) {
            $errors[] = 'Formato de imagen no permitido.';
        } else {
            $ext = pathinfo($f['name'], PATHINFO_EXTENSION);
            $filename = uniqid('img_') . '.' . $ext;
            $dest = 'uploads/' . $filename;
            if (!move_uploaded_file($f['tmp_name'], $dest)) {
                $errors[] = 'No se pudo guardar la imagen.';
            } else {
                $stmt = $conexion->prepare('INSERT INTO posts (usuario_id, titulo, imagen) VALUES (?, ?, ?)');
                $uid = currentUserId();
                $stmt->bind_param('iss', $uid, $titulo, $dest);
                $stmt->execute();
                $stmt->close();
                header('Location: index.php');
                exit;
            }
        }
    }
}
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Subir imagen</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="wrap">
  <h2>Subir imagen</h2>
  <?php if (!empty($errors)): ?>
    <div class="errors">
      <?php foreach ($errors as $e): ?><div><?= esc($e) ?></div><?php endforeach; ?>
    </div>
  <?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input name="titulo" placeholder="Título" value="<?= esc($_POST['titulo'] ?? '') ?>">
    <input name="imagen" type="file" accept="image/*" required>
    <button>Subir</button>
  </form>
  <p><a href="index.php">Volver</a></p>
</div>
</body></html>
