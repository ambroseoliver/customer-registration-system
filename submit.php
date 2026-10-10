<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method Not Allowed');
}

function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

function character_count(string $value): ?int
{
    if (preg_match('//u', $value) !== 1) {
        return null;
    }

    $count = preg_match_all('/./us', $value);

    return $count === false ? null : $count;
}

$values = [
    'fullName' => post_string('fullName'),
    'email' => post_string('email'),
    'phone' => post_string('phone'),
    'message' => post_string('message'),
];
$errors = [];
$status = '';
$postedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($postedToken)
    || !is_string($sessionToken)
    || $sessionToken === ''
    || !hash_equals($sessionToken, $postedToken)
) {
    $status = 'Your session expired. Please try again.';
} else {
    $nameLength = character_count($values['fullName']);
    if ($nameLength === null || $nameLength < 2 || $nameLength > 100) {
        $errors['fullName'] = 'Please enter a name between 2 and 100 characters.';
    } elseif (preg_match('/[\p{C}]/u', $values['fullName']) === 1) {
        $errors['fullName'] = 'Please remove unsupported characters from your name.';
    }

    if (
        strlen($values['email']) > 254
        || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false
    ) {
        $errors['email'] = 'Please enter a valid email address (up to 254 characters).';
    }

    $phoneLength = character_count($values['phone']);
    $phoneDigits = preg_replace('/\D/', '', $values['phone']);
    if (
        $phoneLength === null
        || $phoneLength > 30
        || preg_match('/\A\+?[0-9().\-\s]+\z/', $values['phone']) !== 1
        || $phoneDigits === null
        || strlen($phoneDigits) < 7
        || strlen($phoneDigits) > 15
    ) {
        $errors['phone'] = 'Enter a phone number with 7 to 15 digits, using only standard phone punctuation.';
    }

    $messageLength = character_count($values['message']);
    if ($messageLength === null || $messageLength < 10 || $messageLength > 500) {
        $errors['message'] = 'Please enter a message between 10 and 500 characters.';
    }

    if ($errors === []) {
        $values['email'] = strtolower($values['email']);

        try {
            $pdo = require __DIR__ . '/config/database.php';
            $statement = $pdo->prepare(
                'INSERT INTO customers (full_name, email, phone, message)
                 VALUES (:full_name, :email, :phone, :message)'
            );
            $statement->execute([
                'full_name' => $values['fullName'],
                'email' => $values['email'],
                'phone' => $values['phone'],
                'message' => $values['message'],
            ]);

            $_SESSION['registration_flash'] = [
                'success' => 'Your profile was created successfully. Thank you for registering.',
            ];
            unset($_SESSION['csrf_token']);
            redirect_to_registration();
        } catch (PDOException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                $errors['email'] = 'An account with this email address is already registered.';
            } else {
                error_log('Customer registration database error: ' . $exception->getMessage());
                $status = 'We could not save your registration right now. Please try again later.';
            }
        } catch (RuntimeException $exception) {
            error_log('Customer registration configuration error: ' . $exception->getMessage());
            $status = 'Registration is temporarily unavailable. Please try again later.';
        }
    }
}

$_SESSION['registration_flash'] = [
    'values' => $values,
    'errors' => $errors,
    'status' => $status,
];

redirect_to_registration();
