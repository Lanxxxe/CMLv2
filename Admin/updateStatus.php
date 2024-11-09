<?php
// process.php
session_start();
include 'config.php'; // Include your database connection file

function redirectWithMessage($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: orderdetails.php");
    exit();
}

try {
    if (isset($_POST['action'])) {
        // Log received data for debugging
        error_log("Received userID: " . $_POST['user_id']);
        error_log("Received payment_id: " . $_POST['payment_id']);
        error_log("Received status: " . $_POST['status']);
        
        $userID = $_POST['user_id'] ?? null;
        $payment_id = $_POST['payment_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (empty($userID) || empty($payment_id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
            exit();
        }
    
        // Prepare the SQL query
        $stmt = $DB_con->prepare("UPDATE orderdetails SET order_status = :status WHERE user_id = :user_id AND payment_id = :payment_id");
    
        // Execute the query
        $stmt->execute([
            ':status' => $status,
            ':user_id' => $userID,
            ':payment_id' => $payment_id
        ]);
    
        // Check if any rows were affected
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true, 'message' => 'Status updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes made or invalid user/payment ID.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid action specified.']);
    }
    

} catch (Exception $e) {
    // Roll back transaction if one is active
    if ($DB_con->inTransaction()) {
        $DB_con->rollBack();
    }
    redirectWithMessage('error', $e->getMessage());
}