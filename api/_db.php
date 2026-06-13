<?php
// Shared DB connection for the API
define('SQL_INJECTION_IN_PHP', true);
require_once __DIR__ . '/../vendor/autoload.php';

use SqlInjection\MySQLConnection;

function get_pdo(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $pdo = (new MySQLConnection())->connect();
    }
    return $pdo;
}

function json_out(int $status, mixed $data): void {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    exit;
}

function get_body(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}
