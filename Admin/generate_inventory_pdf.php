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

ob_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            line-height: 1.6;
            color: #333;
        }
        
        .header {
            text-align: right;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
            position: relative;
        }
        
        .company-name {
            position: absolute;
            left: 0;
            top: 0;
            font-size: 24px;
            font-weight: bold;
        }
        
        .report-title {
            position: absolute;
            right: 0;
            top: 0;
            font-size: 20px;
            font-weight: bold;
        }
        
        .report-info {
            margin-top: 38px;
            font-size: 12px;
            text-align: right;
            line-height: 13px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 12px;
        }
        
        th {
            background-color: #2c3e50;
            color: white;
            padding: 10px;
            text-align: left;
        }
        
        td {
            padding: 8px;
            border: 1px solid #222;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-low {
            background-color: #df6540;
        }
        
        .status-medium {
            background-color: #df9440;
        }
        
        .status-warning {
            background-color: #d8df40;
        }
        
        .status-good {
            background-color: #70df40;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">CML Paint Trading</div>
        <div class="report-title">Inventory Report</div>
        <div class="report-info">
            Date Printed: <?php echo date('F d, Y h:i A'); ?><br>
            Printed by: <?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Name of Item</th>
                <th>Brand Name</th>
                <th>Product Type</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Expiration Date</th>
                <th>Date Added</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $DB_con->prepare('SELECT * FROM items');
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $date = new DateTime($row['item_date']);
                    $date1 = new DateTime($row['expiration_date']);
                    $formattedDate = $date->format('F j, Y');
                    $formattedDate1 = $date1->format('F j, Y');
                    
                    // Determine status class
                    ?>
                    <tr>
                        <td><?php echo $row['item_name'] . ($row['gl'] ? " (" . $row['gl'] . ")" : ""); ?></td>
                        <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['type']); ?></td>
                        <td>₱<?php echo number_format($row['item_price'], 2); ?></td>
                        <td><?php echo $row['quantity'] . " " . $row['gl']; ?></td>
                        <td><?php echo $formattedDate1; ?></td>
                        <td><?php echo $formattedDate; ?></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" style="text-align: center;">No items found.</td>
                </tr>
                <?php
            }
            ?>
        </tbody>
    </table>
</body>
</html>
<?php
$html = ob_get_clean();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$canvas = $dompdf->getCanvas();
$font = 'helvetica';
$size = 10;
$canvas->page_text(520, 820, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, $size, [0, 0, 0, 0.7]);

$dompdf->stream('inventory_report.pdf', ['Attachment' => true]);
?>
