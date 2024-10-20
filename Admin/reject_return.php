<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];

    $stmt_rejected = $DB_con->prepare('UPDATE returnitems SET status = "Rejected" WHERE return_id = :return_id');
    $stmt_rejected->bindParam(':return_id', $return_id);
    
    if ($stmt_rejected->execute()) {
        echo json_encode(['success' => true, 'message' => 'Return has been successfully rejected.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error rejecting return: ' . $stmt_rejected->errorInfo()[2]]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No return ID provided.']);
}
?>
