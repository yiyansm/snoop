<?php
// like.php - endpoint AJAX para dar/remover like
include 'config.php';
include 'helpers.php';
header('Content-Type: application/json');

if (!isLogged()) {
    echo json_encode(['ok' => false, 'error' => 'No autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$post_id = isset($input['post_id']) ? (int)$input['post_id'] : 0;
$action = $input['action'] ?? '';

if ($post_id <= 0) {
    echo json_encode(['ok' => false, 'error' => 'post_id inválido']);
    exit;
}

$uid = currentUserId();

if ($action === 'like') {
    // Insertar si no existe (uso de UNIQUE en DB evita duplicados)
    $stmt = $conexion->prepare('INSERT IGNORE INTO likes (usuario_id, post_id) VALUES (?, ?)');
    $stmt->bind_param('ii', $uid, $post_id);
    $stmt->execute();
    $stmt->close();
} elseif ($action === 'unlike') {
    $stmt = $conexion->prepare('DELETE FROM likes WHERE usuario_id = ? AND post_id = ?');
    $stmt->bind_param('ii', $uid, $post_id);
    $stmt->execute();
    $stmt->close();
} else {
    echo json_encode(['ok' => false, 'error' => 'acción inválida']);
    exit;
}

// Devolver nuevo conteo
$stmt = $conexion->prepare('SELECT COUNT(*) AS c FROM likes WHERE post_id = ?');
$stmt->bind_param('i', $post_id);
$stmt->execute();
$stmt->bind_result($count);
$stmt->fetch();
$stmt->close();

echo json_encode(['ok' => true, 'likes' => $count]);
exit;
?>