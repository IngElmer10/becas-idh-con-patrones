<?php

declare(strict_types=1);

final class AuthController
{
    public function login(): void
    {
        if (!empty($_SESSION['user'])) {
            redirect_to('dashboard');
        }

        render_view('auth/login', [
            'title' => 'Inicio de sesión',
            'error' => $_SESSION['flash_error'] ?? null,
            'base_path' => app_base_path(),
        ]);
        unset($_SESSION['flash_error']);
    }

    public function doLogin(): void
    {
        $codigo = trim((string)($_POST['codigo'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($codigo === '' || $password === '') {
            $_SESSION['flash_error'] = 'Debe ingresar código y contraseña.';
            redirect_to('auth/login');
        }

        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->findByCodigo($codigo);

        /**
         * IMPORTANTE (por requerimiento del proyecto):
         * Se deshabilita la encriptación/verificación con hash.
         *
         * Producción sería:
         * - password_hash($password, PASSWORD_DEFAULT)
         * - password_verify($password, $hash)
         */
        if (!$user || $password !== (string)$user['password_hash']) {
            $_SESSION['flash_error'] = 'Credenciales incorrectas.';
            redirect_to('auth/login');
        }

        if ((int)$user['activo'] !== 1) {
            $_SESSION['flash_error'] = 'Usuario inactivo.';
            redirect_to('auth/login');
        }

        if (!empty($user['must_reset_password'])) {
            $_SESSION['flash_error'] = 'Contraseña vencida o restablecimiento requerido.';
            redirect_to('auth/login');
        }

        if (!empty($user['password_expires_at'])) {
            $expiresAt = strtotime((string)$user['password_expires_at']);
            if ($expiresAt !== false && $expiresAt < time()) {
                $_SESSION['flash_error'] = 'Contraseña vencida o restablecimiento requerido.';
                redirect_to('auth/login');
            }
        }

        Session::regenerate();
        $_SESSION['user'] = [
            'id' => (int)$user['id'],
            'codigo' => (string)$user['codigo'],
            'nombre' => (string)$user['nombre'],
            'rol' => (string)$user['rol'],
        ];

        redirect_to('dashboard');
    }

    public function logout(): void
    {
        Session::destroy();
        redirect_to('auth/login');
    }

    public function dashboard(): void
    {
        require_auth();

        $rol = (string)($_SESSION['user']['rol'] ?? '');
        render_view('auth/dashboard', [
            'title' => 'Menú',
            'rol' => $rol,
            'user' => $_SESSION['user'],
            'base_path' => app_base_path(),
        ]);
    }
}

