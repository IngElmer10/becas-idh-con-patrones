<?php

declare(strict_types=1);

final class PostulacionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM postulaciones WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function findForUserConv(int $idUsuario, int $idConvocatoria): ?array
    {
        $stmt = $this->db->prepare('
            SELECT * FROM postulaciones
            WHERE id_usuario = :u AND id_convocatoria = :c
            LIMIT 1
        ');
        $stmt->execute([':u' => $idUsuario, ':c' => $idConvocatoria]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO postulaciones (id_usuario, id_convocatoria, telefono, direccion, cuenta_bancaria, estado)
            VALUES (:id_usuario, :id_convocatoria, :telefono, :direccion, :cuenta_bancaria, :estado)
        ');
        $stmt->execute([
            ':id_usuario' => $data['id_usuario'],
            ':id_convocatoria' => $data['id_convocatoria'],
            ':telefono' => $data['telefono'],
            ':direccion' => $data['direccion'],
            ':cuenta_bancaria' => $data['cuenta_bancaria'],
            ':estado' => $data['estado'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function byUser(int $idUsuario): array
    {
        $stmt = $this->db->prepare('
            SELECT p.*, c.nombre AS convocatoria_nombre, c.gestion, c.tipo_beca, c.estado AS convocatoria_estado
            FROM postulaciones p
            INNER JOIN convocatorias c ON c.id = p.id_convocatoria
            WHERE p.id_usuario = :u
            ORDER BY p.id DESC
        ');
        $stmt->execute([':u' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function setEstado(int $id, string $estado): void
    {
        $stmt = $this->db->prepare('UPDATE postulaciones SET estado = :e WHERE id = :id');
        $stmt->execute([':e' => $estado, ':id' => $id]);
    }

    public function pendientesRevision(): array
    {
        $stmt = $this->db->query('
            SELECT DISTINCT p.*, u.nombre AS estudiante_nombre, u.codigo AS estudiante_codigo, c.nombre AS convocatoria_nombre
            FROM postulaciones p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            INNER JOIN convocatorias c ON c.id = p.id_convocatoria
            INNER JOIN documentos d ON d.id_postulacion = p.id
            WHERE p.estado IN ("pendiente_documentos","en_revision_inicial","observada")
            ORDER BY p.id DESC
        ');
        return $stmt->fetchAll();
    }

    public function aptasEvaluacion(): array
    {
        $stmt = $this->db->query('
            SELECT p.*, u.nombre AS estudiante_nombre, u.codigo AS estudiante_codigo, c.nombre AS convocatoria_nombre
            FROM postulaciones p
            INNER JOIN usuarios u ON u.id = p.id_usuario
            INNER JOIN convocatorias c ON c.id = p.id_convocatoria
            WHERE p.estado IN ("doc_validada","apta_evaluacion","evaluada")
            ORDER BY p.id DESC
        ');
        return $stmt->fetchAll();
    }

    public function evaluadasPorConvocatoria(int $idConvocatoria): array
    {
        $stmt = $this->db->prepare('
            SELECT p.*, e.puntaje, e.estado AS eval_estado, u.nombre AS estudiante_nombre, u.codigo AS estudiante_codigo
            FROM postulaciones p
            INNER JOIN evaluaciones e ON e.id_postulacion = p.id AND e.estado = "final"
            INNER JOIN usuarios u ON u.id = p.id_usuario
            WHERE p.id_convocatoria = :c
            ORDER BY e.puntaje DESC, p.id ASC
        ');
        $stmt->execute([':c' => $idConvocatoria]);
        return $stmt->fetchAll();
    }

    public function countByConvocatoria(int $idConvocatoria): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) AS total FROM postulaciones WHERE id_convocatoria = :c');
        $stmt->execute([':c' => $idConvocatoria]);
        return (int)($stmt->fetch()['total'] ?? 0);
    }
}

