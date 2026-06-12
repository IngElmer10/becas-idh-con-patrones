<?php

declare(strict_types=1);

final class EvaluacionController
{
    public function bandeja(): void
    {
        require_auth(['evaluador']);

        $postModel = new PostulacionModel();
        $items = $postModel->aptasEvaluacion();

        render_view('evaluacion/bandeja', [
            'title' => 'Bandeja de evaluación',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function evaluar(): void
    {
        require_auth(['evaluador']);

        $idPost = (int)($_GET['id_post'] ?? 0);
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post) {
            $_SESSION['flash_error'] = 'Postulación no encontrada.';
            redirect_to('evaluacion');
        }

        // Alterno: aún no revisada documentalmente
        if (!in_array((string)$post['estado'], ['doc_validada','apta_evaluacion','evaluada'], true)) {
            $_SESSION['flash_error'] = 'La postulación aún no fue habilitada para evaluación.';
            redirect_to('evaluacion');
        }

        $evalModel = new EvaluacionModel();
        $eval = $evalModel->findByPostulacion($idPost);

        render_view('evaluacion/evaluar', [
            'title' => 'Evaluar postulante',
            'post' => $post,
            'eval' => $eval,
            'errors' => $_SESSION['form_errors'] ?? [],
        ]);
        unset($_SESSION['form_errors']);
    }

    public function guardar(): void
    {
        require_auth(['evaluador']);

        $idPost = (int)($_POST['id_post'] ?? 0);
        $estado = (string)($_POST['estado'] ?? 'final'); // final | borrador
        if (!in_array($estado, ['final','borrador'], true)) $estado = 'final';

        $c1 = (float)($_POST['c1'] ?? 0);
        $c2 = (float)($_POST['c2'] ?? 0);
        $c3 = (float)($_POST['c3'] ?? 0);
        $obs = trim((string)($_POST['observaciones'] ?? ''));

        $errors = [];
        // Alterno: falta un criterio por completar (solo si final)
        if ($estado === 'final') {
            if ($c1 <= 0) $errors['c1'] = 'Criterio 1 es obligatorio.';
            if ($c2 <= 0) $errors['c2'] = 'Criterio 2 es obligatorio.';
            if ($c3 <= 0) $errors['c3'] = 'Criterio 3 es obligatorio.';
        }

        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post) $errors['post'] = 'Postulación inválida.';

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['flash_error'] = 'Revisa los criterios.';
            redirect_to('evaluacion/evaluar?id_post=' . $idPost);
        }

        $puntaje = round($c1 + $c2 + $c3, 2);
        $criterios = json_encode([
            'criterio_1' => $c1,
            'criterio_2' => $c2,
            'criterio_3' => $c3,
        ], JSON_UNESCAPED_UNICODE);

        $evalModel = new EvaluacionModel();
        $evalModel->upsert([
            'id_postulacion' => $idPost,
            'id_evaluador' => (int)$_SESSION['user']['id'],
            'puntaje' => $puntaje,
            'criterios_json' => $criterios ?: '{}',
            'observaciones' => $obs === '' ? null : $obs,
            'estado' => $estado,
        ]);

        // Postcondición CU06
        if ($estado === 'final') {
            $postModel->setEstado($idPost, 'evaluada');
            $_SESSION['flash'] = 'Evaluación guardada (final).';
        } else {
            $postModel->setEstado($idPost, 'apta_evaluacion');
            $_SESSION['flash'] = 'Evaluación guardada como borrador.';
        }

        redirect_to('evaluacion');
    }
}

