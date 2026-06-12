<?php

declare(strict_types=1);

final class RequisitoModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function byConvocatoria(int $idConvocatoria): array
    {
        $stmt = $this->db->prepare('SELECT * FROM requisitos WHERE id_convocatoria = :id ORDER BY id ASC');
        $stmt->execute([':id' => $idConvocatoria]);
        return $stmt->fetchAll();
    }

    public function replaceForConvocatoria(int $idConvocatoria, array $requisitos): void
    {
        $del = $this->db->prepare('DELETE FROM requisitos WHERE id_convocatoria = :id');
        $del->execute([':id' => $idConvocatoria]);

        $ins = $this->db->prepare('
            INSERT INTO requisitos (id_convocatoria, descripcion, obligatorio)
            VALUES (:id_convocatoria, :descripcion, :obligatorio)
        ');

        foreach ($requisitos as $r) {
            $ins->execute([
                ':id_convocatoria' => $idConvocatoria,
                ':descripcion' => $r['descripcion'],
                ':obligatorio' => $r['obligatorio'] ? 1 : 0,
            ]);
        }
    }
}

