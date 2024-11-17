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
        
        .sales-summary {
            display: flex;
            justify-content: space-between;
            margin: 20px 0;
            padding: 10px;
        }
        
        .summary-box {
            width: 30%;
            padding: 15px;
            background-color: #f5f5f5;
            border-radius: 5px;
            text-align: center;
        }
        
        .summary-box h3 {
            margin: 0;
            color: #333;
            font-size: 16px;
        }
        
        .summary-box .amount {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin: 10px 0;
        }
        
        .summary-box .date {
            font-size: 12px;
            color: #666;
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
            border-bottom: 1px solid #ddd;
        }
        
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        .status-completed {
            color: #27ae60;
        }
        
        .status-cancelled {
            color: #e74c3c;
        }
        
        .status-pending {
            color: #f39c12;
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
    </style>
</head>
<body>
    <div class="header">
        <div class="company-name">CML Paint Trading</div>
        <div class="report-title">Sales Report</div>
        <div class="report-info">
            Date Printed: <?php echo date('F d, Y h:i A'); ?><br>
            Printed by: <?php echo htmlspecialchars($_SESSION['user_firstname'] . ' ' . $_SESSION['user_lastname']); ?>
        </div>
    </div>


<?php
    $current_user_id = $_SESSION['user_id'];

    $stmt_dates = $DB_con->prepare('SELECT MIN(DATE(order_date)) as min_date, MAX(DATE(order_date)) as max_date FROM orderdetails');
    $stmt_dates->execute();
    $date_range = $stmt_dates->fetch(PDO::FETCH_ASSOC);

    $end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');
    $start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('@0'));

    $start_date_weekly = date('Y-m-d', strtotime($end_date . '-7 days'));
    $start_date_monthly = date('Y-m-d', strtotime($end_date . '-1 months'));

    // Validate dates
    $min_date = $date_range['min_date'];
    $max_date = $date_range['max_date'];

    // Add date filter to existing order type string
    $date_filter = " AND (DATE(order_date) BETWEEN '{$start_date}' AND '{$end_date}')";


    // Fetch daily sales
    $stmt_daily = $DB_con->prepare(
        'SELECT SUM(order_total) as daily_sales, DATE(order_date) as date
        FROM orderdetails
            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
        WHERE DATE(order_date) = ? AND user_id = ? ' . $date_filter
    );
    $stmt_daily->execute([$end_date, $current_user_id]);
    $daily = $stmt_daily->fetch(PDO::FETCH_ASSOC);
    $dailySales = $daily['daily_sales'] ?? 0;
    $dailyDate = $daily['date'] ?? date('Y-m-d');

    // Fetch weekly sales
    $stmt_weekly = $DB_con->prepare(
        'SELECT SUM(order_total) as weekly_sales
         FROM orderdetails 
            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
         WHERE (DATE(order_date) BETWEEN ? AND ?) AND user_id = ? ' . $date_filter
    );
    $stmt_weekly->execute([$start_date_weekly, $end_date, $current_user_id]);
    $weekly = $stmt_weekly->fetch(PDO::FETCH_ASSOC);
    $weeklySales = $weekly['weekly_sales'] ?? 0;
    $weeklyStartDate = $start_date_weekly;
    $weeklyEndDate = $end_date;

    // Fetch monthly sales
    $stmt_monthly = $DB_con->prepare(
        'SELECT SUM(order_total) as monthly_sales
         FROM orderdetails 
            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
         WHERE (DATE(order_date) BETWEEN ? AND ?) AND user_id = ? ' . $date_filter
    );
    $stmt_monthly->execute([$start_date_monthly, $end_date, $current_user_id]);
    $monthly = $stmt_monthly->fetch(PDO::FETCH_ASSOC);
    $monthlySales = $monthly['monthly_sales'] ?? 0;
    $monthlyStartDate = $start_date_monthly;
    $monthlyEndDate = $end_date;
?>

    <table>
        <thead>
            <tr>
                <th>Transaction Date</th>
                <th>Invoice No.</th>
                <th>Customer</th>
                <th>Products</th>
                <th>Qty</th>
                <th>Total Amount</th>
                <th>Payment</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $stmt = $DB_con->prepare('
                SELECT 
                    users.user_email, 
                    users.user_firstname, 
                    users.user_lastname,
                    orderdetails.*,
                    paymentform.payment_method
                FROM users 
                INNER JOIN orderdetails ON users.user_id = orderdetails.user_id 
                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id 
                WHERE date(orderdetails.order_date) <= ? AND users.user_id = ?
                ORDER BY orderdetails.order_date DESC
            ');
            $stmt->execute([$end_date, $current_user_id]);

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $customerName = $row['user_firstname'] . ' ' . $row['user_lastname'];
                    $orderDate = date('M d, Y', strtotime($row['order_date']));
                    $statusClass = strtolower($row['order_status']) == 'completed' ? 'status-completed' : 
                                 (strtolower($row['order_status']) == 'cancelled' ? 'status-cancelled' : 'status-pending');
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($orderDate); ?></td>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($customerName); ?></td>
                        <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                        <td>₱<?php echo number_format($row['order_total'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['payment_method'] ?? 'N/A'); ?></td>
                        <td><span class="<?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="8" style="text-align: center;">No transactions found.</td>
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

$dompdf->stream('sales_report.pdf', ['Attachment' => true]);
?>