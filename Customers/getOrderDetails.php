<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("config.php");

header('Content-Type: application/json');

// Log incoming parameters
error_log("Received order_id: " . $_GET['order_id'] . " payment_id: " . $_GET['payment_id']);

if (isset($_GET['order_id']) && isset($_GET['payment_id'])) {
    $order_id = $_GET['order_id'];
    $payment_id = $_GET['payment_id'];

    try {
        // Prepare the statement to fetch order details
        $stmtOrder = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_id = :order_id");
        $stmtOrder->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $stmtOrder->execute();
        $orderDetails = $stmtOrder->fetch(PDO::FETCH_ASSOC);

        // Log order details
        error_log("Order details: " . print_r($orderDetails, true));

        // Prepare the statement to fetch payment details
        $stmtPayment = $DB_con->prepare("SELECT payment_image_path FROM paymentform WHERE id = :payment_id");
        $stmtPayment->bindParam(':payment_id', $payment_id, PDO::PARAM_INT);
        $stmtPayment->execute();
        $paymentDetails = $stmtPayment->fetch(PDO::FETCH_ASSOC);

        // Log payment details
        error_log("Payment details: " . print_r($paymentDetails, true));

        if ($orderDetails && $paymentDetails) {
            echo json_encode([
                'orderDetails' => $orderDetails,
                'paymentDetails' => $paymentDetails
            ]);
        } else {
            echo json_encode(['error' => 'Order or payment details not found.']);
        }
    } catch (PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        echo json_encode(['error' => 'Database error occurred:' . " " . $e]);
    }
} else {
    echo json_encode(['error' => 'Missing order_id or payment_id parameter.']);
}
?>