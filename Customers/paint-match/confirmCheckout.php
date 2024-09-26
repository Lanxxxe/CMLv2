<?php


session_start();
include_once('db-connect.php');

if (isset($_POST['confirmCheckout'])) {
    $query = "DELETE FROM cartitems";

    $statement = $DB_con->prepare($query);

    if ($statement->execute()){

        header('Location: ../paint-match.php');
        exit();
    }
} else {
    header('Location: ../paint-match.php');
    exit();
}