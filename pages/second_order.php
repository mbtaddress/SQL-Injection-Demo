<?php
/**
 * DEMO: Second-Order (Stored) SQL Injection
 *
 * Attack flow:
 * 1. Register with a malicious username like:  admin'--
 *    → The INSERT uses a prepared statement, so it's stored safely in the DB.
 * 2. Visit the "Change Username" feature. It fetches the stored username from DB
 *    and injects it raw into a second query → injection fires on retrieval.
 *
 * Step-by-step:
 *   a) Register with email: victim2@demo.com, username: admin'--
 *   b) Log in as victim2@demo.com
 *   c) Click "Trigger Second-Order Injection" — the stored payload fires
 *
 * What makes this dangerous: the write (registration) is SAFE.
 * The vulnerability is in the READ that happens later.
 */

if ( ! defined('SQL_INJECTION_IN_PHP') ) {
    die('Direct access not permitted');
}

$message = '';
$stored_username = '';

// Step 1: SAFE register — uses prepared statement
if (isset($_POST['register'])) {
    $email    = $_POST['email'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($email && $username && $password) {
        // Safe INSERT — parameterized, so admin'-- is stored literally
        $stmt = $pdo->prepare("INSERT INTO second_order_users (email, username, password) VALUES (:email, :username, :password)");
        $stmt->execute([
            ':email'    => $email,
            ':username' => $username,
            ':password' => $password,
        ]);

        $message = '<div class="alert alert-success">
            ✅ Registered safely using a prepared statement.<br>
            <strong>Stored username:</strong> <code>' . htmlspecialchars($username) . '</code><br>
            Even though the username contains SQL syntax, it is stored literally. Now log in below.
        </div>';
    }
}

// Step 2: SAFE login — uses prepared statement
if (isset($_POST['login_second_order'])) {
    $email    = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, username FROM second_order_users WHERE email = :email AND password = :password");
    $stmt->execute([':email' => $email, ':password' => $password]);
    $row = $stmt->fetch(\PDO::FETCH_ASSOC);

    if ($row) {
        $_SESSION['so_user_id']  = $row['id'];
        $_SESSION['so_username'] = $row['username'];
        $message = '<div class="alert alert-success">
            ✅ Logged in as: <code>' . htmlspecialchars($row['username']) . '</code><br>
            Username was fetched safely. Now trigger the vulnerable feature below.
        </div>';
    } else {
        $message = '<div class="alert alert-danger">Login failed.</div>';
    }
}

// Step 3: VULNERABLE feature — fetches stored username and inserts it raw into a new query
if (isset($_POST['trigger_injection'])) {
    if (isset($_SESSION['so_user_id'])) {
        // SAFE: fetch the stored username
        $stmt = $pdo->prepare("SELECT username FROM second_order_users WHERE id = :id");
        $stmt->execute([':id' => $_SESSION['so_user_id']]);
        $stored_username = $stmt->fetchColumn();

        // VULNERABLE: the fetched value is now trusted and used without sanitization
        $vulnerable_sql = "SELECT id, email, username FROM second_order_users WHERE username = '{$stored_username}'";

        echo '<div class="alert alert-secondary"><strong>Vulnerable query (using stored username):</strong><br>'
             . '<code>' . htmlspecialchars($vulnerable_sql) . '</code></div>';

        try {
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $result = $pdo->query($vulnerable_sql);

            echo '<div class="alert alert-danger">';
            echo '<strong>Query result (injection fired on retrieval):</strong><br>';
            echo '<table class="table table-sm table-bordered mb-0">';
            echo '<thead><tr><th>ID</th><th>Email</th><th>Username</th></tr></thead><tbody>';
            foreach ($result as $row) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                echo '</tr>';
            }
            echo '</tbody></table>';
            echo '</div>';
        } catch (\PDOException $e) {
            echo '<div class="alert alert-danger"><strong>DB Error:</strong> ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        $message = '<div class="alert alert-warning">Please log in first (Step 2).</div>';
    }
}
?>

<div class="card mb-4 border-danger">
  <div class="card-header bg-danger text-white">
    <strong>⚠️ Vulnerable Page — Second-Order (Stored) Injection Demo</strong>
  </div>
  <div class="card-body">
    <p class="mb-1"><strong>Objective:</strong> Show that safe storage doesn't mean safe retrieval. A payload stored correctly can fire later in a different query.</p>
    <p class="mb-1"><strong>Steps:</strong></p>
    <ol class="mb-0">
      <li>Register with username: <code>admin'--</code> (the INSERT is safe — stored literally)</li>
      <li>Log in with those credentials</li>
      <li>Click "Trigger Injection" — the stored payload fires in the UPDATE/SELECT query</li>
    </ol>
    <p class="mt-2 mb-0 text-muted small">The write is protected. The read is not. This is second-order injection.</p>
  </div>
</div>

<?= $message ?>

<!-- Step 1: Register -->
<div class="card mb-3">
  <div class="card-header"><strong>Step 1 — Register (safe INSERT)</strong></div>
  <div class="card-body">
    <form method="post">
      <div class="row g-2">
        <div class="col-md-4">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-4">
          <input type="text" name="username" class="form-control" placeholder="Username — try: admin'--" required>
        </div>
        <div class="col-md-3">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col-md-1">
          <button type="submit" name="register" class="btn btn-primary w-100">Register</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Step 2: Login -->
<div class="card mb-3">
  <div class="card-header"><strong>Step 2 — Login (safe SELECT)</strong></div>
  <div class="card-body">
    <form method="post">
      <div class="row g-2">
        <div class="col-md-5">
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="col-md-5">
          <input type="password" name="password" class="form-control" placeholder="Password" required>
        </div>
        <div class="col-md-2">
          <button type="submit" name="login_second_order" class="btn btn-success w-100">Login</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Step 3: Trigger -->
<?php if (isset($_SESSION['so_user_id'])): ?>
<div class="card mb-3 border-danger">
  <div class="card-header bg-warning"><strong>Step 3 — Trigger the Injection (vulnerable SELECT using stored username)</strong></div>
  <div class="card-body">
    <p>Logged in as: <code><?= htmlspecialchars($_SESSION['so_username'] ?? '') ?></code></p>
    <p>Clicking below will take the stored username and embed it raw into a new SQL query.</p>
    <form method="post">
      <button type="submit" name="trigger_injection" class="btn btn-danger">Trigger Second-Order Injection</button>
    </form>
  </div>
</div>
<?php endif; ?>

<!-- Show all registered users for reference -->
<?php
try {
    $all = $pdo->query("SELECT id, email, username FROM second_order_users ORDER BY id DESC LIMIT 10");
    if ($all && $all->rowCount() > 0):
?>
<div class="card mt-3">
  <div class="card-header">Registered Users (second_order_users table)</div>
  <div class="card-body p-0">
    <table class="table table-sm table-striped mb-0">
      <thead><tr><th>ID</th><th>Email</th><th>Username (stored payload)</th></tr></thead>
      <tbody>
      <?php foreach ($all as $u): ?>
        <tr>
          <td><?= htmlspecialchars($u['id']) ?></td>
          <td><?= htmlspecialchars($u['email']) ?></td>
          <td><code><?= htmlspecialchars($u['username']) ?></code></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; } catch (\Exception $e) {
    echo '<div class="alert alert-warning">Note: Run the updated SQL schema to create the <code>second_order_users</code> table.</div>';
} ?>
