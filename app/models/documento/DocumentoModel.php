<?php

declare(strict_types=1);

final class DocumentoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function estadoPorRequisito(int $idPostulacion): array
    {
        $stmt = $this->db->prepare('SELECT id_requisito, estado, observacion, ruta_archivo FROM documentos WHERE id_postulacion = :p');
        $stmt->execute([':p' => $idPostulacion]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[(int)$r['id_requisito']] = $r;
        }
        return $map;
    }

    public function upsert(int $idPostulacion, int $idRequisito, string $rutaArchivo, string $estado = 'recibido', ?string $observacion = null): void
    {
        $stmt = $this->db->prepare('
            INSERT INTO documentos (id_postulacion, id_requisito, ruta_archivo, estado, observacion)
            VALUES (:p, :r, :ruta, :estado, :obs)
            ON DUPLICATE KEY UPDATE
                ruta_archivo = VALUES(ruta_archivo),
                estado = VALUES(estado),
                observacion = VALUES(observacion)
        ');
        $stmt->execute([
            ':p' => $idPostulacion,
            ':r' => $idRequisito,
            ':ruta' => $rutaArchivo,
            ':estado' => $estado,
            ':obs' => $observacion,
        ]);
    }

    public function setRevision(int $idPostulacion, int $idRequisito, string $estado, ?string $observacion): void
    {
        $stmt = $this->db->prepare('
            UPDATE documentos
            SET estado = :e, observacion = :o
            WHERE id_postulacion = :p AND id_requisito = :r
        ');
        $stmt->execute([
            ':e' => $estado,
            ':o' => $observacion,
            ':p' => $idPostulacion,
            ':r' => $idRequisito,
        ]);
    }
}

