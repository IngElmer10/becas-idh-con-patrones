<?php

declare(strict_types=1);

/**
 * ValidadorDuplicado — Eslabón 1 de la Cadena de Responsabilidad (GoF).
 *
 * Verifica que no exista ya un documento para el requisito dado dentro de la
 * postulación, a menos que el estudiante haya marcado explícitamente la opción
 * de reemplazo ($contexto['replace'] === true).
 *
 * Este eslabón NO necesita acceder a la base de datos directamente; recibe
 * el mapa de documentos ya cargados ($contexto['docMap']) que el controlador
 * obtiene desde DocumentoModel::estadoPorRequisito().
 *
 * Patrón: Chain of Responsibility
 * Caso de uso: CU04 Cargar Documentación
 */
final class ValidadorDuplicado extends ManejadorDocumento
{
    /**
     * Verifica que el documento no esté duplicado para la postulación.
     *
     * @param array $archivo  Entrada de $_FILES['archivo']
     * @param array $contexto Debe contener:
     *                        - 'id_requisito' (int)
     *                        - 'replace'      (bool)
     *                        - 'docMap'       (array)  mapa [id_requisito => fila]
     * @throws \RuntimeException si existe un documento previo y replace es false
     */
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
