<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Routed SQLi</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">10. Routed SQL Injection</h2>
  <span class="badge bg-warning text-dark">Advanced</span>
  <span class="badge bg-secondary">Multi-hop</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>Routed SQL injection is a variant where the injection point is in <strong>Query 1</strong>, but the exploited query is <strong>Query 2</strong>. The attacker manipulates what Query 1 returns so that when its result is used in Query 2 without parameterization, injection fires in the second query.</p>
    <p>Unlike second-order injection (where the payload is stored then reused), routed injection happens within the same request — the data routes through the application's logic from one query to another.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/routed.php</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>The Two-Query Pattern</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>// Query 1 — finds a user ID by name (in our demo this is parameterized)
$stmt = $pdo->prepare("SELECT id FROM user WHERE firstname = :name");
$stmt->execute([':name' => $name]);
$id = $stmt->fetchColumn();  // returns: 8

// Query 2 — VULNERABLE: uses the ID from Q1 without parameterization
$profile = $pdo->query("SELECT * FROM user WHERE id = {$id}");
//                                              ↑ if $id is "8 UNION SELECT..." → injection!</code></pre>
    <p class="mt-2 text-danger"><strong>Root cause:</strong> Q2 trusts the value produced by Q1 as if it were clean data. In a routed scenario, the attacker finds a way to make Q1 return a malicious value.</p>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Understand the routing</h6>
    <p>Normal flow:</p>
    <div class="bg-light p-2 rounded mb-3">
      Search "tesh" → Q1 returns <code>8</code> → Q2: <code>WHERE id = 8</code> → normal profile
    </div>

    <h6 class="fw-bold">Step 2 — Attack path: make Q1 return a malicious value</h6>
    <p>In a real routed scenario, the attacker controls what Q1 returns via one of:</p>
    <ul class="mb-3">
      <li><strong>A separate injection in Q1</strong> — if Q1 itself is also vulnerable (not the case in this demo)</li>
      <li><strong>Second-order combination</strong> — register username <code>8 UNION SELECT...</code>, which gets stored then looked up by Q1</li>
      <li><strong>Parameter pollution</strong> — manipulate how Q1 receives its input to change its return value</li>
    </ul>

    <h6 class="fw-bold">Step 3 — Simulate Q2 injection directly</h6>
    <p>To see Q2's vulnerability, bypass Q1 and directly test Q2 via the <code>error_based</code> or <code>union_search</code> pages which use the same raw-ID pattern:</p>
    <div class="bg-light p-2 rounded mb-1"><code>8 UNION SELECT 1,email,password,account_type,5,6 FROM user--</code></div>
    <p class="text-muted small mb-3">When Q1 is made to return this string and Q2 receives it unparameterized, the UNION fires.</p>

    <h6 class="fw-bold">Step 4 — Combined second-order + routed attack</h6>
    <ol>
      <li>Register with username: <code>8) UNION SELECT 1,email,password,4,5,6 FROM user--</code></li>
      <li>A feature looks up your user by username (Q1)</li>
      <li>Q1 returns the stored malicious string</li>
      <li>Q2 embeds it raw: <code>WHERE id = 8) UNION SELECT ...</code></li>
    </ol>

    <a href="?action=routed" class="btn btn-sm btn-outline-warning">Open Routed SQLi Demo →</a>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Routed vs Second-Order — Key Difference</strong></div>
  <div class="card-body">
    <table class="table table-sm table-bordered small mb-0">
      <thead class="table-dark"><tr><th>Property</th><th>Second-Order</th><th>Routed</th></tr></thead>
      <tbody>
        <tr><td>When does injection fire?</td><td>Future request (stored, then triggered later)</td><td>Same request (routed through app logic)</td></tr>
        <tr><td>Where is payload stored?</td><td>In the database</td><td>Not stored — flows in one request</td></tr>
        <tr><td>How many queries?</td><td>1 (write) + 1 (read) across 2 requests</td><td>2 queries in same request</td></tr>
        <tr><td>Detection difficulty</td><td>Very hard — write path looks clean</td><td>Hard — Q1 looks safe, Q2 is rarely reviewed</td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>// Fix: cast to int and parameterize Q2
$id = (int) $stmt->fetchColumn();  // int cast blocks non-numeric injection

$stmt2 = $pdo->prepare("SELECT * FROM user WHERE id = :id");
$stmt2->execute([':id' => $id]);

// OR: combine both queries into one parameterized query
$combined = $pdo->prepare(
    "SELECT u2.* FROM user u2
     WHERE u2.id = (SELECT u1.id FROM user u1 WHERE u1.firstname = :name)"
);
$combined->execute([':name' => $name]);</code></pre>
    <p class="mb-0 text-success"><strong>Key principle:</strong> Treat any value used in a SQL query as untrusted — even if it came from the database. Cast numeric IDs, parameterize strings, and avoid multi-step raw query chaining.</p>
  </div>
</div>
