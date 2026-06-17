<?php

declare(strict_types=1);

/**
 * ValidadorExtension — Eslabón 2 de la Cadena de Responsabilidad (GoF).
 *
 * Valida que el archivo subido tenga una extensión permitida
 * (pdf, jpg, jpeg, png).
 *
 * Patrón: Chain of Responsibility
 * Caso de uso: CU04 Cargar Documentación
 */
final class ValidadorExtension extends ManejadorDocumento
{
    /** @var string[] Extensiones permitidas */
    private array $extensionesPermitidas;

    public function __construct(array $extensionesPermitidas = ['pdf', 'jpg', 'jpeg', 'png'])
    {
        $this->extensionesPermitidas = $extensionesPermitidas;
    }

    /**
     * Verifica que el nombre del archivo tenga una extensión válida.
     *
     * @param array $archivo  Entrada de $_FILES['archivo']
     * @param array $contexto Datos del contexto (id_postulacion, id_requisito, etc.)
     * @throws \RuntimeException si la extensión no está permitida
     */
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
