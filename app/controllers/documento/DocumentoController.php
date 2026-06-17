<?php

declare(strict_types=1);

// Controlador para la gestion de documentos de postulacion
// Maneja la subida de los archivos de requisitos
final class DocumentoController
{
    public function misPostulaciones(): void
    {
        require_auth(['estudiante']);

        $postModel = new PostulacionModel();
        $items = $postModel->byUser((int) $_SESSION['user']['id']);

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

        $idPost = (int) ($_GET['id_post'] ?? 0);
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);

        if (!$post || (int) $post['id_usuario'] !== (int) $_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('documento');
        }

        // Requisitos de la convocatoria
        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int) $post['id_convocatoria']);

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

    // Sube el archivo aplicando los validadores en cadena
    public function doUpload(): void
    {
        require_auth(['estudiante']);

        $idPost = (int) ($_POST['id_post'] ?? 0);
        $idReq = (int) ($_POST['id_requisito'] ?? 0);
        $replace = (int) ($_POST['replace'] ?? 0) === 1;

        // Validaciones generales de la postulacion y requisitos
        $postModel = new PostulacionModel();
        $post = $postModel->find($idPost);
        if (!$post || (int) $post['id_usuario'] !== (int) $_SESSION['user']['id']) {
            $_SESSION['flash_error'] = 'Postulación inválida.';
            redirect_to('documento');
        }

        $reqModel = new RequisitoModel();
        $requisitos = $reqModel->byConvocatoria((int) $post['id_convocatoria']);
        $reqOk = false;
        foreach ($requisitos as $r) {
            if ((int) $r['id'] === $idReq) {
                $reqOk = true;
                break;
            }
        }
        if (!$reqOk) {
            $_SESSION['flash_error'] = 'Requisito inválido.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        // Verificación básica de error de upload de PHP (antes de la cadena)
        if (empty($_FILES['archivo']) || !is_array($_FILES['archivo'])) {
            $_SESSION['flash_error'] = 'No se recibió ningún archivo.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }
        $f = $_FILES['archivo'];
        if (($f['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            $_SESSION['flash_error'] = 'Error al recibir el archivo (código PHP: ' . $f['error'] . ').';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        // Creamos la cadena de validacion: duplicado -> extension -> tamano
        $docModel = new DocumentoModel();
        $docMap = $docModel->estadoPorRequisito($idPost);

        $validadorDuplicado = new ValidadorDuplicado();
        $validadorExtension = new ValidadorExtension();
        $validadorTamano = new ValidadorTamano();

        // Enlazamos los validadores en orden
        $validadorDuplicado
            ->setNext($validadorExtension);
        $validadorExtension
            ->setNext($validadorTamano);


        $contexto = [
            'id_postulacion' => $idPost,
            'id_requisito' => $idReq,
            'replace' => $replace,
            'docMap' => $docMap,
        ];

        try {
            // Ejecutamos la cadena de validaciones
            $validadorDuplicado->procesar($f, $contexto);
        } catch (\RuntimeException $e) {
            $_SESSION['flash_error'] = $e->getMessage();
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        // Si todo esta bien, guardamos el archivo y registramos en la BD
        $ext = strtolower(pathinfo((string) ($f['name'] ?? ''), PATHINFO_EXTENSION));
        $targetDir = __DIR__ . '/../../../public/uploads/' . $idPost;
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }

        $safeName = 'req_' . $idReq . '.' . $ext;
        $targetPath = $targetDir . '/' . $safeName;

        if (!move_uploaded_file((string) $f['tmp_name'], $targetPath)) {
            $_SESSION['flash_error'] = 'No se pudo guardar el archivo en el servidor.';
            redirect_to('documento/upload?id_post=' . $idPost);
        }

        $publicPath = '/public/uploads/' . $idPost . '/' . $safeName;
        $docModel->upsert($idPost, $idReq, $publicPath, 'recibido', null);

        // Actualizamos el estado de la postulacion a revision inicial
        if (($post['estado'] ?? '') === 'pendiente_documentos') {
            $postModel->setEstado($idPost, 'en_revision_inicial');
        }

        $_SESSION['flash'] = 'Documento cargado y marcado como recibido.';
        redirect_to('documento/upload?id_post=' . $idPost);
    }
}
