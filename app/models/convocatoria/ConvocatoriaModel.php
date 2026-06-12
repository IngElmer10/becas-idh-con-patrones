<?php

declare(strict_types=1);

final class ConvocatoriaModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function all(): array
    {
        $stmt = $this->db->query('SELECT * FROM convocatorias ORDER BY id DESC');
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM convocatorias WHERE id = :id');
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function abiertasEnPlazo(): array
    {
        $stmt = $this->db->query('
            SELECT * FROM convocatorias
            WHERE estado = "abierta"
              AND fecha_inicio <= CURDATE()
              AND fecha_fin >= CURDATE()
            ORDER BY id DESC
        ');
        return $stmt->fetchAll();
    }

    public function existsDuplicate(string $nombre, int $gestion, string $tipoBeca, ?int $excludeId = null): bool
    {
        $sql = 'SELECT id FROM convocatorias WHERE nombre = :nombre AND gestion = :gestion AND tipo_beca = :tipo_beca';
        $params = [
            ':nombre' => $nombre,
            ':gestion' => $gestion,
            ':tipo_beca' => $tipoBeca,
        ];
        if ($excludeId !== null) {
            $sql .= ' AND id <> :exclude_id';
            $params[':exclude_id'] = $excludeId;
        }

        $stmt = $this->db->prepare($sql . ' LIMIT 1');
        $stmt->execute($params);
        return (bool)$stmt->fetch();
    }

    public function create(array $data): int
    {
        $stmt = $this->db->prepare('
            INSERT INTO convocatorias (nombre, gestion, tipo_beca, fecha_inicio, fecha_fin, estado)
            VALUES (:nombre, :gestion, :tipo_beca, :fecha_inicio, :fecha_fin, :estado)
        ');
        $stmt->execute([
            ':nombre' => $data['nombre'],
            ':gestion' => $data['gestion'],
            ':tipo_beca' => $data['tipo_beca'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':estado' => $data['estado'],
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $stmt = $this->db->prepare('
            UPDATE convocatorias
            SET nombre = :nombre,
                gestion = :gestion,
                tipo_beca = :tipo_beca,
                fecha_inicio = :fecha_inicio,
                fecha_fin = :fecha_fin,
                estado = :estado
            WHERE id = :id
        ');
        $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':gestion' => $data['gestion'],
            ':tipo_beca' => $data['tipo_beca'],
            ':fecha_inicio' => $data['fecha_inicio'],
            ':fecha_fin' => $data['fecha_fin'],
            ':estado' => $data['estado'],
        ]);
    }

    public function close(int $id): void
    {
        $stmt = $this->db->prepare('UPDATE convocatorias SET estado = :estado WHERE id = :id');
        $stmt->execute([':estado' => 'cerrada', ':id' => $id]);
    }

    public function createWithRequisitos(array $convocatoria, array $requisitos): int
    {
        $reqModel = new RequisitoModel();
        $this->db->beginTransaction();
        try {
            $id = $this->create($convocatoria);
            $reqModel->replaceForConvocatoria($id, $requisitos);
            $this->db->commit();
            return $id;
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }

    public function updateWithRequisitos(int $id, array $convocatoria, array $requisitos): void
    {
        $reqModel = new RequisitoModel();
        $this->db->beginTransaction();
        try {
            $this->update($id, $convocatoria);
            $reqModel->replaceForConvocatoria($id, $requisitos);
            $this->db->commit();
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) $this->db->rollBack();
            throw $e;
        }
    }
}

