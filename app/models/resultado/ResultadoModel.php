<?php

declare(strict_types=1);

final class ResultadoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function publicadosPorUsuario(int $idUsuario): array
    {
        $stmt = $this->db->prepare('
            SELECT r.*, c.nombre AS convocatoria_nombre, c.gestion, c.tipo_beca
            FROM resultados r
            INNER JOIN postulaciones p ON p.id = r.id_postulacion
            INNER JOIN convocatorias c ON c.id = r.id_convocatoria
            WHERE p.id_usuario = :u AND r.publicado = 1
            ORDER BY r.id DESC
        ');
        $stmt->execute([':u' => $idUsuario]);
        return $stmt->fetchAll();
    }

    public function upsert(array $data): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO resultados (id_convocatoria, id_postulacion, puntaje_final, estado_final, publicado)
            VALUES (:c, :p, :puntaje, :estado_final, :pub)
            ON DUPLICATE KEY UPDATE
                puntaje_final = VALUES(puntaje_final),
                estado_final = VALUES(estado_final),
                publicado = VALUES(publicado)
        ');
        $stmt->execute([
            ':c' => $data['id_convocatoria'],
            ':p' => $data['id_postulacion'],
            ':puntaje' => $data['puntaje_final'],
            ':estado_final' => $data['estado_final'],
            ':pub' => $data['publicado'],
        ]);
    }
}

