<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';

/**
 * Accept POST requests only.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Method Not Allowed');
}

/**
 * Safely retrieve a string from POST data.
 */
function post_string(string $key): string
{
    $value = $_POST[$key] ?? '';

    return is_string($value) ? trim($value) : '';
}

/**
 * Count Unicode characters safely.
 *
 * Returns null if the input contains invalid UTF-8.
 */
function character_count(string $value): ?int
{
    if (preg_match('//u', $value) !== 1) {
        return null;
    }

    $count = preg_match_all('/./us', $value);

    return $count === false ? null : $count;
}

/**
 * Collect submitted form values.
 */
$values = [
    'fullName' => post_string('fullName'),
    'email'    => post_string('email'),
    'phone'    => post_string('phone'),
    'message'  => post_string('message'),
];

$errors = [];
$status = '';

/**
 * Verify the CSRF token.
 */
$postedToken = $_POST['csrf_token'] ?? '';
$sessionToken = $_SESSION['csrf_token'] ?? '';

if (
    !is_string($postedToken)
    || !is_string($sessionToken)
    || $sessionToken === ''
    || !hash_equals($sessionToken, $postedToken)
) {
    $_SESSION['registration_flash'] = [
        'values' => [],
        'errors' => [],
        'status' => 'Your session expired. Please refresh the page and try again.',
    ];

    redirect_to_registration();
}

/**
 * Validate the customer's full name.
 */
$nameLength = character_count($values['fullName']);

if (
    $nameLength === null
    || $nameLength < 2
    || $nameLength > 100
) {
    $errors['fullName'] =
        'Please enter a name between 2 and 100 characters.';
} elseif (preg_match('/[\p{C}]/u', $values['fullName']) === 1) {
    $errors['fullName'] =
        'Please remove unsupported characters from your name.';
}

/**
 * Validate the email address.
 */
$email = $values['email'];

if (
    strlen($email) > 254
    || filter_var($email, FILTER_VALIDATE_EMAIL) === false
) {
    $errors['email'] =
        'Please enter a valid email address of up to 254 characters.';
} else {
    // Normalize the email address for consistent storage.
    $values['email'] = strtolower($email);
}

/**
 * Validate the phone number.
 *
 * Accepted characters:
 * digits, spaces, parentheses, periods, hyphens,
 * and an optional leading plus sign.
 */
$phone = $values['phone'];
$phoneLength = character_count($phone);

// Corrected regular expression: remove all non-digit characters.
$phoneDigits = preg_replace('/\D/', '', $phone);

if (
    $phoneLength === null
    || $phoneLength < 7
    || $phoneLength > 30
    || preg_match('/\A\+?[0-9().\-\s]+\z/', $phone) !== 1
    || $phoneDigits === null
    || strlen($phoneDigits) < 7
    || strlen($phoneDigits) > 15
) {
    $errors['phone'] =
        'Enter a valid phone number containing 7 to 15 digits. '
        . 'You may use a country code and standard phone punctuation.';
}

/**
 * Validate the additional message.
 */
$messageLength = character_count($values['message']);

if (
    $messageLength === null
    || $messageLength < 10
    || $messageLength > 500
) {
    $errors['message'] =
        'Please enter a message between 10 and 500 characters.';
}

/**
 * Save the registration if all validation checks pass.
 */
if ($errors === []) {
    try {
        // Load the PDO database connection.
        $pdo = require __DIR__ . '/config/database.php';

        // Use a prepared statement to prevent SQL injection.
        $sql = '
            INSERT INTO customers (
                full_name,
                email,
                phone,
                message
            ) VALUES (
                :full_name,
                :email,
                :phone,
                :message
            )
        ';

        $statement = $pdo->prepare($sql);

        $statement->execute([
            ':full_name' => $values['fullName'],
            ':email'     => $values['email'],
            ':phone'     => $values['phone'],
            ':message'   => $values['message'],
        ]);

        // Invalidate the submitted token after successful registration.
        unset($_SESSION['csrf_token']);

        // Store a success message for the registration page.
        $_SESSION['registration_flash'] = [
            'success' => 'Your profile was created successfully. Thank you for registering.',
            'values'  => [],
            'errors'  => [],
            'status'  => '',
        ];

        redirect_to_registration();

    } catch (PDOException $exception) {
        // MySQL error 1062 indicates a duplicate-key violation.
        if (($exception->errorInfo[1] ?? null) === 1062) {
            $errors['email'] =
                'An account with this email address is already registered.';
        } else {
            error_log(
                'Customer registration database error: '
                . $exception->getMessage()
            );

            $status =
                'We could not save your registration right now. '
                . 'Please try again later.';
        }

    } catch (RuntimeException $exception) {
        // Log configuration errors without exposing internal details.
        error_log(
            'Customer registration configuration error: '
            . $exception->getMessage()
        );

        $status =
            'Registration is temporarily unavailable. '
            . 'Please try again later.';
    } catch (Throwable $exception) {
        // Handle unexpected errors without exposing technical details.
        error_log(
            'Unexpected customer registration error: '
            . $exception->getMessage()
        );

        $status =
            'An unexpected error occurred. Please try again later.';
    }
}

/**
 * Preserve form values and display validation errors.
 */
$_SESSION['registration_flash'] = [
    'values' => $values,
    'errors' => $errors,
    'status' => $status !== ''
        ? $status
        : 'Please correct the highlighted fields and try again.',
];

// Return to the form using the Post/Redirect/Get pattern.
redirect_to_registration();