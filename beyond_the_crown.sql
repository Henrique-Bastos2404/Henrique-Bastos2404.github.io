DROP DATABASE IF EXISTS beyond_the_crown;
CREATE DATABASE IF NOT EXISTS beyond_the_crown
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE beyond_the_crown;

CREATE TABLE tipos_utilizadores (
    id INT NOT NULL AUTO_INCREMENT,
    designacao VARCHAR(50) NOT NULL UNIQUE,
    PRIMARY KEY (id)
);

-- Tabela de Utilizadores
CREATE TABLE utilizadores (
    id INT NOT NULL AUTO_INCREMENT,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    tipo_id INT NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    foto_perfil VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (id),
    CONSTRAINT fk_utilizadores_tipo
        FOREIGN KEY (tipo_id) REFERENCES tipos_utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);

-- Tabela de Pontuações
CREATE TABLE scores (
    id INT NOT NULL AUTO_INCREMENT,
    utilizador_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,
    level INT NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    CONSTRAINT fk_scores_utilizador
        FOREIGN KEY (utilizador_id) REFERENCES utilizadores(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

INSERT INTO tipos_utilizadores (designacao) VALUES
('Administrador'),
('Utilizador');

INSERT INTO utilizadores (ativo, tipo_id, email, `password`, username, foto_perfil) VALUES
(TRUE, 1, 'hdinis110@gmail.com', SHA2('Abacate24', 256), 'CBO', NULL);