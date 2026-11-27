<?php

require_once __DIR__ . "/../config/db.php";

try {
    $opciones = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // PDO lanza excepciones en fallos de SQL
        PDO::ATTR_EMULATE_PREPARES => false,                // Obliga a usar sentencias preparadas nativas
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC    // Devuelve arrays asociativos en los fetch
    ];

    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, $opciones);
} catch (PDOException $e) {
    error_log("Error en la conexión PDO: " . $e->getMessage());
    die("No se ha podido establecer la conexión con la base de datos");
}

/**
 * Inicia la sesión PHP de forma segura si no estaba activa.
 */
function iniciarSesionSegura(): void
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Escapa cadenas de texto para salida segura en HTML (prevención XSS).
 */
function escapar(?string $texto): string
{
    return htmlspecialchars((string) $texto, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}