<?php

declare(strict_types=1);

/**
 * ActualizadorEstadosPostulacion — Observador Concreto 1 (GoF Observer).
 *
 * Al recibir la notificación de publicación, actualiza de forma masiva
 * el estado final de cada postulación evaluada en la tabla 'postulaciones',
 * marcándolas como 'seleccionado' o 'no_seleccionado' según el orden de
 * puntaje y el número de cupos disponibles.
 *
 * Este observador usa la misma instancia PDO (Database::getInstance()) que
 * el resto de los modelos, garantizando la integridad dentro de la transacción
 * abierta por ResultadoController.
 *
 * Patrón: Observer
 * Caso de uso: CU08 Publicar Resultados
 */
final class ActualizadorEstadosPostulacion implements Observador
{
    private PostulacionModel $postulacionModel;

    public function __construct()
    {
        $this->postulacionModel = new PostulacionModel();
    }

    /**
     * Actualiza masivamente el estado de cada postulación evaluada.
     *
     * @param array $datos Contexto del evento:
     *                     - 'cupos'     (int)   número de becas disponibles
     *                     - 'evaluadas' (array) filas ordenadas desc por puntaje
     */
    public function actualizar(array $datos): void
    {
        $cupos    = (int)($datos['cupos'] ?? 0);
        $evaluadas = (array)($datos['evaluadas'] ?? []);

        foreach ($evaluadas as $idx => $postulacion) {
            $estadoFinal = ($idx < $cupos) ? 'seleccionado' : 'no_seleccionado';
            $this->postulacionModel->setEstado((int)$postulacion['id'], $estadoFinal);
        }
    }
}
