<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

if (isset($_GET['return_id'])) {
    $return_id = $_GET['return_id'];

    $stmt_delete = $DB_con->prepare('DELETE FROM returnitems WHERE return_id = :return_id');
    $stmt_delete->bindParam(':return_id', $return_id);
    
    if ($stmt_delete->execute()) {
        echo json_encode(['success' => true, 'message' => 'Return has been successfully deleted.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error deleting return: ' . $stmt_delete->errorInfo()[2]]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'No return ID provided.']);
}
?>
