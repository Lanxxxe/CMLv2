<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Database connection parameters
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$servername =$_ENV['DB_HOST']; 
$username =$_ENV['DB_USER']; 
$password =$_ENV['DB_PASS']; 
$dbname =$_ENV['DB_NAME']; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    return;
}

// Sanitize and validate input fields
$firstName = $_SESSION['user_firstname'] ?? '';
$lastName = $_SESSION['user_lastname'] ?? '';
$mobile = $_SESSION['user_mobile'] ?? '';
$email = $_SESSION['user_email'] ?? '';
$address = $_SESSION['user_address'] ?? '';
$paymentType = 'Full Payment';
$pay = 'Walk In';
$token = $_POST['_token'] ?? null;

$item_ids = null;
$qtys = null;

if (isset($token)) {
    $item_ids = $_SESSION['item_ids'] ?? [];
    $qtys = $_SESSION['qtys'] ?? [];
} else {
    $item_ids = $_POST['item_ids'] ?? [];
    $qtys = $_POST['qtys'] ?? [];
}

// Process only if user type is Cashier and payment type is fullpayment
if ($_SESSION['user_type'] !== 'Cashier' || empty($item_ids)) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access, invalid user, or missing item IDs.']);
    return; // Exit if not a Cashier, not fullpayment, or no item IDs provided
}

// Validate item_ids
if (!is_array($item_ids) || empty($item_ids)) {
    echo json_encode(['success' => false, 'message' => 'Invalid item IDs.']);
    return;
}

// Start transaction
$conn->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
try {
    // Insert payment information
    $stmt_insert = $conn->prepare("INSERT INTO paymentform (firstname, lastname, email, address, mobile, payment_method, payment_type, payment_image_path, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $order_status = 'Confirmed';
    $path = '';
    $stmt_insert->bind_param('sssssssss', $firstName, $lastName, $email, $address, $mobile, $pay, $paymentType, $path, $order_status);
    
    if (!$stmt_insert->execute()) {
        throw new Exception('Error: ' . $stmt_insert->error);
    }
    
    $payment_id = $stmt_insert->insert_id;
    $stmt_insert->close();
    
    // Update item quantities and statuses based on item_ids
    $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
    $update_sql = "UPDATE items SET quantity = quantity - 1 WHERE item_id IN ($placeholders)";
    $stmt_update = $conn->prepare($update_sql);
    $stmt_update->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);

    if (!$stmt_update->execute()) {
        throw new Exception('Error updating item quantities: ' . $conn->error);
    }
    $stmt_update->close();

    $select_sql = "SELECT * FROM items WHERE item_id IN ($placeholders)";
    $stmt_select = $conn->prepare($select_sql);
    $stmt_select->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);
    $stmt_select->execute();
    $items = $stmt_select->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_select->close();

    $totalPrice = 0; // To keep track of total price

    foreach ($items as $index => $item) {
        // Calculate total price for the order
        $itemQuantity = $qtys[$index]; // Get qua 'payment_id' => $payment_idntity from the submitted data
        $itemTotalPrice = $item['item_price'] * $itemQuantity;
        $totalPrice += $itemTotalPrice;

        // Prepare parameters for insertion into orderdetails
        $params[] = [
            'user_id' => $_SESSION['user_id'], // Assuming you have user_id in the session
            'order_name' => $item['item_name'],
            'order_price' => $item['item_price'],
            'order_quantity' => $itemQuantity,
            'order_total' => $itemQuantity * $item['item_price'],
            'order_status' => 'Pending', // Or whatever status you prefer
            'order_date' => date('Y-m-d H:i:s'),
            'order_pick_up' =>  date('Y-m-d H:i:s'), // Add appropriate values if needed
            'order_pick_place' =>  'Caloocan', // Add appropriate values if needed
            'gl' => $item['gl'],
            'product_id' => $item['item_id'] // Assuming you have this in your fetched data
        ];
    }

     // Prepare and execute insertion for each order detail
    $stmt_last_order_result= $conn->query("SELECT max(order_id) as last_order_id FROM orderdetails");
    $last_order_id = $stmt_last_order_result->fetch_array()[0];

    $insertOrderDetailsSQL = "INSERT INTO orderdetails (user_id, order_name, order_price, order_quantity, order_total, order_status, order_date, order_pick_up, order_pick_place, gl, product_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_insert_item = $conn->prepare($insertOrderDetailsSQL);

    foreach ($params as $param) {
        // Create temporary variables for binding
        $userId = $param['user_id'];
        $orderName = $param['order_name'];
        $orderPrice = $param['order_price'];
        $orderQuantity = $param['order_quantity'];
        $orderTotal = $param['order_total'];
        $orderStatus = $param['order_status'] ?? '';
        $orderDate = $param['order_date'] ?? '';
        $orderPickUp = $param['order_pick_up'] ?? '';
        $orderPickPlace = $param['order_pick_place'] ?? '';
        $gl = $param['gl'] ?? '';
        $productId = $param['product_id'] ?? '';

        // Now bind the parameters
        if (!$stmt_insert_item->bind_param(
            'issdissssss',
            $userId,
            $orderName,
            $orderPrice,
            $orderQuantity,
            $orderTotal,
            $orderStatus,
            $orderDate,
            $orderPickUp,
            $orderPickPlace,
            $gl,
            $productId
        )) {
            throw new Exception('Error binding parameters: ' . $stmt_insert_item->error);
        }

        if (!$stmt_insert_item->execute()) {
            throw new Exception('Error inserting order details: ' . $stmt_insert_item->error);
        }
    }

    $stmt_insert_item->close();

    if (isset($token)) {
        if ($token !== $_SESSION['_token']) {
            $conn->rollback();
            if ($last_order_id) {
                $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $last_order_id");
            }
            if ($payment_id) {
                $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $payment_id");
            }
            echo json_encode(['success' => false, 'message' => 'Invalid token']);
            exit;
        }
        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Payment successfully processed.']);
        unset($_SESSION['item_ids']);
        unset($_SESSION['qtys']);
        exit;
    } else {
        $conn->rollback();
        if ($last_order_id) {
            $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $last_order_id");
        }
        if ($payment_id) {
            $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $payment_id");
        }
        $token = bin2hex(random_bytes(32));
        $_SESSION['_token'] = $token;
        include './cashier_preview.php';
        $_SESSION['item_ids'] = $item_ids;
        $_SESSION['qtys'] = $qtys;
        echo json_encode(['success' => true, 'message' => $preview, 'payment_id']);
        exit;
    }
   

} catch (Exception $e) {
    $conn->rollback();
    if ($last_order_id) {
        $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $last_order_id");
    }
    if ($payment_id) {
        $conn->query("ALTER TABLE paymentform AUTO_INCREMENT = $payment_id");
    }
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
