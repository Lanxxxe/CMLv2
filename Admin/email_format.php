<?php
use PHPMailer\PHPMailer\PHPMailer;

function sendPaymentReminder($payment_id, $DB_con) {
    // Get order details
    $stmt = $DB_con->prepare("
        SELECT 
            u.user_email,
            u.user_firstname,
            u.user_lastname,
            pf.id as payment_id,
            pf.payment_type,
            SUM(od.order_total) as total_amount,
            (SELECT SUM(amount) FROM payment_track WHERE payment_id = pf.id) as amount_paid
        FROM 
            users u
            INNER JOIN orderdetails od ON u.user_id = od.user_id
            INNER JOIN paymentform pf ON od.payment_id = pf.id
        WHERE 
            pf.id = ?
        GROUP BY 
            pf.id
    ");
    
    $stmt->execute([$payment_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($order) {
        $remaining_balance = $order['total_amount'] - $order['amount_paid'];
        
        // Only send reminder if there's a remaining balance
        if ($remaining_balance > 0) {
            try {
                $mail = new PHPMailer(true);
                
                // Server settings
                $mail->isSMTP();
                $mail->Host = HOST;
                $mail->SMTPAuth = true;
                $mail->Username = EMAIL_ADDRESS;
                $mail->Password = EMAIL_PASSWORD;
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;
                
                // Recipients
                $mail->setFrom(EMAIL_ADDRESS, 'CML Paint Trading');
                $mail->addAddress($order['user_email'], $order['user_firstname'] . ' ' . $order['user_lastname']);
                $mail->addReplyTo(EMAIL_ADDRESS, 'CML Paint Trading Payments');
                
                // Content
                $mail->isHTML(true);
                $mail->Subject = "Payment Reminder - Order #" . $payment_id;
                $mail->Body = composeReminderMessage($order, $remaining_balance);
                $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $mail->Body));
                
                $mail->send();
                return true;
            } catch (Exception $e) {
                error_log("Mailer Error: " . $e->getMessage());
                return false;
            }
        }
    }
    return false;
}

function composeReminderMessage($order, $remaining_balance) {
    date_default_timezone_set('Asia/Manila');
    $currentDateTime = new DateTime();
    $formattedDateTime = $currentDateTime->format('F j, Y - h:ia');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            
            .container {
                max-width: 600px;
                width: 100%;
                margin: 0 auto;
                padding: 15px;
            }
            
            .header {
                background-color: #033c73;
                color: white;
                padding: 20px 15px;
                text-align: center;
                margin: -15px -15px 0;
            }
            
            .content {
                padding: 20px 15px;
                background-color: #f9f9f9;
            }
            
            .important-notice {
                background-color: #fff3cd;
                border: 1px solid #ffeeba;
                color: #856404;
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
            }
            
            .reply-instructions {
                background-color: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
            }
            
            .footer {
                text-align: center;
                padding: 20px 15px;
                font-size: 12px;
                color: #666;
            }

            @media screen and (max-width: 480px) {
                .container {
                    padding: 10px;
                }
                
                .header {
                    margin: -10px -10px 0;
                    padding: 15px 10px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Payment Reminder</h1>
            </div>
            
            <div class="content">
                <p><strong>Dear ' . htmlspecialchars($order['user_firstname'] . ' ' . $order['user_lastname']) . ',</strong></p>
                
                <p>This is a friendly reminder regarding your remaining balance for Order #' . htmlspecialchars($order['payment_id']) . '.</p>
                
                <div class="important-notice">
                    <h2>Payment Details</h2>
                    <p><strong>Order ID:</strong> ' . htmlspecialchars($order['payment_id']) . '</p>
                    <p><strong>Remaining Balance:</strong> ₱' . number_format($remaining_balance, 2) . '</p>
                    <p><strong>Due Date:</strong> ' . date('F j, Y', strtotime('+3 days')) . '</p>
                </div>
                
                <div class="reply-instructions">
                    <h2>How to Submit Your Payment</h2>
                    <ol>
                        <li>Complete your payment through our accepted payment methods</li>
                        <li>Take a clear photo or screenshot of your payment receipt</li>
                        <li>Reply to this email with your proof of payment attached</li>
                    </ol>
                </div>
                
                <p>If you have already made the payment, please disregard this notice and reply with your proof of payment.</p>
            </div>
            
            <div class="footer">
                <p>Thank you for choosing CML Paint Trading!</p>
                <p>For any questions, please contact our customer service.</p>
                <p>&copy; 2024 CML Paint Trading | All Rights Reserved</p>
            </div>
        </div>
    </body>
    </html>';
    return $html;
}
