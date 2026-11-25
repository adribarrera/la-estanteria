// Modo claro / oscuro (se recuerda entre páginas)
const raiz = document.documentElement;
let oscuro = false;

try { oscuro = localStorage.getItem("tema") === "oscuro"; } catch { }

function aplicarTema() {
    if (oscuro) raiz.dataset.tema = "oscuro";
    else delete raiz.dataset.tema;
}

aplicarTema(); // se aplica enseguida para evitar parpadeos

document.addEventListener("DOMContentLoaded", () => {
    const boton = document.querySelector(".btn-tema");
    const texto = boton.querySelector(".texto");

    function actualizarBoton() {
        boton.setAttribute("aria-pressed", String(oscuro));
        texto.textContent = oscuro ? "Tema claro" : "Tema oscuro";
    }

    boton.addEventListener("click", () => {
        oscuro = !oscuro;
        aplicarTema();
        try { localStorage.setItem("tema", oscuro ? "oscuro" : "claro"); } catch { }
        actualizarBoton();
    });

    actualizarBoton();
});