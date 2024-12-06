<?php
session_start(); // Ensure session is started
include_once 'config.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_POST['returnItem'])) {

    // ITEM INFORMATION
    $userID = $_SESSION['user_id'] ?? null;
    $reason = $_POST['reason'] ?? null;
    $quantity = $_POST['quantity'] ?? null;
    $productName = $_POST['productName'] ?? null;
    $branch = $_POST['branch'] ?? null;
    $status = "Pending";

    // Debug: Check if all required data is set
    if (!$userID || !$reason || !$quantity || !$productName || !$branch) {
        echo "Missing required fields. Debug Info: ";
        var_dump(compact('userID', 'reason', 'quantity', 'productName', 'branch'));
        exit();
    }

    // Define the directory where the images will be stored
    $target_dir = "returnItems/";

    // Handle product image upload
    $productImageName = basename($_FILES["productImage"]["name"]);
    $target_file_product = $target_dir . $productImageName;
    $productImagePath = "";

    // Handle receipt image upload
    $receiptImageName = basename($_FILES["receipt"]["name"]);
    $target_file_receipt = $target_dir . $receiptImageName;
    $receiptImagePath = "";

    // Debug: Check uploaded files
    if (!isset($_FILES["productImage"]) || !isset($_FILES["receipt"])) {
        echo "File upload missing. Debug Info: ";
        var_dump($_FILES);
        exit();
    }

    // Move product image to the folder
    if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file_product)) {
        $productImagePath = $target_file_product; // Store file path
    } else {
        echo "Error uploading product image. Debug Info: ";
        var_dump($_FILES["productImage"]);
        exit();
    }

    // Move receipt image to the folder
    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file_receipt)) {
        $receiptImagePath = $target_file_receipt; // Store file path
    } else {
        echo "Error uploading receipt image. Debug Info: ";
        var_dump($_FILES["receipt"]);
        exit();
    }

    // Insert data into the database using PDO
    try {
        $sql = "INSERT INTO returnitems (user_id, product_name, reason, quantity, product_image, receipt_image, status, branch)
                VALUES (:user_id, :product_name, :reason, :quantity, :product_image, :receipt_image, :status, :branch)";

        $stmt = $DB_con->prepare($sql);

        // Debug: Output the SQL query before executing
        echo "Prepared SQL: ";
        echo $sql . "\n";

        // Bind parameters
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':product_image', $productImagePath);
        $stmt->bindParam(':receipt_image', $receiptImagePath);
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':branch', $branch);

        // Execute the query
        $stmt->execute();

        echo "Return item information saved successfully.";
        header("Location: ./returnItemPage.php");
    } catch (PDOException $e) {
        // Debug: Show detailed PDO error
        echo "PDO Error: " . $e->getMessage();
        echo "Debug Info: ";
        var_dump(compact('userID', 'productName', 'reason', 'quantity', 'productImagePath', 'receiptImagePath', 'status', 'branch'));
    }

    $DB_con = null; // Close the connection

} else {
    header('Location: ../index.php');
    exit();
}
?>
