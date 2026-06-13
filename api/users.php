<?php
/**
 * VULNERABLE API — GET /api/users.php
 *
 * Injection points:
 *   ?id=1                   → integer injection (WHERE id = {id})
 *   ?role=user              → string injection (WHERE account_type = '{role}')
 *   ?sort=firstname         → ORDER BY injection (ORDER BY {sort})
 *   ?search=john            → LIKE injection (LIKE '%{search}%')
 *
 * Example attacks:
 *   ?id=1 UNION SELECT 1,email,password,account_type,5,6 FROM user--
 *   ?role=' OR '1'='1
 *   ?sort=firstname,password--   (extra column leak)
 *   ?search=%' UNION SELECT id,name,filepath,1,1,1 FROM secret--
 */
header('Content-Type: application/json');
header('X-Powered-By: PHP/VulnerableAPI');  // intentional header info leak
require_once __DIR__ . '/_db.php';

$pdo    = get_pdo();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $id     = $_GET['id']     ?? '';
    $role   = $_GET['role']   ?? '';
    $sort   = $_GET['sort']   ?? 'id';
    $search = $_GET['search'] ?? '';

    // Base query
    $sql = "SELECT id, firstname, lastname, email, phone, account_type FROM user WHERE 1=1";

    if ($id !== '')     $sql .= " AND id = {$id}";           // VULN: integer injection
    if ($role !== '')   $sql .= " AND account_type = '{$role}'";   // VULN: string injection
    if ($search !== '') $sql .= " AND (firstname LIKE '%{$search}%' OR lastname LIKE '%{$search}%')"; // VULN: LIKE injection

    $sql .= " ORDER BY {$sort}";   // VULN: ORDER BY injection

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result = $pdo->query($sql);
        $rows   = $result->fetchAll(PDO::FETCH_ASSOC);

        // Expose the raw SQL in response header — common API mistake
        header('X-Debug-SQL: ' . str_replace("\n", ' ', $sql));

        json_out(200, [
            'status'  => 'ok',
            'query'   => $sql,   // VULN: exposing the query in response body
            'count'   => count($rows),
            'results' => $rows,
        ]);
    } catch (PDOException $e) {
        json_out(500, [
            'status' => 'error',
            'query'  => $sql,
            'error'  => $e->getMessage(),  // VULN: raw DB error in response
        ]);
    }
} else {
    json_out(405, ['error' => 'Method not allowed']);
}
