<?php

session_start();
include 'config.php';


if (isset($_POST['action'])){
    if ($_POST['action'] == 'update_quantity') {
        $quantity = trim($_POST['quantity']);
        $total = trim($_POST['total']);
        $orderID = trim($_POST['order_id']);
    
        $updateStmt = $DB_con->prepare("UPDATE orderdetails SET order_quantity = ?, order_total = ? WHERE order_id = ?");
        $updateStmt->execute([$quantity, $total, $orderID]);
    
        redirectWithMessage('success', 'Quantity update!');
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