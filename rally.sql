
-- Tabla de roles
CREATE TABLE roles (
    id INT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

-- Insertar roles: administrador, gestor (moderador), participante
INSERT INTO roles (id, nombre) VALUES
(1, 'administrador'),
(2, 'gestor'),
(3, 'participante');

-- Tabla de usuarios (incluye todos los roles: admin, gestor, participante)
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);
INSERT INTO admins (nombre, apellidos, email, password, rol_id)
VALUES ('admin', 'ejemplo', 'admin@email.com', '$2y$10$p/0k8pxoBSdS5C12knnZLeKVkmvwusZpubNnCoc9Nuod/jUMY4okq', 1);

CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles(id)
);

CREATE TABLE concursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    reglas TEXT,
    fecha_inicio DATETIME NOT NULL,
    fecha_fin DATETIME NOT NULL,
    max_fotos_por_usuario INT DEFAULT 3,
    max_votos_por_ip INT DEFAULT 2,
    max_participantes INT DEFAULT 100,
    tamano_maximo_bytes INT DEFAULT 2097152,
    formatos_aceptados VARCHAR(255) DEFAULT 'image/jpeg,image/png',
    imagen_portada_base64 LONGTEXT DEFAULT NULL,
    imagen_portada_mime_type VARCHAR(50) DEFAULT NULL,
    fecha_inicio_votacion DATETIME NOT NULL,
    fecha_fin_votacion DATETIME NOT NULL
);

-- Tabla de fotografías
CREATE TABLE fotografias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    concurso_id INT NOT NULL,
    titulo VARCHAR(150) NOT NULL,
    descripcion TEXT,
    imagen_base64 LONGTEXT NOT NULL,
    mime_type VARCHAR(50) NOT NULL,
    estado ENUM('pendiente', 'admitida', 'rechazada') DEFAULT 'pendiente',
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id) ON DELETE CASCADE
);

-- Tabla de votos (voto anónimo por IP)
CREATE TABLE votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    concurso_id INT NOT NULL,
    fotografia_id INT NOT NULL,
    ip_votante VARCHAR(45) NOT NULL,
    fecha_voto TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fotografia_id) REFERENCES fotografias(id) ON DELETE CASCADE,
    FOREIGN KEY (concurso_id) REFERENCES concursos(id) ON DELETE CASCADE
);