<?php

declare(strict_types=1);

/**
 * Observador — Interfaz del participante Observador (GoF Observer).
 *
 * Todos los observadores concretos deben implementar esta interfaz para
 * recibir notificaciones del Sujeto (ResultadoController) cuando los
 * resultados de una convocatoria son publicados oficialmente.
 *
 * Patrón: Observer
 * Caso de uso: CU08 Publicar Resultados
 */
interface Observador
{
    /**
     * Método invocado automáticamente por el Sujeto al producirse el evento.
     *
     * @param array $datos Contexto del evento:
     *                     - 'id_convocatoria' (int)
     *                     - 'cupos'           (int)
     *                     - 'evaluadas'       (array) postulaciones evaluadas ordenadas por puntaje
     */
    public function actualizar(array $datos): void;
}
