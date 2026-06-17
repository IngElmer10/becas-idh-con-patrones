<?php

declare(strict_types=1);

// Observador concreto para guardar logs de la publicacion
final class LoggerAuditoriaPublicacion implements Observador
{
    private string $rutaLog;

    public function __construct(?string $rutaLog = null)
    {
        $this->rutaLog = $rutaLog
            ?? __DIR__ . '/../../../logs/auditoria_publicaciones.log';
    }

    // Guarda una linea de auditoria en el archivo log
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
