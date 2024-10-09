<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!$_SESSION['admin_username']) {
    header("Location: ../index.php");
}

require_once('../../vendor/autoload.php');
require_once '../config.php'; // Include your DB config if you need to fetch data

use Dompdf\Dompdf;
use Dompdf\Options;

// Create a new Dompdf instance
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

// Start output buffering to capture HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Sales Report</title>
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
        <h1 class="mb-4">Monthly Sales Report</h1>
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
                // Fetch monthly transactions
                $stmt_monthly = $DB_con->prepare('SELECT * FROM orderdetails WHERE DATE(order_pick_up) BETWEEN DATE_FORMAT(CURDATE(), "%Y-%m-01") AND LAST_DAY(CURDATE())');
                $stmt_monthly->execute();
                while ($row = $stmt_monthly->fetch(PDO::FETCH_ASSOC)) {
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
$dompdf->stream('monthly_sales_report.pdf', ['Attachment' => true]);
?>

