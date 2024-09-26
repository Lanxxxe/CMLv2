<?php
session_start();
include("db_conection.php");

$response = array();

    $user_email = $_POST['ruser_email'];
    $user_password = $_POST['ruser_password'];
    $user_firstname = $_POST['ruser_firstname'];
    $user_lastname = $_POST['ruser_lastname'];
    $user_address = $_POST['ruser_address'];
    $user_mobile = $_POST['ruser_mobile'];

    $check_user = "SELECT * FROM users WHERE user_email='$user_email'";
    $run_query = mysqli_query($dbcon, $check_user);

    if (mysqli_num_rows($run_query) > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Customer already exists, please try another one!';
    } else {
        $saveaccount = "INSERT INTO users (user_email, user_password, user_firstname, user_lastname, user_address, user_mobile) 
                        VALUES ('$user_email', '$user_password', '$user_firstname', '$user_lastname', '$user_address', '$user_mobile')";
        if (mysqli_query($dbcon, $saveaccount)) {
            $response['status'] = 'success';
            $response['message'] = 'Data successfully saved, you may now login!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Registration failed, please try again!';
        }
    }


echo json_encode($response);
exit();
?>
