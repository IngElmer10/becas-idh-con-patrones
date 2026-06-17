<?php

declare(strict_types=1);

// Clase base para la cadena de responsabilidad
// Define la estructura para enlazar los validadores del archivo
abstract class ManejadorDocumento
{
    private ?ManejadorDocumento $siguiente = null;

    // Guarda el siguiente validador en la cadena
    public function setNext(ManejadorDocumento $manejador): ManejadorDocumento
    {
        $this->siguiente = $manejador;
        return $manejador;
    }

    // Para procesar la validacion. Si falla lanza una excepcion
    abstract public function procesar(array $archivo, array $contexto): void;

    // Pasa al siguiente validador si hay uno configurado
    protected function pasarAlSiguiente(array $archivo, array $contexto): void
    {
        if ($this->siguiente !== null) {
            $this->siguiente->procesar($archivo, $contexto);
        }
    }
}
