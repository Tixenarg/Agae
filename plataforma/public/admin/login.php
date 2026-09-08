<?php

declare(strict_types=1);

use App\Repositories\AdminUsuarioRepository;
use App\Services\AdminAuthService;
use App\Support\AdminSession;

require_once dirname(__DIR__, 2)
    . '/vendor/autoload.php';

AdminSession::iniciar();

if (
    AdminSession::autenticado()
) {
    header(
        'Location: /public/admin/index.php'
    );

    exit;
}

$error = null;

if (
    ($_SERVER['REQUEST_METHOD'] ?? '')
    === 'POST'
) {
    try {
        /** @var PDO $pdo */
        $pdo =
            require dirname(__DIR__, 2)
                . '/config/database.php';

        $repository =
            new AdminUsuarioRepository(
                $pdo
            );

        $auth =
            new AdminAuthService(
                $repository
            );

        $usuario =
            $auth->login(
                (string) (
                    $_POST['email']
                    ?? ''
                ),
                (string) (
                    $_POST['password']
                    ?? ''
                )
            );

        if ($usuario === null) {
            $error =
                'Email o contraseña incorrectos.';
        } else {
            AdminSession::login(
                $usuario
            );

            header(
                'Location: /public/admin/index.php'
            );

            exit;
        }

    } catch (Throwable $exception) {
        error_log(
            sprintf(
                '[admin-login] %s en %s:%d',
                $exception->getMessage(),
                $exception->getFile(),
                $exception->getLine()
            )
        );

        $error =
            'No se pudo iniciar sesión.';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1"
>

<title>AGAE Platform</title>

<style>

* {
    box-sizing: border-box;
}

html,
body {
    margin: 0;
    min-height: 100%;
}

body {
    min-height: 100vh;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 24px;

    background: #0b0b0b;

    font-family:
        -apple-system,
        BlinkMacSystemFont,
        "Segoe UI",
        Arial,
        sans-serif;

    color: #111111;
}

.login {
    width: 100%;
    max-width: 420px;
}

.brand {
    margin-bottom: 28px;

    text-align: center;

    color: #ffffff;
}

.brand-title {
    font-size: 30px;
    font-weight: 800;
    letter-spacing: -1px;
}

.brand-subtitle {
    margin-top: 7px;

    font-size: 12px;
    letter-spacing: 2px;

    color: #888888;
}

.card {
    padding: 32px;

    background: #ffffff;

    border-radius: 22px;
}

h1 {
    margin: 0;

    font-size: 26px;
    letter-spacing: -0.7px;
}

.description {
    margin: 7px 0 28px 0;

    color: #737373;

    font-size: 14px;
}

label {
    display: block;

    margin-bottom: 7px;

    font-size: 13px;
    font-weight: 700;
}

input {
    width: 100%;
    min-height: 52px;

    padding: 0 15px;
    margin-bottom: 18px;

    border: 1px solid #d8d8d8;
    border-radius: 12px;

    outline: none;

    font-size: 16px;
}

input:focus {
    border-color: #111111;
}

button {
    width: 100%;
    min-height: 54px;

    margin-top: 4px;

    border: 0;
    border-radius: 12px;

    background: #111111;
    color: #ffffff;

    font-size: 16px;
    font-weight: 800;

    cursor: pointer;
}

.error {
    margin-bottom: 20px;
    padding: 12px 14px;

    border-radius: 10px;

    background: #fee2e2;
    color: #991b1b;

    font-size: 14px;
}

</style>

</head>

<body>

<div class="login">

    <div class="brand">

        <div class="brand-title">
            AGAE
        </div>

        <div class="brand-subtitle">
            PLATFORM
        </div>

    </div>

    <div class="card">

        <h1>
            Iniciar sesión
        </h1>

        <p class="description">
            Administración de la plataforma de eventos.
        </p>

        <?php if ($error !== null): ?>

            <div class="error">
                <?= htmlspecialchars(
                    $error
                ) ?>
            </div>

        <?php endif; ?>

        <form method="post">

            <label for="email">
                Email
            </label>

            <input
                type="email"
                id="email"
                name="email"
                autocomplete="username"
                required
            >

            <label for="password">
                Contraseña
            </label>

            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button type="submit">
                Ingresar
            </button>

        </form>

    </div>

</div>

</body>
</html>