<?php
session_start();

if(isset($_GET['order_id'])) {
    $_SESSION['order_id'] = $_GET['order_id'];
    header("Location: payment_form.php");
    exit();
} else {
    echo "Order ID not set.";
}
?>
