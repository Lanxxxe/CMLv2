<?php

session_start();

include_once 'db-connect.php';

if (isset($_POST['removeButton'])) {
    $currentBrand = $_POST['brand'];
    $palletID = $_POST['palletID'];
    $curStep = $_POST['curStep'];

    $query = "DELETE FROM cartitems WHERE itemID = :itemID";
    $statement = $DB_con->prepare($query);
    $statement->bindParam(':itemID', $palletID);

    if ($statement->execute()) {
        header("Location: ../paint-match.php?step=" . urlencode($curStep) . "&brandName=" . urlencode($currentBrand));        
        exit;
} else {
    // Display a JavaScript alert with the error message
    $errorInfo = $statement->errorInfo();
    echo "<script>alert('Error adding pallet to the database: " . addslashes($errorInfo[2]) . "');</script>";
    }
}