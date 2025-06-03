<?php
session_start();

// Limpiar todas las variables de sesión
$_SESSION = [];

// Destruir la cookie de sesión si está habilitada
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Incluir vista
include("../frontend/logout.php");
?>
