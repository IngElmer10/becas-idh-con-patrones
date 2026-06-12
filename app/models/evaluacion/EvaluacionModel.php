<?php

declare(strict_types=1);

final class EvaluacionModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByPostulacion(int $idPostulacion): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM evaluaciones WHERE id_postulacion = :p LIMIT 1');
        $stmt->execute([':p' => $idPostulacion]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function upsert(array $data): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO evaluaciones (id_postulacion, id_evaluador, puntaje, criterios_json, observaciones, estado)
            VALUES (:p, :e, :puntaje, :criterios, :obs, :estado)
            ON DUPLICATE KEY UPDATE
                id_evaluador = VALUES(id_evaluador),
                puntaje = VALUES(puntaje),
                criterios_json = VALUES(criterios_json),
                observaciones = VALUES(observaciones),
                estado = VALUES(estado)
        ');
        $stmt->execute([
            ':p' => $data['id_postulacion'],
            ':e' => $data['id_evaluador'],
            ':puntaje' => $data['puntaje'],
            ':criterios' => $data['criterios_json'],
            ':obs' => $data['observaciones'],
            ':estado' => $data['estado'],
        ]);
    }
}

