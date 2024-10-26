<?php
function composedMessage($orders) {
    // Get the first order to access user details
    $order = $orders[0];
    date_default_timezone_set('Asia/Manila');
    $currentDateTime = new DateTime();
    $formattedDateTime = $currentDateTime->format('F j, Y - h:ia');
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background-color: #033c73; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; background-color: #f9f9f9; }
            .receipt { background-color: white; padding: 20px; margin-top: 20px; border: 1px solid #ddd; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
            table { width: 100%; border-collapse: collapse; margin: 15px 0; }
            th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
            th { background-color: #f2f2f2; }
            .total-row { font-weight: bold; }
            .status { color: #28a745; font-weight: bold; }
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
                    <p><strong>Order Status:</strong> <span class="status">Approved</span></p>
                    <p><strong>Pick-up Location:</strong> ' . htmlspecialchars($order['order_pick_place']) . '</p>
                    <p><strong>Pick-up Date:</strong> ' . date('F j, Y - h:ia', strtotime($order['order_pick_up'])) . '</p>
                    
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
