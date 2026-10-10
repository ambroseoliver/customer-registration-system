<?php
declare(strict_types=1);

$isSecureRequest = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isSecureRequest,
    'httponly' => true,
    'samesite' => 'Lax',
]);

if (!session_start()) {
    throw new RuntimeException('Unable to start the application session.');
}

function escape_html(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function redirect_to_registration(): never
{
    header('Location: index.php', true, 303);
    exit;
}
