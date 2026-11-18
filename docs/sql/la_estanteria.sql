CREATE DATABASE IF NOT EXISTS la_estanteria CHARACTER SET utf8mb4;

USE la_estanteria;

DROP TABLE IF EXISTS resenas;
DROP TABLE IF EXISTS libros;

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    genero VARCHAR(50) NOT NULL,
    anio_publicacion INT NOT NULL,
    estado ENUM('pendiente', 'leyendo', 'leido') NOT NULL DEFAULT 'pendiente',
    sinopsis TEXT NOT NULL,
    portada VARCHAR(255) NOT NULL DEFAULT 'default.webp',
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE resenas (
    id_resena INT AUTO_INCREMENT PRIMARY KEY,
    id_libro INT NOT NULL,
    lector_nombre VARCHAR(80) NOT NULL,
    puntuacion TINYINT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resenas_libro
        FOREIGN KEY (id_libro) REFERENCES libros(id_libro)
        ON DELETE CASCADE
);

-- Datos iniciales mínimos de verificación
INSERT INTO libros (titulo, autor, genero, anio_publicacion, estado, sinopsis, portada) VALUES
('Dune', 'Frank Herbert', 'Ciencia Ficción', 1965, 'leido', 'En el desértico planeta Arrakis, la familia Atreides lucha por el control de la especia melange.', 'dune.webp'),
('El nombre del viento', 'Patrick Rothfuss', 'Fantasía', 2007, 'leyendo', 'Kvothe relata desde una posada su vida como músico, arcanista y leyenda viva.', 'nombre_viento.webp');

INSERT INTO resenas (id_libro, lector_nombre, puntuacion, comentario) VALUES
(1, 'Elena G.', 5, 'Obra maestra absoluta de la ciencia ficción. Imprescindible.');