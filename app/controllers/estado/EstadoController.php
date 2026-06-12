<?php

declare(strict_types=1);

final class EstadoController
{
    public function misPostulaciones(): void
    {
        require_auth(['estudiante']);

        $postModel = new PostulacionModel();
        $items = $postModel->byUser((int)$_SESSION['user']['id']);

        render_view('estado/index', [
            'title' => 'Mis postulaciones',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function ver(): void
    {
        require_auth(['estudiante']);

        $idPost = (int)($_GET['id_post'] ?? 0);
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post || (int)$post['id_usuario'] !== (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('estado');
        }

        $convModel = new ConvocatoriaModel();
        $conv = $convModel->find((int)$post['id_convocatoria']);

        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int)$post['id_convocatoria']);

        $docModel = new DocumentoModel();
        $docMap = $docModel->estadoPorRequisito($idPost);

        $resModel = new ResultadoModel();
        $resultados = $resModel->publicadosPorUsuario((int)$_SESSION['user']['id']);
        $resForPost = null;
        foreach ($resultados as $r) {
            if ((int)$r['id_postulacion'] === $idPost) { $resForPost = $r; break; }
        }

        render_view('estado/ver', [
            'title' => 'Estado de postulación',
            'post' => $post,
            'conv' => $conv,
            'requisitos' => $requisitos,
            'docMap' => $docMap,
            'resultado' => $resForPost,
        ]);
    }
}

