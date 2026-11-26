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
        // Se guarda el título del libro visitado.
        this.ultimoLibro = titulo;
    }

    /**
     * Guarda el género preferido del lector.
     * @param {string} genero - Género del libro consultado.
     * @returns {void}
     */
    guardarGenero(genero) {
        //Se guarda el género preferido del lector.
        this.generoPreferido = genero;
    }

    /**
     * Añade el libro a favoritos si no está, o lo quita si ya estaba.
     * @param {string} titulo - Título del libro.
     * @returns {boolean} true si el libro queda en favoritos, false si se ha quitado.
     */
    toggleFavorito(titulo) {
        // Buscamos la posición del título en el array.
        const posicion = this.favoritos.indexOf(titulo);

        if (posicion === -1) {
            // No estaba: lo añadimos al final.
            this.favoritos.push(titulo);
            return true;
        }

        // Ya estaba: lo quitamos.
        this.favoritos.splice(posicion, 1);
        return false;
    }

    /**
     * Devuelve un resumen legible con los datos del lector.
     * @returns {string} Texto con el último libro, los favoritos y el género preferido.
     */
    obtenerResumen() {
        // Texto del último libro consultado
        let ultimo;
        if (this.ultimoLibro) {
            ultimo = `Último libro: ${this.ultimoLibro}`;
        } else {
            ultimo = 'No hay visitas recientes.';
        }

        // Texto de la lista de favoritos
        let favs;
        if (this.favoritos.length > 0) {
            favs = `Favoritos: ${this.favoritos.join(', ')}`;
        } else {
            favs = 'No hay favoritos.';
        }

        // Texto del género preferido
        let genero;
        if (this.generoPreferido) {
            genero = `Género preferido: ${this.generoPreferido}`;
        } else {
            genero = 'No hay género preferido.';
        }

        // Unimos las tres líneas en un solo texto con template literals
        return `${ultimo}\n${favs}\n${genero}`;
    }
}

/**
 * Crea una cookie con el nombre, valor y número de días que se conservará.
 * @param {string} nombre - Nombre de la cookie.
 * @param {string} valor - Valor de la cookie.
 * @param {number} dias - Número de días que se conservará la cookie.
 * @returns {void}
 */
export function setCookie(nombre, valor, dias) {
    // Fecha actual.
    const fecha = new Date();
    // Sumamos los días convertidos a milisegundos.
    fecha.setTime(fecha.getTime() + dias * 24 * 60 * 60 * 1000);
    // Creamos la cookie.
    document.cookie = `${nombre}=${encodeURIComponent(valor)}; expires=${fecha.toUTCString()}; path=/`;
}

/**
 * Busca una cookie por su nombre y devuelve su valor.
 * @param {string} nombre - Nombre de la cookie que se busca.
 * @returns {string|null} El valor decodificado, o null si no existe.
 */
export function getCookie(nombre) {
    // Comprueba que el navegador tiene las cookies activadas.
    if (!navigator.cookieEnabled) {
        return null;
    }
    // Recorremos todas las cookies.
    for (const cookie of document.cookie.split("; ")) {
        // Divide la cookie en nombre y valor.
        const [nombreCookie, valorCookie] = cookie.split("=");
        // Si el nombre coincide, devolvemos el valor decodificado.
        if (nombreCookie === nombre) {
            return decodeURIComponent(valorCookie);
        }
    }
    return null;
}

/**
 * Guarda en una cookie el título del libro que se está consultando.
 * @param {LectorClub} lector - Instancia del lector actual.
 * @returns {void}
 */
function guardarUltimaLectura(lector) {
    // Comprobamos que estamos en la ficha de uno de los libros.
    const infoLibro = document.querySelector('.info-libro');
    if (!infoLibro) {
        return;
    }
    // Leer el título del libro desde <h1>
    const titulo = infoLibro.querySelector('h1').textContent;
    lector.registrarVisita(titulo);
    // Guardamos el título del libro en una cookie.
    setCookie('ultimoLibro', titulo, 30);
}
