-- Código SQL para crear la base de datos y las tablas

CREATE DATABASE vacaciones;
USE vacaciones;

CREATE TABLE empleados (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nombre VARCHAR(50) NOT NULL,
  password VARCHAR(50) NOT NULL,
  fecha_ingreso DATE NOT NULL,
  vacaciones_disponibles INT NOT NULL
);

CREATE TABLE solicitudes (
  id INT PRIMARY KEY AUTO_INCREMENT,
  id_empleado INT NOT NULL,
  fecha_inicio DATE NOT NULL,
  fecha_fin DATE NOT NULL,
  estado VARCHAR(10) NOT NULL,
  comentario VARCHAR(100),
  FOREIGN KEY (id_empleado) REFERENCES empleados(id)
);

-- Código SQL para insertar algunos datos de ejemplo

INSERT INTO empleados (nombre, password, fecha_ingreso, vacaciones_disponibles) VALUES
('Juan', '1234', '2020-01-01', 15),
('Ana', '5678', '2019-05-01', 20),
('Pedro', 'abcd', '2021-03-01', 10),
('Laura', 'efgh', '2020-07-01', 12),
('Carlos', 'admin', '2018-01-01', 25); -- Carlos es el administrador o jefe

INSERT INTO solicitudes (id_empleado, fecha_inicio, fecha_fin, estado, comentario) VALUES
(1, '2023-11-01', '2023-11-05', 'pendiente', NULL),
(2, '2023-10-15', '2023-10-20', 'aprobada', 'Disfruta tus vacaciones'),
(3, '2023-12-01', '2023-12-10', 'rechazada', 'No hay suficiente personal'),
(4, '2023-11-10', '2023-11-15', 'pendiente', NULL);
