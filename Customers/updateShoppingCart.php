<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'config.php';


if (isset($_POST['action'])){
    if ($_POST['action'] == 'update_quantity') {
        $quantity = trim($_POST['quantity']);
        $total = trim($_POST['total']);
        $orderID = trim($_POST['order_id']);
        $productID = trim($_POST['product_id']);

        // Query the current stock of the product
        $stockStmt = $DB_con->prepare("SELECT quantity FROM items WHERE item_id = ?");
        $stockStmt->execute([$productID]);
        $product = $stockStmt->fetch(PDO::FETCH_ASSOC);

        $qtyStmt = $DB_con->prepare("SELECT order_quantity FROM orderdetails WHERE order_id = ?");
        $qtyStmt->execute([$orderID]);
        $prevOrderQty = $qtyStmt->fetch(PDO::FETCH_NUM)[0];
    
        if ($product) {
            $currentStock = $product['quantity'];

            // Check if the updated quantity is within the stock
            if ($quantity <= $currentStock) {
                // Proceed to update orderdetails
                $updateStmt = $DB_con->prepare("UPDATE orderdetails SET order_quantity = ?, order_total = ? WHERE order_id = ?");
                $updateStmt->execute([$quantity, $total, $orderID]);

                $updateStmt = $DB_con->prepare("UPDATE items SET quantity = (quantity + :old_order_qty) - :quantity WHERE item_id = :item_id");
                $updateStmt->bindParam(':old_order_qty', $prevOrderQty);
                $updateStmt->bindParam(':quantity', $quantity);
                $updateStmt->bindParam(':item_id', $productID);
                $updateStmt->execute();

                echo json_encode(['status' => 'success', 'message' => 'Quantity updated successfully!']);
            } else {
                // Insufficient stock
                echo json_encode(['status' => 'error', 'message' => 'Insufficient stock for the requested quantity!']);
            }
        } else {
            // Product not found
            echo json_encode(['status' => 'error', 'message' => 'Product not found.']);
        }
    }
    else if ($_POST['action'] == 'update_pickup_date') {
        $pickupDate = trim($_POST['pickup_date']);
        $orderID = trim($_POST['order_id']);
    
        $updateStmt = $DB_con->prepare("UPDATE orderdetails SET order_pick_up = ? WHERE order_id = ?");
        $updateStmt->execute([$pickupDate, $orderID]);
    
        redirectWithMessage('success', 'Order pick up date update!');
    }
    else if ($_POST['action'] == 'update_pickup_place') {
        $pickupPlace = trim($_POST['pickup_place']);
        $orderID = trim($_POST['order_id']);
    
        $updateStmt = $DB_con->prepare("UPDATE orderdetails SET order_pick_place = ? WHERE order_id = ?");
        $updateStmt->execute([$pickupPlace, $orderID]);
    
        redirectWithMessage('success', 'Order pick up place update!');
    }
}
