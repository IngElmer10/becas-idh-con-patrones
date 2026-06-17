<?php

declare(strict_types=1);

/**
 * ManejadorDocumento — Clase abstracta base del patrón Cadena de Responsabilidad (GoF).
 *
 * Define la interfaz común de todos los eslabones de validación y gestiona
 * el encadenamiento al siguiente manejador.
 *
 * Patrón: Chain of Responsibility
 * Caso de uso: CU04 Cargar Documentación
 */
abstract class ManejadorDocumento
{
    private ?ManejadorDocumento $siguiente = null;

    /**
     * Establece el siguiente eslabón en la cadena y lo devuelve
     * para permitir encadenamiento fluido:
     *   $a->setNext($b)->setNext($c)
     */
    public function setNext(ManejadorDocumento $manejador): ManejadorDocumento
    {
        $this->siguiente = $manejador;
        return $manejador;
    }

    /**
     * Procesa la solicitud. Si este eslabón no la rechaza, la pasa al siguiente.
     * Lanza una RuntimeException con el mensaje de error si la validación falla.
     *
     * @param array $archivo  Entrada de $_FILES['archivo']
     * @param array $contexto Datos adicionales: ['id_postulacion', 'id_requisito', 'replace', 'docMap']
     * @throws \RuntimeException cuando la validación falla en este eslabón
     */
    abstract public function procesar(array $archivo, array $contexto): void;

    /**
     * Delega al siguiente eslabón si existe. Los eslabones concretos deben
     * llamar a este método al final de su implementación de procesar().
     */
    protected function pasarAlSiguiente(array $archivo, array $contexto): void
    {
        if ($this->siguiente !== null) {
            $this->siguiente->procesar($archivo, $contexto);
        }
    }
}
