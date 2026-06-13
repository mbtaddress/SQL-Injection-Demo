<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register — Vulnerable | SQLi Demo</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container">
    <a class="navbar-brand" href="index.php">🔓 SQLi Demo</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link active" href="register.php">Register (Vuln)</a></li>
        <li class="nav-item"><a class="nav-link" href="register_safe.php">Register (Safe)</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <!-- Vuln Banner -->
      <div class="alert alert-danger mb-4">
        <h5 class="alert-heading">⚠️ Intentionally Vulnerable Registration</h5>
        <p class="mb-1">This form uses raw string interpolation — your input goes directly into the SQL INSERT query.</p>
        <p class="mb-0">It also demonstrates <strong>Second-Order Injection</strong>: register with a malicious username, then log in and trigger the injection when the app re-uses your stored username in a raw query.</p>
      </div>

      <?php
      session_start();
      require_once 'Config/Config.php';

      define('SQL_INJECTION_IN_PHP', true);
      require 'vendor/autoload.php';
      use SqlInjection\MySQLConnection;
      $pdo = (new MySQLConnection())->connect();

      $msg = '';

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $firstname = $_POST['firstname'] ?? '';
          $lastname  = $_POST['lastname']  ?? '';
          $age       = $_POST['age']       ?? 0;
          $email     = $_POST['email']     ?? '';
          $phone     = $_POST['phone']     ?? '';
          $password  = $_POST['password']  ?? '';
          $username  = $_POST['username']  ?? '';

          // VULNERABLE — every field is raw string interpolation
          $sql = "INSERT INTO user(firstname, lastname, age, email, phone, password, account_type)
                  VALUES ('$firstname', '$lastname', $age, '$email', '$phone', '$password', 'user')";

          // Also insert into second_order_users to demonstrate second-order
          $sql2 = "INSERT INTO second_order_users(email, username, password)
                   VALUES ('$email', '$username', '$password')";

          // Show what the query looks like
          echo '<div class="alert alert-secondary mb-3"><strong>Generated SQL:</strong><br><code>' . htmlspecialchars($sql) . '</code><br><br><code>' . htmlspecialchars($sql2) . '</code></div>';

          try {
              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
              $pdo->exec($sql);
              $pdo->exec($sql2);
              $msg = '<div class="alert alert-success">✅ Registered! <a href="login.php">Login now</a> — or try the <a href="profile.php?action=second_order">Second-Order trigger</a> after logging in.</div>';
          } catch (\PDOException $e) {
              $msg = '<div class="alert alert-danger"><strong>DB Error (exposed intentionally):</strong><br><code>' . htmlspecialchars($e->getMessage()) . '</code></div>';
          }
      }
      echo $msg;
      ?>

      <div class="card shadow-sm">
        <div class="card-header bg-danger text-white"><strong>Create Account (Vulnerable)</strong></div>
        <div class="card-body">
          <form method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" placeholder="Try: John', 'admin')--" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Username <span class="text-danger small">(used in 2nd-order demo)</span></label>
                <input type="text" name="username" class="form-control" placeholder="Try: admin'-- " required>
                <small class="text-muted">Stored safely but reused in a raw query later</small>
              </div>
              <div class="col-md-6">
                <label class="form-label">Age</label>
                <input type="number" name="age" class="form-control" value="25">
              </div>
              <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
              </div>
              <div class="col-md-12">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
            </div>
            <hr>
            <button type="submit" class="btn btn-danger">Register (Vulnerable)</button>
            <a href="register_safe.php" class="btn btn-outline-success ms-2">Switch to Safe Version</a>
          </form>
        </div>
      </div>

      <!-- Injection tips -->
      <div class="card mt-4 border-warning">
        <div class="card-header bg-warning text-dark"><strong>Injection Payloads to Try</strong></div>
        <div class="card-body">
          <table class="table table-sm table-bordered small mb-0">
            <thead class="table-dark"><tr><th>Field</th><th>Payload</th><th>Effect</th></tr></thead>
            <tbody>
              <tr><td>First Name</td><td><code>John', 'admin')--</code></td><td>Terminates INSERT, sets role</td></tr>
              <tr><td>First Name</td><td><code>x'), ('hacker','hacker',25,'h@h.com','000','pass','admin')--</code></td><td>Injects a second row with admin role</td></tr>
              <tr><td>Username</td><td><code>admin'--</code></td><td>Stored safely, fires as 2nd-order later</td></tr>
              <tr><td>Username</td><td><code>' UNION SELECT 1,email,password FROM user--</code></td><td>2nd-order UNION extraction</td></tr>
              <tr><td>Password</td><td><code>pass'), ('evil','evil',0,'e@e.com','0','admin','admin')--</code></td><td>Injects admin user via password field</td></tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
