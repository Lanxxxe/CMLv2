<?php
session_start();

$user_id = $_SESSION['user_id'];
require "config.php";

$stmt = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_status = 'Pending' AND user_id = :user_id");
$stmt->execute(array(':user_id' => $user_id));
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$response = [];

foreach ($_POST['order_ids'] as $order_id) {
    $valid = false;
    foreach ($orders as $k => $v) {
        if ($order_id == $v['order_id']) {
            $valid = true;
            break;
        }
    }
    if (!$valid) {
        $response['status'] = 'failed';
        $response['message'] = 'Found an invalid id! Value: ' . $order_id;
        break;
    } else {
        $response['status'] = 'success';
        $response['message'] = 'Checkout successfully!';
    }
}

if ($response['status'] === 'success') {
    $_SESSION['order_ids'] = $_POST['order_ids'];
}

echo json_encode($response);

