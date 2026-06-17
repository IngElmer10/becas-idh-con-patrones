<?php

declare(strict_types=1);

// Validador de tamano maximo del archivo
final class ValidadorTamano extends ManejadorDocumento
{
    private int $maxBytes;

    public function __construct(int $maxBytes = 5 * 1024 * 1024)
    {
        $this->maxBytes = $maxBytes;
    }

    // Comprueba que el archivo no supere el tamano limite
    public function procesar(array $archivo, array $contexto): void
    {
        $size = (int)($archivo['size'] ?? 0);

        if ($size > $this->maxBytes) {
            $limiteMB = round($this->maxBytes / 1024 / 1024, 1);
            throw new \RuntimeException(
                sprintf('El archivo excede el tamaño máximo permitido de %s MB.', $limiteMB)
            );
        }

        $this->pasarAlSiguiente($archivo, $contexto);
    }
}
