<?php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $payment_id = $_POST['payment_id'];
    
    try {
        switch($action) {
            case 'process_downpayment':
                $amount = floatval($_POST['amount']);
                $payment_image = $_FILES['payment_image'] ?? null;
                
                // Get current payment details
                $stmt = $DB_con->prepare("
                    SELECT p.*, 
                           SUM(o.order_total) as total_amount 
                    FROM paymentform p 
                    LEFT JOIN orderdetails o ON p.id = o.payment_id 
                    WHERE p.id = :id 
                    GROUP BY p.id
                ");
                $stmt->execute([':id' => $payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment_image && $payment_image['error'] === 0) {
                    $target_dir = "./uploaded_images/";
                    $file_extension = strtolower(pathinfo($payment_image['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($payment_image['tmp_name'], $target_file)) {
                        // Update payment amount and image path
                        $new_amount = $payment['amount'] + $amount;
                        $stmt = $DB_con->prepare("
                            UPDATE paymentform 
                            SET amount = :amount, 
                                payment_status = 'Confirm',
                                payment_image_path = :image_path 
                            WHERE id = :id
                        ");
                        $result = $stmt->execute([
                            ':amount' => $new_amount,
                            ':image_path' => $new_filename,
                            ':id' => $payment_id
                        ]);
                        
                        if ($result) {
                            echo json_encode(['status' => 'success', 'message' => 'Down payment processed successfully']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Failed to process payment']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to upload payment image']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Payment image is required']);
                }
                break;
                
            case 'process_installment':
                $amount = floatval($_POST['amount']);
                $payment_image = $_FILES['payment_image'] ?? null;
                
                // Get current payment details
                $stmt = $DB_con->prepare("
                    SELECT p.*, 
                           SUM(o.order_total) as total_amount 
                    FROM paymentform p 
                    LEFT JOIN orderdetails o ON p.id = o.payment_id 
                    WHERE p.id = :id 
                    GROUP BY p.id
                ");
                $stmt->execute([':id' => $payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment_image && $payment_image['error'] === 0) {
                    $target_dir = "./uploaded_images/";
                    $file_extension = strtolower(pathinfo($payment_image['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($payment_image['tmp_name'], $target_file)) {
                        // Update payment amount, months paid, and image path
                        $new_amount = $payment['amount'] + $amount;
                        $new_months_paid = $payment['months_paid'] + 1;
                        
                        $stmt = $DB_con->prepare("
                            UPDATE paymentform 
                            SET months_paid = :months_paid,
                                payment_image_path = :image_path 
                            WHERE id = :id
                        ");
                        $result = $stmt->execute([
                            ':months_paid' => $new_months_paid,
                            ':image_path' => $new_filename,
                            ':id' => $payment_id
                        ]);
                        
                        if ($result) {
                            echo json_encode(['status' => 'success', 'message' => 'Installment payment recorded successfully']);
                        } else {
                            echo json_encode(['status' => 'error', 'message' => 'Failed to record payment']);
                        }
                    } else {
                        echo json_encode(['status' => 'error', 'message' => 'Failed to upload payment image']);
                    }
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Payment image is required']);
                }
                break;
                
            case 'get_payment_details':
                $stmt = $DB_con->prepare("
                    SELECT p.*,
                           SUM(o.order_total) as total_amount,
                           GROUP_CONCAT(
                               CONCAT(o.order_name, ' (', o.order_quantity, ' ', o.gl, ')')
                               SEPARATOR ', '
                           ) as items
                    FROM paymentform p
                    LEFT JOIN orderdetails o ON p.id = o.payment_id
                    WHERE p.id = :id
                    GROUP BY p.id
                ");
                $stmt->execute([':id' => $payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment) {
                    // Calculate additional details
                    if ($payment['payment_type'] === 'Installment') {
                        $remaining = $payment['total_amount'] - $payment['amount'];
                        $monthly_payment = $remaining / (12 - $payment['months_paid']);
                        $added_day = 30 * ($payment['months_paid'] + 1);
                        $next_due = date('Y-m-d', strtotime("+$added_day day", strtotime($payment['created_at'])));
                        
                        $payment['monthly_payment'] = $monthly_payment;
                        $payment['next_due'] = $next_due;
                    }
                    
                    echo json_encode(['status' => 'success', 'data' => $payment]);
                } else {
                    echo json_encode(['status' => 'error', 'message' => 'Payment not found']);
                }
                break;
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>
