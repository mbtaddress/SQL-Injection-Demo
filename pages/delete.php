<?php
//session_start();
if($_SESSION['role'] <> "admin"){
   header('location: profile.php');
}

if ( ! defined('SQL_INJECTION_IN_PHP' ) ) {
	die( 'Direct access not permitted' );
}

$id           = $_GET['id'];

$delete_query = 'DELETE FROM user where id = ' . $id;

$result = $pdo->exec( $delete_query );

if ( $result ) {
	?>
	<div class="alert alert-success" role="alert">
		User <?= $id ?> deleted
	</div>
	<?php
} else {
	?>
	<div class="alert alert-warning" role="alert">
		Couldn't find user <?= $id ?>
	</div>
	<?php
}
?>
	<a class="btn btn-primary active" href="?action=search">Back</a>
<?php


