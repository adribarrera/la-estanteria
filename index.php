<?php
// Obligamos a que los tipos de datos coincidan exactamente sin conversiones
declare(strict_types=1);

require_once __DIR__ . "/includes/conexion.php";

iniciarSesionSegura();

// Recogemos el término de búsqueda si el usuario escribe algo en el buscador del header (?q=...)
$busqueda = trim($_GET["q"] ?? "");
$librosPorGenero = [];
$resenasDestacadas = [];
$errorBBDD = "";

// LIBROS DEL CATÁLOGO CON O SIN FILTRO
try {
    if ($busqueda !== "") {
        $sqlLibros = "SELECT id_libro, titulo, autor, genero, portada FROM libros WHERE titulo LIKE :busqueda_titulo OR autor LIKE :busqueda_autor ORDER BY genero ASC, titulo ASC";
        $stmtLibros = $pdo->prepare($sqlLibros);
        $patron = "%" . $busqueda . "%";
        $stmtLibros->execute([":busqueda_titulo" => $patron, ":busqueda_autor" => $patron]);
    } else {
        // Sin búsqueda. Traemos todo el catálogo ordenado por género y fecha de alta más reciente
        $sqlLibros = "SELECT id_libro, titulo, autor, genero, portada FROM libros ORDER BY genero ASC, fecha_registro DESC";
        $stmtLibros = $pdo->prepare($sqlLibros);
        $stmtLibros->execute();
    }

    // fetchAll() devuelve todas las filas en un array asi que las agrupamos por su género
    $todosLosLibros = $stmtLibros->fetchAll();
    foreach ($todosLosLibros as $libro) {
        $genero = $libro["genero"];
        $librosPorGenero[$genero][] = $libro;
    }

    // OBTENER LAS 3 RESEÑAS MÁS RECIENTES
    // Unimos reseñas con usuarios y libros con INNER JOIN para sacar el nombre del autor y el título del libro
    $sqlResenas = "SELECT r.puntuacion, r.comentario, u.nombre_completo, l.id_libro, l.titulo AS titulo_libro FROM resenas r INNER JOIN usuarios u ON r.id_usuario = u.id_usuario INNER JOIN libros l ON r.id_libro = l.id_libro ORDER BY r.fecha_creacion DESC LIMIT 3";
    $stmtResenas = $pdo->prepare($sqlResenas);
    $stmtResenas->execute();
    $resenasDestacadas = $stmtResenas->fetchAll();

} catch (PDOException $e) {
    error_log("Error al cargar el catálogo en index.php: " . $e->getMessage());
    $errorBBDD = "No se ha podido cargar el catálogo en este momento. Inténtalo de nuevo más tarde.";
}

$tituloPagina = "Catálogo";
$paginaActiva = "inicio";
require_once __DIR__ . "/includes/header.php";
?>
    <main>
        <section class="seccion seccion-catalogo" id="catalogo">
            <div class="encabezado-seccion">
                <h1>Catálogo de libros</h1>
                <?php if ($busqueda !== ""): ?>
                    <p>
                        Resultados para <strong>"<?= escapar($busqueda) ?>"</strong> ·
                        <a href="index.php" class="enlace-limpiar-busqueda">Ver todo el catálogo</a>
                    </p>
                <?php else: ?>
                    <p>Explora las lecturas seleccionadas por nuestra comunidad.</p>
                <?php endif; ?>
            </div>

            <?php if ($errorBBDD !== ""): ?>
                <div class="caja-resumen alerta-error" role="alert">
                    <p><?= escapar($errorBBDD) ?></p>
                </div>
            <?php elseif (empty($librosPorGenero)): ?>
                <div class="caja-resumen estado-vacio">
                    <p>No hemos encontrado ningún libro que coincida con tu búsqueda.</p>
                    <a href="formulario.php" class="btn btn-primario">Añadir un libro nuevo</a>
                </div>
            <?php else: ?>
                <div class="lista-generos">
                    <!-- Recorremos cada género (clave) y su lista de libros (valor) -->
                    <?php foreach ($librosPorGenero as $genero => $libros): ?>
                        <section class="genero">
                            <div class="genero-titulo">
                                <h2><?= escapar($genero) ?></h2>
                                <span class="genero-linea"></span>
                            </div>
                            <div class="rejilla-libros">
                                <?php foreach ($libros as $libro): ?>
                                    <?php
                                    // Comprobamos si existe la imagen física en assets/media/portadas/
                                    $rutaRelativaPortada = "assets/media/img/portadas/" . $libro["portada"];
                                    $tieneImagen = ($libro["portada"] !== "" && is_file(__DIR__ . "/" . $rutaRelativaPortada));
                                    ?>
                                    <a href="detalle.php?id=<?= (int) $libro["id_libro"] ?>" class="libro" aria-label="Ver detalle de <?= escapar($libro["titulo"]) ?>">
                                        <div class="portada">
                                            <?php if ($tieneImagen): ?>
                                                <img src="<?= escapar($rutaRelativaPortada) ?>" alt="Portada de <?= escapar($libro["titulo"]) ?>">
                                            <?php else: ?>
                                                <em>PORTADA</em>
                                            <?php endif; ?>
                                        </div>
                                        <span><?= escapar($libro["titulo"]) ?></span>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        </section>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="seccion seccion-resenas-dest" id="resenas-destacadas">
            <div class="encabezado-seccion">
                <h2>Reseñas destacadas</h2>
                <p>Lo que está leyendo el club esta semana.</p>
            </div>

            <?php if (empty($resenasDestacadas)): ?>
                <div class="caja-resumen estado-vacio">
                    <p>Aún no hay reseñas publicadas. Entra en cualquier libro del catálogo y deja la primera.</p>
                </div>
            <?php else: ?>
                <div class="rejilla-resenas">
                    <?php foreach ($resenasDestacadas as $resena): ?>
                        <?php
                        // Nos aseguramos que la puntuación esté entre 1 y 5 y generamos las estrellas que se ven
                        $puntos = max(1, min(5, (int) $resena["puntuacion"]));
                        $estrellas = str_repeat("★", $puntos) . str_repeat("☆", 5 - $puntos);
                        ?>
                        <article class="resena">
                            <div class="resena-cabecera">
                                <div class="avatar" aria-hidden="true"></div>
                                <div>
                                    <strong><?= escapar($resena["nombre_completo"]) ?></strong>
                                    <span class="estrellas" role="img" aria-label="<?= $puntos ?> de 5 estrellas"><?= $estrellas ?></span>
                                </div>
                            </div>
                            <p><?= escapar($resena["comentario"]) ?></p>
                            <p class="resena-libro-ref">
                                En <a href="detalle.php?id=<?= (int) $resena["id_libro"] ?>"><?= escapar($resena["titulo_libro"]) ?></a>
                            </p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </main>

<?php require_once __DIR__ . "/includes/footer.php"; ?>