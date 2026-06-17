<?php

declare(strict_types=1);

/**
 * ResultadoController — CU08 Publicar Resultados.
 *
 * REFACTORIZADO con el patrón Observador (GoF).
 *
 * El controlador implementa la interfaz Sujeto, gestionando una lista de
 * observadores. Al confirmar la publicación, el método publicar() invoca
 * notificar(), que dispara en cadena a todos los observadores registrados
 *   (ActualizadorEstadosPostulacion, LoggerAuditoriaPublicacion y NotificadorEstudiantes) antes de
 * enviar la respuesta final a la vista.
 *
 * La lógica de negocio original (transacción PDO, upsert en ResultadoModel)
 * se preserva íntegramente; sólo se traslada la actualización masiva de estados
 * de postulaciones y el logging al interior de los observadores.
 */
final class ResultadoController implements Sujeto
{
    /** @var Observador[] Lista de observadores suscritos */
    private array $observadores = [];

    // -------------------------------------------------------------------------
    // Implementación de la interfaz Sujeto
    // -------------------------------------------------------------------------

    public function adjuntar(Observador $observador): void
    {
        // Evita duplicados usando comparación por identidad de objeto
        if (!in_array($observador, $this->observadores, true)) {
            $this->observadores[] = $observador;
        }
    }

    public function desadjuntar(Observador $observador): void
    {
        $this->observadores = array_values(
            array_filter(
                $this->observadores,
                fn(Observador $o) => $o !== $observador
            )
        );
    }

    public function notificar(array $datos): void
    {
        foreach ($this->observadores as $observador) {
            $observador->actualizar($datos);
        }
    }

    // -------------------------------------------------------------------------
    // Acciones del controlador MVC
    // -------------------------------------------------------------------------

    public function index(): void
    {
        require_auth(['administrador']);

        $convModel = new ConvocatoriaModel();
        $convocatorias = $convModel->all();

        render_view('resultado/index', [
            'title' => 'Publicar resultados',
            'convocatorias' => $convocatorias,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    /**
     * Publica los resultados de una convocatoria.
     *
     * Flujo refactorizado con el patrón Observador:
     *   1. Valida convocatoria y postulaciones (lógica de negocio preexistente).
     *   2. Registra los resultados en la tabla 'resultados' dentro de una transacción.
     *   3. Adjunta los observadores al sujeto (this).
     *   4. Invoca notificar() → los observadores actualizan estados y escriben el log.
     *   5. Confirma la transacción y redirige.
     */
    public function publicar(): void
    {
        require_auth(['administrador']);

        $idConv = (int) ($_POST['id_convocatoria'] ?? 0);
        $cupos = max(1, (int) ($_POST['cupos'] ?? 10));

        // -----------------------------------------------------------------
        // Validaciones de negocio (lógica preexistente)
        // -----------------------------------------------------------------
        $convModel = new ConvocatoriaModel();
        $conv = $convModel->find($idConv);
        if (!$conv) {
            $_SESSION['flash_error'] = 'Convocatoria inválida.';
            redirect_to('resultado');
        }

        if (($conv['estado'] ?? '') === 'abierta') {
            $_SESSION['flash_error'] = 'La convocatoria aún está abierta.';
            redirect_to('resultado');
        }

        $postModel = new PostulacionModel();
        $evaluadas = $postModel->evaluadasPorConvocatoria($idConv);
        if (empty($evaluadas)) {
            $_SESSION['flash_error'] = 'No hay postulaciones con evaluación final.';
            redirect_to('resultado');
        }

        $total = $postModel->countByConvocatoria($idConv);
        if ($total > count($evaluadas)) {
            $_SESSION['flash_error'] = 'Existen postulaciones sin evaluación final.';
            redirect_to('resultado');
        }

        // -----------------------------------------------------------------
        // PATRÓN OBSERVADOR
        // Registro de observadores concretos en el sujeto (this)
        // -----------------------------------------------------------------
        $this->adjuntar(new ActualizadorEstadosPostulacion());
        $this->adjuntar(new LoggerAuditoriaPublicacion());
        $this->adjuntar(new NotificadorEstudiantes());

        // -----------------------------------------------------------------
        // Transacción PDO: inserta/actualiza filas en 'resultados'
        // (lógica de negocio preexistente, sin cambios)
        // -----------------------------------------------------------------
        $resModel = new ResultadoModel();

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            foreach ($evaluadas as $idx => $p) {
                $estadoFinal = ($idx < $cupos) ? 'seleccionado' : 'no_seleccionado';
                $resModel->upsert([
                    'id_convocatoria' => $idConv,
                    'id_postulacion' => (int) $p['id'],
                    'puntaje_final' => (float) $p['puntaje'],
                    'estado_final' => $estadoFinal,
                    'publicado' => 1,
                ]);
            }

            // -----------------------------------------------------------------
            // Notificación a los observadores (dentro de la misma transacción,
            // para garantizar atomicidad con los UPDATE de postulaciones)
            // -----------------------------------------------------------------
            $this->notificar([
                'id_convocatoria' => $idConv,
                'cupos' => $cupos,
                'evaluadas' => $evaluadas,
            ]);

            $db->commit();
            $_SESSION['flash'] = 'Resultados publicados correctamente.';
        } catch (\Throwable $e) {
            $db = Database::getInstance();
            if ($db->inTransaction()) {
                $db->rollBack();
            }
            $_SESSION['flash_error'] = 'Error al publicar resultados: ' . $e->getMessage();
        }

        redirect_to('resultado');
    }
}
