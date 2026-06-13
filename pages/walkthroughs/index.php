<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<h2 class="mb-1">SQL Injection Walkthroughs</h2>
<p class="text-muted mb-4">Step-by-step guides for every vulnerability demonstrated in this app, including attack payloads, what to observe, and how to fix it.</p>

<div class="row g-4">

  <!-- Login Bypass -->
  <div class="col-md-6">
    <div class="card h-100 border-danger">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <strong>1. Authentication Bypass</strong>
        <span class="badge bg-light text-danger">Basic</span>
      </div>
      <div class="card-body">
        <p class="card-text">Bypass the login form entirely using comment-based and tautology injections. No valid password required.</p>
        <div class="mb-2"><span class="badge bg-secondary">In-band</span> <span class="badge bg-secondary">Classic</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=login_bypass" class="btn btn-danger btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- UNION -->
  <div class="col-md-6">
    <div class="card h-100 border-danger">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <strong>2. UNION-Based Extraction</strong>
        <span class="badge bg-light text-danger">Intermediate</span>
      </div>
      <div class="card-body">
        <p class="card-text">Use UNION SELECT to pull data from hidden tables including <code>secret</code> and <code>information_schema</code>.</p>
        <div class="mb-2"><span class="badge bg-secondary">In-band</span> <span class="badge bg-secondary">Data Exfil</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=union" class="btn btn-danger btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- Error-Based -->
  <div class="col-md-6">
    <div class="card h-100 border-warning">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <strong>3. Error-Based Injection</strong>
        <span class="badge bg-dark text-white">Intermediate</span>
      </div>
      <div class="card-body">
        <p class="card-text">Force the database to leak data inside error messages using <code>extractvalue()</code> and <code>updatexml()</code>.</p>
        <div class="mb-2"><span class="badge bg-secondary">In-band</span> <span class="badge bg-secondary">Error Channel</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=error_based" class="btn btn-warning btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- Blind Boolean -->
  <div class="col-md-6">
    <div class="card h-100 border-warning">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <strong>4. Blind Boolean-Based</strong>
        <span class="badge bg-dark text-white">Advanced</span>
      </div>
      <div class="card-body">
        <p class="card-text">Extract data character by character using only true/false page responses — no data, no errors visible.</p>
        <div class="mb-2"><span class="badge bg-secondary">Blind</span> <span class="badge bg-secondary">Boolean</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=blind_boolean" class="btn btn-warning btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- Time-Based -->
  <div class="col-md-6">
    <div class="card h-100 border-warning">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <strong>5. Time-Based Blind</strong>
        <span class="badge bg-dark text-white">Advanced</span>
      </div>
      <div class="card-body">
        <p class="card-text">Use <code>SLEEP()</code> to extract data when there is zero visual output — the only signal is response delay.</p>
        <div class="mb-2"><span class="badge bg-secondary">Blind</span> <span class="badge bg-secondary">Timing</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=time_based" class="btn btn-warning btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- Second Order -->
  <div class="col-md-6">
    <div class="card h-100 border-warning">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <strong>6. Second-Order (Stored)</strong>
        <span class="badge bg-dark text-white">Advanced</span>
      </div>
      <div class="card-body">
        <p class="card-text">A payload is stored safely with a prepared statement but fires later when retrieved and used in a raw query.</p>
        <div class="mb-2"><span class="badge bg-secondary">Stored</span> <span class="badge bg-secondary">Delayed</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=second_order" class="btn btn-warning btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- Privilege Escalation -->
  <div class="col-md-6">
    <div class="card h-100 border-danger">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <strong>7. Privilege Escalation</strong>
        <span class="badge bg-light text-danger">Intermediate</span>
      </div>
      <div class="card-body">
        <p class="card-text">Inject extra columns into an UPDATE SET clause to change your own <code>account_type</code> to <code>admin</code>.</p>
        <div class="mb-2"><span class="badge bg-secondary">UPDATE</span> <span class="badge bg-secondary">Privilege</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=privesc" class="btn btn-danger btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

  <!-- WAF Bypass -->
  <div class="col-md-6">
    <div class="card h-100 border-info">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <strong>8. WAF Bypass Techniques</strong>
        <span class="badge bg-light text-info">Advanced</span>
      </div>
      <div class="card-body">
        <p class="card-text">Evade naive keyword-blacklist WAFs using case variation, comment injection, hex encoding, and more.</p>
        <div class="mb-2"><span class="badge bg-secondary">Evasion</span> <span class="badge bg-secondary">Obfuscation</span></div>
      </div>
      <div class="card-footer">
        <a href="?action=walkthrough&topic=waf_bypass" class="btn btn-info btn-sm">View Walkthrough →</a>
      </div>
    </div>
  </div>

</div>

<!-- Tools Section -->
<hr class="my-5">
<h3 class="mb-3">🛠 Tools Used During SQL Injection Testing</h3>
<div class="row g-4">

  <div class="col-md-4">
    <div class="card h-100 border-secondary">
      <div class="card-header bg-dark text-white"><strong>sqlmap</strong> — Automated SQLi</div>
      <div class="card-body">
        <p class="small mb-2">The most widely used automated SQL injection tool. Detects and exploits injection points, dumps databases, and bypasses WAFs automatically.</p>
        <p class="small mb-1"><strong>Basic usage against this app:</strong></p>
        <pre class="bg-light p-2 small rounded">sqlmap -u "http://localhost:8080/profile.php?action=union_search&search=test" \
  --dbs --batch</pre>
        <pre class="bg-light p-2 small rounded">sqlmap -u "http://localhost:8080/profile.php?action=error_based&uid=1" \
  -D sql_demo --tables --batch</pre>
        <pre class="bg-light p-2 small rounded">sqlmap -u "http://localhost:8080/profile.php?action=blind_boolean&uid=1" \
  --technique=B --dump -T user --batch</pre>
        <pre class="bg-light p-2 small rounded">sqlmap -u "http://localhost:8080/profile.php?action=time_based&uid=1" \
  --technique=T --level=3 --batch</pre>
      </div>
      <div class="card-footer"><a href="https://sqlmap.org" target="_blank" class="btn btn-dark btn-sm">sqlmap.org ↗</a></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card h-100 border-secondary">
      <div class="card-header bg-dark text-white"><strong>Burp Suite</strong> — HTTP Interception</div>
      <div class="card-body">
        <p class="small mb-2">Industry-standard proxy for intercepting, modifying, and replaying HTTP requests. Essential for manual SQLi testing and understanding request flow.</p>
        <p class="small mb-1"><strong>Workflow with this app:</strong></p>
        <ol class="small ps-3 mb-2">
          <li>Set browser proxy to <code>127.0.0.1:8080</code></li>
          <li>Intercept the search or login request</li>
          <li>Send to <strong>Repeater</strong> to manually test payloads</li>
          <li>Send to <strong>Intruder</strong> for automated character brute-force (blind SQLi)</li>
          <li>Use <strong>Scanner</strong> (Pro) for automatic injection detection</li>
        </ol>
        <p class="small mb-0"><strong>Intruder payload for blind boolean:</strong><br>
        <code>1 AND substring((SELECT password FROM user WHERE id=8),§1§,1)='§a§'</code></p>
      </div>
      <div class="card-footer"><a href="https://portswigger.net/burp" target="_blank" class="btn btn-dark btn-sm">portswigger.net ↗</a></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card h-100 border-secondary">
      <div class="card-header bg-dark text-white"><strong>Havij / BBQSQL / others</strong></div>
      <div class="card-body">
        <p class="small mb-2">Other tools useful during SQL injection practice:</p>
        <ul class="small ps-3">
          <li><strong>BBQSQL</strong> — Python-based blind SQLi framework, scriptable</li>
          <li><strong>Havij</strong> — GUI-based automated tool (Windows)</li>
          <li><strong>NoSQLMap</strong> — for NoSQL injection (MongoDB etc.)</li>
          <li><strong>ghauri</strong> — modern sqlmap alternative with better WAF bypass</li>
          <li><strong>curl</strong> — raw HTTP request crafting</li>
          <li><strong>Python requests</strong> — scripting automated blind extraction</li>
        </ul>
        <p class="small mb-1 mt-2"><strong>curl quick test:</strong></p>
        <pre class="bg-light p-2 small rounded">curl -s "http://localhost:8080/profile.php?\
action=error_based&uid=1+AND+extractvalue\
(1,concat(0x7e,(SELECT+version())))"</pre>
      </div>
      <div class="card-footer"><a href="?action=walkthrough&topic=tools" class="btn btn-dark btn-sm">Full Tools Guide →</a></div>
    </div>
  </div>

</div>
