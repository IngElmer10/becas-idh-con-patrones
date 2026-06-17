<?php

declare(strict_types=1);

// Interfaz para los observadores
interface Observador
{
    // Se ejecuta de manera automatica cuando el sujeto notifica cambios
    public function actualizar(array $datos): void;
}
