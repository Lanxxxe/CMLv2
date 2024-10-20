<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!$_SESSION['admin_username']) {
    header("Location: ../index.php");
}

require_once('../../vendor/autoload.php');
require_once './config.php'; // Include your DB config if you need to fetch data

use Dompdf\Dompdf;
use Dompdf\Options;

// Create a new Dompdf instance
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

$order_type = $_GET['order_type'] ?? null;
if ($order_type !== 'walk_in' && $order_type !== 'gcash') {
    $order_type = null;
}

// Start output buffering to capture HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Sales Report</title>
    <style>
        body {
            margin: 30px;
        }
        h1 {
            color: #333;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h1 class="mb-4">Weekly Sales Report</h1>
        <table class="table table-striped table-bordered">
            <thead class="thead-dark">
                <tr>
                    <th>Transaction ID</th>
                    <th>Product</th>
                    <th>Total</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php
                    $order_type_str = '';
                    if (isset($order_type)) {
                        if($order_type === 'walk_in') {
                            $order_type_str = ' AND LCASE(paymentform.payment_method) = \'walk in\'';
                        } else if($order_type === 'gcash') {
                            $order_type_str = ' AND LCASE(paymentform.payment_method) = \'gcash\'';
                        }
                    }
                // Fetch weekly transactions
                    $stmt_weekly = $DB_con->prepare(
                        'SELECT orderdetails.*,
                                DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) as start_date, 
                                DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY) as end_date 
                         FROM orderdetails 
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                         WHERE DATE(order_date) BETWEEN 
                               DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                               AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)' . $order_type_str
                    );
                $stmt_weekly->execute();
                while ($row = $stmt_weekly->fetch(PDO::FETCH_ASSOC)) {
                    echo '<tr>';
                    echo '<td>' . $row['order_id'] . '</td>';
                    echo '<td>' . $row['order_name'] . '</td>';
                    echo '<td>₱ ' . number_format($row['order_total'], 2) . '</td>';
                    echo '<td>' . date('F j, Y', strtotime($row['order_pick_up'])) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php
// Capture the content and clean the buffer
$html = ob_get_clean();

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('weekly_sales_report.pdf', ['Attachment' => true]);
?>

