<?php
require_once 'Config/Config.php';
if ( ! defined('SQL_INJECTION_IN_PHP' ) ) {
    die( 'Direct access not permitted' );
}

?>

    <form method="get">
        <input type="hidden" name="action" value="search"/>
        <label>
            First name:
            <input type="text" name="first_name" value="<?= $_GET['first_name'] ?? '' ?>">
        </label>
        <label>
            Last name:
            <input type="text" name="last_name" value="<?= $_GET['last_name'] ?? '' ?>">
        </label>
        <input type="submit" value="Submit">
    </form>


<?php

    $first_name = $_GET['first_name'] ?? '';
    $last_name  = $_GET['last_name'] ?? '';

    $count_query = 'SELECT COUNT(*) as num_rows from user where account_type = "user" ';

    $query = 'SELECT * from user where account_type = "user" ';

    $filters = '';
    $parameters = [];

    if (  ! empty( $first_name ) || ! empty( $last_name ) ) {

        if ( isset( $_GET['first_name'] ) && ! empty( $_GET['first_name'] ) ) {
            $filters .= "AND firstname LIKE :first_name ";
            $parameters['first_name'] = $_GET['first_name'];
        }

        if ( isset( $_GET['last_name'] ) && ! empty( $_GET['last_name'] ) ) {
            $filters .= "AND lastname LIKE :last_name ";
            $parameters['last_name'] = $_GET['last_name'];
        }
    }

    $page  = $_GET['page'] ?? 1;
    //$query .= $filters . ' LIMIT 5 OFFSET :page';
      $query .= $filters . ' LIMIT 5 OFFSET ' . ( $page - 1 ) * 5;
    //$parameters['page'] = ( $page - 1 ) * 5;

    $prepared_query = $pdo->prepare( $query);
    $prepared_query->execute( $parameters );
    $result = $prepared_query->fetchAll();

    unset($parameters['page']);
    $count_prepared_query = $pdo->prepare( $count_query . $filters );
    $count_prepared_query->execute( $parameters );
    $count_query = $count_prepared_query->fetchAll();

    $count_result = $count_query ? $count_query[0]['num_rows'] : 0;
    $num_pages = ( $count_result / 5 ) + ( ( $count_result % 5 ) ? 1 : 0 );


    ?>
<table class="table">
    <thead>
    <tr>
        <th scope="col">Id</th>
        <th scope="col">First name</th>
        <th scope="col">Last name</th>
        <th scope="col">Phone</th>
        <th scope="col">email</th>
        <th scope="col">Role</th>
       <?php if($_SESSION['role'] == "admin"){ 
        echo '<th scope="col">Actions</th>'; } ?>
    </tr>
    </thead>
    <tbody>
    <?php

    if ( $result ) {
        
        foreach ( $result as $row ) {
            
            echo '<tr>';
            echo '<th scope="row">' . $row['id'] . '</th>';
            echo '<td>' . $row['firstname'] . '</td>';
            echo '<td>' . $row['lastname'] . '</td>';
            echo '<td>' . $row['phone'] . '</td>';
            echo '<td>' . $row['email'] . '</td>';
            echo '<td>' . $row['account_type'] . '</td>';
           
        if($_SESSION['role'] == "admin"){
   

            echo '<td>';
            echo '<a href="?action=update&id=' . $row['id'] . '">Edit<i class="fas fa-pencil-alt"></i></a>&nbsp;';
            echo '<a href="?action=delete&id=' . $row['id'] . '">Delete<i class="fas fa-trash"></i></a>';
            echo '</td>';
        }
            echo '</tr>';
        
        }

    }

    
    ?>
    </tbody>
</table>
<p>Number of users: <?= $count_result ?></p>
<?php
for ( $i = 1; $i <= $num_pages; $i ++ ) {
    if ( $action === 'search' ) {
        $filter = '&action=search&first_name=' . $first_name . '&last_name=' . $last_name;
    } else {
        $filter = '';
    }
    echo '<a href="?page=' . $i . $filter . '">' . $i . '</a> ';
}
?>
<hr/>
<a href="?action=insert" class="btn btn-primary">Add User</a>


