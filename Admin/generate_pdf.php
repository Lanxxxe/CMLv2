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
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <style>
        <?php
            echo file_get_contents("bootstrap/css/bootstrap.min.css");
            echo file_get_contents("font-awesome/css/font-awesome.min.css");
            echo file_get_contents("./css/salesreport.css");
        ?>
    </style>
    <style>
        .sales-report-content {
            display: flex; /* Use flexbox for layout */
            justify-content: space-between; /* Space items evenly */
            margin-top: 20px; /* Add space at the top */
        }

        .sales-report-item {
            background-color: #f0f8f0; /* Default background color for items */
            border-radius: 10px; /* Rounded corners */
            padding: 20px; /* Padding inside the items */
            width: 30%; /* Width of each item */
            text-align: center; /* Center text */
            position: relative; /* To position icons if needed */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
            margin-bottom: 10px;
        }

        .sales-report-item h2 {
            font-size: 24px; /* Size of the sales amount */
            margin: 0; /* Remove margin */
        }

        .sales-report-item p {
            margin: 5px 0; /* Space between paragraphs */
        }

        .sales-report-item img {
            position: absolute; /* Position the icon */
            top: 10px; /* Distance from top */
            right: 10px; /* Distance from right */
            width: 40px; /* Size of the icon */
            height: 40px; /* Size of the icon */
        }

        #wrapper {
            margin: 30px 50px;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <div id="page-wrapper">
            <div class="sales-report-container">
                <h1>Sales Report</h1>

                <div class="sales-report-content">
                    <?php
                        $order_type_str = '';
                        if (isset($order_type)) {
                            if($order_type === 'walk_in') {
                                $order_type_str = ' AND LCASE(paymentform.payment_method) = \'walk in\'';
                            } else if($order_type === 'gcash') {
                                $order_type_str = ' AND LCASE(paymentform.payment_method) = \'gcash\'';
                            }
                        }
                    // Fetch daily sales
                    $stmt_daily = $DB_con->prepare(
                        'SELECT SUM(order_total) as daily_sales, DATE(order_date) as date
                        FROM orderdetails
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                        WHERE DATE(CURDATE()) = DATE(order_date)' . $order_type_str);
                    $stmt_daily->execute();
                    $daily = $stmt_daily->fetch(PDO::FETCH_ASSOC);
                    $dailySales = $daily['daily_sales'] ?? 0;
                    $dailyDate = $daily['date'] ?? date('Y-m-d');

                    // Fetch weekly sales
                    $stmt_weekly = $DB_con->prepare(
                        'SELECT SUM(order_total) as weekly_sales, 
                                DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) as start_date, 
                                DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY) as end_date 
                         FROM orderdetails 
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                         WHERE DATE(order_date) BETWEEN 
                               DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                               AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)' . $order_type_str
                    );
                    $stmt_weekly->execute();
                    $weekly = $stmt_weekly->fetch(PDO::FETCH_ASSOC);
                    $weeklySales = $weekly['weekly_sales'] ?? 0;
                    $weeklyStartDate = $weekly['start_date'] ?? date('Y-m-d', strtotime('-7 days'));
                    $weeklyEndDate = $weekly['end_date'] ?? date('Y-m-d');

                    // Fetch monthly sales
                    $stmt_monthly = $DB_con->prepare(
                        'SELECT SUM(order_total) as monthly_sales, 
                                DATE_FORMAT(CURDATE(), "%Y-%m-01") as start_date, 
                                LAST_DAY(CURDATE()) as end_date 
                         FROM orderdetails 
                            LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                         WHERE DATE(order_date) BETWEEN 
                               DATE_FORMAT(CURDATE(), "%Y-%m-01") 
                               AND LAST_DAY(CURDATE())' . $order_type_str
                    );
                    $stmt_monthly->execute();
                    $monthly = $stmt_monthly->fetch(PDO::FETCH_ASSOC);
                    $monthlySales = $monthly['monthly_sales'] ?? 0;
                    $monthlyStartDate = $monthly['start_date'] ?? date('Y-m-d', strtotime('-1 month'));
                    $monthlyEndDate = $monthly['end_date'] ?? date('Y-m-d');
                    ?>

                    <!-- Daily Sales Container -->
                    <div class="sales-report-item">
                        <div>
                            <h2>₱ <?php echo number_format($dailySales, 2); ?></h2>
                            <p>Daily Sales</p>
                            <p><?php echo date('F j, Y', strtotime($dailyDate)); ?></p>
                        </div>
                    </div>
                    
                    <!-- Weekly Sales Container -->
                    <div class="sales-report-item">
                        <div>
                            <h2>₱ <?php echo number_format($weeklySales, 2); ?></h2>
                            <p>Weekly Sales</p>
                            <p><?php echo date('F j, Y', strtotime($weeklyStartDate)) . ' - ' . date('F j, Y', strtotime($weeklyEndDate)); ?></p>
                        </div>
                    </div>

                    <!-- Monthly Sales Container -->
                    <div class="sales-report-item">
                        <div>
                            <h2>₱ <?php echo number_format($monthlySales, 2); ?></h2>
                            <p>Monthly Sales</p>
                            <p><?php echo date('F j, Y', strtotime($monthlyStartDate)) . ' - ' . date('F j, Y', strtotime($monthlyEndDate)); ?></p>
                        </div>
                    </div>
                </div>

                <div class="sales-report-transactions">
                    <h2>Transactions</h2>
                    <div class="transactions-table">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Products Ordered</th>
                                    <th>Total Payment</th>
                                    <th>Order Status</th>
                                </tr>
                            </thead>    
                            <tbody>
                                <?php
                                $stmt = $DB_con->prepare('SELECT users.user_email, users.user_firstname, users.user_lastname, users.user_address, orderdetails.* FROM users INNER JOIN orderdetails ON users.user_id = orderdetails.user_id ORDER BY orderdetails.order_pick_up DESC');
                                $stmt->execute();

                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $customerName = $row['user_firstname'] . ' ' . $row['user_lastname'];
                                        $productsOrdered = $row['order_name'];
                                        $orderStatus = $row['order_status'];
                                        $totalBill = $row['order_total'];
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($customerName); ?></td>
                                            <td><?php echo htmlspecialchars($productsOrdered); ?></td>
                                            <td><span style="font-family: DejaVu Sans;">₱</span> <?php echo htmlspecialchars(number_format($totalBill, 2)); ?></td>
                                            <td><?php echo htmlspecialchars($orderStatus); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="4">No transactions found.</td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
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
$dompdf->stream('sales_report.pdf', ['Attachment' => true]);
?>

