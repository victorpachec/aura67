<?php
declare(strict_types=1);

$pdo = null;
$connectionError = null;

loadDotEnv(__DIR__ . '/.env');

$host = envValue('DB_HOST');
$port = envValue('DB_PORT') ?: '3306';
$user = envValue('DB_USER');
$pass = envValue('DB_PASSWORD');
$db = envValue('DB_NAME');

if ($host === '' || $user === '' || $db === '') {
    $connectionError = 'Variaveis DB_HOST, DB_USER e DB_NAME sao obrigatorias';
} else {
    try {
        $pdo = new PDO(
            "mysql:host=$host;port=$port;dbname=$db;charset=utf8",
            $user,
            $pass,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );
    } catch (PDOException $e) {
        $connectionError = $e->getMessage();
    }
}

function envValue(string $key): string
{
    $value = $_ENV[$key] ?? getenv($key);
    if ($value === false || $value === null) {
        return '';
    }

    return trim((string) $value);
}

function loadDotEnv(string $filePath): void
{
    if (!is_file($filePath) || !is_readable($filePath)) {
        return;
    }

    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if ($lines === false) {
        return;
    }

    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        $parts = explode('=', $line, 2);
        if (count($parts) !== 2) {
            continue;
        }

        $name = trim($parts[0]);
        $value = trim($parts[1]);

        if ($name === '') {
            continue;
        }

        if ((str_starts_with($value, '"') && str_ends_with($value, '"')) || (str_starts_with($value, "'") && str_ends_with($value, "'"))) {
            $value = substr($value, 1, -1);
        }

        if (!array_key_exists($name, $_ENV)) {
            $_ENV[$name] = $value;
        }

        if (getenv($name) === false) {
            putenv("$name=$value");
        }
    }
}
