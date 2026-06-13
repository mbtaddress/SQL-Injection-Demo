<?php
/**
 * VULNERABLE API — GET /api/search.php
 *
 * Injection points:
 *   ?q=      → LIKE injection + UNION possible
 *   ?limit=  → raw integer in LIMIT clause
 *   ?offset= → raw integer in OFFSET clause
 *
 * Example attacks:
 *   ?q=%' UNION SELECT id,name,filepath,1 FROM secret--
 *   ?q=%' AND 1=2 UNION SELECT 1,group_concat(table_name),3,4 FROM information_schema.tables WHERE table_schema=database()--
 *   ?limit=5 UNION SELECT 1,2,3,4--  (LIMIT clause injection)
 */
header('Content-Type: application/json');
require_once __DIR__ . '/_db.php';

$pdo    = get_pdo();
$q      = $_GET['q']      ?? '';
$limit  = $_GET['limit']  ?? '10';
$offset = $_GET['offset'] ?? '0';

// VULN: raw LIMIT/OFFSET values
$sql = "SELECT id, firstname, lastname, email FROM user WHERE 1=1";

if ($q !== '') {
    // VULN: raw LIKE injection
    $sql .= " AND (firstname LIKE '%{$q}%' OR lastname LIKE '%{$q}%' OR email LIKE '%{$q}%')";
}

// VULN: unsanitized LIMIT/OFFSET
$sql .= " LIMIT {$limit} OFFSET {$offset}";

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $pdo->query($sql);
    $rows   = $result->fetchAll(PDO::FETCH_ASSOC);

    json_out(200, [
        'status'  => 'ok',
        'query'   => $sql,
        'count'   => count($rows),
        'results' => $rows,
    ]);
} catch (PDOException $e) {
    json_out(500, [
        'status' => 'error',
        'query'  => $sql,
        'error'  => $e->getMessage(),
    ]);
}
