<?php

declare(strict_types=1);

// Validador de las extensiones de archivos permitidas
final class ValidadorExtension extends ManejadorDocumento
{
    // Array de extensiones validas
    private array $extensionesPermitidas;

    public function __construct(array $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'])
    {
        $this->extensionesPermitidas = $extensionesPermitidas;
    }

    // Valida que el archivo tenga una extension correcta
    public function procesar(array $archivo, array $contexto): void
    {
        $nombre = (string)($archivo['name'] ?? '');
        $ext    = strtolower(pathinfo($nombre, PATHINFO_EXTENSION));

        if (!in_array($ext, $this->extensionesPermitidas, true)) {
            throw new \RuntimeException(
                sprintf(
                    'Formato no permitido: ".%s". Se aceptan: %s.',
                    $ext,
                    implode(', ', $this->extensionesPermitidas)
                )
            );
        }

        $this->pasarAlSiguiente($archivo, $contexto);
    }
}
