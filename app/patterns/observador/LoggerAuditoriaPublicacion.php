<?php

declare(strict_types=1);

/**
 * LoggerAuditoriaPublicacion — Observador Concreto 2 (GoF Observer).
 *
 * Al recibir la notificación de publicación, registra en un archivo de log
 * que los resultados de la convocatoria fueron publicados oficialmente.
 * Incluye: fecha/hora UTC, id_convocatoria, cupos y número de postulaciones
 * procesadas.
 *
 * El archivo de log se escribe en: /logs/auditoria_publicaciones.log
 * (relativo a la raíz del proyecto). La carpeta se crea automáticamente si
 * no existe.
 *
 * Patrón: Observer
 * Caso de uso: CU08 Publicar Resultados
 */
final class LoggerAuditoriaPublicacion implements Observador
{
    private string $rutaLog;

    public function __construct(?string $rutaLog = null)
    {
        // Ruta por defecto: <raiz_proyecto>/logs/auditoria_publicaciones.log
        $this->rutaLog = $rutaLog
            ?? __DIR__ . '/../../../logs/auditoria_publicaciones.log';
    }

    /**
     * Escribe una entrada de auditoría en el archivo de log.
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
        $total = count((array) ($datos['evaluadas'] ?? []));
        $timestamp = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');

        $linea = sprintf(
            "[AUDITORÍA] %s UTC | Convocatoria #%d | Cupos: %d | Postulaciones procesadas: %d\n",
            $timestamp,
            $idConv,
            $cupos,
            $total
        );

        file_put_contents($this->rutaLog, $linea, FILE_APPEND | LOCK_EX);
    }
}
