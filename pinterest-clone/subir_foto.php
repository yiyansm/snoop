<?php
include 'config.php';
include 'helpers.php';

if (!isLogged()) {
    header("Location: login.php");
    exit;
}

$uid = currentUserId();

if (!empty($_FILES['foto']['tmp_name'])) {

    $nombre_archivo = 'uploads/perfil_' . $uid . '_' . time() . '.jpg';
    move_uploaded_file($_FILES['foto']['tmp_name'], $nombre_archivo);

    // Guardar en BD
    $stmt = $conexion->prepare("UPDATE usuarios SET foto_perfil = ? WHERE id = ?");
    $stmt->bind_param("si", $nombre_archivo, $uid);
    $stmt->execute();
    $stmt->close();

    header("Location: profile.php?foto=1");
    exit;
}
?>