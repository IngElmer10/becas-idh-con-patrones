<?php

declare(strict_types=1);

final class ConvocatoriaController
{
    public function index(): void
    {
        require_auth(['administrador']);

        $model = new ConvocatoriaModel();
        $items = $model->all();

        render_view('convocatoria/index', [
            'title' => 'Gestionar convocatorias',
            'items' => $items,
            'flash' => $_SESSION['flash'] ?? null,
            'flash_error' => $_SESSION['flash_error'] ?? null,
            'base_path' => app_base_path(),
        ]);
        unset($_SESSION['flash'], $_SESSION['flash_error']);
    }

    public function create(): void
    {
        require_auth(['administrador']);

        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        render_view('convocatoria/form', [
            'title' => 'Nueva convocatoria',
            'mode' => 'create',
            'convocatoria' => $old['convocatoria'] ?? [
                'nombre' => '',
                'gestion' => (int)date('Y'),
                'tipo_beca' => '',
                'fecha_inicio' => date('Y-m-d'),
                'fecha_fin' => date('Y-m-d', strtotime('+30 days')),
                'estado' => 'borrador',
            ],
            'requisitos' => $old['requisitos'] ?? [
                ['descripcion' => '', 'obligatorio' => 1],
            ],
            'errors' => $_SESSION['form_errors'] ?? [],
            'base_path' => app_base_path(),
        ]);
        unset($_SESSION['form_errors']);
    }

    public function edit(): void
    {
        require_auth(['administrador']);

        $id = (int)($_GET['id'] ?? 0);
        $convModel = new ConvocatoriaModel();
        $reqModel = new RequisitoModel();

        $conv = $convModel->find($id);
        if (!$conv) {
            $_SESSION['flash_error'] = 'Convocatoria no encontrada.';
            redirect_to('convocatoria');
        }

        $old = $_SESSION['old'] ?? null;
        unset($_SESSION['old']);

        $requisitos = $old['requisitos'] ?? $reqModel->byConvocatoria($id);
        if (!$requisitos) {
            $requisitos = [['descripcion' => '', 'obligatorio' => 1]];
        }

        render_view('convocatoria/form', [
            'title' => 'Editar convocatoria',
            'mode' => 'edit',
            'id' => $id,
            'convocatoria' => $old['convocatoria'] ?? $conv,
            'requisitos' => $requisitos,
            'errors' => $_SESSION['form_errors'] ?? [],
            'base_path' => app_base_path(),
        ]);
        unset($_SESSION['form_errors']);
    }

    public function store(): void
    {
        require_auth(['administrador']);

        $data = $this->normalizePost();
        $errors = $this->validate($data, null);
        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = $data;
            $_SESSION['flash_error'] = 'Revisa los errores del formulario.';
            redirect_to('convocatoria/create');
        }

        try {
            $convModel = new ConvocatoriaModel();
            $convModel->createWithRequisitos($data['convocatoria'], $data['requisitos']);
            $_SESSION['flash'] = 'Convocatoria registrada correctamente.';
            redirect_to('convocatoria');
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al guardar la convocatoria.';
            redirect_to('convocatoria/create');
        }
    }

    public function update(): void
    {
        require_auth(['administrador']);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID inválido.';
            redirect_to('convocatoria');
        }

        $data = $this->normalizePost();
        $errors = $this->validate($data, $id);
        if ($errors) {
            $_SESSION['form_errors'] = $errors;
            $_SESSION['old'] = $data;
            $_SESSION['flash_error'] = 'Revisa los errores del formulario.';
            redirect_to('convocatoria/edit?id=' . $id);
        }

        try {
            $convModel = new ConvocatoriaModel();
            $convModel->updateWithRequisitos($id, $data['convocatoria'], $data['requisitos']);
            $_SESSION['flash'] = 'Convocatoria actualizada correctamente.';
            redirect_to('convocatoria');
        } catch (Throwable $e) {
            $_SESSION['flash_error'] = 'Error al actualizar la convocatoria.';
            redirect_to('convocatoria/edit?id=' . $id);
        }
    }

    public function close(): void
    {
        require_auth(['administrador']);

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $_SESSION['flash_error'] = 'ID inválido.';
            redirect_to('convocatoria');
        }

        $model = new ConvocatoriaModel();
        $model->close($id);
        $_SESSION['flash'] = 'Convocatoria cerrada.';
        redirect_to('convocatoria');
    }

    private function normalizePost(): array
    {
        $nombre = trim((string)($_POST['nombre'] ?? ''));
        $gestion = (int)($_POST['gestion'] ?? 0);
        $tipoBeca = trim((string)($_POST['tipo_beca'] ?? ''));
        $fechaInicio = (string)($_POST['fecha_inicio'] ?? '');
        $fechaFin = (string)($_POST['fecha_fin'] ?? '');
        $estado = (string)($_POST['estado'] ?? 'borrador');

        $reqDesc = $_POST['req_descripcion'] ?? [];
        $reqObl = $_POST['req_obligatorio'] ?? [];

        $requisitos = [];
        if (is_array($reqDesc)) {
            foreach ($reqDesc as $i => $desc) {
                $desc = trim((string)$desc);
                if ($desc === '') {
                    continue;
                }
                $requisitos[] = [
                    'descripcion' => $desc,
                    'obligatorio' => isset($reqObl[$i]) ? (bool)$reqObl[$i] : false,
                ];
            }
        }

        return [
            'convocatoria' => [
                'nombre' => $nombre,
                'gestion' => $gestion,
                'tipo_beca' => $tipoBeca,
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'estado' => in_array($estado, ['borrador', 'abierta', 'cerrada'], true) ? $estado : 'borrador',
            ],
            'requisitos' => $requisitos,
        ];
    }

    private function validate(array $data, ?int $id): array
    {
        $errors = [];
        $c = $data['convocatoria'];

        if ($c['nombre'] === '') {
            $errors['nombre'] = 'Nombre es obligatorio.';
        }
        if ($c['gestion'] < 2000 || $c['gestion'] > 2100) {
            $errors['gestion'] = 'Gestión inválida.';
        }
        if ($c['tipo_beca'] === '') {
            $errors['tipo_beca'] = 'Tipo de beca es obligatorio.';
        }

        $start = strtotime($c['fecha_inicio']);
        $end = strtotime($c['fecha_fin']);
        if ($start === false || $end === false) {
            $errors['fechas'] = 'Fechas inválidas.';
        } elseif ($start > $end) {
            $errors['fechas'] = 'Fechas inválidas: inicio no puede ser mayor a fin.';
        }

        $convModel = new ConvocatoriaModel();
        if ($c['nombre'] !== '' && $c['gestion'] > 0 && $c['tipo_beca'] !== '') {
            if ($convModel->existsDuplicate($c['nombre'], (int)$c['gestion'], $c['tipo_beca'], $id)) {
                $errors['duplicada'] = 'Convocatoria duplicada (nombre + gestión + tipo).';
            }
        }

        // Requisitos: debe existir al menos uno, y al menos uno obligatorio
        if (empty($data['requisitos'])) {
            $errors['requisitos'] = 'Debe registrar al menos un requisito/documento exigido.';
        } else {
            $hasMandatory = false;
            foreach ($data['requisitos'] as $r) {
                if (!empty($r['obligatorio'])) {
                    $hasMandatory = true;
                    break;
                }
            }
            if (!$hasMandatory) {
                $errors['requisitos'] = 'Falta un requisito obligatorio (marque al menos uno como obligatorio).';
            }
        }

        // Incoherencia de estado: no permitir "abierta" si ya está fuera de fechas (política)
        if (($c['estado'] ?? '') === 'abierta' && $end !== false && $end < strtotime(date('Y-m-d'))) {
            $errors['estado'] = 'No puede quedar "abierta" con fecha fin ya vencida.';
        }

        return $errors;
    }
}

