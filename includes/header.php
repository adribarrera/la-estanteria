<?php
// Nos aseguramos que la sesión esta iniciada antes de enviar HTML
if (function_exists("iniciarSesionSegura")) {
    iniciarSesionSegura();
} elseif (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Si la página tiene un título propio lo usamos y si no ponemos el genérico
$tituloFinal = isset($tituloPagina) ? escapar($tituloPagina) . " · La Estantería" : "La Estantería · Club de lectura";

// Valores por defecto para evitar avisos si la página no los define
$paginaActiva = $paginaActiva ?? "";
$busquedaActual = $_GET["q"] ?? "";
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $tituloFinal ?></title>
    <link rel="icon" type="image/svg+xml" href="assets/media/logo/favicon.svg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="assets/css/styles.css">
</head>

<body>
    <div class="cabecera-fija">
        <header class="cabecera">
            <a href="index.php" class="marca">
                <span class="marca-icono" aria-hidden="true"></span>
                <span>La Estantería</span>
            </a>

            <div class="cabecera-acciones">
                <!-- Al enviar manda ?q=... a index.php manteniendo lo que ha escrito el usuario -->
                <form action="index.php" method="get" class="buscador">
                    <label for="buscador-catalogo" class="sr-only">Buscar</label>
                    <input type="search" id="buscador-catalogo" name="q" value="<?= escapar($busquedaActual) ?>" placeholder="Buscar...">
                    <span aria-hidden="true">⌕</span>
                </form>

                <button type="button" class="btn-tema" aria-pressed="false">
                        <span class="punto" aria-hidden="true"></span>
                        <span class="texto">Tema oscuro</span>
                </button>

                <!-- Si hay sesión iniciada mostramos su usuario y Salir. Si no, el botón de Login -->
                <?php if (isset($_SESSION["id_usuario"])): ?>
                    <span class="btn-tema" title="<?= escapar($_SESSION["nombre_completo"] ?? "") ?>">
                        @<?= escapar($_SESSION["nombre_usuario"]) ?>
                    </span>

                    <a href="logout.php" class="btn-login" aria-label="Cerrar sesión">
                        <span>Salir</span>
                    </a>
                <?php else: ?>
                    <a href="login.php" class="btn-login" aria-label="Iniciar Sesión o registrarse">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                        <span>Login</span>
                    </a>
                <?php endif; ?>
            </div>
        </header>

        <!-- Marcamos con la clase activo la pestaña que corresponde a la página actual -->
        <nav class="navbar" aria-label="Navegación principal">
            <a href="index.php" <?= $paginaActiva === 'inicio' ? 'class="activo" aria-current="page"' : '' ?>>Inicio</a>
            <a href="index.php#catalogo" <?= $paginaActiva === 'catalogo' ? 'class="activo" aria-current="page"' : '' ?>>Catálogo</a>
            <a href="index.php#resenas-destacadas">Reseñas recientes</a>
            <a href="formulario.php" <?= $paginaActiva === 'formulario' ? 'class="activo" aria-current="page"' : '' ?>>Añadir libro</a>
        </nav>
    </div>