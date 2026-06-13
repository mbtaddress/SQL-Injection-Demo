<?php
/**
 * VULNERABLE API — POST /api/login.php
 *
 * Injection via JSON body:
 *   {"email": "' OR '1'='1'--", "password": "x"}
 *   {"email": "admin@gami.com'--", "password": "x"}
 *
 * Also vulnerable via X-Forwarded-For header injection (logged to DB):
 *   -H "X-Forwarded-For: ' OR '1'='1'--"
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_db.php';

$pdo = get_pdo();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(405, ['error' => 'POST only']);
}

$body     = get_body();
$email    = $body['email']    ?? '';
$password = $body['password'] ?? '';

// VULN: raw interpolation from JSON body
$sql = "SELECT id, firstname, email, account_type FROM user
        WHERE email = '{$email}' AND password = '{$password}'";

// VULN: X-Forwarded-For header injected into audit log query
$ip  = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$log_sql = "INSERT INTO login_log (email, ip, created_at) VALUES ('{$email}', '{$ip}', NOW())";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Try to create log table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS login_log (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(200),
        ip VARCHAR(200),
        created_at DATETIME
    )");

    // Log the attempt (vulnerable to header injection)
    try { $pdo->exec($log_sql); } catch (Exception $e) { /* swallow */ }

    $result = $pdo->query($sql);
    $user   = $result->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        json_out(200, [
            'status'   => 'authenticated',
            'query'    => $sql,
            'user'     => $user,
            'token'    => base64_encode($user['email'] . ':' . time()),  // VULN: weak token
        ]);
    } else {
        json_out(401, [
            'status' => 'unauthorized',
            'query'  => $sql,    // VULN: exposed query even on failure
        ]);
    }
} catch (PDOException $e) {
    json_out(500, [
        'status' => 'error',
        'query'  => $sql,
        'error'  => $e->getMessage(),
    ]);
}
