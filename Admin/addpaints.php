<?php
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
$brand = $_POST['paint_brand_id'];
$item_price = $_POST['item_price'];
$expiration_date = $_POST['expiration_date'];
$quantity = $_POST['quantity'];
$type = $_POST['paint_type_id'];
$gl = $_POST['gl'];
$pallet_id = $_POST['pallet'];
$branch = $_SESSION['current_branch'];

$get_type_query = "SELECT type_name FROM product_type WHERE type_id = '$type'";
$type_result = mysqli_query($dbcon, $get_type_query);
$type_row = mysqli_fetch_assoc($type_result);
$type_name = $type_row['type_name'];

$get_brand_query = "SELECT brand_name FROM brands WHERE brand_id = '$brand'";
$brand_result = mysqli_query($dbcon, $get_brand_query);
$brand_row = mysqli_fetch_assoc($brand_result);
$brand_name = $brand_row['brand_name'];

// Fetch brand name
$stmt = $dbcon->prepare("SELECT * FROM items WHERE item_name=? AND brand_name=? AND type=? AND pallet_id=? AND gl=? AND branch=? AND expiration_date=?");
$stmt->bind_param("sssssss", $item_name, $brand_name, $type_name, $pallet_id, $gl, $branch, $expiration_date);
$stmt->execute();
$run_query = $stmt->get_result();


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
            
            // Fetch type name
            // $get_type_query = "SELECT type_name FROM product_type WHERE type_id = '$type'";
            // $type_result = mysqli_query($dbcon, $get_type_query);
            // $type_row = mysqli_fetch_assoc($type_result);
            // $type_name = $type_row['type_name'];


            $saveitem = "INSERT INTO items (item_name, brand_name, item_image, item_date, expiration_date, item_price, type, quantity, gl, pallet_id, branch)
                        VALUES ('$item_name', '$brand_name', '$itempic', CURDATE(), '$expiration_date', '$item_price', '$type_name', '$quantity', '$gl', '$pallet_id', '$branch')";
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
