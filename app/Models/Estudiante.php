<?php
// app/Models/Estudiante.php
// Modelo para gestionar los estudiantes del sistema
// Un estudiante pertenece a una sola seccion (relacion 1:N mediante `estudiantes.seccion_id`),
// garantizando 100% de compatibilidad con la base de datos actual en produccion.
// Incluye soporte para busqueda por cedula, paginacion y verificacion de periodos cerrados.

namespace App\Models;

use PDO;
use PDOException;

class Estudiante
{
    // Propiedad privada que almacena la conexion PDO
    private PDO $db;

    // Constructor que recibe la conexion desde el controlador
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Obtiene todos los estudiantes con datos de su seccion, materia y periodo
    public function obtenerTodos(): array
    {
        $stmt = $this->db->query("
            SELECT 
                e.id,
                e.cedula,
                e.nombres,
                e.apellidos,
                e.fecha_nacimiento,
                TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad,
                e.genero,
                e.direccion,
                e.telefono,
                e.correo,
                e.carrera,
                e.seccion_id,
                s.codigo_seccion,
                m.nombre as materia_nombre,
                m.codigo as materia_codigo,
                pe.nombre as periodo_nombre,
                pe.estado as periodo_estado
            FROM estudiantes e
            LEFT JOIN secciones s ON e.seccion_id = s.id
            LEFT JOIN materias m ON s.materia_id = m.id
            LEFT JOIN periodos pe ON s.periodo_id = pe.id
            ORDER BY e.id DESC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene estudiantes paginados con busqueda opcional por cedula
    public function obtenerPaginados(int $limite, int $offset, string $busqueda = ''): array
    {
        if ($busqueda !== '') {
            $sql = "
                SELECT 
                    e.id,
                    e.cedula,
                    e.nombres,
                    e.apellidos,
                    e.fecha_nacimiento,
                    TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad,
                    e.genero,
                    e.direccion,
                    e.telefono,
                    e.correo,
                    e.carrera,
                    e.seccion_id,
                    s.codigo_seccion,
                    m.nombre as materia_nombre,
                    m.codigo as materia_codigo,
                    pe.nombre as periodo_nombre,
                    pe.estado as periodo_estado
                FROM estudiantes e
                LEFT JOIN secciones s ON e.seccion_id = s.id
                LEFT JOIN materias m ON s.materia_id = m.id
                LEFT JOIN periodos pe ON s.periodo_id = pe.id
                WHERE e.cedula LIKE :busqueda
                ORDER BY e.id DESC
                LIMIT $limite OFFSET $offset
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $sql = "
            SELECT 
                e.id,
                e.cedula,
                e.nombres,
                e.apellidos,
                e.fecha_nacimiento,
                TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad,
                e.genero,
                e.direccion,
                e.telefono,
                e.correo,
                e.carrera,
                e.seccion_id,
                s.codigo_seccion,
                m.nombre as materia_nombre,
                m.codigo as materia_codigo,
                pe.nombre as periodo_nombre,
                pe.estado as periodo_estado
            FROM estudiantes e
            LEFT JOIN secciones s ON e.seccion_id = s.id
            LEFT JOIN materias m ON s.materia_id = m.id
            LEFT JOIN periodos pe ON s.periodo_id = pe.id
            ORDER BY e.id DESC
            LIMIT $limite OFFSET $offset
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cuenta el total de estudiantes aplicando la busqueda opcional
    public function contarTotal(string $busqueda = ''): int
    {
        if ($busqueda !== '') {
            $sql = "
                SELECT COUNT(*) as total
                FROM estudiantes
                WHERE cedula LIKE :busqueda
            ";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':busqueda' => '%' . $busqueda . '%']);
        } else {
            $sql = "
                SELECT COUNT(*) as total
                FROM estudiantes
            ";
            $stmt = $this->db->query($sql);
        }

        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']);
    }

    // Obtiene un estudiante especifico por su id con los datos de su seccion actual
    public function obtenerPorId(int $id): array|false
    {
        $sql = "
            SELECT 
                e.*,
                TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad,
                s.codigo_seccion,
                s.aula,
                s.horario,
                m.id as materia_id,
                m.codigo as materia_codigo,
                m.nombre as materia_nombre,
                m.creditos as materia_creditos,
                pe.nombre as periodo_nombre,
                pe.estado as periodo_estado,
                CONCAT(p.nombres, ' ', p.apellidos) as profesor_nombre
            FROM estudiantes e
            LEFT JOIN secciones s ON e.seccion_id = s.id
            LEFT JOIN materias m ON s.materia_id = m.id
            LEFT JOIN periodos pe ON s.periodo_id = pe.id
            LEFT JOIN profesores p ON s.profesor_id = p.id
            WHERE e.id = :id
            LIMIT 1
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Obtiene los estudiantes inscritos en una seccion especifica (`estudiantes.seccion_id`)
    public function obtenerPorSeccion(int $seccionId): array
    {
        $sql = "
            SELECT 
                e.id,
                e.cedula,
                e.nombres,
                e.apellidos,
                e.carrera,
                e.correo,
                TIMESTAMPDIFF(YEAR, e.fecha_nacimiento, CURDATE()) as edad
            FROM estudiantes e
            WHERE e.seccion_id = :seccion_id
            ORDER BY e.apellidos ASC, e.nombres ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':seccion_id' => $seccionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Obtiene estudiantes que NO tienen seccion asignada (`WHERE seccion_id IS NULL`)
    // Se usa en el modal de inscripcion para listar disponibles sin tocar el esquema de BD
    public function obtenerSinSeccion(): array
    {
        $stmt = $this->db->query("
            SELECT 
                id,
                cedula,
                nombres,
                apellidos,
                carrera
            FROM estudiantes
            WHERE seccion_id IS NULL
            ORDER BY apellidos ASC, nombres ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Verifica si la seccion actual del estudiante pertenece a un periodo cerrado
    public function tieneInscripcionEnPeriodoCerrado(int $estudianteId): bool
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes e
            INNER JOIN secciones s ON e.seccion_id = s.id
            INNER JOIN periodos pe ON s.periodo_id = pe.id
            WHERE e.id = :estudiante_id
              AND pe.estado = 'cerrado'
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':estudiante_id' => $estudianteId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']) > 0;
    }

    // Verifica si una cedula ya existe en estudiantes
    public function existeCedula(string $cedula): bool
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes
            WHERE cedula = :cedula
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':cedula' => $cedula]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Verifica si una cedula existe exceptuando un id (para ediciones)
    public function existeCedulaExceptoId(string $cedula, int $idExcluir): bool
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes
            WHERE cedula = :cedula AND id != :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':cedula' => $cedula,
            ':id' => $idExcluir
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Verifica si un correo ya existe (solo si el correo no esta vacio)
    public function existeCorreo(string $correo): bool
    {
        if (empty($correo)) {
            return false;
        }
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes
            WHERE correo = :correo
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':correo' => $correo]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Verifica si un correo existe exceptuando un id (para ediciones)
    public function existeCorreoExceptoId(string $correo, int $idExcluir): bool
    {
        if (empty($correo)) {
            return false;
        }
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes
            WHERE correo = :correo AND id != :id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':id' => $idExcluir
        ]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return $resultado['total'] > 0;
    }

    // Crea un nuevo estudiante
    public function crear(array $datos): bool
    {
        try {
            $sql = "
                INSERT INTO estudiantes (cedula, nombres, apellidos, fecha_nacimiento, 
                                        genero, direccion, telefono, correo, carrera)
                VALUES (:cedula, :nombres, :apellidos, :fecha_nacimiento,
                        :genero, :direccion, :telefono, :correo, :carrera)
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':cedula' => $datos['cedula'],
                ':nombres' => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nacimiento' => $datos['fecha_nacimiento'],
                ':genero' => $datos['genero'],
                ':direccion' => $datos['direccion'],
                ':telefono' => $datos['telefono'],
                ':correo' => $datos['correo'],
                ':carrera' => $datos['carrera']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Actualiza un estudiante existente
    public function actualizar(int $id, array $datos): bool
    {
        try {
            $sql = "
                UPDATE estudiantes
                SET cedula = :cedula,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    fecha_nacimiento = :fecha_nacimiento,
                    genero = :genero,
                    direccion = :direccion,
                    telefono = :telefono,
                    correo = :correo,
                    carrera = :carrera
                WHERE id = :id
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':id' => $id,
                ':cedula' => $datos['cedula'],
                ':nombres' => $datos['nombres'],
                ':apellidos' => $datos['apellidos'],
                ':fecha_nacimiento' => $datos['fecha_nacimiento'],
                ':genero' => $datos['genero'],
                ':direccion' => $datos['direccion'],
                ':telefono' => $datos['telefono'],
                ':correo' => $datos['correo'],
                ':carrera' => $datos['carrera']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Elimina un estudiante por su id
    public function eliminar(int $id): bool
    {
        try {
            $sql = "
                DELETE FROM estudiantes
                WHERE id = :id
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':id' => $id]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Asigna un estudiante a una seccion (`estudiantes.seccion_id`)
    public function asignarSeccion(int $estudianteId, int $seccionId): bool
    {
        try {
            $sql = "
                UPDATE estudiantes
                SET seccion_id = :seccion_id
                WHERE id = :estudiante_id
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([
                ':seccion_id' => $seccionId,
                ':estudiante_id' => $estudianteId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Quita un estudiante de su seccion actual (`SET seccion_id = NULL`)
    public function quitarSeccion(int $estudianteId): bool
    {
        try {
            $sql = "
                UPDATE estudiantes
                SET seccion_id = NULL
                WHERE id = :estudiante_id
            ";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([':estudiante_id' => $estudianteId]);
        } catch (PDOException $e) {
            return false;
        }
    }

    // Cuenta cuantos estudiantes tiene una seccion (`WHERE seccion_id = :seccion_id`)
    public function contarPorSeccion(int $seccionId): int
    {
        $sql = "
            SELECT COUNT(*) as total
            FROM estudiantes
            WHERE seccion_id = :seccion_id
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':seccion_id' => $seccionId]);
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
        return intval($resultado['total']);
    }

    // Obtiene los inscritos de una seccion con todos sus datos
    // Se usa exclusivamente para el reporte Excel de inscritos
    public function obtenerInscritosPorSeccion(int $seccionId): array
    {
        $sql = "
            SELECT 
                id,
                cedula,
                nombres,
                apellidos,
                TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad,
                genero,
                carrera,
                correo,
                telefono
            FROM estudiantes
            WHERE seccion_id = :seccion_id
            ORDER BY apellidos ASC, nombres ASC
        ";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':seccion_id' => $seccionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}