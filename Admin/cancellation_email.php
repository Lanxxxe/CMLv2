<?php
use PHPMailer\PHPMailer\PHPMailer;


function composeCancellationMessage($orders, $cancellation_reason = '') {
    // Get the first order to access user details
    $order = $orders[0];
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
            /* Reset styles for email clients */
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }
            
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
                -webkit-text-size-adjust: 100%;
                -ms-text-size-adjust: 100%;
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
            
            .header h1 {
                font-size: 24px;
                word-break: break-word;
            }
            
            .content {
                padding: 20px 15px;
                background-color: #f9f9f9;
            }
            
            .cancellation-notice {
                background-color: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 15px;
                margin: 15px 0;
                border-radius: 4px;
            }
            
            .receipt {
                background-color: white;
                padding: 15px;
                margin-top: 20px;
                border: 1px solid #ddd;
                overflow-x: auto;
            }
            
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                min-width: 500px;
            }
            
            th, td {
                padding: 8px;
                text-align: left;
                border-bottom: 1px solid #ddd;
                font-size: 14px;
            }
            
            th {
                background-color: #f2f2f2;
                white-space: nowrap;
            }
            
            .total-row {
                font-weight: bold;
            }
            
            .status {
                color: #dc3545;
                font-weight: bold;
            }
            
            .footer {
                text-align: center;
                padding: 20px 15px;
                font-size: 12px;
                color: #666;
            }
            
            p, li {
                font-size: 14px;
                margin-bottom: 10px;
            }
            
            h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            ul {
                padding-left: 20px;
                margin: 15px 0;
            }
            
            @media screen and (max-width: 480px) {
                .container {
                    padding: 10px;
                }
                
                .header {
                    margin: -10px -10px 0;
                    padding: 15px 10px;
                }
                
                .header h1 {
                    font-size: 20px;
                }
                
                .content, .receipt {
                    padding: 12px;
                }
                
                p, li {
                    font-size: 13px;
                }
                
                h2 {
                    font-size: 18px;
                }
                
                .receipt {
                    margin: 15px -5px;
                }
                
                td:last-child {
                    font-weight: bold;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Order Cancelled</h1>
            </div>
            
            <div class="content">
                <p><strong>Dear ' . htmlspecialchars($order['user_firstname'] . ' ' . $order['user_lastname']) . ',</strong></p>
                
                <p>We regret to inform you that your order has been cancelled. Here are the details of your cancelled order:</p>
                
                <div class="cancellation-notice">
                    <h2>Cancellation Details</h2>
                    <p><strong>Date Cancelled:</strong> ' . $formattedDateTime . '</p>';
                    
                if (!empty($cancellation_reason)) {
                    $html .= '<p><strong>Reason for Cancellation:</strong> ' . htmlspecialchars($cancellation_reason) . '</p>';
                }
                
                $html .= '</div>
                
                <div class="receipt">
                    <h2>Order Details</h2>
                    <p><strong>Order ID:</strong> ' . htmlspecialchars($order['order_id']) . '</p>
                    <p><strong>Name:</strong> ' . htmlspecialchars($order['user_firstname'] . ' ' . $order['user_lastname']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($order['user_email']) . '</p>
                    <p><strong>Mobile Number:</strong> ' . htmlspecialchars($order['user_mobile']) . '</p>
                    <p><strong>Order Status:</strong> <span class="status">Cancelled</span></p>
                    
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantity</th>
                                    <th>Unit</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>';
                            
                            $total = 0;
                            foreach ($orders as $item) {
                                $total += $item['order_total'];
                                $html .= '
                                <tr>
                                    <td>' . htmlspecialchars($item['order_name']) . '</td>
                                    <td>' . htmlspecialchars($item['order_quantity']) . '</td>
                                    <td>' . htmlspecialchars($item['gl']) . '</td>
                                    <td>₱' . number_format($item['order_price'], 2) . '</td>
                                    <td>₱' . number_format($item['order_total'], 2) . '</td>
                                </tr>';
                            }
                            
                            $html .= '
                                <tr class="total-row">
                                    <td colspan="4" align="right"><strong>Total Amount:</strong></td>
                                    <td>₱' . number_format($total, 2) . '</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>If you have made any payments, our team will process your refund according to our refund policy</li>
                    <li>You can place a new order at any time through our website</li>
                    <li>For any questions about this cancellation, please contact our customer service</li>
                </ul>
            </div>
            
            <div class="footer">
                <p>We apologize for any inconvenience caused.</p>
                <p>Thank you for your understanding.</p>
                <p>This is an automated email. Please do not reply.</p>
                <p>&copy; 2024 CML Paint Trading | All Rights Reserved</p>
            </div>
        </div>
    </body>
    </html>';
    return $html;
}

function sendCancellationEmail($orders, $cancellation_reason = '') {
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
        
        // Get the first order for user details
        $order = $orders[0];
        
        // Recipients
        $mail->setFrom(EMAIL_ADDRESS, 'CML Paint Trading');
        $mail->addAddress($order['user_email'], $order['user_firstname'] . ' ' . $order['user_lastname']);
        $mail->addReplyTo(EMAIL_ADDRESS, 'CML Paint Trading Support');
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Order Cancelled - Order #" . $order['order_id'];
        $mail->Body = composeCancellationMessage($orders, $cancellation_reason);
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $mail->Body));
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}
