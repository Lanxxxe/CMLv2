<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];

    $stmt_confirmed = $DB_con->prepare('UPDATE returnitems SET status = "Confirmed" WHERE return_id = :return_id');
    $stmt_confirmed->bindParam(':return_id', $return_id);
    
    if ($stmt_confirmed->execute()) {
        echo json_encode(['success' => true, 'message' => 'Return has been successfully confirmed.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error confirming return: ' . $stmt_confirmed->errorInfo()[2]]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No return ID provided.']);
}
?>
