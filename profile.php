<?php
    session_start();
    if(!isset($_SESSION['email'])){
        header('location: login.php');
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SQL Injection Demo — Vulnerable</title>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <style>
        .sidebar { min-height: calc(100vh - 56px); background: #212529; }
        .sidebar .nav-link { color: #adb5bd; font-size: 0.875rem; padding: 0.4rem 1rem; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,255,255,0.1); border-radius: 4px; }
        .sidebar .nav-section { color: #6c757d; font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.08em; padding: 0.75rem 1rem 0.25rem; }
        .badge-vuln { background: #dc3545; font-size: 0.65rem; }
    </style>
</head>
<body>

<!-- Top Navbar -->
<nav class="navbar navbar-dark bg-dark px-3" style="height:56px">
  <span class="navbar-brand mb-0 h6">
    🔓 SQL Injection Demo
    <span class="badge bg-danger ms-2">VULNERABLE VERSION</span>
  </span>
  <div class="d-flex align-items-center">
    <span class="text-light me-3 small"><?= htmlspecialchars($_SESSION['email']) ?></span>
    <a href="profile_safe.php" class="btn btn-outline-success btn-sm me-2">Switch to Safe Version</a>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Sign Out</a>
  </div>
</nav>

<div class="d-flex">

  <!-- Sidebar -->
  <nav class="sidebar p-2" style="width:240px; flex-shrink:0">

    <div class="nav-section">Basic Attacks</div>
    <a href="?action=search" class="nav-link">🔍 User Search (SQLi)</a>
    <a href="?action=insert" class="nav-link">➕ Insert User</a>
    <a href="?action=privesc" class="nav-link">👑 Privilege Escalation</a>
    <a href="?action=changepassword&id=<?= $_SESSION['id'] ?? 1 ?>" class="nav-link">🔑 Change Password</a>

    <div class="nav-section mt-2">Advanced Attacks</div>
    <a href="?action=union_search" class="nav-link">🔗 UNION Extraction</a>
    <a href="?action=error_based" class="nav-link">💥 Error-Based</a>
    <a href="?action=blind_boolean" class="nav-link">👁 Blind Boolean</a>
    <a href="?action=time_based" class="nav-link">⏱ Time-Based Blind</a>
    <a href="?action=second_order" class="nav-link">🔄 Second-Order</a>
    <a href="?action=oob" class="nav-link">📡 Out-of-Band (OOB)</a>
    <a href="?action=routed" class="nav-link">🔀 Routed SQLi</a>
    <a href="?action=waf_bypass" class="nav-link">🛡 WAF Bypass</a>

    <div class="nav-section mt-2">📖 Walkthroughs</div>
    <a href="?action=walkthroughs" class="nav-link">📋 All Walkthroughs</a>
    <a href="?action=walkthrough&topic=login_bypass" class="nav-link ps-3 small">↳ Auth Bypass</a>
    <a href="?action=walkthrough&topic=union" class="nav-link ps-3 small">↳ UNION Extraction</a>
    <a href="?action=walkthrough&topic=error_based" class="nav-link ps-3 small">↳ Error-Based</a>
    <a href="?action=walkthrough&topic=blind_boolean" class="nav-link ps-3 small">↳ Blind Boolean</a>
    <a href="?action=walkthrough&topic=time_based" class="nav-link ps-3 small">↳ Time-Based</a>
    <a href="?action=walkthrough&topic=second_order" class="nav-link ps-3 small">↳ Second-Order</a>
    <a href="?action=walkthrough&topic=privesc" class="nav-link ps-3 small">↳ Privilege Escalation</a>
    <a href="?action=walkthrough&topic=waf_bypass" class="nav-link ps-3 small">↳ WAF Bypass</a>
    <a href="?action=walkthrough&topic=oob" class="nav-link ps-3 small">↳ OOB Injection</a>
    <a href="?action=walkthrough&topic=routed" class="nav-link ps-3 small">↳ Routed SQLi</a>
    <a href="?action=walkthrough&topic=api" class="nav-link ps-3 small">↳ API SQLi</a>
    <a href="?action=walkthrough&topic=tools" class="nav-link ps-3 small">↳ 🛠 Tools Guide</a>

    <div class="nav-section mt-2">Reference</div>
    <a href="../api/index.php" class="nav-link text-info">🔌 Vulnerable API</a>
    <a href="profile_safe.php" class="nav-link text-success">✅ Safe Version →</a>
  </nav>

  <!-- Main Content -->
  <div class="flex-grow-1 p-4">
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
            require( 'pages/delete.php' );
        } elseif ( $action === 'update' && isset( $_GET['id'] ) && (int) $_GET['id'] > 0 ) {
            require( 'pages/update.php' );
        } elseif ( $action === 'insert' ) {
            require( 'pages/insert.php' );
        } elseif ( $action === 'changepassword' ) {
            require( 'pages/changepassword.php' );
        } elseif ( $action === 'union_search' ) {
            require( 'pages/union_search.php' );
        } elseif ( $action === 'error_based' ) {
            require( 'pages/error_based.php' );
        } elseif ( $action === 'blind_boolean' ) {
            require( 'pages/blind_boolean.php' );
        } elseif ( $action === 'time_based' ) {
            require( 'pages/time_based.php' );
        } elseif ( $action === 'second_order' ) {
            require( 'pages/second_order.php' );
        } elseif ( $action === 'privesc' ) {
            require( 'pages/privesc.php' );
        } elseif ( $action === 'routed' ) {
            require( 'pages/routed.php' );
        } elseif ( $action === 'oob' ) {
            require( 'pages/oob.php' );
        } elseif ( $action === 'waf_bypass' ) {
            require( 'pages/waf_bypass.php' );
        } elseif ( $action === 'walkthroughs' || $action === 'walkthrough' ) {
            require( 'pages/walkthroughs.php' );
        } else {
            require( 'pages/user_list.php' );
        }
    }
    ?>
  </div>

</div>
</body>
</html>
