<?php

if ( ! defined( 'SQL_INJECTION_IN_PHP' ) ) {
	die( 'Direct access not permitted' );
}

if ( isset( $_GET['first_name'], $_GET['last_name'], $_GET['email'], $_GET['password'] ) ) {


	$insert_query = 'INSERT INTO user(firstname, lastname, email, phone, password) VALUES ( :first_name, :last_name, :email, :phone, :password )';

	

	$prepared_statement = $pdo->prepare( $insert_query );
	$prepared_statement->bindParam( 'first_name', $_GET['first_name'] );
	$prepared_statement->bindParam( 'last_name', $_GET['last_name'] );
	$prepared_statement->bindParam( 'email', $_GET['email'] );
	$prepared_statement->bindParam( 'phone', $_GET['phone'] );
	$prepared_statement->bindParam( 'password', $_GET['password'] );
	$prepared_statement->execute();

	$result = $prepared_statement->rowCount();

	if ( $result ) {
		?>
		<div class="alert alert-success" role="alert">
			User inserted
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-warning" role="alert">
			There was a problem while inserting the new user.
		</div>
		<?php
	}
	?>
	<a class="btn btn-primary active" href="?action=search">Back</a>
	<?php
} else {
	?>
	
	<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Add User</h1>
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
              	<input type="hidden" name="action" value="insert"/>
                <div class="card-body">
                  <div class="form-group">
                    <label for="fname">First Name</label>
                    <input type="text" name="first_name" class="form-control" id="fname" >
                  </div>
                  <div class="form-group">
                    <label for="lname">Last Name</label>
                    <input type="text" name="last_name" class="form-control" id="lname" >
                  </div>
                  <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" name="email" class="form-control" id="email" >
                  </div>
                  <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="text" name="phone" class="form-control" id="phone" >
                  </div>
                  <div class="form-group">
                    <label for="pass">Password</label>
                    <input type="password" name="password" class="form-control" id="pass" >
                  </div>
                 
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <input type="submit" class="btn btn-primary" value="Submit">
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
