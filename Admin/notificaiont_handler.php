<?php
class NotificationHandler {
    private $DB_con;
    
    public function __construct($DB_con) {
        $this->DB_con = $DB_con;
    }

    public function getNotificationContent($id, $type) {
        $details = $this->fetchNotificationDetails($id, $type);
        if (!$details) {
            return $this->generateErrorHtml();
        }
        
        return $this->generateNotificationHtml($details, $type);
    }

    private function fetchNotificationDetails($id, $type) {
        $query = $this->getQueryForType($type);
        if (!$query) {
            return null;
        }

        try {
            $stmt = $this->DB_con->prepare($query);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error fetching notification details: " . $e->getMessage());
            return null;
        }
    }

    private function getQueryForType($type) {
        switch ($type) {
            case 'ordered':
                return "SELECT 
                    a.id, a.user_email, a.created_at,
                    p.firstname, p.lastname, p.email, p.mobile, 
                    p.address, p.payment_method, p.payment_type, 
                    p.amount, p.payment_status,
                    GROUP_CONCAT(o.order_name) as orders,
                    SUM(o.order_quantity) as total_quantity,
                    SUM(o.order_total) as total_amount
                FROM admin_notifications a
                JOIN paymentform p ON a.payment_id = p.id
                JOIN orderdetails o ON p.id = o.payment_id
                WHERE a.id = ?
                GROUP BY a.id";

            case 'confirmed':
                return "SELECT 
                    a.id, a.user_email, a.created_at,
                    if(o.order_id IS NULL,
                        (SELECT GROUP_CONCAT(CONCAT(o2.order_quantity, ' ', o2.gl, ' of ', o2.order_name) SEPARATOR ', ') FROM orderdetails o2 WHERE o2.payment_id = a.payment_id GROUP BY o2.payment_id),
                        CONCAT(o.order_quantity, ' ', o.gl, ' of ', o.order_name)) as orders,
                    if(a.payment_id is NULL, o.order_total, (SELECT SUM(o2.order_total) FROM orderdetails o2 WHERE o2.payment_id = a.payment_id)) AS order_total,
                    p.created_at AS order_date,
                    p.payment_method, p.payment_type, p.amount, 
                    p.payment_status, p.payment_image_path
                FROM admin_notifications a, paymentform p
                JOIN orderdetails o ON p.id = o.payment_id
                    WHERE a.id = ? AND (a.order_id is NOT NULL AND o.order_id = a.order_id OR a.payment_id IS NOT NULL AND p.id = a.payment_id)";

            case 'cancelled':
            case 'returned':
                return "SELECT 
                    a.id, a.user_email, a.created_at,
                    o.order_name, o.order_quantity, o.order_total, 
                    o.order_date, o.gl,
                    p.payment_method, p.payment_type, p.amount, 
                    p.payment_status, p.payment_image_path
                FROM admin_notifications a
                JOIN orderdetails o ON a.order_id = o.order_id
                JOIN paymentform p ON o.payment_id = p.id
                WHERE a.id = ?";

            case 'requested':
                return "SELECT 
                    a.id, 
                    a.user_email, 
                    a.created_at,
                    a.payment_id,
                    p.firstname, 
                    p.lastname, 
                    p.email,
                    p.payment_type, 
                    p.amount as current_amount,
                    p.months_paid,
                    pt.track_id,
                    pt.amount as payment_amount, 
                    pt.status as payment_status,
                    COALESCE(pt.date_tracked, p.created_at) AS date_tracked,
                    GROUP_CONCAT(DISTINCT o.order_name) as orders,
                    SUM(o.order_total) as total_amount,
                    COUNT(DISTINCT o.order_id) as total_orders
                FROM admin_notifications a
                LEFT JOIN paymentform p ON a.payment_id = p.id
                LEFT JOIN payment_track pt ON p.id = pt.payment_id 
                    AND pt.status = 'Requested'
                LEFT JOIN orderdetails o ON p.id = o.payment_id
                WHERE a.id = ?
                GROUP BY a.id, p.id, pt.track_id";

            case 'return request':
            case 'return rejected':
            case 'return deleted':
                return "SELECT 
                    a.id, a.user_email, a.created_at,
                    r.return_id, r.reason, r.quantity,
                    r.product_image, r.receipt_image,
                    r.product_name, r.status as return_status,
                    u.firstname, u.lastname
                FROM admin_notifications a
                JOIN returnitems r ON a.return_id = r.return_id
                JOIN users u ON r.user_id = u.id
                WHERE a.id = ?";

            default:
                return null;
        }
    }

    private function generateNotificationHtml($details, $type) {
        switch ($type) {
            case 'ordered':
                return $this->generateOrderedHtml($details);
            case 'confirmed':
                return $this->generateConfirmedHtml($details);
            case 'cancelled':
                return $this->generateCancelledHtml($details);
            case 'returned':
                return $this->generateReturnedHtml($details);
            case 'requested':
                return $this->generateRequestedHtml($details);
            case 'return request':
            case 'return rejected':
            case 'return deleted':
                return $this->generateReturnHtml($details, $type);
            default:
                return $this->generateErrorHtml();
        }
    }

    private function generateOrderedHtml($details) {
        return "
        <div class='notification-detail'>
            <h3>New Order Details</h3>
            <div class='customer-info'>
                <p><strong>Customer:</strong> {$details['firstname']} {$details['lastname']}</p>
                <p><strong>Email:</strong> {$details['email']}</p>
                <p><strong>Mobile:</strong> {$details['mobile']}</p>
                <p><strong>Address:</strong> {$details['address']}</p>
            </div>
            <div class='order-info'>
                <p><strong>Orders:</strong> {$details['orders']}</p>
                <p><strong>Total Quantity:</strong> {$details['total_quantity']}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($details['total_amount'], 2) . "</p>
                <p><strong>Payment Method:</strong> {$details['payment_method']}</p>
                <p><strong>Payment Type:</strong> {$details['payment_type']}</p>
            </div>
            <div class='timestamp'>
                <small>Ordered on: " . date('M d, Y h:i A', strtotime($details['created_at'])) . "</small>
            </div>
        </div>";
    }

    private function generateConfirmedHtml($details) {
        $imagePath = !empty($details['payment_image_path']) ? '../Customers/' . $details['payment_image_path'] : '';
        
        return "
        <div class='notification-detail'>
            <h3>Order Confirmation</h3>
            <div class='order-info'>
                <p><strong>Orders:</strong> {$details['orders']}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($details['order_total'], 2) . "</p>
                <p><strong>Payment Method:</strong> {$details['payment_method']}</p>
            </div>
            " . ($imagePath ? "<div class='payment-proof'><img src='{$imagePath}' alt='Payment Proof'></div>" : "") . "
            <div class='timestamp'>
                <small>Confirmed on: " . date('M d, Y h:i A', strtotime($details['created_at'])) . "</small>
            </div>
        </div>";
    }

    private function generateRequestedHtml($details) {
        $paymentType = $details['payment_type'];
        $isInstallment = ($paymentType === 'Installment');
        $totalAmount = floatval($details['total_amount']);
        $currentAmount = floatval($details['current_amount']);
        $paymentAmount = floatval($details['payment_amount']);
        
        // Calculate remaining amount
        $remainingAmount = $totalAmount - $currentAmount;
        
        // Calculate progress for installment
        $monthsPaid = intval($details['months_paid']);
        $totalMonths = 12; // Based on the stored procedure
        
        $progressHtml = $isInstallment 
            ? "<p><strong>Months Paid:</strong> {$monthsPaid} of {$totalMonths}</p>"
            : "<p><strong>Amount Paid:</strong> ₱" . number_format($currentAmount, 2) . " of ₱" . number_format($totalAmount, 2) . "</p>";

        return "
        <div class='notification-detail'>
            <h3>Payment Request</h3>
            <div class='customer-info'>
                <p><strong>Customer:</strong> {$details['firstname']} {$details['lastname']}</p>
                <p><strong>Email:</strong> {$details['email']}</p>
            </div>
            <div class='payment-info'>
                <p><strong>Payment Type:</strong> {$paymentType}</p>
                <p><strong>Orders:</strong> {$details['orders']}</p>
                <p><strong>Total Orders:</strong> {$details['total_orders']}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($totalAmount, 2) . "</p>
                {$progressHtml}
                <p><strong>Current Payment Amount:</strong> ₱" . number_format($paymentAmount, 2) . "</p>
                <p><strong>Remaining Balance:</strong> ₱" . number_format($remainingAmount, 2) . "</p>
            </div>
            <div class='timestamp'>
                <small>Requested on: " . date('M d, Y h:i A', strtotime($details['date_tracked'])) . "</small>
            </div>
        </div>";
    }

    private function generateReturnHtml($details, $type) {
        $statusText = [
            'return request' => 'Return Requested',
            'returned' => 'Item Returned',
            'return rejected' => 'Return Rejected',
            'return deleted' => 'Return Request Deleted'
        ][$type];

        $productImage = !empty($details['product_image']) ? '../' . $details['product_image'] : '';
        $receiptImage = !empty($details['receipt_image']) ? '../' . $details['receipt_image'] : '';

        return "
        <div class='notification-detail'>
            <h3>{$statusText}</h3>
            <div class='customer-info'>
                <p><strong>Customer:</strong> {$details['firstname']} {$details['lastname']}</p>
                <p><strong>Email:</strong> {$details['user_email']}</p>
            </div>
            <div class='return-info'>
                <p><strong>Product:</strong> {$details['product_name']}</p>
                <p><strong>Quantity:</strong> {$details['quantity']}</p>
                <p><strong>Reason:</strong> {$details['reason']}</p>
            </div>
            <div class='images'>
                " . ($productImage ? "<div class='image-container'><p>Product Image:</p><img src='{$productImage}' alt='Product'></div>" : "") . "
                " . ($receiptImage ? "<div class='image-container'><p>Receipt Image:</p><img src='{$receiptImage}' alt='Receipt'></div>" : "") . "
            </div>
            <div class='timestamp'>
                <small>Request made on: " . date('M d, Y h:i A', strtotime($details['created_at'])) . "</small>
            </div>
        </div>";
    }

    private function generateCancelledHtml($details) {
        $imagePath = !empty($details['payment_image_path']) ? '../Customers/' . $details['payment_image_path'] : '';
        
        return "
        <div class='notification-detail'>
            <h3>Order Cancelled</h3>
            <div class='order-info'>
                <p><strong>Order:</strong> {$details['order_name']}</p>
                <p><strong>Quantity:</strong> {$details['order_quantity']} {$details['gl']}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($details['order_total'], 2) . "</p>
                <p><strong>Payment Method:</strong> {$details['payment_method']}</p>
                <p><strong>Payment Type:</strong> {$details['payment_type']}</p>
                <p><strong>Payment Status:</strong> <span class='status-cancelled'>{$details['payment_status']}</span></p>
            </div>
            " . ($imagePath ? "
            <div class='payment-proof'>
                <p><strong>Submitted Payment Proof:</strong></p>
                <img src='{$imagePath}' alt='Payment Proof'>
            </div>" : "") . "
            <div class='timestamp'>
                <small>Cancelled on: " . date('M d, Y h:i A', strtotime($details['created_at'])) . "</small>
            </div>
        </div>";
    }

    private function generateReturnedHtml($details) {
        $imagePath = !empty($details['payment_image_path']) ? '../Customers/' . $details['payment_image_path'] : '';
        
        return "
        <div class='notification-detail'>
            <h3>Order Refunded</h3>
            <div class='order-info'>
                <p><strong>Order:</strong> {$details['order_name']}</p>
                <p><strong>Quantity:</strong> {$details['order_quantity']} {$details['gl']}</p>
                <p><strong>Total Amount:</strong> ₱" . number_format($details['order_total'], 2) . "</p>
                <p><strong>Payment Method:</strong> {$details['payment_method']}</p>
                <p><strong>Payment Type:</strong> {$details['payment_type']}</p>
                <p><strong>Payment Status:</strong> <span class='status-cancelled'>{$details['payment_status']}</span></p>
            </div>
            " . ($imagePath ? "
            <div class='payment-proof'>
                <p><strong>Submitted Payment Proof:</strong></p>
                <img src='{$imagePath}' alt='Payment Proof'>
            </div>" : "") . "
            <div class='timestamp'>
                <small>Cancelled on: " . date('M d, Y h:i A', strtotime($details['created_at'])) . "</small>
            </div>
        </div>";
    }

    private function generateErrorHtml() {
        return "
        <div class='notification-detail error'>
            <p>Error: Could not load notification details.</p>
        </div>";
    }
}

// Usage example:
/*
$handler = new NotificationHandler($DB_con);
$notificationHtml = $handler->getNotificationContent($notificationId, $notificationType);
echo $notificationHtml;
*/
