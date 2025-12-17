<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include 'config.php';
include 'helpers.php';

// Paginación simple (opcional)
$limit = 20;
$offset = 0;
if (isset($_GET['page']) && is_numeric($_GET['page'])) {
    $page = (int)$_GET['page'];
    if ($page > 1) $offset = ($page - 1) * $limit;
}

// Traer posts con conteo de likes y si el usuario actual ha hecho like
$sql = "
SELECT p.*, u.nombre,
  (SELECT COUNT(*) FROM likes l WHERE l.post_id = p.id) AS likes_count,
  (SELECT COUNT(*) FROM likes l2 WHERE l2.post_id = p.id AND l2.usuario_id = ?) AS liked_by_me
FROM posts p
JOIN usuarios u ON u.id = p.usuario_id
ORDER BY p.fecha DESC
LIMIT ? OFFSET ?
";

$stmt = $conexion->prepare($sql);
$uid = currentUserId() ?? 0;
$stmt->bind_param('iii', $uid, $limit, $offset);
$stmt->execute();
$result = $stmt->get_result();
$posts = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>feed</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="topbar">
  <div class="wrap">
    <h1><a href="/">S N O O P</a></h1>
    <nav>
    <?php if (isLogged()): ?>
      <a href="upload.php">Subir</a>
      <a href="profile.php">Mi perfil</a>
      <a href="logout.php">Salir</a>
    <?php else: ?>
      <a href="login.php">Entrar</a>
      <a href="register.php">Registrarse</a>
    <?php endif; ?>
    </nav>
  </div>
</header>

<main class="wrap">
  <section class="grid" id="grid">
    <?php foreach ($posts as $p): ?>
      <article class="card" data-post-id="<?= $p['id'] ?>">
        <div class="card-image">
          <img src="<?= esc($p['imagen']) ?>" alt="<?= esc($p['titulo']) ?>">
        </div>
        <div class="card-body">
          <h3><?= esc($p['titulo']) ?></h3>
          <p class="meta">Subido por <?= esc($p['nombre']) ?> • <?= esc($p['fecha']) ?></p>
          <div class="actions">
            <button class="like-btn" data-liked="<?= $p['liked_by_me'] ? '1' : '0' ?>">❤</button>
            <span class="likes-count"><?= $p['likes_count'] ?></span>
          </div>
        </div>
      </article>
    <?php endforeach; ?>
  </section>
</main>

<script src="js/app.js"></script>
</body>
</html>
