<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Register — Safe | SQLi Demo</title>
  <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body class="bg-light">

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">🔒 SQLi Demo</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Register (Vuln)</a></li>
        <li class="nav-item"><a class="nav-link active" href="register_safe.php">Register (Safe)</a></li>
        <li class="nav-item"><a class="nav-link" href="loginsafe.php">Login (Safe)</a></li>
      </ul>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-lg-7">

      <div class="alert alert-success mb-4">
        <h5 class="alert-heading">✅ Secure Registration</h5>
        <p class="mb-0">This form uses PDO prepared statements with bound parameters. Every field is treated as data — no injection possible regardless of what you enter.</p>
      </div>

      <?php
      session_start();

      define('SQL_INJECTION_IN_PHP', true);
      require 'vendor/autoload.php';
      use SqlInjection\MySQLConnection;
      $pdo = (new MySQLConnection())->connect();

      $msg  = '';
      $safe_sql_display = '';

      if ($_SERVER['REQUEST_METHOD'] === 'POST') {
          $firstname = $_POST['firstname'] ?? '';
          $lastname  = $_POST['lastname']  ?? '';
          $age       = (int)($_POST['age'] ?? 0);
          $email     = $_POST['email']     ?? '';
          $phone     = $_POST['phone']     ?? '';
          $password  = $_POST['password']  ?? '';
          $username  = $_POST['username']  ?? '';

          // SAFE — parameterized INSERT
          $sql = "INSERT INTO user(firstname, lastname, age, email, phone, password, account_type)
                  VALUES (:firstname, :lastname, :age, :email, :phone, :password, 'user')";

          $sql2 = "INSERT INTO second_order_users(email, username, password)
                   VALUES (:email, :username, :password)";

          $safe_sql_display = htmlspecialchars($sql);

          try {
              $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

              $stmt = $pdo->prepare($sql);
              $stmt->execute([
                  ':firstname' => $firstname,
                  ':lastname'  => $lastname,
                  ':age'       => $age,
                  ':email'     => $email,
                  ':phone'     => $phone,
                  ':password'  => $password,
              ]);

              $stmt2 = $pdo->prepare($sql2);
              $stmt2->execute([
                  ':email'    => $email,
                  ':username' => $username,
                  ':password' => $password,
              ]);

              $msg = '<div class="alert alert-success">✅ Registered securely! <a href="loginsafe.php">Login now</a>.</div>';
          } catch (\PDOException $e) {
              // Safe error — no raw details
              $msg = '<div class="alert alert-danger">Registration failed. Please try again.</div>';
              error_log($e->getMessage());
          }
      }
      echo $msg;
      ?>

      <?php if ($safe_sql_display): ?>
      <div class="alert alert-secondary mb-3">
        <strong>Parameterized query used:</strong><br>
        <code><?= $safe_sql_display ?></code><br>
        <small class="text-muted">Values are bound separately — SQL structure and data are never mixed.</small>
      </div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-header bg-success text-white"><strong>Create Account (Safe)</strong></div>
        <div class="card-body">
          <form method="POST">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">First Name</label>
                <input type="text" name="firstname" class="form-control" placeholder="Try any injection — it won't work" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Last Name</label>
                <input type="text" name="lastname" class="form-control" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" placeholder="Try: admin'-- (stored safely)" required>
                <small class="text-muted">Stored safely — even if used later in a parameterized query</small>
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
            <button type="submit" class="btn btn-success">Register (Safe)</button>
            <a href="register.php" class="btn btn-outline-danger ms-2">Switch to Vulnerable Version</a>
          </form>
        </div>
      </div>

      <!-- Side-by-side comparison -->
      <div class="card mt-4">
        <div class="card-header bg-dark text-white"><strong>Vulnerable vs Safe — Side by Side</strong></div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6">
              <p class="text-danger fw-bold small">❌ Vulnerable</p>
              <pre class="bg-light p-2 rounded small">$sql = "INSERT INTO user
  (firstname, ..., password)
  VALUES
  ('$firstname', ..., '$password')";</pre>
            </div>
            <div class="col-md-6">
              <p class="text-success fw-bold small">✅ Safe</p>
              <pre class="bg-light p-2 rounded small">$stmt = $pdo->prepare("INSERT INTO user
  (firstname, ..., password)
  VALUES
  (:firstname, ..., :password)");
$stmt->execute([...]);</pre>
            </div>
          </div>
        </div>
      </div>

    </div>
  </div>
</div>
</body>
</html>
