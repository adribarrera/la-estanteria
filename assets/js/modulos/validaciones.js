// ==== Validacion de formularios ====

/**
 * regla de validacion de un campo de formulario
 * @typedef {object} ReglaValidacion
 * @property {boolean} obligatorio - indica que el campo no puede quedar vacio
 * @property {RegExp} patron - expresion regular que debe cumplir el valor
 * @property {string} mensaje - error que se muestra en caso de que no se cumpla el patron
 * @property {function(string): boolean} [comprobar] - comprobacion extra
 * @property {string} [mensajeComprobar] - error que salta cuando falla la comprobacion extra
 */

// objeto propio que valida los formularios con data-validar (propiedades + metodos)
export const Validador = {
    // texto para un campo obligatorio vacio
    mensajeObligatorio: "Este campo es obligatorio.",

    selectores: {
        formularios: "form[data-validar]",
        campo: ".campo, .caja-subida, .caja-resena",
        controles: "input, select, textarea",
    },

    /** @type {Object<string, ReglaValidacion>} */
    reglas: {
        titulo: {
            obligatorio: true,
            patron: /^.{2,100}$/,
            mensaje: "El titulo debe tener entre 2 y 100 caracteres."
        },
        autor: {
            obligatorio: true,
            patron: /^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ][A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'-]{1,59}$/,
            // ^[A-Za-zÁÉÍÓÚÜÑáéíóúüñ] - empieza con una letra
            // [A-Za-zÁÉÍÓÚÜÑáéíóúüñ .'-] - puede contener letras, espacios, puntos, guiones o apostrofes
            // {1,59}$ - debe tener entre 1 y 59 caracteres de longitud
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
        anio_publicacion: {
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
        comentario: {
            obligatorio: true,
            patron: /^[\s\S]{10,500}$/,
            mensaje: "El comentario debe tener entre 10 y 500 caracteres."
        },
        puntuacion: {
            obligatorio: true,
            patron: /^[1-5]$/,
            mensaje: "La puntuación debe ser un número del 1 al 5."
        }
    },

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

    // valida un campo con su regla (en caso de que tenga) y muestra u oculta el error
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
    }
};