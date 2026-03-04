<?php
declare(strict_types=1);

require_once __DIR__ . '/cors.php';
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/connection.php';

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = rtrim(parse_url($uri, PHP_URL_PATH) ?? '/', '/');
$path = $path === '' ? '/' : $path;

if ($method === 'GET' && ($path === '/health' || $path === '/heath')) {
    if (!$pdo instanceof PDO) {
        // Mostra o motivo real
        sendResponse(503, [
            'message' => 'nao conectado ao banco de dados',
            'error' => $connectionError ?? 'pdo null (sem detalhe)',
            'env' => [
                'DB_HOST' => envValue('DB_HOST'),
                'DB_PORT' => envValue('DB_PORT'),
                'DB_USER' => envValue('DB_USER'),
                'DB_NAME' => envValue('DB_NAME'),
            ],
        ]);
    }

    try {
        $pdo->query('SELECT 1');
        sendResponse(200, ['message' => 'conectado ao banco de dados']);
    } catch (PDOException $e) {
        sendResponse(503, ['message' => 'nao conectado ao banco de dados', 'error' => $e->getMessage()]);
    }
}

sendResponse(404, ['error' => 'Rota nao encontrada']);

function sendResponse(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT);
    exit;
}
