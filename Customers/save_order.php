<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);

session_start();
include("db_conection.php");
include("../sweet_alert.php");

if (isset($_POST['order_save'])) {
    $user_id = $_POST['user_id'];
    $order_name = $_POST['order_name'];
    $order_price = $_POST['order_price'];
    $order_quantity = $_POST['order_quantity'];
    $order_pick_up = $_POST['order_pick_up'];
    $order_pick_place = $_POST['order_pick_place'];
    $gl = empty($_POST['gl']) ? null : $_POST['gl']; // Check if 'gl' is set
    $order_total = $order_price * $order_quantity;
    $order_status = 'Pending';
    $product_id = $_POST['product_id'];

    $stmt_item = mysqli_prepare($dbcon, 'SELECT quantity FROM items WHERE item_id = ?');
    mysqli_stmt_bind_param($stmt_item, 'i', $product_id);
    mysqli_stmt_execute($stmt_item);
    $result = mysqli_stmt_get_result($stmt_item);
    $item_data = mysqli_fetch_assoc($result);
    extract($item_data);

    if ($quantity <= 0) {
        callSweetAlert('error', 'Error', 'This item is out of stock. Please try again later.', 'shop.php?id=1');
        exit(1);
    }

    $save_order_details = "INSERT INTO orderdetails (user_id, order_name, order_price, order_quantity, order_total, order_status, order_date, order_pick_up, order_pick_place, gl, product_id) 
                           VALUES (?, ?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?)";
    $stmt = mysqli_prepare($dbcon, $save_order_details);
    mysqli_stmt_bind_param($stmt, "isdidssssi", $user_id, $order_name, $order_price, $order_quantity, $order_total, $order_status, $order_pick_up, $order_pick_place, $gl, $product_id); 
    mysqli_stmt_execute($stmt);

    $subtract = "UPDATE items SET quantity = quantity - ? WHERE item_id = ?";
    $stmt_update = mysqli_prepare($dbcon, $subtract);
    mysqli_stmt_bind_param($stmt_update, "ii", $order_quantity, $product_id);
    mysqli_stmt_execute($stmt_update);

    $_SESSION['order_success'] = 'Item successfully added to cart!';
    header('Location: add_to_cart.php?cart=' . $_POST['cart']);
    exit();
}
?>
