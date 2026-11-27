<?php
// Tipado estricto. Así obligamos a que los tipos de datos coincidan exactamente y no haya conversiones
declare(strict_types=1);

require_once __DIR__ . "/includes/conexion.php";
iniciarSesionSegura();

// Si ya tiene la sesión abierta no tiene sentido que vea el login así que lo mandamos al inicio
if (isset($_SESSION["id_usuario"])) {
    header("Location: index.php");
    exit(); // Cortamos el script aquí para que PHP no siga ejecutando nada después de redirigir
}

$errores = [];
$modoActivo = "login"; // Dejamos marcada la pestaña correcta si hay fallos en el envío

// Comprobamos que se ha llegado aquí enviando un formulario por POST y no entrando directo por URL
if($_SERVER["REQUEST_METHOD"] === "POST") {
    $accion = $_POST["accion"] ?? "";

    // PULSA INICIAR SESION
    if ($accion === "login") {
        $modoActivo ="login";
        $identificador = trim($_POST["login_email"] ?? "");
        $password = $_POST["login_password"] ?? "";

        if ($identificador === "" || $password === "") {
            $errores[] = "Debes introducir tu correo/usuario y tu contraseña";
        } else {
            try {
                // Usamos marcadores con nombre en consultas preparadas para evitar las inyecciones SQL
                $sql = "SELECT id_usuario, nombre_completo, nombre_usuario, password_hash FROM usuarios WHERE email = :email OR nombre_usuario = :usuario LIMIT 1"; // Consulta
                $stmt = $pdo->prepare($sql);                                                                                                                        // Enviamos la consulta a MySQL para que la compile antes de meter los datos
                $stmt->execute([":email" => $identificador,":usuario" => $identificador]);                                                                          // Ejecutamos pasando los valores por separado para que los trate como texto literal
                $usuario = $stmt->fetch();                                                                                                                          // Sacamos la fila encontrada como un array asociativo o devuelve false si no existe el usuario

                // password_verify compara la contraseña escrita en texto plano con el hasheo de la BD
                if ($usuario && password_verify($password, $usuario["password_hash"])) {
                    session_regenerate_id(true);    // Cambia el id de sesión al loguarse para evitar ataques
                    
                    // Guardamos en $_SESSION (en el servidor) los datos mínimos para identificar al usuario
                    $_SESSION["id_usuario"] = (int) $usuario["id_usuario"];
                    $_SESSION["nombre_usuario"] = $usuario["nombre_usuario"];
                    $_SESSION["nombre_completo"] = $usuario["nombre_completo"];

                    header("Location: index.php");
                    exit();
                } else {
                    $errores[] = "Usuario o contraseña incorrectos";
                }
            } catch (PDOException $e) {
                error_log("Error en login: " . $e->getMessage());
                $errores[] = "Ha ocurrido un error al iniciar sesión. Inténtalo de nuevo más tarde";
            }
        }
    }

    // O PULSA REGISTRARSE
    if($accion === "registro") {
        $modoActivo = "registro";
        $nombreCompleto = trim($_POST["reg_nombre"] ?? "");
        $nombreUsuario = trim($_POST["reg_usuario"] ?? "");
        $email = trim($_POST["reg_email"] ?? "");
        $password1 = $_POST["reg_pass1"] ?? "";
        $password2 = $_POST["reg_pass2"] ?? "";

        if ($nombreCompleto === "" || $nombreUsuario === "" || $email === "" || $password1 === "") {
            $errores[] = "Todos los campos obligatorios deben estar rellenos";
        }

        // filter_var valida que el texto tenga estructura de correo electrónico
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errores[] = "El formato del correo electrónico no es válido.";
        }
        if (strlen($password1) < 6) {
            $errores[] = "La contraseña debe tener al menos 6 caracteres.";
        }
        if ($password1 !== $password2) {
            $errores[] = "Las contraseñas no coinciden.";
        }

        if(empty($errores)) {
            try {
                // Comprobamos antes que no haya otro usuario con ese correo o nombre de usuario
                $sqlCheck = "SELECT id_usuario FROM usuarios WHERE email = :email OR nombre_usuario = :usuario LIMIT 1";
                $stmtCheck = $pdo->prepare($sqlCheck);
                $stmtCheck->execute([":email"   => $email, ":usuario" => $nombreUsuario]);

                if ($stmtCheck->fetch()) {
                    $errores[] = "El correo electrónico o nombre de usuario ya está registrado.";
                } else {
                    // Ciframos la contraseña con BCrypt y salt aleatoria automática. Nunca en texto plano.
                    $hash = password_hash($password1,PASSWORD_DEFAULT);

                    $sqlInsert = "INSERT INTO usuarios (nombre_completo, nombre_usuario, email, password_hash) VALUES (:nombre, :usuario, :email, :hash)";

                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([":nombre"  => $nombreCompleto, ":usuario" => $nombreUsuario, ":email" => $email, ":hash" => $hash]);

                    // lastInsertId() nos devuelve el id_usuario autoincrementado que crea MySQL
                    session_regenerate_id(true);
                    $_SESSION["id_usuario"] = (int) $pdo->lastInsertId();
                    $_SESSION["nombre_usuario"] = $nombreUsuario;
                    $_SESSION["nombre_completo"] = $nombreCompleto;

                    header("Location: index.php");
                    exit();
                } 
            } catch(PDOException $e) {
                error_log("Error en el registro " . $e->getMessage());
                $errores[] = "No se ha podido completar el registro. Inténtalo más tarde";
            }  
        }
    }
}

$tituloPagina = "Acceso y Registro";
$paginaActiva = "login";
require_once __DIR__ . "/includes/header.php";
?>

    <main class="seccion pagina-form pagina-auth">
        <div class="tarjeta-form tarjeta-auth">
            <div class="cabecera-form cabecera-auth">
                <p class="sobretitulo">Comunidad lectora</p>
                <h1>Área de clientes</h1>
                <p>Inicia sesión con tu cuenta o regístrate para participar en el club de lectura, añadir valoraciones y guardar tus libros favoritos.</p>
            </div>
            
            <!-- Si hay algún fallo de validación o de credenciales lo mostramos aquí -->
            <?php if (!empty($errores)): ?>
                <div class="caja-resumen alerta-error" role="alert">
                    <?php foreach ($errores as $error): ?>
                        <p><?= escapar($error) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Los radios controlan por CSS qué formulario se ve y PHP marca el que estaba activo -->
            <input type="radio" name="modo" id="auth-login" class="sr-only" <?= $modoActivo === "login" ? "checked" : "" ?>>
            <input type="radio" name="modo" id="auth-registro" class="sr-only" <?= $modoActivo === "registro" ? "checked" : "" ?>>

            <div class="auth-tabs">
                <label for="auth-login" class="auth-tab">Iniciar sesión</label>
                <label for="auth-registro" class="auth-tab">Registrarse</label>
            </div>

            <!-- Iniciar sesión -->
            <div class="auth-panel" id="panel-login">
                <form id="form-login" action="login.php" method="post" class="form-auth">
                    <!-- Campo oculto para saber en PHP qué formulario de los dos se ha enviado -->
                    <input type="hidden" name="accion" value="login">
                    <div class="row gx-3 gy-4">
                        <label class="campo col-12">
                            <span>Correo electrónico o Usuario <b>*</b></span>
                            <input type="text" name="login_email" required value="<?= escapar($_POST["login_email"] ?? "") ?>" placeholder="ejemplo@correo.com" autocomplete="username">
                        </label>

                        <label class="campo col-12">
                            <span>Contraseña <b>*</b></span>
                            <input type="password" name="login_password" required placeholder="Tu contraseña"
                                autocomplete="current-password">
                        </label>
                    </div>

                    <div class="auth-opciones">
                        <label class="auth-recordar">
                            <input type="checkbox" name="recordarme" checked>
                            <span>Recordar mi sesión</span>
                        </label>
                    </div>

                    <div class="auth-acciones-doble">
                        <button type="submit" class="btn btn-primario btn-auth">Iniciar sesión</button>
                        <label for="auth-registro" class="btn btn-secundario btn-auth">Registrar nueva cuenta</label>
                    </div>
                </form>
            </div>

            <!-- Registrarse -->
            <div class="auth-panel" id="panel-registro">
                <form id="form-registro" action="login.php" method="post" class="form-auth">
                    <input type="hidden" name="accion" value="registro">
                    <div class="row gx-3 gy-4">
                        <label class="campo col-md-6">
                            <span>Nombre completo <b>*</b></span>
                            <input type="text" name="reg_nombre" required value="<?= escapar($_POST["reg_nombre"] ?? "") ?>" placeholder="Ej. Clara Montes" autocomplete="name">
                        </label>

                        <label class="campo col-md-6">
                            <span>Nombre de usuario <b>*</b></span>
                            <input type="text" name="reg_usuario" required value="<?= escapar($_POST["reg_usuario"] ?? "") ?>" placeholder="Ej. clara_lectora" autocomplete="username">
                        </label>
                        
                        <label class="campo col-12">
                            <span>Correo electrónico <b>*</b></span>
                            <input type="email" name="reg_email" required value="<?= escapar($_POST["reg_email"] ?? "") ?>" placeholder="ejemplo@correo.com" autocomplete="email">
                        </label>

                        <label class="campo col-md-6">
                            <span>Contraseña <b>*</b></span>
                            <input type="password" name="reg_pass1" required placeholder="Mínimo 6 caracteres" minlength="6" autocomplete="new-password">
                        </label>

                        <label class="campo col-md-6">
                            <span>Repetir contraseña <b>*</b></span>
                            <input type="password" name="reg_pass2" required placeholder="Repite tu contraseña" minlength="6" autocomplete="new-password">
                        </label>
                    </div>

                    <div class="auth-opciones">
                        <label class="auth-recordar">
                            <input type="checkbox" name="terminos" required checked>
                            <span>Acepto las normas comunitarias y la política de privacidad de La Estantería</span>
                        </label>
                    </div>

                    <div class="auth-acciones-doble">
                        <button type="submit" class="btn btn-primario btn-auth">Registrar</button>
                        <label for="auth-login" class="btn btn-secundario btn-auth">Ya tengo cuenta · Iniciar
                            sesión</label>
                    </div>
                </form>
            </div>
        </div>
    </main>
<?php require_once __DIR__ . "/includes/footer.php"; ?>