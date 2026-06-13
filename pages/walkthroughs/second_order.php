<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Second-Order Injection</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">6. Second-Order (Stored) Injection</h2>
  <span class="badge bg-warning text-dark">Advanced</span>
  <span class="badge bg-secondary">Stored</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>Second-order injection is the most deceptive SQL injection variant. The malicious payload is <strong>safely stored</strong> in the database using a prepared statement, so it appears harmless. The vulnerability fires later, in a different part of the application, when the stored value is retrieved and embedded into a new query <strong>without parameterization</strong>.</p>
    <p>This is commonly missed in code reviews because the write path looks secure. Auditing only the input path gives a false sense of safety.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/second_order.php</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>The Two-Phase Vulnerability</strong></div>
  <div class="card-body">
    <p><strong>Phase 1 — SAFE write (prepared statement):</strong></p>
<pre class="bg-light p-3 rounded"><code>// Username 'admin'-- is stored literally in the DB — safe ✅
$stmt = $pdo->prepare(
    "INSERT INTO second_order_users (email, username, password)
     VALUES (:email, :username, :password)"
);
$stmt->execute([':username' => "admin'--", ...]);</code></pre>

    <p class="mt-3"><strong>Phase 2 — VULNERABLE read (raw query using stored value):</strong></p>
<pre class="bg-light p-3 rounded"><code>// Fetched safely ✅
$stmt = $pdo->prepare("SELECT username FROM second_order_users WHERE id = :id");
$stmt->execute([':id' => $id]);
$stored_username = $stmt->fetchColumn();  // = "admin'--"

// VULNERABLE — trusted data treated as safe, injected raw ❌
$sql = "SELECT id, email, username FROM second_order_users
        WHERE username = '{$stored_username}'";
// Becomes: WHERE username = 'admin'--'
// The '--' comments out the closing quote — query is manipulated</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Register with a malicious username</h6>
    <p>Go to Step 1 on the demo page. Use any email but a crafted username:</p>
    <div class="bg-light p-2 rounded mb-1"><strong>Username:</strong> <code>admin'--</code></div>
    <div class="bg-light p-2 rounded mb-3"><strong>Username:</strong> <code>' OR '1'='1</code></div>
    <p class="text-muted small mb-3">The INSERT uses a prepared statement — these are stored literally. The database contains exactly the characters you typed, including the quote.</p>

    <h6 class="fw-bold">Step 2 — Log in</h6>
    <p>Use the email and password you registered with. The login also uses a prepared statement — completely safe.</p>

    <h6 class="fw-bold">Step 3 — Trigger the injection</h6>
    <p>Click "Trigger Second-Order Injection". The app fetches your stored username and embeds it directly into a new query:</p>
    <div class="bg-light p-2 rounded mb-1">
      <code>SELECT id, email, username FROM second_order_users WHERE username = 'admin'--'</code>
    </div>
    <p class="text-muted small mb-3">The <code>'--</code> terminates the string and comments out the rest. The WHERE condition becomes <code>WHERE username = 'admin'</code> — potentially matching a different user's record.</p>

    <h6 class="fw-bold">More dangerous payloads</h6>
    <div class="bg-light p-2 rounded mb-1"><strong>Username:</strong> <code>' UNION SELECT 1, email, password FROM second_order_users-- </code></div>
    <p class="text-muted small mb-1">The stored value turns the retrieval query into a UNION dump.</p>
    <div class="bg-light p-2 rounded mb-3"><strong>Username:</strong> <code>' OR '1'='1</code></div>
    <p class="text-muted small mb-3">Returns all rows — the WHERE is always true.</p>

    <a href="?action=second_order" class="btn btn-sm btn-outline-warning">Open Second-Order Demo →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix — Parameterize Every Query, Including Read-Back Paths</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>// Even though $stored_username came from the database,
// it must still be treated as untrusted and bound as a parameter
$stmt = $pdo->prepare(
    "SELECT id, email, username FROM second_order_users
     WHERE username = :username"
);
$stmt->execute([':username' => $stored_username]);</code></pre>
    <p class="mb-0 text-success"><strong>Rule:</strong> Data that originates from the database is not automatically safe to embed in SQL. Treat all values — regardless of source — as potentially hostile.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Why This is Hard to Find</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li>Security scanners often only test the <em>input</em> to a stored query, not the <em>output</em> of a subsequent query.</li>
      <li>Code reviewers may audit the registration path, see prepared statements, and mark it secure.</li>
      <li>The payload may be stored for days or months before an admin or automated process triggers the vulnerable read path.</li>
      <li><strong>Real-world examples:</strong> WordPress plugin vulnerabilities, forum username injection, CMS profile field injection.</li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Detection Approach</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Grep for concatenated queries</strong> that use variables fetched from the DB: <code>grep -r "\$.*=.*query\|pdo->query.*\$" pages/</code></li>
      <li><strong>Taint analysis tools</strong> like PHPStan with taint tracking, or Psalm with security plugins, can trace values from DB reads to query executions.</li>
      <li><strong>Manual review</strong> — any place a value retrieved from the DB is later used in SQL string construction is a candidate.</li>
    </ul>
  </div>
</div>
