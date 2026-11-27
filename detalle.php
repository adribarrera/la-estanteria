<?php

declare(strict_types=1);

require_once __DIR__ . "/includes/conexion.php";
iniciarSesionSegura();

// Recogemos y validamos el ID del libro que llega por la URL
$idLibro = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if($idLibro === false || $idLibro === null || $idLibro <= 0) {
    header("Location: index.php");
    exit();
}

$errores = [];
$mensajeExito = "";
$usuarioLogueado = $_SESSION["id_usuario"] ?? null;

// Estados definidos en el ENUM de la tabla libros
$estadosValidos = [
    "pendiente" => "Pendiente",
    "leyendo" => "Leyendo",
    "leido" => "Leído"
];

// ACCIONES POR POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    try {
        // ALTA DE RESEÑA
        if ($accion === "crear_resena") {
            if ($usuarioLogueado === null) {
                $errores[] = "Debes iniciar sesión para poder publicar una reseña.";
            } else {
                $puntuacion = filter_input(INPUT_POST, "puntuacion", FILTER_VALIDATE_INT);
                $comentario = trim($_POST["comentario"] ?? "");

                if ($puntuacion === false || $puntuacion === null || $puntuacion < 1 || $puntuacion > 5) {
                    $errores[] = "Selecciona una puntuación válida entre 1 y 5 estrellas";
                }

                if ($comentario === "") {
                    $errores[] = "El comentario de la reseña no puede estar vacío";
                }

                if (empty($errores)) {
                    $sqlInsert = "INSERT INTO resenas (id_libro, id_usuario, puntuacion, comentario) VALUES (:id_libro, :id_usuario, :puntuacion, :comentario)";
                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([":id_libro" => $idLibro, ":id_usuario" => (int)$usuarioLogueado, ":puntuacion" => $puntuacion, ":comentario" => $comentario]);

                    // Evitar duplicar la reseña al recargar
                    header("Location: detalle.php?id=" . $idLibro . "&msg=resena_creada#resenas-libro");
                    exit();
                }
            }
        }

        // ACTUALIZAR ESTADO DEL LIBRO
        else if ($accion === "actualizar_estado") {
            if ($usuarioLogueado === null) {
                $errores[] = "Debes iniciar sesión para cambiar el estado de lectura.";
            } else {
                $nuevoEstado = $_POST["estado"] ?? "";
                if (!array_key_exists($nuevoEstado, $estadosValidos)) {
                    $errores[] = "El estado seleccionado no es válido";
                } else {
                    $sqlUpdate = "UPDATE libros SET estado = :estado WHERE id_libro = :id_libro";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([":estado" => $nuevoEstado,":id_libro" => $idLibro]);

                    header("Location: detalle.php?id=" . $idLibro . "&msg=estado_actualizado");
                    exit();
                }
            }
        }

        // ELIMINAR RESEÑA
        else if ($accion === "eliminar_resena") {

            if ($usuarioLogueado === null) {
                    $errores[] = "Debes iniciar sesión para eliminar una reseña.";
            } else {
                $idResena = filter_input(INPUT_POST, "id_resena", FILTER_VALIDATE_INT);
                if ($idResena !== false && $idResena !== null) {
                // Filtramos por id_resena Y id_usuario para que nadie pueda borrar reseñas de otros
                $sqlDelResena = "DELETE FROM resenas WHERE id_resena = :id_resena AND id_usuario = :id_usuario";
                $stmtDelResena = $pdo->prepare($sqlDelResena);
                $stmtDelResena->execute([":id_resena" => $idResena, ":id_usuario" => (int)$usuarioLogueado
                ]);

                header("Location: detalle.php?id=" . $idLibro . "&msg=resena_eliminada#resenas-libro");
                exit();
                }
            }
        }

        // ELIMINAR LIBRO COMPLETO
        else if ($accion === "eliminar_libro") {
            if ($usuarioLogueado === null) {
                $errores[] = "Debes iniciar sesión para eliminar un libro.";
            } else {
                // Recuperamos primero el nombre de portada/audio por si hay que borrar ficheros
                $stmtFicheros = $pdo->prepare("SELECT portada, audio FROM libros WHERE id_libro = :id_libro");
                $stmtFicheros->execute([":id_libro" => $idLibro]);
                $ficherosLibro = $stmtFicheros->fetch();

                if ($ficherosLibro) {
                    $stmtDelLibro = $pdo->prepare("DELETE FROM libros WHERE id_libro = :id_libro");
                    $stmtDelLibro->execute([":id_libro" => $idLibro]);
                
                    // Borramos la portada si no es la imagen por defecto
                    if ($ficherosLibro["portada"] !== "" && $ficherosLibro["portada"] !== "default_portada.webp") {
                        $rutaImg = __DIR__ . "/assets/media/img/portadas/" . $ficherosLibro["portada"];
                        if (is_file($rutaImg)) {
                            unlink($rutaImg);
                        }
                    }

                    // Borramos también el audio, si tiene
                    if ($ficherosLibro["audio"] !== null && $ficherosLibro["audio"] !== "") {
                        $rutaAudio = __DIR__ . "/assets/media/audio/" . $ficherosLibro["audio"];
                        if (is_file($rutaAudio)) {
                            unlink($rutaAudio);
                        }
                    }
                    
                    header("Location: index.php");
                    exit();
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error en operación POST de detalle.php: " . $e->getMessage());
        $errores[] = "Ha ocurrido un error al procesar la solicitud. Inténtelo de nuevo más tarde";
    }
}

// Mensajes informativos despues de redireccionar con el get
$codigoMsg = $_GET["msg"] ?? "";
if ($codigoMsg === "resena_creada") {
    $mensajeExito = "Tu reseña se ha publicado correctamente.";
} else if ($codigoMsg === "estado_actualizado") {
    $mensajeExito = "Estado de lectura actualizado.";
} else if ($codigoMsg === "resena_eliminada") {
    $mensajeExito = "La reseña se ha eliminado";
}

// CONSULTA DE LECTURA DEL LIBRO Y SUS RESEÑAS
$libro = null;
$resenas = [];

try {
    $sqlLibro = "SELECT id_libro, titulo, autor, genero, anio_publicacion, estado, sinopsis, portada, audio FROM libros WHERE id_libro = :id_libro";
    $stmtLibro = $pdo->prepare($sqlLibro);
    $stmtLibro->execute([":id_libro" => $idLibro]);
    $libro = $stmtLibro->fetch();

    //Si el ID no existe en la BBDD, volvemos al catalogo
    if(!$libro) {
        header("Location: index.php");
        exit();
    }

    // Traemos todas las reseás del libro ordenadas de más reciente a más antigua
    $sqlResenas = "SELECT r.id_resena, r.id_usuario, r.puntuacion, r.comentario, r.fecha_creacion, u.nombre_completo FROM resenas r INNER JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE r.id_libro = :id_libro ORDER BY r.fecha_creacion DESC";
    $stmtResenas = $pdo->prepare($sqlResenas);
    $stmtResenas->execute([":id_libro" => $idLibro]);
    $resenas = $stmtResenas->fetchAll();
} catch (PDOException $e) {
    error_log("Error al cargar el detalle del libro: " . $e->getMessage());
    $errores[] = "No se ha podido cargar la ficha del libro.";
}

// Calculamos la puntuacion media y las estrellas
$totalResenas = count($resenas);
$mediaPuntuacion = 0.0;
if ($totalResenas > 0) {
    $sumaPuntos = 0;
    foreach ($resenas as $r) {
        $sumaPuntos += (int) $r["puntuacion"];
    }
    $mediaPuntuacion = $sumaPuntos / $totalResenas;
}

$estrellasMediaInt = (int) round($mediaPuntuacion);
$estrellasMediaTexto = str_repeat("★", $estrellasMediaInt) . str_repeat("☆", 5 - $estrellasMediaInt);
$mediaFormateada = number_format($mediaPuntuacion, 1, ",", ".");

// Comprobamos rutas fisicas de portada y audio
$rutaRelativaPortada = "assets/media/img/portadas/" . $libro["portada"];
$tieneImagen = ($libro["portada"] !== "" && is_file(__DIR__ . "/" . $rutaRelativaPortada));

$rutaRelativaAudio = ($libro["audio"] !== null && $libro["audio"] !== "") ? "assets/media/audio/" . $libro["audio"] : "";
$tieneAudio = ($rutaRelativaAudio !== "" && is_file(__DIR__ . "/" . $rutaRelativaAudio));

$tituloPagina = $libro["titulo"];
$paginaActiva = "catalogo";
require_once __DIR__ . "/includes/header.php";
?>

    <main>
        <section class="seccion seccion-detalle">
            <?php if ($mensajeExito !== ""): ?>
                <div class="caja-resumen alerta-exito" role="status">
                    <p><?= escapar($mensajeExito) ?></p>
                </div>
            <?php endif; ?>

            <?php if (!empty($errores)): ?>
                <div class="caja-resumen alerta-error" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <p><?= escapar($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Rejilla de Bootstrap. Portada 5/12 e información 7/12 desde md (768px). En móvil una debajo de otra -->
            <div class="row g-5 align-items-center">
                <div class="col-md-5">
                    <div class="detalle-portada">
                        <div class="portada">
                            <?php if ($tieneImagen): ?>
                                <img src="<?= escapar($rutaRelativaPortada) ?>" alt="Portada de <?= escapar($libro["titulo"]) ?>">
                            <?php else: ?>
                                <em><?= escapar($libro["titulo"]) ?></em>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-7 info-libro">
                    <p class="sobretitulo">Libro seleccionado</p>
                    <h1><?= escapar($libro["titulo"]) ?></h1>

                    <div class="valoracion">
                        <?php if ($totalResenas > 0): ?>
                            <span class="estrellas" role="img" aria-label="<?= $mediaFormateada ?> de 5 estrellas"><?= $estrellasMediaTexto ?></span>
                            <span><?= $mediaFormateada ?> · <?= $totalResenas ?> <?= $totalResenas === 1 ? "reseña" : "reseñas" ?></span>
                        <?php else: ?>
                            <span class="estrellas" role="img" aria-label="Sin valoraciones">☆☆☆☆☆</span>
                            <span>Sin reseñas todavía</span>
                        <?php endif; ?>
                    </div>

                    <div class="caja-info">
                        <dl>
                            <div>
                                <dt>Autor</dt>
                                <dd><?= escapar($libro["autor"]) ?></dd>
                            </div>
                            <div>
                                <dt>Año</dt>
                                <dd><?= (int) $libro["anio_publicacion"] ?></dd>
                            </div>
                            <div>
                                <dt>Género</dt>
                                <dd><?= escapar($libro["genero"]) ?></dd>
                            </div>
                            <div>
                                <dt>Estado</dt>
                                <dd>
                                    <span class="estado-punto"></span>
                                    <?= escapar($estadosValidos[$libro["estado"]] ?? $libro["estado"]) ?>
                                </dd>
                            </div>
                        </dl>
                    </div>

                    <?php if ($usuarioLogueado !== null): ?>
                        <!-- Utilidades flex de Bootstrap. Estado a la izquierda, editar/eliminar a la derecha. Si no cabe, baja de línea -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                            <!-- Formulario para actualizar el estado -->
                            <form action="detalle.php?id=<?= $idLibro ?>" method="post" class="form-estado-rapido">
                                <input type="hidden" name="accion" value="actualizar_estado">
                                <label class="sr-only" for="select-estado">Cambiar estado de lectura</label>
                                <select name="estado" id="select-estado">
                                    <?php foreach ($estadosValidos as $clave => $etiqueta): ?>
                                        <option value="<?= escapar($clave) ?>" <?= $libro["estado"] === $clave ? "selected" : "" ?>>
                                            <?= escapar($etiqueta) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-secundario">Guardar estado</button>
                            </form>

                            <!-- Enlace a edición completa y formulario para borrar -->
                            <div class="d-flex align-items-center gap-2">
                                <a href="formulario.php?id=<?= $idLibro ?>" class="btn btn-secundario">Editar libro</a>
                                <form action="detalle.php?id=<?= $idLibro ?>" method="post">
                                    <input type="hidden" name="accion" value="eliminar_libro">
                                    <button type="submit" class="btn btn-peligro">Eliminar libro</button>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="seccion seccion-resumen">
            <h2>Resumen</h2>
            <div class="caja-resumen">
                <p><?= nl2br(escapar($libro["sinopsis"])) ?></p>

                <?php if ($tieneAudio): ?>
                    <div class="bloque-audio-libro">
                        <p><strong>Fragmento en audiolibro:</strong></p>
                        <audio controls preload="metadata">
                            <source src="<?= escapar($rutaRelativaAudio) ?>">
                            Tu navegador no soporta la reproducción de audio HTML5.
                        </audio>
                    </div>
                <?php endif; ?>
            </div>
        </section>

        <section class="seccion seccion-resenas-detalle" id="resenas-libro">
            <div class="encabezado-seccion">
                <h2>Reseñas</h2>
                <p><?= $totalResenas ?> <?= $totalResenas === 1 ? "opinión de lectores" : "opiniones de lectores" ?></p>
            </div>

            <?php if (empty($resenas)): ?>
                <div class="caja-resumen">
                    <p>Nadie ha escrito una reseña para este libro aún. ¡Sé el primero en compartir tu opinión!</p>
                </div>
            <?php else: ?>
                <div class="resenas-apiladas">
                    <?php foreach ($resenas as $resena): ?>
                        <?php
                        $puntos = max(1, min(5, (int) $resena["puntuacion"]));
                        $estrellas = str_repeat("★", $puntos) . str_repeat("☆", 5 - $puntos);
                        $esAutorResena = ($usuarioLogueado !== null && (int) $resena["id_usuario"] === (int) $usuarioLogueado);
                        ?>
                        <article class="resena">
                            <div class="resena-cabecera">
                                <div class="avatar" aria-hidden="true"></div>
                                <div>
                                    <strong><?= escapar($resena["nombre_completo"]) ?></strong>
                                    <span class="estrellas" role="img" aria-label="<?= $puntos ?> de 5 estrellas"><?= $estrellas ?></span>
                                </div>

                                <?php if ($esAutorResena): ?>
                                    <!-- ms-auto (margin-start: auto) lo empuja al extremo derecho de la cabecera de la reseña -->
                                    <form action="detalle.php?id=<?= $idLibro ?>" method="post" class="ms-auto">
                                        <input type="hidden" name="accion" value="eliminar_resena">
                                        <input type="hidden" name="id_resena" value="<?= (int) $resena["id_resena"] ?>">
                                        <button type="submit" class="btn-texto-peligro" aria-label="Eliminar mi reseña">Eliminar</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                            <p><?= nl2br(escapar($resena["comentario"])) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="tarjeta-form tarjeta-form-resena">
                <div class="cabecera-form">
                    <p class="sobretitulo">Participa en el club</p>
                    <h3>Añadir una reseña</h3>
                </div>

                <?php if ($usuarioLogueado === null): ?>
                    <div class="caja-resumen">
                        <p>Para publicar tu valoración necesitas entrar con tu cuenta del club.</p>
                        <a href="login.php" class="btn btn-primario mt-3">Iniciar sesión o registrarse</a>
                    </div>
                <?php else: ?>
                    <form id="form-resena" action="detalle.php?id=<?= $idLibro ?>" method="post">
                        <input type="hidden" name="accion" value="crear_resena">
                        <div class="form-rejilla">
                            <label class="campo campo-ancho">
                                <span>Puntuación <b>*</b></span>
                                <select name="puntuacion" required>
                                    <option value="" selected disabled>Selecciona una nota</option>
                                    <option value="5">★★★★★ (5 - Excelente)</option>
                                    <option value="4">★★★★☆ (4 - Muy bueno)</option>
                                    <option value="3">★★★☆☆ (3 - Entretenido)</option>
                                    <option value="2">★★☆☆☆ (2 - Regular)</option>
                                    <option value="1">★☆☆☆☆ (1 - No me gustó)</option>
                                </select>
                            </label>
                            <label class="campo campo-ancho">
                                <span>Comentario <b>*</b></span>
                                <textarea name="comentario" rows="4" required placeholder="Comparte tu opinión sobre el libro..."></textarea>
                            </label>
                        </div>

                        <div class="form-acciones">
                            <button type="submit" class="btn btn-primario">Publicar reseña</button>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <?php require_once __DIR__ . "/includes/footer.php"; ?>