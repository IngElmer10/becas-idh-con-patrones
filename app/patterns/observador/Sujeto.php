<?php

declare(strict_types=1);

/**
 * Sujeto — Interfaz del participante Sujeto (Subject) del patrón GoF Observer.
 *
 * Define el contrato de gestión de observadores y notificación de eventos.
 * Es implementada por ResultadoController, que actúa como sujeto observable
 * cuando publica los resultados de una convocatoria.
 *
 * Patrón: Observer
 * Caso de uso: CU08 Publicar Resultados
 */
interface Sujeto
{
    /**
     * Registra un observador en la lista de suscriptores.
     */
    public function adjuntar(Observador $observador): void;

    /**
     * Elimina un observador de la lista de suscriptores.
     */
    public function desadjuntar(Observador $observador): void;

    /**
     * Notifica a todos los observadores registrados enviándoles los datos del evento.
     *
     * @param array $datos Contexto del evento de publicación
     */
    public function notificar(array $datos): void;
}
