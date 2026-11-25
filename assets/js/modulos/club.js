/**
 * Clase LectorClub que guarda los datos del lector. 
 */

export class LectorClub {
    /**
     * Inicializa el lector.
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
}