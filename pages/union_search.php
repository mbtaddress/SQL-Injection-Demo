<?php
/**
 * DEMO: UNION-Based SQL Injection
 *
 * Attack goal: Inject a UNION SELECT to pull data from a different table (secret)
 * by exploiting a vulnerable search field.
 *
 * Try this in the search box:
 *   ' UNION SELECT id, name, filepath, 1, 1, 1, 1 FROM secret-- 
 *
 * Or enumerate tables from information_schema:
 *   ' UNION SELECT table_name, column_name, column_type, 1, 1, 1, 1
 *     FROM information_schema.columns WHERE table_schema=database()-- 
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — UNION-Based Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Use a UNION SELECT to extract data from other tables.</p>
    <p class="mb-1"><strong>Try these payloads in the search box:</strong></p>
    <ul class="mb-0">
      <li><code>' UNION SELECT id, name, filepath, 1, 1, 1, 1 FROM secret-- </code> — dumps the <em>secret</em> table</li>
      <li><code>' UNION SELECT table_name, column_name, column_type, 1, 1, 1, 1 FROM information_schema.columns WHERE table_schema=database()-- </code> — enumerates the full schema</li>
    </ul>
    <p class="mt-2 mb-0 text-muted small">Tip: The number of columns in your UNION must match the original query (7 columns here).</p>
  </div>
</div>

<!-- Search Form -->
<form method="get" class="mb-3">
  <input type="hidden" name="action" value="union_search"/>
  <div class="input-group">
    <input type="text" name="search" class="form-control"
           placeholder="Search users by first name..."
           value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
    <button class="btn btn-outline-secondary" type="submit">Search</button>
  </div>
</form>

<?php
// Intentionally vulnerable — raw GET input injected into the query
$search = $_GET['search'] ?? '';

if ($search !== '') {
    // The query selects 7 columns — UNION payloads must match this count
    $sql = "SELECT id, firstname, lastname, email, phone, password, account_type
            FROM user
            WHERE firstname LIKE '%{$search}%'";

    // Show the generated SQL so learners can see the injection
    echo '<div class="alert alert-secondary"><strong>Generated SQL:</strong><br><code>' . htmlspecialchars($sql) . '</code></div>';

    try {
        $result = $pdo->query($sql);

        if ($result && $result->rowCount() > 0) {
            echo '<table class="table table-bordered table-sm">';
            echo '<thead class="table-dark"><tr>
                    <th>ID</th><th>Firstname</th><th>Lastname</th>
                    <th>Email</th><th>Phone</th><th>Password</th><th>Role</th>
                  </tr></thead><tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                foreach ($row as $key => $val) {
                    if (!is_int($key)) { // skip numeric PDO keys
                        echo '<td>' . htmlspecialchars((string)$val) . '</td>';
                    }
                }
                echo '</tr>';
            }
            echo '</tbody></table>';
        } else {
            echo '<div class="alert alert-info">No results found.</div>';
        }
    } catch (\PDOException $e) {
        // Intentionally expose the error to show error-based info leakage
        echo '<div class="alert alert-danger"><strong>DB Error (intentionally exposed):</strong><br>'
             . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>
