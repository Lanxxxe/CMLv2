<?php
try {
    session_start();
    require_once './sweetAlert.php';

    function alertError($msg)
    {
        header("Location: ./error.php?msg=$msg");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['csrf_token']) && hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            include_once '../db_conection.php';
            $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_SPECIAL_CHARS);
            $password_confirm = filter_input(INPUT_POST, 'confirm_password', FILTER_SANITIZE_SPECIAL_CHARS);
            if (empty($password) || empty($password_confirm)) {
                alertError("Please Fill Required Input!");
            }
            if ($password !== $password_confirm) {
                alertError("Password and Confirm password doesn't match!");
            }
            $q = filter_input(INPUT_POST, 'q', FILTER_SANITIZE_SPECIAL_CHARS);

            $stmt = mysqli_prepare($dbcon, "UPDATE users SET user_password = ? WHERE user_email = ?");
            mysqli_stmt_bind_param($stmt, "ss", $password, $q);
            mysqli_stmt_execute($stmt);
            
            if (mysqli_stmt_affected_rows($stmt) > 0) {
                sweetAlert("success", "Success!", "Password updated successfully!", "OK", "../index.php");
            } else {
                alertError("Error updating password. Please try again.");
            }
            mysqli_stmt_close($stmt);
        } else {
            alertError("CSRF token validation failed.");
        }
    }
} catch (Exception $e) {
    sweetAlert("error", "Ooops! Something went wrong", "Please try again later or contact support.", "OK", "./index.php");
    include '../error_log.php';
}
