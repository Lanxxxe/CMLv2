<?php
// process_invoice.php
session_start();
include("config.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $payment_id = $_POST['payment_id'];
    
    try {
        switch($action) {
            case 'process_downpayment':
            case 'process_installment':
                $amount = floatval($_POST['amount']);
                $payment_image = $_FILES['payment_image'] ?? null;
                
                if ($payment_image && $payment_image['error'] === 0) {
                    $target_dir = "./uploaded_images/";
                    $file_extension = strtolower(pathinfo($payment_image['name'], PATHINFO_EXTENSION));
                    $new_filename = uniqid() . '.' . $file_extension;
                    $target_file = $target_dir . $new_filename;
                    
                    if (move_uploaded_file($payment_image['tmp_name'], $target_file)) {
                        // Call the request_payment stored procedure
                        $stmt = $DB_con->prepare("CALL request_payment(?, ?, ?)");
                        $stmt->execute([$payment_id, $amount,  './uploaded_images/' . $new_filename]);
                        $result = $stmt->fetch(PDO::FETCH_ASSOC);
                        
                        echo json_encode([
                            'status' => $result['status'],
                            'message' => $result['message']
                        ]);
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
                       SUM(o.order_total) AS total_amount,
                       GROUP_CONCAT(CONCAT(o.order_name, ' (', o.order_quantity, ' ', o.gl, ')') SEPARATOR ', ') AS items,
                       pt.track_id,
                       pt.status AS track_status,
                       pt.amount AS track_amount,
                       pt.date_tracked
                FROM paymentform p
                LEFT JOIN orderdetails o ON p.id = o.payment_id
                LEFT JOIN payment_track pt ON p.id = pt.payment_id AND pt.date_tracked = (
                    SELECT MAX(pt2.date_tracked)
                    FROM payment_track pt2
                    WHERE pt2.payment_id = p.id
                )
                WHERE p.id = :id
                GROUP BY p.id, pt.track_id, pt.status, pt.amount, pt.date_tracked
                LIMIT 1;
                ");
                $stmt->execute([':id' => $payment_id]);
                $payment = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($payment) {
                    // Calculate additional details for installment
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
                
            case 'confirm_payment':
                $track_id = $_POST['track_id'];
                $stmt = $DB_con->prepare("CALL confirm_payment(?)");
                $stmt->execute([$track_id]);
                $result = $stmt->fetch(PDO::FETCH_ASSOC);
                
                echo json_encode([
                    'status' => $result['status'],
                    'message' => $result['message']
                ]);
                break;
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
    exit;
}
?>
