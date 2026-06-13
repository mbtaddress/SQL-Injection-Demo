<?php
/**
 * Database Reset — wipes all tables and re-seeds default data.
 * Accessible from the lab navbar. Requires an active session.
 */
session_start();

define('SQL_INJECTION_IN_PHP', true);
require 'vendor/autoload.php';
use SqlInjection\MySQLConnection;

$pdo = (new MySQLConnection())->connect();
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$errors  = [];
$success = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_reset'])) {
    try {
        $pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

        // ── Truncate all tables ────────────────────────────────────────────────
        $tables = ['user', 'employees', 'secret', 'second_order_users', 'login_log'];
        foreach ($tables as $t) {
            try {
                $pdo->exec("TRUNCATE TABLE `{$t}`");
                $success[] = "Truncated: <code>{$t}</code>";
            } catch (PDOException $e) {
                // Table may not exist yet — create it below
            }
        }

        // ── Recreate tables that may be missing ───────────────────────────────
        $pdo->exec("CREATE TABLE IF NOT EXISTS `second_order_users` (
            `id`       INT AUTO_INCREMENT PRIMARY KEY,
            `email`    VARCHAR(200) NOT NULL,
            `username` VARCHAR(200) NOT NULL,
            `password` VARCHAR(200) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec("CREATE TABLE IF NOT EXISTS `login_log` (
            `id`         INT AUTO_INCREMENT PRIMARY KEY,
            `email`      VARCHAR(200),
            `ip`         VARCHAR(200),
            `created_at` DATETIME
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

        $pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

        // ── Seed: user table ──────────────────────────────────────────────────
        $pdo->exec("INSERT INTO `user`
            (`id`, `firstname`, `lastname`, `age`, `email`, `phone`, `password`, `account_type`) VALUES
            (1,  'Admin',    'User',    30, 'admin@demo.com',    '0900000001', 'admin123',  'admin'),
            (2,  'Addis',    'Abeba',   28, 'alice@demo.com',    '0900000002', 'addis123',  'user'),
            (3,  'Abebe',      'Kebede',   35, 'bob@demo.com',      '0900000003', 'abebe123',    'user'),
            (4,  'challa',  'Diriba',   22, 'charlie@demo.com',  '0900000004', 'challa123','user'),
            (5,  'Diana',    'Prince',  26, 'diana@demo.com',    '0900000005', 'diana123',  'user'),
            (6,  'Eve',      'Hacker',  31, 'eve@demo.com',      '0900000006', 'eve123',    'user'),
            (7,  'Frank',    'Castle',  40, 'frank@demo.com',    '0900000007', 'frank123',  'user'),
            (8,  'enish',     'belete',    21, 'besu@gmail.com',    '0922101673', '123456',    'admin'),
            (9,  'Manager',  'Demo',    45, 'manager@demo.com',  '0900000009', 'manager123','admin')
        ");
        $success[] = "Seeded: <code>user</code> (9 users — 3 admins, 6 regular)";

        // ── Seed: employees table ─────────────────────────────────────────────
        $pdo->exec("INSERT INTO `employees`
            (`id`, `firstname`, `lastname`, `age`, `email`, `phone`, `password`, `account_type`) VALUES
            (1, 'admin',   'admin',   32, 'admin@gami.com',    '09212321312', 'admin',   'admin'),
            (2, 'manage',  'manager', 43, 'manager@gmail.com', '092312423',   'manager', 'admin')
        ");
        $success[] = "Seeded: <code>employees</code> (2 records)";

        // ── Seed: secret table ────────────────────────────────────────────────
        $pdo->exec("INSERT INTO `secret` (`id`, `name`, `filepath`) VALUES
            (1, 'AWS Production Keys',   '/var/secrets/aws_prod.pem'),
            (2, 'Database Backup',       '/backups/sql_dump_2024.sql'),
            (3, 'API Master Key',        '/config/api_master_key.txt'),
            (4, 'Employee Salary Sheet', '/hr/salaries_2024.xlsx'),
            (5, 'SSH Private Key',       '/root/.ssh/id_rsa'),
            (6, 'JWT Secret',            '/config/jwt_secret.key')
        ");
        $success[] = "Seeded: <code>secret</code> (6 sensitive records)";

        // ── Seed: second_order_users ──────────────────────────────────────────
        $pdo->exec("INSERT INTO `second_order_users` (`email`, `username`, `password`) VALUES
            ('alice@demo.com',  'alice',   'password1'),
            ('bob@demo.com',    'bob',     'password2'),
            ('victim@demo.com', 'victim',  'victim123')
        ");
        $success[] = "Seeded: <code>second_order_users</code> (3 records)";

        // ── Reset AUTO_INCREMENT ──────────────────────────────────────────────
        $pdo->exec("ALTER TABLE `user`               AUTO_INCREMENT = 100");
        $pdo->exec("ALTER TABLE `second_order_users` AUTO_INCREMENT = 10");
        $pdo->exec("ALTER TABLE `employees`          AUTO_INCREMENT = 10");
        $pdo->exec("ALTER TABLE `secret`             AUTO_INCREMENT = 10");
        $success[] = "Reset AUTO_INCREMENT counters";

    } catch (PDOException $e) {
        $errors[] = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Reset Database | SQLi Demo</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark px-3">
  <span class="navbar-brand">🔓 SQLi Demo — Database Reset</span>
  <div>
    <a href="index.php"   class="btn btn-outline-light btn-sm me-1">Home</a>
    <a href="profile.php" class="btn btn-outline-danger  btn-sm me-1">Vulnerable Lab</a>
    <a href="profile_safe.php" class="btn btn-outline-success btn-sm">Safe Lab</a>
  </div>
</nav>

<div class="container py-5" style="max-width:700px">

  <h2 class="mb-1">🔄 Reset Database</h2>
  <p class="text-muted mb-4">Wipes all data and restores the default seed users and records. Use this after experiments to get back to a clean state.</p>

  <?php if (!empty($success)): ?>
  <div class="alert alert-success">
    <h5 class="alert-heading">✅ Database reset successfully!</h5>
    <ul class="mb-3">
      <?php foreach ($success as $s): ?>
        <li><?= $s ?></li>
      <?php endforeach; ?>
    </ul>
    <hr>
    <div class="d-flex gap-2">
      <a href="login.php"   class="btn btn-danger  btn-sm">Login (Vulnerable)</a>
      <a href="loginsafe.php" class="btn btn-success btn-sm">Login (Safe)</a>
      <a href="index.php"   class="btn btn-outline-secondary btn-sm">Home</a>
    </div>
  </div>
  <?php endif; ?>

  <?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <h5 class="alert-heading">❌ Errors during reset</h5>
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?>
        <li><code><?= htmlspecialchars($e) ?></code></li>
      <?php endforeach; ?>
    </ul>
  </div>
  <?php endif; ?>

  <?php if (empty($success)): ?>
  <!-- Confirmation form -->
  <div class="card border-danger">
    <div class="card-header bg-danger text-white"><strong>⚠️ Confirm Reset</strong></div>
    <div class="card-body">
      <p>This will <strong>permanently delete all data</strong> in the following tables and restore defaults:</p>
      <ul>
        <li><code>user</code> — all registered users removed, default users restored</li>
        <li><code>employees</code> — restored to original 2 records</li>
        <li><code>secret</code> — restored to 6 sensitive records</li>
        <li><code>second_order_users</code> — cleared and reseeded</li>
        <li><code>login_log</code> — cleared</li>
      </ul>

      <div class="card bg-light mb-3">
        <div class="card-header"><strong>Default Users After Reset</strong></div>
        <div class="card-body p-0">
          <table class="table table-sm table-striped mb-0">
            <thead class="table-dark">
              <tr><th>Email</th><th>Password</th><th>Role</th></tr>
            </thead>
            <tbody>
              <tr class="table-danger"><td>admin@demo.com</td><td>admin123</td><td><span class="badge bg-danger">admin</span></td></tr>
              <tr class="table-danger"><td>enish@gmail.com</td><td>123456</td><td><span class="badge bg-danger">admin</span></td></tr>
              <tr class="table-danger"><td>manager@demo.com</td><td>manager123</td><td><span class="badge bg-danger">admin</span></td></tr>
              <tr><td>addis@demo.com</td><td>addis123</td><td><span class="badge bg-secondary">user</span></td></tr>
              <tr><td>abebe@demo.com</td><td>abebe123</td><td><span class="badge bg-secondary">user</span></td></tr>
              <tr><td>challa@demo.com</td><td>challa123</td><td><span class="badge bg-secondary">user</span></td></tr>
              <tr><td>diana@demo.com</td><td>diana123</td><td><span class="badge bg-secondary">user</span></td></tr>
              <tr><td>eve@demo.com</td><td>eve123</td><td><span class="badge bg-secondary">user</span></td></tr>
              <tr><td>frank@demo.com</td><td>frank123</td><td><span class="badge bg-secondary">user</span></td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <form method="POST">
        <button type="submit" name="confirm_reset" value="1"
                class="btn btn-danger"
                onclick="return confirm('Are you sure? All current data will be lost.')">
          🔄 Reset Database Now
        </button>
        <a href="javascript:history.back()" class="btn btn-outline-secondary ms-2">Cancel</a>
      </form>
    </div>
  </div>
  <?php endif; ?>

</div>
</body>
</html>
