<?php

declare(strict_types=1);

// Controlador para publicar resultados
// Implementa el patron Observer
final class ResultadoController implements Sujeto
{
    // Lista de observadores
    private array $observadores = [];

    // -------------------------------------------------------------------------
    // Implementación de la interfaz Sujeto
    // -------------------------------------------------------------------------

    public function adjuntar(Observador $observador): void
    {
        // Evita repetidos en la lista
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

    // Publica los resultados y notifica a los observadores
    public function publicar(): void
    {
        require_auth(['administrador']);

        $idConv = (int) ($_POST['id_convocatoria'] ?? 0);
        $cupos = max(1, (int) ($_POST['cupos'] ?? 10));

        // Validaciones previas de la convocatoria
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

        // Registramos los observadores en el controlador
        $this->adjuntar(new ActualizadorEstadosPostulacion());
        $this->adjuntar(new LoggerAuditoriaPublicacion());
        $this->adjuntar(new NotificadorEstudiantes());

        // Registramos en BD usando transaccion
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

            // Notificamos a todos los observadores registrados
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
