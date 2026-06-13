<?php
    session_start();
    if(!isset($_SESSION['email'])){
        header('location: loginsafe.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Demo — Safe</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .sidebar { min-height: calc(100vh - 56px); background: #198754; }
        .sidebar .nav-link { color: #d1e7dd; font-size: 0.875rem; padding: 0.4rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.15); border-radius: 4px; }
        .sidebar .nav-section { color: rgba(255,255,255,0.5); font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.75rem 1rem 0.25rem; }
    </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-success px-3" style="height:56px">
  <span class="navbar-brand mb-0 h6">
    🔒 SQL Injection Demo
    <span class="badge bg-light text-success ms-2">SAFE VERSION</span>
  </span>
  <div class="d-flex align-items-center">
    <span class="text-light me-3 small"><?= htmlspecialchars($_SESSION['email']) ?></span>
    <a href="profile.php" class="btn btn-outline-danger btn-sm me-2">Switch to Vulnerable Version</a>
    <a href="logoutsafe.php" class="btn btn-outline-light btn-sm">Sign Out</a>
  </div>
</nav>

<div class="d-flex">

  <!-- Sidebar -->
  <nav class="sidebar p-2" style="width:240px; flex-shrink:0">

    <div class="nav-section">Safe Pages</div>
    <a href="?action=search" class="nav-link">🔍 User Search (Safe)</a>
    <a href="?action=insert" class="nav-link">➕ Insert User (Safe)</a>
    <a href="?action=changepassword&id=<?= $_SESSION['id'] ?? 1 ?>" class="nav-link">🔑 Change Password</a>

    <div class="nav-section mt-2">Reference</div>
    <a href="profile.php" class="nav-link text-warning">⚠️ Vulnerable Version →</a>
  </nav>

  <!-- Main Content -->
  <div class="flex-grow-1 p-4">

    <div class="alert alert-success py-2 mb-3">
      <strong>✅ Safe Version</strong> — All queries use PDO prepared statements with bound parameters.
      No injection is possible on this side.
    </div>

    <?php
    define( 'SQL_INJECTION_IN_PHP', true );

    require 'vendor/autoload.php';

    use SqlInjection\MySQLConnection;

    $pdo = ( new MySQLConnection() )->connect();
    if ( $pdo === null ) {
        echo '<div class="alert alert-danger">Could not connect to the MySQL database!</div>';
    } else {

        $action = $_GET['action'] ?? '';

        if ( $action === 'delete' && isset( $_GET['id'] ) && (int) $_GET['id'] > 0 ) {
            require( 'pages/deleteSafe.php' );
        } elseif ( $action === 'update' && isset( $_GET['id'] ) && (int) $_GET['id'] > 0 ) {
            require( 'pages/updateSafe.php' );
        } elseif ( $action === 'insert' ) {
            require( 'pages/insertSafe.php' );
        } elseif ( $action === 'changepassword' ) {
            require( 'pages/changepassword.php' );
        } else {
            require( 'pages/user_list_safe.php' );
        }
    }
    ?>
  </div>

</div>
</body>
</html>
