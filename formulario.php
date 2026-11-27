<?php

declare(strict_types=1);

require_once __DIR__ . "/includes/conexion.php";


iniciarSesionSegura();

$usuarioLogueado = $_SESSION["id_usuario"] ?? null;
if ($usuarioLogueado === null) {
    header("Location: login.php");
    exit();
}

$errores = [];

$generosValidos = [
    "Ciencia ficción",
    "Fantasía",
    "Clásico",
    "Terror",
    "Misterio",
    "Romance",
    "Aventura",
    "Poesía"
];

$estadosValidos = [
    "pendiente" => "Pendiente",
    "leyendo" => "Leyendo",
    "leido" => "Leído"
];

// Comprobamos si venimos en modo edición
$idLibro = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
$esEdicion = ($idLibro !== false && $idLibro !== null && $idLibro > 0);

// Valores por defecto del formulario
$datos = [
    "titulo" => "",
    "autor" => "",
    "genero" => "",
    "anio_publicacion" => "",
    "sinopsis" => "",
    "estado" => "",
    "portada" => "",
    "audio" => ""
];

// Si es edición, cargamos el libro actual de la base de datos
if ($esEdicion) {
    try {
        $stmtLibro = $pdo->prepare("SELECT id_libro, titulo, autor, genero, anio_publicacion, estado, sinopsis, portada, audio FROM libros WHERE id_libro = :id_libro");
        $stmtLibro->execute([":id_libro" => $idLibro]);
        $libroActual = $stmtLibro->fetch();

        if (!$libroActual) {
            header("Location: index.php");
            exit();
        }

        $datos = [
            "titulo" => (string)$libroActual["titulo"],
            "autor" => (string)$libroActual["autor"],
            "genero" => (string)$libroActual["genero"],
            "anio_publicacion" => (string)$libroActual["anio_publicacion"],
            "sinopsis" => (string)$libroActual["sinopsis"],
            "estado" => (string)$libroActual["estado"],
            "portada" => (string)($libroActual["portada"] ?? ""),
            "audio" => (string)($libroActual["audio"] ?? "")
        ];

        // Si en la BBDD hay algún genero fuera de la lista
        if ($datos["genero"] !== "" && !in_array($datos["genero"], $generosValidos, true)) {
            $generosValidos[] = $datos["genero"];
        }
    } catch (PDOException $e) {
        error_log("Error al cargar libro para edición: " . $e->getMessage());
        $errores[] = "No se han podido cargar los datos del libro.";
    }
}

// PROCESAMIENTO DEL FORMULARIO
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $datos["titulo"] = trim($_POST["titulo"] ?? "");
    $datos["autor"] = trim($_POST["autor"] ?? "");
    $datos["genero"] = trim($_POST["genero"] ?? "");
    $datos["anio_publicacion"] = trim($_POST["anio_publicacion"] ?? "");
    $datos["sinopsis"] = trim($_POST["sinopsis"] ?? "");
    $datos["estado"] = trim($_POST["estado"] ?? "");

    $anioActual = (int) date("Y");
    $anioInt = filter_var($datos["anio_publicacion"], FILTER_VALIDATE_INT);

    // Validación de campos de texto y selección
    if (mb_strlen($datos["titulo"]) < 2 || mb_strlen($datos["titulo"]) > 100) {
        $errores[] = "El título debe tener entre 2 y 100 caracteres.";
    }

    if (!preg_match("/^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'-]{1,59}$/u", $datos["autor"])) {
        $errores[] = "El autor debe tener entre 2 y 60 caracteres válidos.";
    }

    if ($datos["genero"] === "" || !in_array($datos["genero"], $generosValidos, true)) {
        $errores[] = "Selecciona un género válido.";
    }

    if ($anioInt === false || $anioInt < 1000 || $anioInt > $anioActual) {
        $errores[] = "El año debe ser un número entre 1000 y {$anioActual}.";
    }

    if (mb_strlen($datos["sinopsis"]) < 10 || mb_strlen($datos["sinopsis"]) > 500) {
        $errores[] = "La sinopsis debe tener entre 10 y 500 caracteres.";
    }

    if (!array_key_exists($datos["estado"], $estadosValidos)) {
        $errores[] = "Selecciona un estado de lectura válido.";
    }

    // 2. Validación de imagen de portada
    $nuevaPortadaNombre = null;
    $hayArchivoPortada = isset($_FILES["portada"]) && $_FILES["portada"]["error"] !== UPLOAD_ERR_NO_FILE;

    if (!$esEdicion && !$hayArchivoPortada) {
        $errores[] = "Debes subir una imagen de portada.";
    } elseif ($hayArchivoPortada) {
        $archivoPortada = $_FILES["portada"];
        if ($archivoPortada["error"] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir la imagen de portada.";
        } elseif ($archivoPortada["size"] > 5 * 1024 * 1024) {
            $errores[] = "La portada no puede superar los 5 MB.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimePortada = $finfo->file($archivoPortada["tmp_name"]);
            $mimesPermitidosImg = [
                "image/webp" => "webp",
                "image/jpeg" => "jpg",
                "image/png" => "png"
            ];

            if (!array_key_exists($mimePortada, $mimesPermitidosImg)) {
                $errores[] = "Formato de portada no válido. Solo se admite WEBP, JPG o PNG.";
            } else {
                $extImg = $mimesPermitidosImg[$mimePortada];
                $nuevaPortadaNombre = uniqid("portada_", true) . "." . $extImg;
            }
        }
    }

    // Validación de audio
    $nuevoAudioNombre = null;
    $hayArchivoAudio = isset($_FILES["audio"]) && $_FILES["audio"]["error"] !== UPLOAD_ERR_NO_FILE;

    if ($hayArchivoAudio) {
        $archivoAudio = $_FILES["audio"];
        if ($archivoAudio["error"] !== UPLOAD_ERR_OK) {
            $errores[] = "Error al subir el archivo de audio.";
        } elseif ($archivoAudio["size"] > 15 * 1024 * 1024) {
            $errores[] = "El archivo de audio no puede superar los 15 MB.";
        } else {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeAudio = $finfo->file($archivoAudio["tmp_name"]);
            $mimesPermitidosAudio = [
                "audio/mpeg" => "mp3",
                "audio/mp3" => "mp3",
                "audio/ogg" => "ogg",
                "audio/wav" => "wav",
                "audio/x-wav" => "wav"
            ];

            if (!array_key_exists($mimeAudio, $mimesPermitidosAudio)) {
                $errores[] = "Formato de audio no válido. Solo se admite MP3, OGG o WAV.";
            } else {
                $extAudio = $mimesPermitidosAudio[$mimeAudio];
                $nuevoAudioNombre = uniqid("audio_", true) . "." . $extAudio;
            }
        }
    }

    // Guardado en disco y en Base de Datos
    if (empty($errores)) {
        $dirPortadas = __DIR__ . "/assets/media/img/portadas/";
        $dirAudio = __DIR__ . "/assets/media/audio/";

        if (!is_dir($dirPortadas)) {
            mkdir($dirPortadas, 0755, true);
        }
        if ($nuevoAudioNombre !== null && !is_dir($dirAudio)) {
            mkdir($dirAudio, 0755, true);
        }

        // Movemos los ficheros nuevos antes de hacer la consulta
        if ($nuevaPortadaNombre !== null) {
            if (!move_uploaded_file($_FILES["portada"]["tmp_name"], $dirPortadas . $nuevaPortadaNombre)) {
                $errores[] = "No se pudo guardar la imagen de portada en el servidor.";
            }
        }

        if (empty($errores) && $nuevoAudioNombre !== null) {
            if (!move_uploaded_file($_FILES["audio"]["tmp_name"], $dirAudio . $nuevoAudioNombre)) {
                $errores[] = "No se pudo guardar el archivo de audio en el servidor.";
            }
        }

        if (empty($errores)) {
            try {
                if ($esEdicion) {
                    $portadaFinal = $nuevaPortadaNombre ?? $datos["portada"];
                    $audioFinal = $nuevoAudioNombre ?? ($datos["audio"] !== "" ? $datos["audio"] : null);

                    $sqlUpdate = "UPDATE libros SET titulo = :titulo,autor = :autor, genero = :genero, anio_publicacion = :anio_publicacion, estado = :estado, sinopsis = :sinopsis, portada = :portada, audio = :audio WHERE id_libro = :id_libro";
                    $stmtUpdate = $pdo->prepare($sqlUpdate);
                    $stmtUpdate->execute([":titulo" => $datos["titulo"], ":autor" => $datos["autor"], ":genero" => $datos["genero"], ":anio_publicacion" => $anioInt, ":estado" => $datos["estado"], ":sinopsis" => $datos["sinopsis"], ":portada" => $portadaFinal, ":audio" => $audioFinal, ":id_libro" => $idLibro]);

                    // Si se sube portada nueva borramos la anterior del servidor
                    if ($nuevaPortadaNombre !== null && $datos["portada"] !== "" && $datos["portada"] !== "default_portada.webp") {
                        $rutaAntiguaImg = $dirPortadas . $datos["portada"];
                        if (is_file($rutaAntiguaImg)) {
                            unlink($rutaAntiguaImg);
                        }
                    }

                    // Si se sube audio nuevo borramos el anterior del servidor
                    if ($nuevoAudioNombre !== null && $datos["audio"] !== "") {
                        $rutaAntiguaAudio = $dirAudio . $datos["audio"];
                        if (is_file($rutaAntiguaAudio)) {
                            unlink($rutaAntiguaAudio);
                        }
                    }

                    header("Location: detalle.php?id=" . $idLibro);
                    exit();
                } else {
                    $sqlInsert = "INSERT INTO libros (titulo, autor, genero, anio_publicacion, estado, sinopsis, portada, audio) VALUES (:titulo, :autor, :genero, :anio_publicacion, :estado, :sinopsis, :portada, :audio)";

                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([":titulo" => $datos["titulo"], ":autor" => $datos["autor"], ":genero" => $datos["genero"], ":anio_publicacion" => $anioInt, ":estado" => $datos["estado"], ":sinopsis" => $datos["sinopsis"], ":portada" => $nuevaPortadaNombre, ":audio" => $nuevoAudioNombre]);
                    $nuevoId = (int) $pdo->lastInsertId();

                    header("Location: detalle.php?id=" . $nuevoId);
                    exit();
                }
            } catch (PDOException $e) {
                error_log("Error al guardar libro en formulario.php: " . $e->getMessage());
                $errores[] = "Ha ocurrido un error al guardar el libro.";
            }
        }
    }
}

$tituloPagina = $esEdicion ? "Editar libro" : "Añadir libro";
$paginaActiva = "formulario";
require_once __DIR__ . "/includes/header.php";
?>

    <main class="seccion pagina-form">
        <div class="tarjeta-form">
            <div class="cabecera-form">
                <p class="sobretitulo"><?= $esEdicion ? "Edición de ficha" : "Nuevo libro" ?></p>
                <h1><?= $esEdicion ? "Editar libro" : "Añadir libro" ?></h1>
                <p><?= $esEdicion ? "Modifica los datos de la lectura seleccionada." : "Completa los datos para añadir una nueva lectura al catálogo." ?></p>
            </div>

            <?php if (!empty($errores)): ?>
                <div class="caja-resumen alerta-error mb-4" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <p><?= escapar($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form id="form-libro" action="<?= $esEdicion ? "formulario.php?id=" . $idLibro : "formulario.php" ?>" method="post" enctype="multipart/form-data" data-validar>
                <div class="row gx-3 gy-4">
                    <label class="campo col-md-6">
                        <span>Título <b>*</b></span>
                        <input type="text" name="titulo" required placeholder="Ej. La última carta" value="<?= escapar($datos["titulo"]) ?>">
                    </label>
                    <label class="campo col-md-6">
                        <span>Autor <b>*</b></span>
                        <input type="text" name="autor" required placeholder="Nombre del autor" value="<?= escapar($datos["autor"]) ?>">
                    </label>
                    <label class="campo col-md-6">
                        <span>Género <b>*</b></span>
                        <select name="genero" required>
                            <option value="" disabled <?= $datos["genero"] === "" ? "selected" : "" ?>>Selecciona un género</option>
                            <?php foreach ($generosValidos as $genero): ?>
                                <option value="<?= escapar($genero) ?>" <?= $datos["genero"] === $genero ? "selected" : "" ?>>
                                    <?= escapar($genero) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                    <label class="campo col-md-6">
                        <span>Año <b>*</b></span>
                        <input type="number" name="anio_publicacion" min="1000" max="<?= date("Y") ?>" required placeholder="<?= date("Y") ?>" value="<?= escapar($datos["anio_publicacion"]) ?>">
                    </label>
                    <label class="campo col-12">
                        <span>Sinopsis <b>*</b></span>
                        <textarea name="sinopsis" rows="5" required placeholder="Escribe una breve sinopsis..."><?= escapar($datos["sinopsis"]) ?></textarea>
                    </label>
                    <label class="campo col-12">
                        <span>Estado de lectura <b>*</b></span>
                        <select name="estado" required>
                            <option value="" disabled <?= $datos["estado"] === "" ? "selected" : "" ?>>Selecciona un estado</option>
                            <?php foreach ($estadosValidos as $clave => $etiqueta): ?>
                                <option value="<?= escapar($clave) ?>" <?= $datos["estado"] === $clave ? "selected" : "" ?>>
                                    <?= escapar($etiqueta) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <!-- Subida de portada y audio -->
                <div class="form-extras">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="caja-subida">
                                <span class="mas" aria-hidden="true">+</span>
                                <span><?= $esEdicion ? "Cambiar imagen de portada" : "Añadir imagen de portada" ?></span>
                                <small><?= $esEdicion ? "Opcional · Se mantiene la actual si no subes otra (WEBP, JPG o PNG)" : "Obligatorio (WEBP, JPG o PNG · Máx. 5 MB)" ?></small>
                                <input type="file" name="portada" accept="image/webp,image/jpeg,image/png" class="visually-hidden" <?= $esEdicion ? "" : "required" ?>>
                            </label>
                        </div>

                        <div class="col-md-6">
                            <label class="caja-subida">
                                <span class="mas" aria-hidden="true">♪</span>
                                <span><?= ($esEdicion && $datos["audio"] !== "") ? "Sustituir fragmento de audiolibro" : "Añadir fragmento de audiolibro" ?></span>
                                <small>Opcional (MP3, OGG o WAV · Máx. 15 MB)</small>
                                <input type="file" name="audio" accept="audio/mpeg,audio/ogg,audio/wav" class="visually-hidden">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="form-acciones">
                    <button type="submit" class="btn btn-primario"><?= $esEdicion ? "Guardar cambios" : "Guardar" ?></button>
                    <a href="<?= $esEdicion ? "detalle.php?id=" . $idLibro : "index.php" ?>" class="btn btn-secundario">Cancelar</a>
                </div>
            </form>
        </div>
    </main>

    <?php require_once __DIR__ . "/includes/footer.php"; ?>