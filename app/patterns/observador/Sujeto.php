<?php

declare(strict_types=1);

// Interfaz para el sujeto observable (el que notifica cambios)
interface Sujeto
{
    // Agrega un observador a la lista
    public function adjuntar(Observador $observador): void;

    // Quita un observador de la lista
    public function desadjuntar(Observador $observador): void;

    // Notifica a los observadores pasandoles los datos
    public function notificar(array $datos): void;
}
