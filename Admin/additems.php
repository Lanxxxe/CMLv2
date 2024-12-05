<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
session_start();

$response = array('status' => '', 'message' => '');

if(!isset($_SESSION['admin_username'])) {
    $response['status'] = 'error';
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

include("db_conection.php");
$item_name = $_POST['item_name'];
$item_price = $_POST['item_price'];
$quantity = $_POST['quantity'];
$type = $_POST['type_id'];
$brand = $_POST['brand_id'];
$branch = $_SESSION['current_branch'];

$check_item = "SELECT * FROM items WHERE item_name='$item_name' and brand_name = '$brand'";
$run_query = mysqli_query($dbcon, $check_item);

if(mysqli_num_rows($run_query) > 0) {
    $response['status'] = 'error';
    $response['message'] = 'Item already exists, please try another one!';
} else {
    $imgFile = $_FILES['item_image']['name'];
    $tmp_dir = $_FILES['item_image']['tmp_name'];
    $imgSize = $_FILES['item_image']['size'];

    $upload_dir = 'item_images/';
    $imgExt = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
    $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
    $itempic = rand(1000, 1000000) . "." . $imgExt;

    if(in_array($imgExt, $valid_extensions) && $imgSize < 5000000) {
        if(move_uploaded_file($tmp_dir, $upload_dir . $itempic)) {
            // Fetch brand name
            $get_name_query = "SELECT brand_name FROM brands WHERE brand_id = '$brand'";
            $brand_result = mysqli_query($dbcon, $get_name_query);
            $brand_row = mysqli_fetch_assoc($brand_result);
            $brand_name = $brand_row['brand_name'];
            
            // Fetch type name
            $get_type_query = "SELECT type_name FROM product_type WHERE type_id = '$type'";
            $type_result = mysqli_query($dbcon, $get_type_query);
            $type_row = mysqli_fetch_assoc($type_result);
            $type_name = $type_row['type_name'];

            // Insert the item into the database
            $saveitem = "INSERT INTO items (item_name, brand_name, item_image, item_date, item_price, type, quantity, gl, branch)
                        VALUES ('$item_name', '$brand_name', '$itempic', CURDATE(), '$item_price', '$type_name', '$quantity', '', '$branch')";
            mysqli_query($dbcon, $saveitem);
            
            $response['status'] = 'success';
            $response['message'] = 'Item successfully saved!';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Failed to upload image!';
        }
    } else {
        $response['status'] = 'error';
        $response['message'] = 'Invalid file format or file size too large!';
    }
}

echo json_encode($response);

?>
