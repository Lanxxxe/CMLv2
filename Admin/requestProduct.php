<?php
session_start();

// Check if the user is Admin
if ($_SESSION['user_type'] != 'Admin') {
    header("Location: ../index.php");
    exit;
}

include_once 'config.php';

try {
    // Check if the POST action is 'request_product'
    if (isset($_POST['action']) && $_POST['action'] == 'request_product') {
        // Retrieve the POST data
        $productName = $_POST['product_name'];
        $brandName = $_POST['brand_name'];
        $quantity = intval($_POST['quantity']); // Ensure quantity is an integer
        $requestedBy = $_POST['requested_by'];

        // Insert the data into the database
        $stmt = $DB_con->prepare("
            INSERT INTO product_requests (product_name, product_brand, quantity, requesting_branch, status) 
            VALUES (:product_name, :brand_name, :quantity, :requested_by, 'Pending')
        ");
        
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':brand_name', $brandName);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':requested_by', $requestedBy);

        if ($stmt->execute()) {
            // Respond with success
            echo json_encode(['status' => 'success', 'message' => 'Product request submitted successfully!']);
        } else {
            // Respond with an error
            echo json_encode(['status' => 'error', 'message' => 'Failed to submit the product request.']);
        }
    } else {
        // Invalid action
        echo json_encode(['status' => 'error', 'message' => 'Invalid request action.']);
    }
} catch (PDOException $e) {
    // Handle any database errors
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
