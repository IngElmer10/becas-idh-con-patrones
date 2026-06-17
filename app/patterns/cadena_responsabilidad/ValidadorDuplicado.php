<?php

declare(strict_types=1);

// Validador para no subir documentos duplicados
final class ValidadorDuplicado extends ManejadorDocumento
{
    // Verifica si ya existe el documento y si no se ha marcado para reemplazar
    public function procesar(array $archivo, array $contexto): void
    {
        $idRequisito = (int)($contexto['id_requisito'] ?? 0);
        $replace     = (bool)($contexto['replace'] ?? false);
        $docMap      = (array)($contexto['docMap'] ?? []);

        if (!$replace && isset($docMap[$idRequisito])) {
            throw new \RuntimeException(
                'Ya existe un documento para este requisito. ' .
                'Marca la opción "Reemplazar" si deseas subirlo de nuevo.'
            );
        }

        $this->pasarAlSiguiente($archivo, $contexto);
    }
}
