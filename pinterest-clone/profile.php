<?php
include 'config.php';
include 'helpers.php';
$mostrar_alerta = false;

if (isset($_GET['eliminado']) && $_GET['eliminado'] == 1) {
    $mostrar_alerta = "Post eliminado correctamente ✔";
}

if (isset($_GET['error']) && $_GET['error'] == "no_permitido") {
    $mostrar_alerta = "No puedes eliminar este post ❗";
}
if (!isLogged()) {
    header('Location: login.php');
    exit;
}

$uid = currentUserId();
// Traer posts del usuario
$stmt = $conexion->prepare('SELECT * FROM posts WHERE usuario_id = ? ORDER BY fecha DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$my_posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Traer likes del usuario
$stmt = $conexion->prepare('SELECT p.* FROM posts p JOIN likes l ON l.post_id = p.id WHERE l.usuario_id = ? ORDER BY l.fecha DESC');
$stmt->bind_param('i', $uid);
$stmt->execute();
$res = $stmt->get_result();
$liked_posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Usuario
$stmt = $conexion->prepare('SELECT nombre,email,fecha_registro, foto_perfil FROM usuarios WHERE id = ?');
$stmt->bind_param('i', $uid);
$stmt->execute();
$stmt->bind_result($nombre, $email, $fecha_registro, $foto_perfil);
$stmt->fetch();
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Mi perfil</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<div class="wrap">

<?php if ($mostrar_alerta): ?>
<div class="alerta" id="alerta">
    <span class="cerrar" onclick="document.getElementById('alerta').style.display='none'">×</span>
    <?= esc($mostrar_alerta) ?>
</div>
<?php endif; ?>

 <div class="perfil-contenedor">

    <h2>Mi perfil</h2>

    <div class="foto-perfil">
        <img src="<?= esc($foto_perfil) ?>" alt="foto de perfil">
    </div>

    <!-- Botón visible -->
    <button class="btn-mostrar-input" onclick="mostrarInput()">Cambiar foto de perfil</button>

    <!-- Formulario oculto -->
    <form id="form-foto" action="subir_foto.php" method="post" enctype="multipart/form-data" style="display:none;">
        <input type="file" name="foto" accept="image/*" required>
        <button type="submit" class="btn-subir">Subir nueva foto</button>
    </form>

    <p><strong>Nombre:</strong> <?= esc($nombre) ?></p>
    <p><strong>Email:</strong> <?= esc($email) ?></p>

</div>
  <p><strong>Mi posts</strong></p>
  <section class="grid">
    <?php foreach ($my_posts as $p): ?>
		<article class="card">
			<img src="<?= esc($p['imagen']) ?>" alt="">
			<div class="title-row">
				<h4><?= esc($p['titulo']) ?></h4>
				 <form action="eliminar_post.php" method="post" onsubmit="return confirm('¿Seguro que quieres eliminar este post?');">
				<input type="hidden" name="id" value="<?= esc($p['id']) ?>">
				<button type="submit" class="delete-icon">🗑️</button>
				</form>
			</div>
		</article>
    <?php endforeach; ?>
  </section>
  <h3>Posts que me gustan</h3>
  <section class="grid">
    <?php foreach ($liked_posts as $p): ?>
      <article class="card">
        <img src="<?= esc($p['imagen']) ?>" alt="">
        <h4><?= esc($p['titulo']) ?></h4>
      </article>
    <?php endforeach; ?>
  </section>
  <p><a href="index.php">Volver</a></p>
</div>
<script>
function mostrarInput() {
    document.getElementById("form-foto").style.display = "block";
}
</script>
</body>
</html>
