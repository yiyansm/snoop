<?php
// helpers.php - funciones útiles
function esc($v) {
    return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}

function isLogged() {
    return isset($_SESSION['user_id']);
}

function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}
?>