<html>
<head>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-success">
  <div class="container">
    <a class="navbar-brand" href="index.php">🔒 SQLi Demo</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="register.php">Register (Vuln)</a></li>
        <li class="nav-item"><a class="nav-link" href="register_safe.php">Register (Safe)</a></li>
        <li class="nav-item"><a class="nav-link active" href="loginsafe.php">Login (Safe)</a></li>
        <li class="nav-item"><a class="nav-link" href="login.php">Login (Vuln)</a><
      </ul>
    </div>
  </div>
</nav>

<div>
<div class="container mt-5">
  <div class="row">
      <div class="col-lg-6"></div>
      <div class="col-lg-6">
        <h3 class="mb-4">Sign In</h3>
        <form method="POST" action="db_login_safe.php">
        <div class="form-floating mb-3">
          <input type="text" name="email" class="form-control" id="floatingInput" placeholder="name@example.com">
          <label for="floatingInput">Email address</label>
        </div>
        <div class="form-floating">
          <input type="password" name="password" class="form-control" id="floatingPassword" placeholder="Password">
          <label for="floatingPassword">Password</label>
        </div>
        <br/>
        <div class="mb-3">
          <button type="submit" name="login" class="btn btn-primary form-control">Login</button>
        </div>
        </form>

      </div>
      
  </div>
</div>
</body>
<html>