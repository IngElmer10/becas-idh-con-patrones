<?php

declare(strict_types=1);

/**
 * ValidadorTamano — Eslabón 3 de la Cadena de Responsabilidad (GoF).
 *
 * Valida que el archivo subido no supere el tamaño máximo permitido.
 * El límite por defecto es 5 MB (5 × 1024 × 1024 bytes).
 *
 * Patrón: Chain of Responsibility
 * Caso de uso: CU04 Cargar Documentación
 */
final class ValidadorTamano extends ManejadorDocumento
{
    private int $maxBytes;

    public function __construct(int $maxBytes = 5 * 1024 * 1024)
    {
        $this->maxBytes = $maxBytes;
    }

    /**
     * Verifica que el tamaño del archivo no supere el límite configurado.
     *
     * @param array $archivo  Entrada de $_FILES['archivo']
     * @param array $contexto Datos del contexto
     * @throws \RuntimeException si el archivo es demasiado grande
     */
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
