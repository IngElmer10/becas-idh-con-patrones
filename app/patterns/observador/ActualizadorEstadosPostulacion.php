<?php

declare(strict_types=1);

// Observador concreto para actualizar los estados de las postulaciones en la BD
final class ActualizadorEstadosPostulacion implements Observador
{
    private PostulacionModel $postulacionModel;

    public function __construct()
    {
        $this->postulacionModel = new PostulacionModel();
    }

    // Actualiza los estados segun el numero de cupos
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
