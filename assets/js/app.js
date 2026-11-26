// ==== Depuracion ====

// Configura un logger para gestionar los mensajes de depuración y errores.
const logger = (function () {
    const _DEBUGMODE = false;
    const logger = {
        info: (mensaje) => {
            if (_DEBUGMODE) console.log('[INFO] ${mensaje}');
            error: (mensaje) => {
                console.error('[ERROR] ${mensaje}');
            }
        }
    }
    return logger;
})();

// ==== Estructura de datos ==== 

/**
 * Regla de validacion de un campo de formulario
 * @typedef {object} ReglaValidacion
 * @property {boolean} obligatorio - Indica que el campo no puede quedar vacio
 * @property {RegExp} patron - Expresion regular que debe cumplir el valor
 * @property {string} mensaje - Error que se muestra en caso de que no se cumpla el patron
 * @property {function(string): boolean} [comprobar] - comprobacion extra
 * @property {string} [mensajeComprobar] - error que salta cuando falla la comprobacion extra
 */

const GestorEstanteria = {
    // nombre de la cookie donde se guarda el tema
    nombreCookie: "tema",
    // años que dura el tema
    aniosCookie: 1,
    // tema aplicado por defecto
    temaActual: "claro",
    // texto para un campo obligatorio vacio
    mensajeObligatorio: "Este campo es obligatorio.",

    selectores: {
        botonTema: ".btn-tema",
        buscador: ".buscador input",
        libro: ".libro",
        genero: ".genero",
        listaGeneros: ".lista-generos",
        estadoFiltro: ".estado-filtro",
        formularios: "form[data-validar]",
        campo: ".campo, .caja-subida, .caja-resena",
        controles: "input, select, textarea",
    },

    reglas: {
        titulo: {
            obligatorio: true,
            patron: /^.{2,100}$/,
            mensaje: "El titulo debe tener entre 2 y 100 caracteres."
        },
        autor: {
            obligatorio: true,
            patron: /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'-]{1,59}$/,
            // ^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ] - Empieza con una letra
            // [A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'-] - Puede contener letras, espacios, puntos, guiones o apostrofes
            // {1,59}$ - Debe tener entre 1 y 59 caracteres de longitud
            mensaje: "El autor debe tener entre 2 y 60 caracteres."
        },
        genero: {
            obligatorio: true,
            patron: /^.+$/,
            // (.) puede ser cualquier caracter (letra, numero, espacio, simbolo...)
            // (+) debe de haber al menos un caracter
            // ($) la validacion llega hasta el final, sin nada detras
            mensaje: "Selecciona un género."
        },
        anio: {
            obligatorio: true,
            patron: /^\d{4}$/,
            // (d) debe de haber un digito del 1-9
            // ({4}) debe de aparecer 4 digitos
            // ($) no puede haber mas caracteres
            mensaje: "Escribe un año de 4 cifras",
            comprobar: (valor) => Number(valor) >= 1000 && Number(valor) <= new Date().getFullYear(),
            mensajeComprobar: "El año debe estar entre 1000 y el año actual."
        },
        sinopsis: {
            obligatorio: false,
            patron: /^[\s\S]{10,500}$/,
            // [\s\S] puede ser cualquier caracer (incluyendo el salto de linea)
            // {10,500} minimo 10 caracteres, maximo 500
            mensaje: "La sinopsis debe tener entre 10 y 500 caracteres."
        },
        resena: {
            obligatorio: false,
            patron: /^[\s\S]{10,500}$/,
            mensaje: "La reseña debe tener entre 10 y 500 caracteres."
        },
        puntuacion: {
            obligatorio: false,
            patron: /^[1-5]$/,
            mensaje: "La puntuación debe ser un número del 1 al 5."
        }
    },

    // ==== Cookies ====


    // coge la fecha actual, la guarda y le suma el tiempo declarado (1 año) y guarda las cookies por 1 año
    guardarCookie(nombre, valor) {
        const caducidad = new Date();
        caducidad.setFullYear(caducidad.getFullYear() + this.aniosCookie);
        document.cookie = `${nombre}=${encodeURIComponent(valor)};
    expires=${caducidad.toUTCString()}; path=/`;
    },


    // busca una cookie por su nombre, en caso de que no exista devuelve null
    leerCookie(nombre) {
        const pares = document.cookie.split("; ");

        for (const par of pares) {
            const [clave, valor] = par.split("=");

            if (clave === nombre) {
                return decodeURIComponent(valor);
            }
        }

        return null;
    },

    // ==== Tema claro / oscuro ====


    // aplica el tema claro / oscuro y se guarda el tema actual y se actualiza el boton de cambiar de tema
    aplicarTema(tema) {
        const oscuro = tema === "oscuro";
        this.temaActual = tema;
        document.querySelector("html").setAttribute("data-tema", tema);

        const boton = document.querySelector(this.selectores.botonTema);
        if (boton) {
            boton.setAttribute("aria-pressed", String(oscuro));
            boton.querySelector(".texto").textContent = oscuro ? "Tema claro" : "Tema oscuro";
        }
    },

    alternarTema() {
        const nuevoTema = this.temaActual === "oscuro" ? "claro" : "oscuro";
        this.aplicarTema(nuevoTema);
        this.guardarCookie(this.nombreCookie, nuevoTema);
    },


    // coge el tema guardado en la cookie, si no hay guardado ninguno se pone el claro por defecto y se le asigna un evento al boton de cambiar de tema
    iniciarTema() {
        const guardado = this.leerCookie(this.nombreCookie);
        this.aplicarTema(guardado === "oscuro" ? "oscuro" : "claro");
        const boton = document.querySelector(this.selectores.botonTema);
        if (boton) {
            boton.addEventListener("click", () => this.alternarTema());
        }
    },

    // ==== Filtrado del DOM ====


    // muestra un mensaje informativo sobre el estado del filtro de géneros, creando el aviso si no existe.
    mostrarEstado(mensaje) {
        let aviso = document.querySelector(this.selectores.estadoFiltro);
        if (!aviso) {
            aviso = document.createElement("p");
            aviso.classList.add("estado-filtro");
            aviso.setAttribute("role", "status");
            document.querySelector(this.selectores.listaGeneros).prepend(aviso);
        }
        aviso.textContent = mensaje;
    },


    // Oculta los géneros que no tienen libros visibles y muestra los que sí tienen.
    ocultarGenerosVacios() {
        document.querySelectorAll(this.selectores.genero).forEach((genero) => {
            const visibles = genero.querySelectorAll(`${this.selectores.libro}:not(.oculto)`).length;
            if (visibles === 0) {
                genero.classList.add("oculto");
            } else {
                genero.classList.remove("oculto");
            }
        });
    },


    // filtra los libros segun el titulo, mostrando los que coinciden y ocultando los que no
    filtrarLibros(termino) {
        const buscado = termino.trim().toLowerCase();
        let visibles = 0;
        document.querySelectorAll(this.selectores.libro).forEach((tarjeta) => {
            const titulo = tarjeta.querySelector("span").textContent.toLowerCase();
            if (titulo.includes(buscado)) {
                tarjeta.classList.remove("oculto");
                visibles++;
            } else {
                tarjeta.classList.add("oculto");
            }
        })

        this.ocultarGenerosVacios();

        if (buscado === "") {
            this.mostrarEstado("");
        } else if (visibles === 0) {
            this.mostrarEstado(`No hay resultados para "${termino.trim()}".`);
        } else if (visibles === 1) {
            this.mostrarEstado("1 libro encontrado.");
        } else {
            this.mostrarEstado(`${visibles} libros encontrados.`);
        }
    },


    // inicializa el buscador de libros, activa el filtrado al escribir y evita que el formulario se envie al pulsar enter (es obligatorio enviar el formulario a traves del boton)
    iniciarFiltros() {
        const buscador = document.querySelector(this.selectores.buscador);
        if (!buscador || !document.querySelector(this.selectores.libro)) return;

        buscador.addEventListener("input", (evento) => this.filtrarLibros(evento.target.value));

        const formulario = document.querySelector("form.buscador");
        if (formulario) {
            formulario.addEventListener("submit", (evento) => evento.preventDefault());
        }
    },

    // ==== Validacion de formularios ====


    // devuelve el elemnto de aviso de un campo, en caso de que no exista, crea uno nuevo
    obtenerAviso(campo, control) {
        let aviso = campo.querySelector(".mensaje-error");
        if (!aviso) {
            aviso = document.createElement("small");
            aviso.classList.add("mensaje-error");
            aviso.setAttribute("id", `error-${control.name}`);
            campo.appendChild(aviso);
            control.setAttribute("aria-describedby", `error-${control.name}`);
        }
        return aviso;
    },


    // marca un campo como incorrecto y muestra su mensaje
    mostrarError(campo, control, mensaje) {
        this.obtenerAviso(campo, control).textContent = mensaje;
        control.classList.add("invalido");
        control.setAttribute("aria-invalid", "true");
    },


    // quita el error de un campo
    limpiarError(campo, control) {
        const aviso = campo.querySelector(".mensaje-error");
        if (aviso) {
            aviso.textContent = "";
        }
        control.classList.remove("invalido");
        control.setAttribute("aria-invalid", "false");
    },


    // valida un campo con su regla (en caso de que tenga) y muestra u oculta el rror
    validarCampo(campo) {
        const control = campo.querySelector(this.selectores.controles);
        if (!control) return true;

        const regla = this.reglas[control.name];
        if (!regla) return true; // los campos sin regla no se validan

        const valor = control.value.trim();

        if (valor === "") {
            if (regla.obligatorio) {
                this.mostrarError(campo, control, this.mensajeObligatorio);
                return false;
            }
            this.limpiarError(campo, control);
            return true;
        }

        if (!regla.patron.test(valor)) {
            this.mostrarError(campo, control, regla.mensaje);
            return false;
        }

        if (regla.comprobar && !regla.comprobar(valor)) {
            this.mostrarError(campo, control, regla.mensajeComprobar);
            return false;
        }

        this.limpiarError(campo, control);
        return true;
    },

    // escribe en el formulario un mensaje con el numero de errores
    mostrarResumen(formulario, errores) {
        let resumen = formulario.querySelector(".resumen-formulario");
        if (!resumen) {
            resumen = document.createElement("p");
            resumen.classList.add("resumen-formulario");
            resumen.setAttribute("role", "status");
            formulario.prepend(resumen);
        }

        if (errores === 0) {
            resumen.textContent = "";
        } else if (errores === 1) {
            resumen.textContent = "Hay 1 campo por corregir";
        } else {
            resumen.textContent = `Hay ${errores} campos por corregir`;
        }
    },

    // valida todos los campos del formulario
    validarFormulario(formulario) {
        let errores = 0;
        formulario.querySelectorAll(this.selectores.campo).forEach((campo) => {
            if (!this.validarCampo(campo)) {
                errores++;
            }
        });
        this.mostrarResumen(formulario, errores);
        return errores === 0;
    },

    // controla el evento submit de los formularios con data-validar y valida cada campo al salir de el
    iniciarFormularios() {
        document.querySelectorAll(this.selectores.formularios).forEach((formulario) => {

            formulario.addEventListener("submit", (evento) => {
                // si hay errores se bloquea el envio; si todo es valido, el envio sigue su curso
                if (!this.validarFormulario(formulario)) {
                    evento.preventDefault();
                }
            });

            formulario.querySelectorAll(this.selectores.campo).forEach((campo) => {
                const control = campo.querySelector(this.selectores.controles);
                if (!control) return;
                control.addEventListener("blur", () => this.validarCampo(campo));
                control.addEventListener("change", () => this.validarCampo(campo));
            });
        });
    },


    // ==== Arranque ====

    // arranca el tema y, si existen en la pagina, los filtros y los formularios.
    init() {
        this.iniciarTema();
        this.iniciarFiltros();
        this.iniciarFormularios();
        logger.info("GestionarEstanteria iniciado.");
    }
};

// inicia la aplicacion cuando el DOM ha sido completamente cargado
document.addEventListener("DOMContentLoaded", () => GestorEstanteria.init());

// exporta la clase GestionarEstanteria al objeto global window
window.GestorEstanteria = GestorEstanteria;
