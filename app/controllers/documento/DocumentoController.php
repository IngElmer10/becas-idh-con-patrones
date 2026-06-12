<?php

declare(strict_types=1);

final class DocumentoController
{
    public function misPostulaciones(): void
    {
        require_auth(['estudiante']);

        $postModel = new PostulacionModel();
        $items = $postModel->byUser((int)$_SESSION['user']['id']);

        render_view('documento/mis_postulaciones', [
            'title' => 'Documentación',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function upload(): void
    {
        require_auth(['estudiante']);

        $idPost = (int)($_GET['id_post'] ?? 0);
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);

        if (!$post || (int)$post['id_usuario'] !== (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('documento');
        }

        // Requisitos de la convocatoria
        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int)$post['id_convocatoria']);

        $docModel = new DocumentoModel();
        $docMap = $docModel->estadoPorRequisito($idPost);

        render_view('documento/upload', [
            'title' => 'Cargar documentación',
            'post' => $post,
            'requisitos' => $requisitos,
            'docMap' => $docMap,
            'errors' => $_SESSION['form_errors'] ?? [],
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['form_errors'], $_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function doUpload(): void
    {
        require_auth(['estudiante']);

        $idPost = (int)($_POST['id_post'] ?? 0);
        $idReq = (int)($_POST['id_requisito'] ?? 0);
        $replace = (int)($_POST['replace'] ?? 0) === 1;

        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post || (int)$post['id_usuario'] !== (int)$_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('documento');
        }

        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int)$post['id_convocatoria']);
        $reqOk = false;
        foreach ($requisitos as $r) {
            if ((int)$r['id'] === $idReq) { $reqOk = true; break; }
        }
        if (!$reqOk) {
            $_SESSION['flash_error'] = 'Requisito inválido.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        if (empty($_FILES['archivo']) || !is_array($_FILES['archivo'])) {
            $_SESSION['flash_error'] = 'Archivo inválido.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $f = $_FILES['archivo'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Archivo inválido.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $maxBytes = 5 * 1024 * 1024;
        if ((int)$f['size'] > $maxBytes) {
            $_SESSION['flash_error'] = 'Archivo inválido: excede 5MB.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $name = (string)($f['name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
        if (!in_array($ext, $allowed, true)) {
            $_SESSION['flash_error'] = 'Archivo inválido: formato permitido pdf/jpg/png.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $docModel = new DocumentoModel();
        $docMap = $docModel->estadoPorRequisito($idPost);
        if (!$replace && isset($docMap[$idReq])) {
            $_SESSION['flash_error'] = 'Documento duplicado. Marca “reemplazar” si deseas subirlo de nuevo.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $targetDir = __DIR__ . '/../../../public/uploads/' . $idPost;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $safeName = 'req_' . $idReq . '.' . $ext;
        $targetPath = $targetDir . '/' . $safeName;

        if (!move_uploaded_file((string)$f['tmp_name'], $targetPath)) {
            $_SESSION['flash_error'] = 'No se pudo guardar el archivo.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $publicPath = '/public/uploads/' . $idPost . '/' . $safeName;
        $docModel->upsert($idPost, $idReq, $publicPath, 'recibido', null);

        // Si ya subió al menos un documento, avanza a revisión inicial (flujo post-condición)
        if (($post['estado'] ?? '') === 'pendiente_documentos') {
            $postModel->setEstado($idPost, 'en_revision_inicial');
        }

        $_SESSION['flash'] = 'Documento cargado y marcado como recibido.';
        redirect_to('documento/upload?id_post=' . $idPost);
    }
}

