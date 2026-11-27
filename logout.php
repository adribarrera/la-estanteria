<?php
declare(strict_types=1);

require_once __DIR__ . "/includes/conexion.php";
iniciarSesionSegura();

// Vaciamos todas las variables guardadas en la sesión actual
$_SESSION = [];

// Si PHP usa cookies para guardar el ID de sesión también la borramos del navegador
if (ini_get("session.use_cookies")) {

    // Recuperamos la configuración exacta (ruta, dominio, etc.) con la que se creó la cookie
    $params = session_get_cookie_params();      
    setcookie(session_name(), "", time () - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}

// Destruimos el archivo de sesión del servidor y redirigimos al inicio
session_destroy();

header("Location: index.php");
exit();