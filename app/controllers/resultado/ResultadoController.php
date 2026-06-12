<?php

declare(strict_types=1);

final class ResultadoController
{
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

    public function publicar(): void
    {
        require_auth(['administrador']);

        $idConv = (int)($_POST['id_convocatoria'] ?? 0);
        $cupos = max(1, (int)($_POST['cupos'] ?? 10));

        $convModel = new ConvocatoriaModel();
        $conv = $convModel->find($idConv);
        if (!$conv) {
            $_SESSION['flash_error'] = 'Convocatoria inválida.';
            redirect_to('resultado');
        }

        // Alterno: convocatoria aún abierta
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

        // Alterno: existen postulaciones sin evaluación final (si hay postulaciones pero menos evaluadas)
        $total = $postModel->countByConvocatoria($idConv);
        if ($total > count($evaluadas)) {
            $_SESSION['flash_error'] = 'Existen postulaciones sin evaluación final.';
            redirect_to('resultado');
        }

        $resModel = new ResultadoModel();

        try {
            $db = Database::getInstance();
            $db->beginTransaction();

            foreach ($evaluadas as $idx => $p) {
                $estadoFinal = ($idx < $cupos) ? 'seleccionado' : 'no_seleccionado';
                $resModel->upsert([
                    'id_convocatoria' => $idConv,
                    'id_postulacion' => (int)$p['id'],
                    'puntaje_final' => (float)$p['puntaje'],
                    'estado_final' => $estadoFinal,
                    'publicado' => 1,
                ]);

                $postModel->setEstado((int)$p['id'], $estadoFinal);
            }

            $db->commit();
            $_SESSION['flash'] = 'Resultados publicados.';
        } catch (Throwable $e) {
            $db = Database::getInstance();
            if ($db->inTransaction()) $db->rollBack();
            $_SESSION['flash_error'] = 'Error al publicar resultados.';
        }

        redirect_to('resultado');
    }
}

