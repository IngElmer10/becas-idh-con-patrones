<?php

declare(strict_types=1);

// Helpers mínimos para proyecto pequeño (sin Controller base).

function app_base_path(): string
{
    $config = require __DIR__ . '/../config/config.php';
    return rtrim((string)($config['app']['base_path'] ?? ''), '/');
}

function redirect_to(string $path): void
{
    $base = app_base_path();
    $path = ltrim($path, '/');
    header('Location: ' . $base . '/' . $path);
    exit;
}

function render_view(string $view, array $data = []): void
{
    $data['base_path'] = app_base_path();
    extract($data, EXTR_SKIP);

    $viewPath = __DIR__ . '/../app/views/' . $view . '.view.php';
    if (!is_file($viewPath)) {
        http_response_code(500);
        echo "Vista no encontrada: " . htmlspecialchars($view, ENT_QUOTES, 'UTF-8');
        return;
    }

    require __DIR__ . '/../app/views/layouts/header.view.php';
    require $viewPath;
    require __DIR__ . '/../app/views/layouts/footer.view.php';
}

function require_auth(?array $roles = null): void
{
    Session::start();

    if (empty($_SESSION['user'])) {
        redirect_to('auth/login');
    }

    if ($roles !== null) {
        $rol = $_SESSION['user']['rol'] ?? null;
        if (!$rol || !in_array($rol, $roles, true)) {
            http_response_code(403);
            render_view('auth/forbidden', [
                'title' => 'Acceso denegado',
            ]);
            exit;
        }
    }
}

