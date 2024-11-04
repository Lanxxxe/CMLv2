<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
include("config.php");

if (isset($_GET['order_id']) && isset($_GET['payment_id'])) {
    $order_id = $_GET['order_id'];
    $payment_id = $_GET['payment_id'];

    // Fetch order details
    $stmt = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_id = :order_id");
    $stmt->bindParam(':order_id', $order_id);
    $stmt->execute();
    $orderDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    // Fetch payment details
    $stmt = $DB_con->prepare("SELECT payment_image_path FROM payment_form WHERE id = :payment_id");
    $stmt->bindParam(':payment_id', $payment_id);
    $stmt->execute();
    $paymentDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'orderDetails' => $orderDetails,
        'paymentDetails' => $paymentDetails
    ]);
}
?>