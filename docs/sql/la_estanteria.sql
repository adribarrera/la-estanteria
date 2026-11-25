CREATE DATABASE IF NOT EXISTS la_estanteria CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE la_estanteria;

DROP TABLE IF EXISTS resenas;
DROP TABLE IF EXISTS libros;
DROP TABLE IF EXISTS usuarios;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_completo VARCHAR(120) NOT NULL,
    nombre_usuario VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE libros (
    id_libro INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    autor VARCHAR(100) NOT NULL,
    genero ENUM(
        'Ciencia ficción',
        'Fantasía',
        'Clásico',
        'Terror',
        'Misterio',
        'Romance',
        'Aventura',
        'Poesía'
    ) NOT NULL,
    anio_publicacion INT NOT NULL,
    estado ENUM('pendiente', 'leyendo', 'leido') NOT NULL DEFAULT 'pendiente',
    sinopsis TEXT NOT NULL,
    portada VARCHAR(255) NOT NULL DEFAULT 'default_portada.webp',
    audio VARCHAR(255) DEFAULT NULL,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE resenas (
    id_resena INT AUTO_INCREMENT PRIMARY KEY,
    id_libro INT NOT NULL,
    id_usuario INT NOT NULL,
    puntuacion TINYINT NOT NULL CHECK (puntuacion BETWEEN 1 AND 5),
    comentario TEXT NOT NULL,
    fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_resenas_libro
        FOREIGN KEY (id_libro) REFERENCES libros(id_libro)
        ON DELETE CASCADE,
    CONSTRAINT fk_resenas_usuario
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB;