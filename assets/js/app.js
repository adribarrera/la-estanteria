import { Interfaz } from "./modulos/interfaz.js";
import { Validador } from "./modulos/validaciones.js";
import { iniciarClub } from "./modulos/club.js";

document.addEventListener("DOMContentLoaded", () => {
    Interfaz.iniciarTema();
    Validador.iniciarFormularios();
    iniciarClub();
});