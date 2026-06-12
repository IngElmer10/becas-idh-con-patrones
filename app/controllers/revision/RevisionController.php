<?php

declare(strict_types=1);

final class RevisionController
{
    public function bandeja(): void
    {
        require_auth(['revisor']);

        $postModel = new PostulacionModel();
        $items = $postModel->pendientesRevision();

        render_view('revision/bandeja', [
            'title' => 'Bandeja de revisión',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function ver(): void
    {
        require_auth(['revisor']);

        $idPost = (int)($_GET['id_post'] ?? 0);
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post) {
            $_SESSION['flash_error'] = 'Postulación no encontrada.';
            redirect_to('revision');
        }

        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int)$post['id_convocatoria']);

        $docModel = new DocumentoModel();
        $docMap = $docModel->estadoPorRequisito($idPost);

        render_view('revision/ver', [
            'title' => 'Revisar documentación',
            'post' => $post,
            'requisitos' => $requisitos,
            'docMap' => $docMap,
            'errors' => $_SESSION['form_errors'] ?? [],
        ]);
        unset($_SESSION['form_errors']);
    }

    public function guardar(): void
    {
        require_auth(['revisor']);

        $idPost = (int)($_POST['id_post'] ?? 0);
        $estados = $_POST['doc_estado'] ?? [];
        $obs = $_POST['doc_obs'] ?? [];

        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('revision');
        }

        $docModel = new DocumentoModel();

        $hasRechazado = false;
        $hasObservado = false;

        if (is_array($estados)) {
            foreach ($estados as $idReq => $estado) {
                $idReq = (int)$idReq;
                $estado = (string)$estado;
                if (!in_array($estado, ['valido','observado','rechazado'], true)) {
                    continue;
                }
                $o = is_array($obs) ? (string)($obs[$idReq] ?? '') : '';
                $docModel->setRevision($idPost, $idReq, $estado, trim($o) === '' ? null : trim($o));

                if ($estado === 'rechazado') $hasRechazado = true;
                if ($estado === 'observado') $hasObservado = true;
            }
        }

        // Postcondición CU05
        if ($hasRechazado) {
            $postModel->setEstado($idPost, 'rechazada_doc');
        } elseif ($hasObservado) {
            $postModel->setEstado($idPost, 'observada');
        } else {
            $postModel->setEstado($idPost, 'doc_validada');
        }

        $_SESSION['flash'] = 'Revisión guardada.';
        redirect_to('revision');
    }
}

