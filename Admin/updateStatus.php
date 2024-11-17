<?php
// process.php
session_start();
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


define('HOST', $_ENV['MAILER_HOST']);
define('EMAIL_ADDRESS', $_ENV['MAILER_EMAIL']);
define('EMAIL_PASSWORD', $_ENV['MAILER_PASS']);

include 'config.php'; // Include your database connection file
include 'cancellation_email.php';

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
        error_log("Received order_id: " . $_POST['order_id']);
        error_log("Received status: " . $_POST['status']);
        
        $userID = $_POST['user_id'] ?? null;
        $order_id = $_POST['order_id'] ?? null;
        $status = $_POST['status'] ?? null;
        
        if (empty($userID) || empty($order_id) || empty($status)) {
            echo json_encode(['success' => false, 'message' => 'Missing required parameters.']);
            exit();
        }


        $stmt = $DB_con->prepare("SELECT order_status FROM orderdetails WHERE user_id = :user_id AND order_id = :order_id");
    
        // Execute the query
        $stmt->execute([
            ':user_id' => $userID,
            ':order_id' => $order_id
        ]);
        $curren_order_status = $stmt->fetch(PDO::FETCH_NUM)[0];
        if ($status === $curren_order_status) {
            echo json_encode(['success' => false, 'type' => 'info', 'message' => 'The status is already set to "' . $status . '".']);
            exit;
        }
    
        // Prepare the SQL query
        $stmt = $DB_con->prepare("UPDATE orderdetails SET order_status = :status WHERE user_id = :user_id AND order_id = :order_id AND order_status != :status");
    
        // Execute the query
        $stmt->execute([
            ':status' => $status,
            ':user_id' => $userID,
            ':order_id' => $order_id
        ]);
    
        // Check if any rows were affected

        if ($stmt->rowCount() > 0) {
            if ($status === 'Rejected') {
                $orderDetails = $DB_con->prepare('SELECT orderdetails.*, users.user_email, users.user_firstname, users.user_lastname, users.user_mobile FROM orderdetails JOIN users ON orderdetails.user_id = users.user_id WHERE orderdetails.order_id = :order_id');
                $orderDetails->execute([':order_id' => $order_id]);
                $orders = $orderDetails->fetchAll(PDO::FETCH_ASSOC);
                sendCancellationEmail($orders);
            }
            echo json_encode(['success' => true, 'message' => 'Status updated successfully!']);
        } else {
            echo json_encode(['success' => false, 'message' => 'No changes were made or invalid user/payment ID.']);
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
