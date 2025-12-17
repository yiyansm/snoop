<?php
include 'config.php';
include 'helpers.php';

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$uid = currentUserId();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $post_id = intval($_POST['id']);

    // Verificar que el post pertenece al usuario
    $stmt = $conexion->prepare("SELECT imagen FROM posts WHERE id = ? AND usuario_id = ?");
    $stmt->bind_param("ii", $post_id, $uid);
    $stmt->execute();
    $stmt->bind_result($imagen);
    
    if ($stmt->fetch()) {
        $stmt->close();

        // Eliminar post de la BD
        $stmt = $conexion->prepare("DELETE FROM posts WHERE id = ? AND usuario_id = ?");
        $stmt->bind_param("ii", $post_id, $uid);
        $stmt->execute();
        $stmt->close();

        // Eliminar imagen si existe
        if (!empty($imagen) && file_exists($imagen)) {
            unlink($imagen);
        }

        header("Location: profile.php?eliminado=1");
        exit;

    } else {
        // Intento de borrar un post que no es suyo
        $stmt->close();
        header("Location: profile.php?error=no_permitido");
        exit;
    }
}
?>