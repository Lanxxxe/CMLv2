<?php

session_start();

include_once 'db-connect.php';

if (isset($_POST['addPallet'])) {
    $currentBrand = $_POST['brand'];
    $palletName = $_POST['palletName'];
    $palletCode = $_POST['palletCode'];
    $palletRGB = $_POST['palletRGB'];
    $curStep = $_POST['curStep'];

    $query = "INSERT INTO cartitems (palletName, palletCode, palletRGB) VALUES (:palletname, :code, :rgb)";
    $statement = $DB_con->prepare($query);
    $statement->bindParam(':palletname', $palletName);
    $statement->bindParam(':code', $palletCode);
    $statement->bindParam(':rgb', $palletRGB);

    if ($statement->execute()) {
        // $errorInfo = $statement->errorInfo();

        // echo $errorInfo;
        // Redirect to the same page to clear the form data and prevent re-submission
        header("Location: ../paint-match.php?step=" . urlencode($curStep) . "&brandName=" . urlencode($currentBrand));        
} else {
    // Display a JavaScript alert with the error message
    $errorInfo = $statement->errorInfo();
    echo "<script>alert('Error adding pallet to the database: " . addslashes($errorInfo[2]) . "');</script>";
    }
}
?>
