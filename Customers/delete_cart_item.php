<?php
session_start();
try {
    $user_id = $_SESSION['user_id'];
    require "config.php";

    $response = [];
    $order_ids = [];

    foreach ($_POST['order_ids'] as $order_id) {
        $order_ids[] = $order_id;
        $valid = false;
    }

    if ($response['status'] != 'failed' && count($order_ids) > 0) {
        $_args = implode(',', $order_ids);
        $stmt_delete = $DB_con->prepare("DELETE FROM orderdetails WHERE order_id IN ($_args)");
        if ($stmt_delete->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Successfully deleted selected items!';
        }
    } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid Input! Please enter valid input';
    }
} catch(Exception $e) {
    $response['status'] = 'failed';
    // $response['message'] = $e->getMessage();
    $response['message'] = 'Internal Server Error! Try again later';

    $dateTime = date('Y-m-d H:i:s');

    $errorMessage = sprintf(
        "%s [%d] - %s in %s:%d",
        $dateTime,
        $e->getCode(),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ) . PHP_EOL;
    error_log($errorMessage, 3, '../error.log');
} finally {
    echo json_encode($response);
}

