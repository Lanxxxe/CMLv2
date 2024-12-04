<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!$_SESSION['admin_username']) {
    header("Location: ./index.php");
}

require_once('../vendor/autoload.php');
require_once 'config.php';

use Dompdf\Dompdf;
use Dompdf\Options;

$options = new Options();
$options->set('defaultFont', 'DejaVu Sans');
$dompdf = new Dompdf($options);

// Fetch stock request data
$stmt = $DB_con->prepare('SELECT * FROM product_requests');
$stmt->execute();
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

ob_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
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
            font-size: 8px;
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
        <h1>CML Paint Trading</h1>
        <h3>Stock Request Report</h3>
        <p>Generated on: <?php echo date('F j, Y, h:i A'); ?></p>
    </div>
    <table>
        <thead>
            <tr>
                <th>Request ID</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Quantity</th>
                <th>Requesting Branch</th>
                <th>Approved Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($requests)) { 
                foreach ($requests as $request) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                        <td><?php echo htmlspecialchars($request['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($request['product_brand']); ?></td>
                        <td><?php echo htmlspecialchars($request['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($request['requesting_branch']); ?></td>
                        <td>
                        <?php 
                            if (!empty($request['approved_date'])) {
                                $approvedDate = new DateTime($request['approved_date']);
                                echo $approvedDate->format('F j, Y'); // Example: December 21, 2024
                            } else {
                                echo 'N/A'; // Display N/A if approved_date is null or empty
                            }
                            ?>
                        </td>
                    </tr>
                <?php } 
            } else { ?>
                <tr>
                    <td colspan="6" style="text-align: center;">No stock requests found.</td>
                </tr>
            <?php } ?>
        </tbody>
    </table>

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
$dompdf->stream('stock_request_report.pdf', ['Attachment' => true]);
?>