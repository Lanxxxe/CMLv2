<?php
session_start();
include 'config.php'; // Include your database connection file

// Set headers to ensure JSON response
header('Content-Type: application/json');

// Turn off error reporting to prevent HTML errors from mixing with JSON
error_reporting(0);

// Function to send JSON response
function sendJsonResponse($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Log received data
        error_log("Received POST data: " . print_r($_POST, true));
        error_log("Received FILES data: " . print_r($_FILES, true));
        
        $payment_id = $_POST['payment_id'] ?? null;
        $return_amount = $_POST['return_amount'] ?? null;
        $user_id = $_POST['user_id'] ?? null;
        $o_quantity = $_POST['order_quantity'] ?? null;
        $return_image = $_FILES['return_image'] ?? null;
        $return_status = 'Returned';

        // Validate file upload
        if (!isset($_FILES['return_image'])) {
            sendJsonResponse(false, 'No file was uploaded');
        }

        if ($_FILES['return_image']['error'] !== UPLOAD_ERR_OK) {
            $uploadErrors = array(
                UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
            );
            $errorMessage = $uploadErrors[$_FILES['return_image']['error']] ?? 'Unknown upload error';
            sendJsonResponse(false, $errorMessage);
        }

        $upload_dir = 'refunds/';
        
        // Check/create directory
        if (!file_exists($upload_dir)) {
            if (!mkdir($upload_dir, 0777, true)) {
                error_log("Failed to create directory: $upload_dir");
                sendJsonResponse(false, 'Failed to create upload directory');
            }
        }

        // Check permissions
        if (!is_writable($upload_dir)) {
            error_log("Directory not writable: $upload_dir");
            sendJsonResponse(false, 'Upload directory is not writable');
        }

        // File type validation
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $file_type = mime_content_type($return_image['tmp_name']);
        
        if (!in_array($file_type, $allowed_types)) {
            sendJsonResponse(false, "Invalid file type: $file_type. Only JPEG and PNG images are allowed.");
        }

        // Generate unique filename
        $file_name = 'refund_' . time() . '_' . uniqid() . '_' . basename($return_image['name']);
        $target_file = $upload_dir . $file_name;

        // Try to move the file
        if (!move_uploaded_file($return_image['tmp_name'], $target_file)) {
            $error = error_get_last();
            error_log("Failed to move uploaded file. Error: " . print_r($error, true));
            sendJsonResponse(false, 'Failed to upload the refund proof image. Please check permissions.');
        }

        // Update the payment status to 'Refunded' and log the refund
        $insertstmt = $DB_con->prepare("INSERT INTO return_payments (user_id, return_status, proof_of_payment, amount_return, quantity)
            VALUES (?, ?, ?, ?, ?)");
        $insertstmt->execute([$user_id,  $return_status, $file_name, $return_amount, $o_quantity]);

        // Check if the update was successful
        if ($insertstmt->rowCount() > 0) {
            $updateOrderDetails = $DB_con->prepare("UPDATE orderdetails
                SET order_status = 'Returned' WHERE payment_id = ?");
            $updateOrderDetails->execute([$payment_id]);

            $updatePaymentForm = $DB_con->prepare("UPDATE paymentform
                SET payment_status = 'Returned' WHERE id = ?");
            $updatePaymentForm->execute([$payment_id]);

            echo json_encode([
                'success' => true,
                'message' => 'Refund processed successfully'
            ]);
            exit;
        } else {
            echo json_encode([
                'success' => true,
                'message' => 'Failed to process the refund. Please try again.'
            ]);
        }
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Invalid request method.'
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
    exit;
}

echo json_encode([
    'success' => false,
    'message' => 'An unknown error occurred'
]);
exit;
?>
