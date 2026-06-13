<?php
/**
 * VULNERABLE API — PUT/PATCH /api/user_update.php
 *
 * Injection via JSON body fields.
 * Also vulnerable to mass assignment (attacker sends account_type field).
 *
 * Example attacks:
 * PUT body: {"id": 1, "firstname": "x', account_type='admin' WHERE id=1--", "lastname": "y"}
 * PUT body: {"id": 1, "account_type": "admin"}   // mass assignment
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_db.php';

$pdo    = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

if (!in_array($method, ['PUT', 'PATCH', 'POST'])) {
    json_out(405, ['error' => 'PUT/PATCH only']);
}

$body = get_body();

$id        = $body['id']        ?? '';
$firstname = $body['firstname'] ?? '';
$lastname  = $body['lastname']  ?? '';

if (!$id) json_out(400, ['error' => 'id required']);

// VULN 1: raw string interpolation in UPDATE SET clause
$sql = "UPDATE user SET firstname='{$firstname}', lastname='{$lastname}' WHERE id={$id}";

// VULN 2: mass assignment — attacker can send account_type in the body
if (isset($body['account_type'])) {
    $acct = $body['account_type'];
    $sql = "UPDATE user SET firstname='{$firstname}', lastname='{$lastname}', account_type='{$acct}' WHERE id={$id}";
}

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $affected = $pdo->exec($sql);

    json_out(200, [
        'status'   => 'updated',
        'query'    => $sql,
        'affected' => $affected,
    ]);
} catch (PDOException $e) {
    json_out(500, [
        'status' => 'error',
        'query'  => $sql,
        'error'  => $e->getMessage(),
    ]);
}
