<?php 

session_start();

include_once('db-connect.php');

if (isset($_POST['checkout']) || isset($_POST['addToCart']) && isset($_SESSION['user_email'])){
    
    // USER INFORMATION
    $userID = $_SESSION['user_id'];
    $firstname = $_SESSION['user_firstname'];
    $lastname = $_SESSION['user_lastname'];
    $userEmail = $_SESSION['user_email'];
    $userAddress = $_SESSION['user_address'];
    $userMobile = $_SESSION['user_mobile'];


    // ITEM INFORMATION
    $itemId = $_POST['itemID'];
    $itemName = $_POST['itemName'];
    $itemStock = $_POST['itemStock'];
    $itemPrice = $_POST['price'];
    $itemSize = $_POST['size'];
    $itemQuantity = $_POST['quantity'];
    $itemPickupDate = $_POST['pickupDate'];
    $location = $_POST['pickupPlace'];

    $itemTotal = $itemPrice * $itemQuantity;
    
    $paymentStatus = 'Pending';
    $currentDate = date('Y-m-d');

    if ($itemQuantity > $itemStock) {
        header('Location: ../paint-match.php');
        exit();
    }

    $toOrderDetails = "INSERT INTO orderdetails (user_id, order_name, order_price, order_quantity, order_total, order_status, order_date, order_pick_up, order_pick_place, gl, product_id) 
    VALUES (:userID, :orderName, :orderPrice, :orderQuantity, :orderTotal, :orderStatus, :orderDate, :pickupDate, :pickupPlace, :gl, :product_id)";
    
    $statement = $DB_con->prepare($toOrderDetails);
    $statement->bindParam(':userID', $userID);
    $statement->bindParam(':orderName', $itemName);
    $statement->bindParam(':orderPrice', $itemPrice);
    $statement->bindParam(':orderQuantity', $itemQuantity);
    $statement->bindParam(':orderTotal', $itemTotal);
    $statement->bindParam(':orderStatus', $paymentStatus);
    $statement->bindParam(':orderDate', $currentDate);
    $statement->bindParam(':pickupDate', $itemPickupDate);
    $statement->bindParam(':pickupPlace', $location);
    $statement->bindParam(':gl', $itemSize);
    $statement->bindParam(':product_id', $itemId);
    

    if ($statement->execute()){
        $orderID = $DB_con->lastInsertId();
            
        $query = "DELETE FROM cartitems";
        $statement = $DB_con->prepare($query);
        $statement->execute();

        $updateStock = "UPDATE items SET quantity = quantity - '$itemQuantity' WHERE item_id = :product_id";
        $updateStatement = $DB_con->prepare($updateStock);
        $updateStatement->bindParam(':product_id', $itemId);
        $updateStatement->execute(); 
        if (isset($_POST['checkout'])) {
            header('Location: ../checkout.php?order_id=' . $orderID);
            exit();
        } else  {
            header('Location: ../cart_items.php');
            exit();
        }
    }

} else  {
    header('Location: ../index.php');
    exit();
}