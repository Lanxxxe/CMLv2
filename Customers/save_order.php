<?php
session_start();
include("db_conection.php");

if (isset($_POST['order_save'])) {
    $user_id = $_POST['user_id'];
    $order_name = $_POST['order_name'];
    $order_price = $_POST['order_price'];
    $order_quantity = $_POST['order_quantity'];
    $order_pick_up = $_POST['order_pick_up'];
    $order_pick_place = $_POST['order_pick_place'];
    $gl = isset($_POST['gl']) ? $_POST['gl'] : ''; // Check if 'gl' is set
    $order_total = $order_price * $order_quantity;
    $order_status = 'Pending';
    $product_id = $_POST['product_id'];

    $save_order_details = "INSERT INTO orderdetails (user_id, order_name, order_price, order_quantity, order_total, order_status, order_date, order_pick_up, order_pick_place, gl, product_id) 
                           VALUES ('$user_id', '$order_name', '$order_price', '$order_quantity', '$order_total', '$order_status', CURDATE(), '$order_pick_up', '$order_pick_place', '$gl', '$product_id')";
    mysqli_query($dbcon, $save_order_details);

    $subtract = "UPDATE items SET quantity = quantity - '$order_quantity' WHERE item_id = '$product_id'";
    mysqli_query($dbcon, $subtract);

    $_SESSION['order_success'] = 'Item successfully added to cart!';
    header('Location: add_to_cart.php?cart=' . $_POST['cart']);
    exit();
}
?>
