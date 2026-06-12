<?php

declare(strict_types=1);

final class UsuarioModel
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function findByCodigo(string $codigo): ?array
    {
        $stmt = $this->db->prepare('SELECT * FROM usuarios WHERE codigo = :codigo LIMIT 1');
        $stmt->execute([':codigo' => $codigo]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}

