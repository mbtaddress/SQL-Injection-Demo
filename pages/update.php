<?php

    
    if($_SESSION['role'] <> "admin"){
        header('location: profile.php');
    }


if ( ! defined( 'SQL_INJECTION_IN_PHP' ) ) {
	die( 'Direct access not permitted' );
}

if ( isset( $_GET['first_name'], $_GET['last_name'], $_GET['email'], $_GET['password']) ) {

	$update_query = "UPDATE user SET firstname='{$_GET['first_name']}', lastname='{$_GET['last_name']}', email='{$_GET['email']}', phone='{$_GET['phone']}', password='{$_GET['password']}' WHERE id={$_GET['id']}";

	$result = $pdo->exec( $update_query );

	if ( $result ) {
		?>
		<div class="alert alert-success" role="alert">
			User updated
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-warning" role="alert">
			There was a problem while updating the new user: <?= json_encode( $pdo->errorInfo() ) ?>
		</div>
		<?php
	}
	?>
	<a class="btn btn-primary active" href="?action=search">Back</a>
	<?php
} else {

	$query = "SELECT id, firstname, lastname, email, phone from user where id={$_GET['id']}";
	$row   = $pdo->query( $query )->fetch();

	?>
	
	<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Edit User <?= $_GET['id'] ?></h1>
          </div>
        </div>
      </div><!-- /.container-fluid -->
    </section>

    <!-- Main content -->
    <!-- Main content -->
    <section class="content">
      <div class="container-fluid">
        <div class="row">
          <!-- left column -->
          <div class="col-md-12">
            <!-- jquery validation -->
            <div class="card card-primary">
              
              <!-- /.card-header -->
              <!-- form start -->
              <form id="quickForm" method="get">
              	<input type="hidden" name="action" value="update"/>
				        <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                <div class="card-body">
                  <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" name="first_name" class="form-control" id="fname" value="<?= $row['firstname'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" name="last_name" class="form-control" id="lname" value="<?= $row['lastname'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" id="email" value="<?= $row['email'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" class="form-control" id="phone" value="<?= $row['phone'] ?>">
                  </div>
                  <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" name="password" class="form-control" id="pass" >
                  </div>
                 
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Submit</button> | 
                  <a href="?action=search" class="btn btn-secondary">Back</a>
                </div>
              </form>
            </div>
            <!-- /.card -->
            </div>
          <!--/.col (left) -->
          <!-- right column -->
          <div class="col-md-6">

          </div>
          <!--/.col (right) -->
        </div>
        <!-- /.row -->
      </div><!-- /.container-fluid -->
    </section>
    <!-- /.content -->
  </div>
	<?php
}