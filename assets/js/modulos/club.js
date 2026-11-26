/**
 * Clase LectorClub que guarda los datos del lector. 
 */

export class LectorClub {
    /**
     * Inicializa el lector. Se ejecuta automáticamente al crearlo con new.
     * @param {string|null} ultimoLibro - Título del último libro consultado, o null.
     * @param {string[]} favoritos - Lista de títulos.
     * @param {string} generoPreferido - Género favorito.
     */
    constructor(ultimoLibro = null, favoritos = [], generoPreferido = '') {
        // Guardamos datos en el objeto.
        this.ultimoLibro = ultimoLibro;
        this.favoritos = favoritos;
        this.generoPreferido = generoPreferido;
    }
    
    /**
     * Registra el último libro que ha consultado el lector.
     * @param {string} titulo - Título del libro visitado.
     * @returns {void}
     */
    registrarVisita(titulo) {
        this.ultimoLibro = titulo;
    }

    /**
     * Guarda el género preferido del lector.
     * @param {string} genero - Género del libro consultado.
     * @returns {void}
     */
    guardarGenero(genero) {
        this.generoPreferido = genero;
    }
}