<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$valid_steps = ["1", "2", "2browse", "2search", "3", "4"];
$step = filter_input(INPUT_GET, "step", FILTER_SANITIZE_SPECIAL_CHARS);
$brand = filter_input(INPUT_GET, "brandName", FILTER_SANITIZE_SPECIAL_CHARS);
$lastStep = filter_input(INPUT_GET, "lastStep", FILTER_SANITIZE_SPECIAL_CHARS);
$receiptID = filter_input(INPUT_GET, "receiptID", FILTER_SANITIZE_SPECIAL_CHARS);


if (!empty($step) && !in_array($step, $valid_steps)) {
    header("Location: HTTP/1.0 404 Not Found");
}


session_start();

if (!$_SESSION['user_email']) {

    header("Location: ../index.php");
}

?>

<?php
include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>

<?php
include("config.php");
$stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>

<?php

require_once 'config.php';

if (isset($_GET['delete_id'])) {


    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id =:order_id');
    $stmt_delete->bindParam(':order_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: cart_items.php");
}

?>
<?php

require_once 'config.php';

if (isset($_GET['update_id'])) {

    $stmt_delete = $DB_con->prepare('update orderdetails set order_status="Ordered" WHERE order_status="Pending" and user_id =:user_id');
    $stmt_delete->bindParam(':user_id', $_GET['update_id']);
    $stmt_delete->execute();
    echo "<script>alert('Item/s successfully ordered!')</script>";

    header("Location: orders.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />

    
    <link rel="stylesheet" href="paint-match/css/paintMatch.css">

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12" integrity="sha384-ujb1lZYygJmzgSwoxRggbCHcjc0rB2XoQrxeTUQyRjrOnlCoYta87iKBWq3EsdM2" crossorigin="anonymous"></script>
    <script type="text/javascript" src="./paint-match/js/functions.js" defer></script>
    <script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

</head>

<body>
    <div id="wrapper">
        <?php include_once('navigation.php') ?>

        <div id="page-wrapper">
        
            <div class="container-fluid">
                <?php
                    $step = filter_input(INPUT_GET, "step", FILTER_SANITIZE_SPECIAL_CHARS);
                
                    if (empty($step) || $step === "1") {
                        include_once("paint-match/step1.php");
                    } else if ($step === "2" && !empty($brand)) {
                        include_once("paint-match/step2.php");
                    } else if ($step === "2browse" && !empty($brand)) {
                        include_once("paint-match/step2-browse.php");
                    } else if ($step === "2search" && !empty($brand)) {
                        include_once("paint-match/step2-search.php");
                    } else if ($step === "3" && !empty($brand)) {
                        include_once("paint-match/step3.php");
                    } else if ($step === "4" && !empty($receiptID)){
                        include_once("paint-match/step4.php");
                    }
                ?>
            </div>
        </div>
    </div>

</body>

</html>