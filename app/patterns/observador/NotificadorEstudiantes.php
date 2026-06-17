<?php

declare(strict_types=1);

// Observador concreto para simular el envio de correos
final class NotificadorEstudiantes implements Observador
{
    private string $rutaLog;

    public function __construct(?string $rutaLog = null)
    {
        $this->rutaLog = $rutaLog
            ?? __DIR__ . '/../../../logs/mail_queue.log';
    }

    // Escribe los correos simulados en un archivo de texto
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
