-- Crear la base de datos vacaciones
CREATE DATABASE vacaciones;
-- Asignar un usuario y una clave a la base de datos
GRANT ALL PRIVILEGES ON vacaciones.* TO 'usuario'@'localhost' IDENTIFIED BY 'clave';
-- Seleccionar la base de datos vacaciones
USE vacaciones;
-- Crear la tabla empleados
CREATE TABLE empleados (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  apellido VARCHAR(50) NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  dias_disponibles INT DEFAULT 15,
  dias_tomados INT DEFAULT 0
);
-- Insertar algunos registros de ejemplo
INSERT INTO empleados (nombre, apellido, email) VALUES
('Juan', 'Perez', 'juan.perez@empresa.com'),
('María', 'Garcia', 'maria.garcia@empresa.com'),
('Pedro', 'Lopez', 'pedro.lopez@empresa.com');
