<?php 
session_start();

if ($_SESSION['user_type'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}

include_once 'config.php';

try {
    if (isset($_POST['action'])){
        if ($_POST['action'] == 'edit_user'){
            $userId = trim($_POST['user_id']);
            $userEmail = trim($_POST['user_email']);
            $userPassword = trim($_POST['user_password']);
            $userFirstName = trim($_POST['user_firstName']);
            $userLastName = trim($_POST['user_lastName']);
            $userAddress = trim($_POST['user_address']);
            $userMobile = trim($_POST['user_mobile']);

            // Prepare SQL query with placeholders
            $edituser = $DB_con->prepare("UPDATE users SET 
            user_email = :email, 
            user_password = :password, 
            user_firstname = :first_name, 
            user_lastname = :last_name, 
            user_address = :address, 
            user_mobile = :mobile 
            WHERE user_id = :id");

            // Bind parameters to the placeholders
            $edituser->bindParam(':email', $userEmail);
            $edituser->bindParam(':password', $userPassword); // Make sure to hash the password if it's plaintext
            $edituser->bindParam(':first_name', $userFirstName);
            $edituser->bindParam(':last_name', $userLastName);
            $edituser->bindParam(':address', $userAddress);
            $edituser->bindParam(':mobile', $userMobile);
            $edituser->bindParam(':id', $userId);

            // Execute the query and check if it was successful
            if ($edituser->execute()) {
                redirectWithMessage('success', 'Product updated successfully!');
            } else {
                redirectWithMessage('error', 'Unexpected error occur');
            }
        }

        else if ($_POST['action'] == 'add_user') {
            $userEmail = trim($_POST['user_email']);
            $userPassword = trim($_POST['user_password']); // Hash the password
            $userFirstName = trim($_POST['user_firstName']);
            $userLastName = trim($_POST['user_lastName']);
            $userAddress = trim($_POST['user_address']);
            $userMobile = trim($_POST['user_mobile']);
            $type = 'Cashier';
            // Prepare SQL query with placeholders
            $addUser = $DB_con->prepare("INSERT INTO users (
                user_email, user_password, user_firstname, user_lastname, user_address, user_mobile, type
            ) VALUES (
                :email, :password, :first_name, :last_name, :address, :mobile, :type
            )");
    
            // Bind parameters to the placeholders
            $addUser->bindParam(':email', $userEmail);
            $addUser->bindParam(':password', $userPassword);
            $addUser->bindParam(':first_name', $userFirstName);
            $addUser->bindParam(':last_name', $userLastName);
            $addUser->bindParam(':address', $userAddress);
            $addUser->bindParam(':mobile', $userMobile);
            $addUser->bindParam(':type', $type);
            
            // Execute the query and check if it was successful
            if ($addUser->execute()) {
                echo 'success';
            } else {
                echo 'error';
            }
        }

        else if ($_POST['action'] == 'delete_user') {
            $userId = trim($_POST['user_id']);
    
            // Prepare SQL query to delete the user
            $deleteUser = $DB_con->prepare("DELETE FROM users WHERE user_id = :id");
            $deleteUser->bindParam(':id', $userId);
    
            // Execute the query and check if it was successful
            if ($deleteUser->execute()) {
                echo 'success';
            } else {
                echo 'error';
            }
        }
    }
} catch ( Exception $e) {
    // Roll back transaction if one is active
    if ($DB_con->inTransaction()) {
        $DB_con->rollBack();
    }
    redirectWithMessage('error', $e->getMessage());
}



