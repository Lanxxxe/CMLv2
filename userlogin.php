<?php

try {
    session_start();
    include("db_conection.php");
    $response = array();

    $user_email = $_POST['user_email'];
    $user_password = $_POST['user_password'];

    $check_user = "SELECT * FROM users WHERE user_email='$user_email' AND user_password='$user_password'";
    $run = mysqli_query($dbcon, $check_user);

    if (mysqli_num_rows($run)) {
        $row = mysqli_fetch_assoc($run);
        $user_type = $row['type'];
        $userid = $row['user_id'];
        $user_firstname = $row['user_firstname'];
        $user_lastname = $row['user_lastname'];
        $user_address = $row['user_address'];
        $user_mobile = $row['user_mobile'];
        try {
            $assignedBranch = $row['assigned_branch'];
        } catch(Exception $e) {
            $assignedBranch = '';
        }

        $_SESSION['user_id'] = $userid;    
        $_SESSION['user_email'] = $user_email;
        $_SESSION['admin_username'] = $user_email;
        $_SESSION['user_firstname'] = $user_firstname;
        $_SESSION['user_lastname'] = $user_lastname;
        $_SESSION['user_address'] = $user_address;
        $_SESSION['user_mobile'] = $user_mobile;
        $_SESSION['user_type'] = $user_type;
        if ($assignedBranch) {
            $_SESSION['current_branch'] = $assignedBranch;
        }
    } else {
        throw new Exception('Email or password is incorrect!');
    }

    $response['status'] = 'success';
    $response['message'] = 'You\'re successfully logged in!';
    if ($user_type == 'Customer') {
        $response['redirect'] = './Customers/index.php';
    } elseif ($user_type == 'Admin') {
        $response['redirect'] = './Admin/index.php';
    }elseif ($user_type == 'Cashier') {
        $response['redirect'] = './Customers/index.php';
    } else {
        throw new Exception('Email or password is incorrect!');
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    include './error_log.php';
}

echo json_encode($response);