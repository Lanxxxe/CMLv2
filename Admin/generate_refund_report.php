<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!$_SESSION['admin_username']) {
    header("Location: ./index.php");
}

require_once('../vendor/autoload.php');
require_once 'config.php'; // Include your DB config if you need to fetch data

use Dompdf\Dompdf;
use Dompdf\Options;

// Create a new Dompdf instance
$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

date_default_timezone_set('Asia/Manila');
// Get current date and time
$currentDate = date('F j, Y, g:i a');
$printedBy = "Admin"; // Adjust this if needed

// Fetch filtered data for the report
$filter_date = $_GET['filter_date'] ?? null;
$filter_type = $_GET['filter_type'] ?? null;

$query = "SELECT 
            rp.return_payment_id,
            rp.user_id,
            rp.return_status,
            rp.proof_of_payment,
            rp.amount_return,
            rp.quantity,
            DATE(rp.date) as date_only,
            CONCAT(u.user_firstname, ' ', u.user_lastname) as customer_name
          FROM return_payments rp
          LEFT JOIN users u ON rp.user_id = u.user_id";

$conditions = [];
$params = [];

if (!empty($filter_date)) {
    $conditions[] = "DATE(rp.date) = :filter_date";
    $params[':filter_date'] = $filter_date;
}
if ($filter_type === 'daily') {
    $conditions[] = "DATE(rp.date) = CURDATE()";
} elseif ($filter_type === 'weekly') {
    $conditions[] = "YEARWEEK(rp.date, 1) = YEARWEEK(CURDATE(), 1)";
} elseif ($filter_type === 'monthly') {
    $conditions[] = "MONTH(rp.date) = MONTH(CURDATE()) AND YEAR(rp.date) = YEAR(CURDATE())";
}

if (!empty($conditions)) {
    $query .= " WHERE " . implode(" AND ", $conditions);
}

$query .= " ORDER BY rp.return_payment_id DESC";
$stmt = $DB_con->prepare($query);
$stmt->execute($params);
$return_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Start output buffering to capture HTML
ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { 
            font-family: Arial, sans-serif; 
        }

        .header { 
            text-align: center; 
            font-size: 14px; 
        }
        
        .title { 
            font-size: 18px; 
            margin-bottom: 20px; 
        }
        
        table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        
        th, td { 
            padding: 8px; 
            border: 1px solid #ddd; 
            text-align: center; 
        }
        
        th { 
            background-color: #f2f2f2; 
        }
        
        .watermark { 
            position: absolute; 
            top: 30%; 
            left: 25%; 
            opacity: 0.1; 
            font-size: 100px; 
            transform: rotate(-30deg); 
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>CML Paint Trading</h2>
        <p>Date Printed: <?php echo $currentDate ?></p>
        <p>Printed By: <?php echo htmlspecialchars($printedBy) ?></p>
        <h3 class="title">Refund Report</h3>
        <p>Page 1 of 1</p>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Refund Date</th>
                <th>Total Refund (Php)</th>
                <th>Customer Name</th>
                <th>Returned Quantity</th>
            </tr>
        </thead>
        <tbody>
<?php

// Populate table rows with data
$totalRefund = 0;
foreach ($return_payments as $payment) {
    ?> 
        <tr>
            <td><?php echo htmlspecialchars($payment['date_only']) ?></td>
            <td> Php<?php echo number_format($payment['amount_return'], 2) ?></td>
            <td><?php echo htmlspecialchars($payment['customer_name']) ?></td>
            <td><?php echo htmlspecialchars($payment['quantity']) ?></td>
        </tr>
    <?php
    $totalRefund += $payment['amount_return'];
}

?>
        </tbody>
    </table>
    <h4>Total Refund Amount: Php<?php echo number_format($totalRefund, 2) ?></h4>
    
    <div class="watermark">CML Paint Trading</div>
</body>
</html>

<?php
$html = ob_get_clean();

// Load HTML into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation
$dompdf->setPaper('A4', 'portrait');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF to Browser
$dompdf->stream('refund_report.pdf', ['Attachment' => true]);
?>