<?php
/**
 * DEMO: Routed SQL Injection
 *
 * Concept: The input is injected into Query 1. Query 1's RESULT is then
 * used unsanitized in Query 2. The attack "routes" through the application's
 * own logic — Query 1 is the vector, Query 2 is the target.
 *
 * Flow in this demo:
 *   1. User searches for a username (Query 1 — looks up user ID)
 *   2. App takes the returned ID and fetches full profile (Query 2 — vulnerable)
 *   3. Attacker crafts Query 1's result to inject into Query 2
 */
if ( ! defined('SQL_INJECTION_IN_PHP') ) die('Direct access not permitted');

$result1_rows = [];
$result2_rows = [];
$q1 = '';
$q2 = '';
$search = $_GET['search'] ?? '';

if ($search !== '') {
    // ── Query 1: find user ID by username ─────────────────────────────────────
    // This query is parameterized — safe on its own
    $stmt = $pdo->prepare("SELECT id FROM user WHERE firstname = :name");
    $stmt->execute([':name' => $search]);
    $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

    $q1 = "SELECT id FROM user WHERE firstname = " . htmlspecialchars("'$search'") . "  ← safe (parameterized)";

    if (!empty($rows)) {
        foreach ($rows as $r) {
            $id = $r['id'];

            // ── Query 2: fetch profile using ID from Query 1 ──────────────────
            // VULNERABLE — the returned ID is concatenated raw into Query 2
            $q2 = "SELECT id, firstname, lastname, email, password, account_type FROM user WHERE id = {$id}";

            try {
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                $result2 = $pdo->query($q2);
                foreach ($result2 as $row) {
                    $result2_rows[] = $row;
                }
            } catch (\PDOException $e) {
                echo '<div class="alert alert-danger"><strong>Query 2 Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
        }
    }
}
?>

<div class="card mb-4 border-warning">
  <div class="card-header bg-warning text-dark">
    <strong>⚠️ Routed SQL Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Concept:</strong> Query 1 (safe) produces a result that flows into Query 2 (vulnerable). The attacker manipulates what Query 1 returns so that Query 2 executes an injection.</p>
    <p class="mb-1"><strong>How:</strong> Inject into the first query so it returns a value like <code>1 UNION SELECT 1--</code> — when this is embedded in Query 2, it becomes SQL, not data.</p>
    <p class="mb-0 text-muted small">Search by first name below. Query 1 looks up the user's ID. Query 2 fetches their full profile using that ID.</p>
  </div>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="routed"/>
  <div class="input-group">
    <input type="text" name="search" class="form-control"
           placeholder="Search by first name — or try routed payloads"
           value="<?= htmlspecialchars($search) ?>">
    <button class="btn btn-outline-secondary" type="submit">Search</button>
  </div>
</form>

<?php if ($search !== ''): ?>

<div class="row g-3 mb-3">
  <div class="col-md-6">
    <div class="alert alert-success mb-0">
      <strong>Query 1 (safe — parameterized):</strong><br>
      <code><?= htmlspecialchars($q1) ?></code>
    </div>
  </div>
  <div class="col-md-6">
    <div class="alert alert-danger mb-0">
      <strong>Query 2 (VULNERABLE — raw ID from Query 1):</strong><br>
      <code><?= htmlspecialchars($q2 ?: 'Not executed — no user found') ?></code>
    </div>
  </div>
</div>

<?php if (!empty($result2_rows)): ?>
<table class="table table-bordered table-sm">
  <thead class="table-dark">
    <tr><th>ID</th><th>Firstname</th><th>Lastname</th><th>Email</th><th>Password</th><th>Role</th></tr>
  </thead>
  <tbody>
    <?php foreach ($result2_rows as $row): ?>
    <tr>
      <?php foreach ($row as $k => $v): if (!is_int($k)): ?>
      <td><?= htmlspecialchars((string)$v) ?></td>
      <?php endif; endforeach; ?>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
  <div class="alert alert-info">No results. Try searching: <code>tesh</code></div>
<?php endif; ?>

<?php endif; ?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white"><strong>Routed Injection Payloads</strong></div>
  <div class="card-body">
    <p class="small mb-2">These payloads don't inject into Query 1's WHERE clause (that's parameterized). Instead they are crafted to make Query 1 return a value that <em>becomes</em> SQL in Query 2.</p>

    <h6 class="fw-bold">Scenario: User "tesh" has ID 8. Query 2 becomes: <code>WHERE id = 8</code></h6>
    <div class="bg-light p-2 rounded mb-3">
      <strong>Search:</strong> <code>tesh</code><br>
      <span class="text-muted small">Q1 returns 8 → Q2: <code>WHERE id = 8</code> → safe result</span>
    </div>

    <h6 class="fw-bold">What if we could control what Q1 returns?</h6>
    <p class="small">In a real routed scenario, the attacker finds another injection point that controls the intermediate value. For example, a stored value in the DB (second-order variant) or a manipulated lookup that returns a crafted ID like <code>1 UNION SELECT email FROM user--</code>.</p>

    <h6 class="fw-bold">Simulated Routed Payload (manually set the intermediate value)</h6>
    <p class="small">Since Q1 is safe here, to demonstrate Q2's vulnerability, use the <code>uid</code> URL parameter to directly test it:</p>
    <div class="bg-light p-2 rounded mb-1"><a href="?action=error_based&uid=8 UNION SELECT 1,email,password,account_type,5,6 FROM user--">Try via error_based page with UNION</a></div>

    <h6 class="fw-bold mt-3">Classic Routed SQLi Pattern (code example)</h6>
    <pre class="bg-light p-2 rounded small">// Unsafe pattern — trusting a DB-returned value in Query 2
$username = $_GET['username'];

// Q1 — looks safe
$stmt = $pdo->prepare("SELECT id FROM user WHERE firstname = :name");
$stmt->execute([':name' => $username]);
$id = $stmt->fetchColumn();  // e.g. returns "8 UNION SELECT..."

// Q2 — VULNERABLE: $id came from DB but is used raw
$profile = $pdo->query("SELECT * FROM user WHERE id = {$id}");</pre>

    <p class="small mb-0">The attacker's goal is to make Q1 return a malicious value (via stored injection, parameter manipulation, or a separate vuln) so Q2 executes their payload.</p>
  </div>
</div>

<div class="card border-success">
  <div class="card-header bg-success text-white"><strong>Fix</strong></div>
  <div class="card-body">
<pre class="bg-light p-3 rounded small">// Fix Q2 — parameterize regardless of where the value came from
$id = (int) $stmt->fetchColumn();  // cast to int — breaks non-numeric injection

$stmt2 = $pdo->prepare("SELECT * FROM user WHERE id = :id");
$stmt2->execute([':id' => $id]);</pre>
    <p class="mb-0 text-success"><strong>Rule:</strong> Parameterize every query. Never trust a value just because it came from the database — values from DB reads are potential injection sources in Q2.</p>
  </div>
</div>
