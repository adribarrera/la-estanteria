// ==== Interfaz: depuracion y tema claro / oscuro ====

// reutilizamos las cookies de club.js (admiten dias por parametro y comprueban navigator.cookieEnabled)
import { setCookie, getCookie } from "./club.js";

// ==== Depuracion ====

// configura un logger para gestionar los mensajes de depuracion y errores.
export const logger = (function () {
    const _DEBUGMODE = false;
    return {
        info: (mensaje) => {
            if (_DEBUGMODE) console.log(`[INFO] ${mensaje}`);
        },
        error: (mensaje) => {
            console.error(`[ERROR] ${mensaje}`);
        }
    };
})();

// ==== Objeto propio: Interfaz ====

// objeto que gestiona el tema claro / oscuro (propiedades + metodos)
export const Interfaz = {
    nombreCookie: "tema", // nombre de la cookie donde se guarda el tema
    diasCookie: 365, // dias que dura la cookie del tema (1 año)
    temaActual: "claro", // tema aplicado por defecto

    selectores: {
        botonTema: ".btn-tema", // boton que cambia claro / oscuro
    },

    // aplica el tema claro / oscuro, guarda el tema actual y actualiza el boton de cambiar de tema
    aplicarTema(tema) {
        const oscuro = tema === "oscuro";
        this.temaActual = tema;
        document.querySelector("html").setAttribute("data-tema", tema);

        const boton = document.querySelector(this.selectores.botonTema);
        if (boton) {
            boton.setAttribute("aria-pressed", String(oscuro));
            const texto = boton.querySelector(".texto");
            if (texto) {
                texto.textContent = oscuro ? "Tema claro" : "Tema oscuro";
            }
        }
    },

    // cambia entre claro y oscuro y recuerda la eleccion en una cookie
    alternarTema() {
        const nuevoTema = this.temaActual === "oscuro" ? "claro" : "oscuro";
        this.aplicarTema(nuevoTema);
        setCookie(this.nombreCookie, nuevoTema, this.diasCookie);
    },

    // devuelve el tema inicial: el de la cookie y, si no hay, el que prefiere el sistema (window.matchMedia)
    obtenerTemaInicial() {
        const guardado = getCookie(this.nombreCookie);
        if (guardado === "oscuro" || guardado === "claro") {
            return guardado;
        }
        const sistemaOscuro = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;
        return sistemaOscuro ? "oscuro" : "claro";
    },

    // aplica el tema inicial y asigna el evento al boton de cambiar de tema
    iniciarTema() {
        this.aplicarTema(this.obtenerTemaInicial());

        const boton = document.querySelector(this.selectores.botonTema);
        if (boton) {
            boton.addEventListener("click", () => this.alternarTema());
        }
        logger.info("Tema iniciado.");
    }
};