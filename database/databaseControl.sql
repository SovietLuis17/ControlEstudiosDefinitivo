-- ============================================================
-- Sistema de Control de Estudios (UPT Valencia)
-- Script de creacion de base de datos e inicializacion segura
-- Motor: MySQL / MariaDB con InnoDB y utf8mb4
--
-- NOTA DE COMPATIBILIDAD CON PRODUCCION:
-- Este esquema conserva exactamente las 7 tablas y columnas originales
-- en produccion (`periodos`, `materias`, `plan_evaluacion`, `profesores`,
-- `secciones`, `estudiantes`, `notas`) sin requerir migraciones ni
-- alteraciones de tablas para ejecutar las 3 mejoras rapidas del sistema:
-- 1. Modulo "Mi Perfil y Cambio de Contrasena" (opera sobre `profesores`).
-- 2. Bloqueo automatico de notas e inscripciones en periodos cerrados
--    (opera leyendo `periodos.estado = 'cerrado'`).
-- 3. Control de cupo maximo por seccion y Reporte/Boletin Individual del
--    estudiante en PDF (opera sobre `estudiantes.seccion_id` y `notas`).
-- ============================================================

CREATE DATABASE IF NOT EXISTS controlEstudios
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE controlEstudios;

-- ============================================================
-- Tabla: periodos
-- Guarda los periodos academicos (ej: 2026-1, 2026-2)
-- Cuando estado = 'cerrado', el sistema bloquea en modo solo lectura
-- cualquier carga/edicion de notas o inscripcion/retiro de estudiantes.
-- ============================================================
CREATE TABLE periodos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(20) NOT NULL UNIQUE,
    estado ENUM('activo', 'cerrado') NOT NULL DEFAULT 'activo',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: materias
-- Catalogo general de materias del sistema
-- ============================================================
CREATE TABLE materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    creditos INT NOT NULL DEFAULT 3,
    descripcion TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: plan_evaluacion
-- Define las actividades y porcentajes por materia (suma = 100)
-- ============================================================
CREATE TABLE plan_evaluacion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia_id INT NOT NULL,
    nombre_actividad VARCHAR(100) NOT NULL,
    porcentaje DECIMAL(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_plan_materia FOREIGN KEY (materia_id)
        REFERENCES materias(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uk_materia_actividad UNIQUE (materia_id, nombre_actividad)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: profesores
-- Incluye tanto profesores como admins (rol = 'admin' o 'profesor')
-- ============================================================
CREATE TABLE profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('M', 'F', 'Otro') NOT NULL,
    direccion VARCHAR(200) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    especialidad VARCHAR(100) NULL,
    rol ENUM('admin', 'profesor') NOT NULL DEFAULT 'profesor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: secciones
-- Instancia de una materia en un periodo, dictada por un profesor
-- ============================================================
CREATE TABLE secciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia_id INT NOT NULL,
    profesor_id INT NOT NULL,
    periodo_id INT NOT NULL,
    codigo_seccion VARCHAR(10) NOT NULL,
    aula VARCHAR(30) NULL,
    horario VARCHAR(100) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_seccion_materia FOREIGN KEY (materia_id)
        REFERENCES materias(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_seccion_profesor FOREIGN KEY (profesor_id)
        REFERENCES profesores(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_seccion_periodo FOREIGN KEY (periodo_id)
        REFERENCES periodos(id) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT uk_seccion_periodo UNIQUE (materia_id, periodo_id, codigo_seccion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: estudiantes
-- El campo seccion_id es NULL hasta que se asigne a una seccion (relacion 1:N)
-- ============================================================
CREATE TABLE estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cedula VARCHAR(20) NOT NULL UNIQUE,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    genero ENUM('M', 'F', 'Otro') NOT NULL,
    direccion VARCHAR(200) NULL,
    telefono VARCHAR(20) NULL,
    correo VARCHAR(150) NULL,
    carrera VARCHAR(100) NULL,
    seccion_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudiante_seccion FOREIGN KEY (seccion_id)
        REFERENCES secciones(id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Tabla: notas
-- Registra la nota de un estudiante por cada actividad del plan
-- ============================================================
CREATE TABLE notas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    plan_evaluacion_id INT NOT NULL,
    nota DECIMAL(5,2) NOT NULL CHECK (nota >= 0 AND nota <= 20),
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_nota_estudiante FOREIGN KEY (estudiante_id)
        REFERENCES estudiantes(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_nota_plan FOREIGN KEY (plan_evaluacion_id)
        REFERENCES plan_evaluacion(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uk_estudiante_actividad UNIQUE (estudiante_id, plan_evaluacion_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Datos iniciales: Admin por defecto
-- Email: admin@system.com
-- Password inicial: admin123 (Cambiar tras el primer ingreso desde "Mi Perfil")
-- ============================================================
INSERT INTO profesores (cedula, nombres, apellidos, fecha_nacimiento, genero, direccion, telefono, correo, password, especialidad, rol)
VALUES (
    '00000000',
    'Administrador',
    'del Sistema',
    '1990-01-01',
    'Otro',
    'Sede principal',
    '0000000000',
    'admin@system.com',
    '$2y$10$e0MYzXyjpJS7Pd0RVvHwHe.YYr3hFZhvvAUaHMvdh6mZDH3gxPLu6',
    'Administracion',
    'admin'
);

-- ============================================================
-- Datos de prueba: Periodos
-- ============================================================
INSERT INTO periodos (nombre, estado) VALUES
('2026-1', 'activo'),
('2025-2', 'cerrado');

-- ============================================================
-- Datos de prueba: Materias
-- ============================================================
INSERT INTO materias (codigo, nombre, creditos, descripcion) VALUES
('PROGII-01', 'Programacion II', 4, 'Fundamentos de programacion orientada a objetos'),
('MATII-01', 'Matematicas II', 3, 'Calculo diferencial e integral'),
('BASE-01', 'Base de Datos', 4, 'Diseno y administracion de bases de datos relacionales');

-- ============================================================
-- Datos de prueba: Planes de evaluacion (100%)
-- ============================================================
INSERT INTO plan_evaluacion (materia_id, nombre_actividad, porcentaje) VALUES
(1, 'Parcial 1', 25.00),
(1, 'Parcial 2', 25.00),
(1, 'Proyecto Final', 30.00),
(1, 'Examen Final', 20.00);

INSERT INTO plan_evaluacion (materia_id, nombre_actividad, porcentaje) VALUES
(2, 'Parcial 1', 30.00),
(2, 'Parcial 2', 30.00),
(2, 'Examen Final', 40.00);

INSERT INTO plan_evaluacion (materia_id, nombre_actividad, porcentaje) VALUES
(3, 'Parcial 1', 25.00),
(3, 'Parcial 2', 25.00),
(3, 'Practicas', 20.00),
(3, 'Examen Final', 30.00);

-- ============================================================
-- Datos de prueba: Profesor de ejemplo (SEC-03 corregido: rol = 'profesor')
-- Email: profesor@system.com
-- Password: Profesor123* (hasheada)
-- ============================================================
INSERT INTO profesores (cedula, nombres, apellidos, fecha_nacimiento, genero, direccion, telefono, correo, password, especialidad, rol)
VALUES (
    '12345678',
    'Carlos',
    'Perez',
    '1985-05-15',
    'M',
    'Av. Principal 123',
    '04141234567',
    'profesor@system.com',
    '$2y$10$uLTIhb81mctTrGpyd/DGPOdSTR2KjrUrJfSJFGNPPr7g8xxKdigkq',
    'Programacion',
    'profesor'
);

-- ============================================================
-- Datos de prueba: Secciones (IDs 1 y 2)
-- ============================================================
INSERT INTO secciones (materia_id, profesor_id, periodo_id, codigo_seccion, aula, horario) VALUES
(1, 2, 1, 'A', 'Aula 301', 'Lunes-Miercoles 8:00-10:00'),
(2, 2, 1, 'B', 'Aula 205', 'Martes-Jueves 10:00-12:00');

-- ============================================================
-- Datos de prueba: Estudiantes (vinculados a las secciones 1 y 2)
-- ============================================================
INSERT INTO estudiantes (cedula, nombres, apellidos, fecha_nacimiento, genero, direccion, telefono, correo, carrera, seccion_id) VALUES
('20123456', 'Ana', 'Gomez', '2003-03-15', 'F', 'Calle 1', '04141111111', 'ana@email.com', 'Informatica', 1),
('20654321', 'Luis', 'Rivas', '2002-07-22', 'M', 'Calle 2', '04142222222', 'luis@email.com', 'Informatica', 1),
('20111111', 'Maria', 'Diaz', '2003-11-08', 'F', 'Calle 3', '04143333333', 'maria@email.com', 'Informatica', 2);

-- ============================================================
-- Fin del script
-- ============================================================