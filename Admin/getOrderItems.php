<?php
include 'config.php'; // Your database connection file

if (isset($_GET['payment_id'])) {
    $payment_id = $_GET['payment_id'];
    
    $stmt = $DB_con->prepare("
        SELECT order_name, order_price, order_quantity, order_total 
        FROM orderdetails 
        WHERE payment_id = :payment_id
    ");
    $stmt->bindParam(':payment_id', $payment_id);
    $stmt->execute();
    
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($items);
}
?>
