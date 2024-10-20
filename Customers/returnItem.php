<?php
session_start(); // Ensure session is started
include_once 'config.php';


if (isset($_SESSION['user_email']) && isset($_POST['returnItem'])) {

    // ITEM INFORMATION
    $userID = $_SESSION['user_id'];
    $reason = $_POST['reason'];
    $quantity = $_POST['quantity'];
    $productName = $_POST['productName'];
    $status = "Pending";

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

    // Move product image to the folder
    if (move_uploaded_file($_FILES["productImage"]["tmp_name"], $target_file_product)) {
        $productImagePath = $target_file_product; // Store file path
    } else {
        echo "Error uploading product image.";
        exit();
    }

    // Move receipt image to the folder
    if (move_uploaded_file($_FILES["receipt"]["tmp_name"], $target_file_receipt)) {
        $receiptImagePath = $target_file_receipt; // Store file path
    } else {
        echo "Error uploading receipt image.";
        exit();
    }

    // Insert data into the database using PDO
    try {
        $sql = "INSERT INTO returnitems (user_id, product_name, reason, quantity, product_image, receipt_image, status)
                VALUES (:user_id, :product_name ,:reason, :quantity, :product_image, :receipt_image, :status)";

        $stmt = $DB_con->prepare($sql);
        $stmt->bindParam(':user_id', $userID);
        $stmt->bindParam(':product_name', $productName);
        $stmt->bindParam(':reason', $reason);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':product_image', $productImagePath);
        $stmt->bindParam(':receipt_image', $receiptImagePath);
        $stmt->bindParam(':status', $status);

        $stmt->execute();

        echo "Return item information saved successfully.";
        header("Location: ./returnItemPage.php");
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

    $DB_con = null; // Close the connection

} else {
    header('Location: ../index.php');
    exit();
}
?>
