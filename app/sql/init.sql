CREATE DATABASE IF NOT EXISTS la_esperanza;
USE la_esperanza;

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) NOT NULL UNIQUE,
    direccion VARCHAR(150) NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('administrador','productor','comprador') NOT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    id_productor INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT NOT NULL,
    unidad_medida VARCHAR(50) NOT NULL,
    estado ENUM('activo','inactivo') DEFAULT 'activo',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_productor) REFERENCES usuarios(id_usuario)
);

CREATE TABLE solicitudes (
    id_solicitud INT AUTO_INCREMENT PRIMARY KEY,
    id_comprador INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    observacion TEXT,
    estado ENUM('pendiente','confirmada','cancelada','finalizada') DEFAULT 'pendiente',
    fecha_solicitud TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);

CREATE TABLE entregas (
    id_entrega INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    fecha_entrega DATE NOT NULL,
    lugar VARCHAR(150) NOT NULL,
    observacion TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud)
);

CREATE TABLE calificaciones (
    id_calificacion INT AUTO_INCREMENT PRIMARY KEY,
    id_solicitud INT NOT NULL,
    id_comprador INT NOT NULL,
    puntuacion INT NOT NULL,
    comentario TEXT,
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_solicitud) REFERENCES solicitudes(id_solicitud),
    FOREIGN KEY (id_comprador) REFERENCES usuarios(id_usuario)
);

INSERT INTO usuarios(nombre, telefono, direccion, password, rol)
VALUES
('Administrador Comunidad', '55550001', 'Comunidad La Esperanza', SHA2('admin123', 256), 'administrador'),
('Productor Demo', '55550002', 'Sector agricola', SHA2('prod123', 256), 'productor'),
('Comprador Demo', '55550003', 'Sector central', SHA2('comp123', 256), 'comprador');

INSERT INTO productos(id_productor, nombre, descripcion, precio, cantidad, unidad_medida)
VALUES
(2, 'Tomate', 'Tomate fresco de la comunidad', 15.00, 100, 'libra'),
(2, 'Maiz', 'Maiz criollo disponible para venta', 10.00, 200, 'libra');