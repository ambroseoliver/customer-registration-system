<?php

declare(strict_types=1);

/**
 * Retrieve a required environment variable.
 */
function required_database_setting(string $name): string
{
    $value = getenv($name);

    if ($value === false || $value === '') {
        throw new RuntimeException(
            "The {$name} environment variable is not configured."
        );
    }

    return $value;
}

/**
 * Database configuration.
 */
$host = getenv('DB_HOST') ?: '127.0.0.1';
$port = getenv('DB_PORT') ?: '3306';

$database = required_database_setting('DB_NAME');
$username = required_database_setting('DB_USER');
$password = required_database_setting('DB_PASSWORD');

/**
 * Validate the database port.
 */
if (!ctype_digit($port) || (int) $port < 1 || (int) $port > 65535) {
    throw new RuntimeException(
        'DB_PORT must be a valid TCP port.'
    );
}

/**
 * Validate the database name.
 */
if (!preg_match('/\A[a-zA-Z0-9_]+\z/', $database)) {
    throw new RuntimeException(
        'DB_NAME contains unsupported characters.'
    );
}

/**
 * Build the PDO connection string.
 */
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
    $host,
    (int) $port,
    $database
);

/**
 * Establish and return the database connection.
 */
return new PDO($dsn, $username, $password, [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES   => false,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);