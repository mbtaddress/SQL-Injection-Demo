<?php

if ( ! defined( 'SQL_INJECTION_IN_PHP' ) ) {
	die( 'Direct access not permitted' );
}

if ( isset( $_GET['oldpassword'], $_GET['newpassword']) ) {
  $query = "SELECT password from user where id={$_GET['id']}";
  $row   = $pdo->query( $query )->fetch();
  $oldpass = $_GET['oldpassword'];
  if ($row['password'] <> $oldpass) {
    echo '<div class="alert alert-success" role="alert">';
    echo "Please enter previous password correctly?";
    echo '</div>';
  }else{

	$update_query = "UPDATE user SET password='{$_GET['newpassword']}' WHERE id={$_GET['id']}";

	$result = $pdo->exec( $update_query );

	if ( $result ) {
		?>
		<div class="alert alert-success" role="alert">
			Password changed
		</div>
		<?php
	} else {
		?>
		<div class="alert alert-warning" role="alert">
			There was a problem while updating the new user: <?= json_encode( $pdo->errorInfo() ) ?>
		</div>
		<?php
	}
	
} }

	

	?>
	
	<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <div class="container-fluid">
        <div class="row mb-2">
          <div class="col-sm-6">
            <h1>Change Password</h1>
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
              	<input type="hidden" name="action" value="changepassword"/>
				        <input type="hidden" name="id" value="<?= $_GET['id'] ?>">
                <div class="card-body">
                  <div class="form-group">
                    
                  <div class="form-group">
                    <label for="pass">Old Password</label>
                    <input type="password" name="oldpassword" class="form-control" id="pass" required>
                  </div>

                  <div class="form-group">
                    <label for="pass">New Password</label>
                    <input type="password" name="newpassword" class="form-control" id="password" required >
                  </div>

                  <div class="form-group">
                    <label for="pass">Confirm Password</label>
                    <input type="password" name="password" class="form-control" id="confirm_password" required>
                  </div>
                 
                </div>
                <!-- /.card-body -->
                <div class="card-footer">
                  <button type="submit" class="btn btn-primary">Change</button> | 
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
  <script type="text/javascript">
    var password = document.getElementById("password"), 
    confirm_password = document.getElementById("confirm_password");
    function validatePassword(){
      if (password.value != confirm_password.value) {
        confirm_password.setCustomValidity('Passwords Don\'t match');      
      }
      else{
        confirm_password.setCustomValidity('');
      }
    }
    password.onchange = validatePassword;
    confirm_password.onkeyup = validatePassword;
  </script>
	