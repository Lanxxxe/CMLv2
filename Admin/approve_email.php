<?php
function composedMessage($orders, $payment_type, $latest_payment_amount, $total_amount_paid, $remaining_balance) {
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
            
            /* Container with responsive width */
            .container {
                max-width: 600px;
                width: 100%;
                margin: 0 auto;
                padding: 15px;
            }
            
            /* Responsive header */
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
            
            /* Content area */
            .content {
                padding: 20px 15px;
                background-color: #f9f9f9;
            }
            
            /* Receipt section */
            .receipt {
                background-color: white;
                padding: 15px;
                margin-top: 20px;
                border: 1px solid #ddd;
                overflow-x: auto; /* Allow horizontal scroll for table on mobile */
            }
            
            /* Responsive table */
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 15px 0;
                min-width: 500px; /* Ensure minimum width for readability */
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
                color: #28a745;
                font-weight: bold;
            }
            
            /* Footer */
            .footer {
                text-align: center;
                padding: 20px 15px;
                font-size: 12px;
                color: #666;
            }
            
            /* Typography adjustments for mobile */
            p, li {
                font-size: 14px;
                margin-bottom: 10px;
            }
            
            h2 {
                font-size: 20px;
                margin-bottom: 15px;
            }
            
            /* List styles */
            ul {
                padding-left: 20px;
                margin: 15px 0;
            }
            
            /* Mobile-specific adjustments */
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
                
                /* Make prices more readable on mobile */
                td:last-child {
                    font-weight: bold;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Order Approved!</h1>
            </div>
            
            <div class="content">
                <p><strong>Dear Customer,</strong></p>
                
                <p>Great news! Your order has been approved and is now being processed. Thank you for shopping with CML Paint Trading!</p>
                
                <div class="receipt">
                    <h2>Order Receipt</h2>
                    <p><strong>Date Approved:</strong> ' . $formattedDateTime . '</p>
                    <p><strong>Order ID:</strong> ' . htmlspecialchars($order['order_id']) . '</p>
                    <p><strong>Name:</strong> ' . htmlspecialchars($order['user_firstname'] . ' ' . $order['user_lastname']) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($order['user_email']) . '</p>
                    <p><strong>Mobile Number:</strong> ' . htmlspecialchars($order['user_mobile']) . '</p>
                    <p><strong>Payment Type:</strong> ' . htmlspecialchars($payment_type) . '</p>
                    <p><strong>Order Status:</strong> <span class="status">Approved</span></p>
                    <p><strong>Pick-up Location:</strong> ' . htmlspecialchars($order['order_pick_place']) . '</p>
                    <p><strong>Pick-up Date:</strong> ' . date('F j, Y - h:ia', strtotime($order['order_pick_up'])) . '</p>
                    
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
                    </div>';
                    
                if (strcasecmp($payment_type, 'Full Payment') !== 0) {
                    $html .= 
                    '<br>
                     <p><strong>Current Amount Paid:</strong>₱' . number_format($latest_payment_amount) . '</p>
                     <p><strong>Total Amount Paid:</strong>₱' .   number_format($total_amount_paid) . '</p>
                     <p><strong>Remaining Balance:</strong>₱' .   number_format($remaining_balance) . '</p>
                     <br>';
                }

                $html .= '</div>
                
                <p><strong>Important Notes:</strong></p>
                <ul>
                    <li>Keep this receipt for your records</li>
                    <li>For any questions, please contact our customer service</li>
                </ul>
            </div>
            
            <div class="footer">
                <p>Thank you for choosing CML Paint Trading!</p>
                <p>This is an automated email. Please do not reply.</p>
                <p>&copy; 2024 CML Paint Trading | All Rights Reserved</p>
            </div>
        </div>
    </body>
    </html>';
    return $html;
}
