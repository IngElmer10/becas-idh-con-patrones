<?php

declare(strict_types=1);

/**
 * NotificadorEstudiantes — Observador Concreto 3 (GoF Observer).
 *
 * Al recibir la notificación de publicación, simula el envío de correos
 * escribiendo una cola de mensajes personalizados en el archivo 'logs/mail_queue.log'
 * para cada estudiante evaluado.
 *
 * Patrón: Observer
 * Caso de uso: CU08 Publicar Resultados
 */
final class NotificadorEstudiantes implements Observador
{
    private string $rutaLog;

    public function __construct(?string $rutaLog = null)
    {
        // Ruta por defecto: <raiz_proyecto>/logs/mail_queue.log
        $this->rutaLog = $rutaLog
            ?? __DIR__ . '/../../../logs/mail_queue.log';
    }

    /**
     * Simula el envío de correos escribiendo en la cola de mensajes.
     *
     * @param array $datos Contexto del evento:
     *                     - 'id_convocatoria' (int)
     *                     - 'cupos'           (int)
     *                     - 'evaluadas'       (array)
     */
    public function actualizar(array $datos): void
    {
        $dir = dirname($this->rutaLog);
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $idConv = (int) ($datos['id_convocatoria'] ?? 0);
        $cupos = (int) ($datos['cupos'] ?? 0);
        $evaluadas = (array) ($datos['evaluadas'] ?? []);
        $timestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $contenido = "";
        foreach ($evaluadas as $idx => $p) {
            $seleccionado = $idx < $cupos;
            $estadoStr = $seleccionado ? 'SELECCIONADO' : 'NO SELECCIONADO';
            $nombre = $p['estudiante_nombre'] ?? 'Estudiante';
            $codigo = $p['estudiante_codigo'] ?? 'N/A';
            $puntaje = $p['puntaje'] ?? 0.0;

            $contenido .= sprintf(
                "[%s UTC] Correo en cola para: %s (Código: %s) | Convocatoria #%d | Puntaje: %s | Estado: %s\n" .
                "Cuerpo: Estimado(a) %s, le informamos que el resultado de su postulación para la convocatoria #%d es: %s con un puntaje de %s.\n" .
                "--------------------------------------------------------------------------------\n",
                $timestamp,
                $nombre,
                $codigo,
                $idConv,
                $puntaje,
                $estadoStr,
                $nombre,
                $idConv,
                $estadoStr,
                $puntaje
            );
        }

        file_put_contents($this->rutaLog, $contenido, FILE_APPEND | LOCK_EX);
    }
}
