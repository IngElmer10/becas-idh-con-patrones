<?php

declare(strict_types=1);

final class PostulacionController
{
    public function convocatoriasDisponibles(): void
    {
        require_auth(['estudiante']);

        $convModel = new ConvocatoriaModel();
        $items = $convModel->abiertasEnPlazo();

        render_view('postulacion/convocatorias', [
            'title' => 'Convocatorias disponibles',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function form(): void
    {
        require_auth(['estudiante']);

        $idConv = (int)($_GET['id_conv'] ?? 0);
        if ($idConv <= 0) {
            $_SESSION['flash_error'] = 'Convocatoria inválida.';
            redirect_to('postulacion');
        }

        $convModel = new ConvocatoriaModel();
        $conv = $convModel->find($idConv);
        if (!$conv) {
            $_SESSION['flash_error'] = 'Convocatoria no encontrada.';
            redirect_to('postulacion');
        }

        // Alterno: fuera de plazo o no abierta.
        $today = date('Y-m-d');
        if (($conv['estado'] ?? '') !== 'abierta' || $conv['fecha_inicio'] > $today || $conv['fecha_fin'] < $today) {
            $_SESSION['flash_error'] = 'Convocatoria fuera de plazo.';
            redirect_to('postulacion');
        }

        $postModel = new PostulacionModel();
        $idUser = (int)($_SESSION['user']['id'] ?? 0);
        $existing = $postModel->findForUserConv($idUser, $idConv);
        if ($existing) {
            $_SESSION['flash_error'] = 'Ya tienes una postulación activa para esta convocatoria.';
            redirect_to('estado');
        }

        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        render_view('postulacion/form', [
            'title' => 'Registrar postulación',
            'conv' => $conv,
            'errors' => $_SESSION['form_errors'] ?? [],
            'data' => $old ?? [
                'telefono' => '',
                'direccion' => '',
                'cuenta_bancaria' => '',
                'confirm' => 0,
            ],
        ]);
        unset($_SESSION['form_errors']);
    }

    public function store(): void
    {
        require_auth(['estudiante']);

        $idConv = (int)($_POST['id_convocatoria'] ?? 0);
        $telefono = trim((string)($_POST['telefono'] ?? ''));
        $direccion = trim((string)($_POST['direccion'] ?? ''));
        $cuenta = trim((string)($_POST['cuenta_bancaria'] ?? ''));
        $confirm = (int)($_POST['confirm'] ?? 0);

        $errors = [];
        if ($telefono === '') $errors['telefono'] = 'Teléfono es obligatorio.';
        if ($direccion === '') $errors['direccion'] = 'Dirección es obligatoria.';
        if ($cuenta === '') $errors['cuenta_bancaria'] = 'Cuenta bancaria es obligatoria.';
        if ($confirm !== 1) $errors['confirm'] = 'Debe confirmar el envío.';

        $convModel = new ConvocatoriaModel();
        $conv = $convModel->find($idConv);
        if (!$conv) $errors['conv'] = 'Convocatoria no encontrada.';

        if ($conv) {
            $today = date('Y-m-d');
            if (($conv['estado'] ?? '') !== 'abierta' || $conv['fecha_inicio'] > $today || $conv['fecha_fin'] < $today) {
                $errors['conv'] = 'Convocatoria fuera de plazo.';
            }
        }

        $idUser = (int)($_SESSION['user']['id'] ?? 0);
        $postModel = new PostulacionModel();
        if ($idUser > 0 && $idConv > 0) {
            $existing = $postModel->findForUserConv($idUser, $idConv);
            if ($existing) {
                $errors['duplicada'] = 'Ya tienes una postulación activa para esta convocatoria.';
            }
        }

        $old = [
            'telefono' => $telefono,
            'direccion' => $direccion,
            'cuenta_bancaria' => $cuenta,
            'confirm' => $confirm,
        ];

        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = $old;
            $_SESSION['flash_error'] = 'Faltan datos obligatorios.';
            redirect_to('postulacion/form?id_conv=' . $idConv);
        }

        // Registro de postulación => pendiente de documentos
        try {
            $idPost = $postModel->create([
                'id_usuario' => $idUser,
                'id_convocatoria' => $idConv,
                'telefono' => $telefono,
                'direccion' => $direccion,
                'cuenta_bancaria' => $cuenta,
                'estado' => 'pendiente_documentos',
            ]);
            $_SESSION['flash'] = 'Postulación registrada. Ahora carga tus documentos.';
            redirect_to('documento/upload?id_post=' . $idPost);
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al registrar la postulación.';
            redirect_to('postulacion/form?id_conv=' . $idConv);
        }
    }
}

