<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

?>

<?php

require_once 'config.php';

if (isset($_GET['delete_id'])) {

    $stmt_select = $DB_con->prepare('SELECT item_image FROM items WHERE item_id =:item_id');
    $stmt_select->execute(array(':item_id' => $_GET['delete_id']));
    $imgRow = $stmt_select->fetch(PDO::FETCH_ASSOC);
    unlink("item_images/" . $imgRow['item_image']);


    $stmt_delete = $DB_con->prepare('DELETE FROM items WHERE item_id =:item_id');
    $stmt_delete->bindParam(':item_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: items.php");
}

extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email = :user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}


$stmt_edit = $DB_con->prepare("SELECT SUM(order_total) AS total FROM orderdetails WHERE user_id = :user_id AND order_status = 'Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    <link rel="stylesheet" type="text/css" href="../Admin/css/salesreport.css" />
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <script src="js/datatables.min.js"></script>
    <script src="../assets/js/chart.umd.min.js"></script>
    
    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        .transactions-header {
            padding: 0;
            width: 100%;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .transactions-tabs {
            display: flex;
            flex: 1;
            min-width: 300px;
        }

        .date-filter-form {
            display: flex;
            align-items: center;
            padding: 0 15px;
            gap: 10px;
        }

        .date-inputs {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-input-group input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
            height: 34px;
        }

        .date-separator {
            color: #666;
            font-weight: 500;
        }

        .transactions-actions {
            display: flex;
            padding-right: 15px;
            gap: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            height: 34px;
        }

        .action-btn i {
            font-size: 14px;
        }

        @media (max-width: 1200px) {
            .transactions-header {
                flex-direction: column;
                padding: 10px 0;
            }
            
            .transactions-tabs {
                width: 100%;
                overflow-x: auto;
            }
            
            .date-filter-form {
                padding: 10px 15px;
                width: 100%;
                justify-content: center;
            }
            
            .transactions-actions {
                padding: 10px 15px;
                width: 100%;
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .date-inputs {
                flex-wrap: wrap;
                justify-content: center;
            }
            
            .date-input-group {
                width: 100%;
                max-width: 200px;
            }
        }
        .date-filter {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            width: 100%;
        }

        .date-filter-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .date-filter input[type="date"] {
            padding: 6px 10px;
            border: 1px solid #dee2e6;
            border-radius: 4px;
            font-size: 14px;
        }

        .date-filter span {
            color: #495057;
            font-weight: 500;
        }

        .date-filter button {
            padding: 6px 15px;
            margin-left: 10px;
        }

        @media print {
            .date-filter {
                display: none !important;
            }
        }
        #filterTab {
            display: flex;
            align-items: center;
            position: fixed;
            top: 50px;
            background: #333;
            border-radius: 0;
            width: calc(100% - 225px);
        }
        #filterTab .mnav {
            padding: 5px 20px;
            border: none;
            font-size: 18px;
            margin-right: 2px;
            background: #f2f2f2;
            border-radius: 5px;
            color: #0f0f0f;
        }
        #filterTab .mnav:first-child {
            margin-left: 5px;
        }
        #saveAsPDFBtn {
            margin-left: auto;
            margin-right: 10px;
        }
        .printBtn {
            margin-right: 10px;
        }

        #filterTab .activeFilterTab {
            background: gray;
            color: white;
        }
        #filterTab .mnav:hover:not(.activeFilterTab) {
            color: gray;
            transition: all 300ms;
        }
        .sales-report-container {
            height: auto;
        }


        .sales-report-transactions {
            margin: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .sales-report-transactions h2 {
            padding: 20px;
            margin: 0;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            color: #333;
            font-size: 1.5rem;
        }

        .transactions-table {
            padding: 20px;
            overflow-x: auto;
        }

        .transactions-table table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 1rem;
        }

        .transactions-table thead th {
            background-color: #f8f9fa;
            color: #495057;
            font-weight: 600;
            padding: 12px 15px;
            text-align: left;
            border-bottom: 2px solid #dee2e6;
            white-space: nowrap;
        }

        .transactions-table tbody td {
            padding: 12px 15px;
            border-bottom: 1px solid #dee2e6;
            color: #212529;
        }

        .transactions-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .order-status {
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 500;
            text-align: center;
            display: inline-block;
            min-width: 100px;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }

        .transactions-header {
            padding: 0;
            width: 100%;
            background: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .transactions-tabs {
            display: flex;
            flex: 1;
        }

        .tab-btn {
            padding: 15px 25px;
            color: #495057;
            font-weight: 500;
            text-decoration: none;
            border: none;
            border-right: 1px solid #dee2e6;
            background: transparent;
            transition: all 0.2s ease;
        }

        .tab-btn:hover {
            background: rgba(13, 110, 253, 0.05);
            color: #0d6efd;
        }

        .tab-btn.active {
            background: #0d6efd;
            color: white;
        }

        .transactions-actions {
            display: flex;
            align-items: center;
            padding-right: 15px;
            gap: 10px;
        }

        .transactions-separator {
            flex: 1;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 500;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 6px;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .action-btn i {
            font-size: 14px;
        }

        .transactions-header .tab-btn:first-child {
            border-top-left-radius: 8px;
        }

        .transactions-header .tab-btn.active {
            background-color: #666;
        }

        .transactions-header .tab-btn:hover {
            text-decoration: none;
        }

        .transactions-header .tab-btn:not(.active):hover {
            color: inherit;
        }

        .print-header {
            display: none;
            margin: 20px;
        }

        .h-head-text, .h-label {
            margin: 1px;
            font-weight: bold;
        }

        .reports-info {
            display: flex;
            flex-direction: column;
            align-items: end;
        }

        .reports-info > * {
            margin: 1px;
        }

            @media print {
                @page {
                    size: 800px auto;
                    margin: 0;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                body * {
                    visibility: hidden !important;
                }

                .printable, .printable * {
                    visibility: visible !important;
                }

                .printable {
                    position: absolute !important;
                    left: 0 !important;
                    top: 0 !important;
                    width: 100% !important;
                    padding: 0.75rem !important;
                    margin: 0 !important;
                }

                #page-wrapper:not(.printable) {
                    display: none !important;
                }

                .printable tr * {
                    white-space: wrap !important;
                    overflow: wrap !important;
                    text-wrap: wrap !important;
                }

                /* Force background colors to print */
                .sales-report-item {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                /* Apply specific background colors */
                .sales-report-item:nth-child(1) {
                    background-color: #bfea91 !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .sales-report-item:nth-child(2) {
                    background-color: #91d4ea !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                .sales-report-item:nth-child(3) {
                    background-color: #cb91ea !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }

                /* Ensure text remains visible */
                .sales-report-item h2,
                .sales-report-item p {
                    color: black !important;
                }
                
                .hide-in-print {
                    display: none !important;
                }
                .modal {
                    position: absolute !important;
                    left: 0 !important;
                    top: 0 !important;
                    margin: 0 !important;
                    padding: 0 !important;
                }

                .modal-dialog {
                    margin: 0 !important;
                    padding: 0 !important;
                }
                .close {
                    display: none !important;
                }
                .print-header {
                    display: flex !important;
                    justify-content: space-between;
                }
            }

    </style>

</head>

<body>
    <div id="wrapper">
        <?php require_once "navigation.php" ?>

        <div id="page-wrapper">
            <div class="sales-report-container">
                <h1><?php echo $_SESSION['user_firstname'] ?>, Sales Report</h1>

                <div id="analyticReports">
                    <div id="trpContainer">
                        <canvas id="topRequestedProduct"></canvas>
                    </div>

                    <div class="sales-report-content">
                        <?php
                            $current_user_id = $_SESSION['user_id'];
                            $branch = $_SESSION['current_branch'];
                            // $order_type_str = '';
                            // if (isset($order_type)) {
                            //     if($order_type === 'walk_in') {
                            //         $order_type_str = ' AND LCASE(paymentform.payment_method) = \'walk in\'';
                            //     } else if($order_type === 'gcash') {
                            //         $order_type_str = ' AND LCASE(paymentform.payment_method) = \'gcash\'';
                            //     }
                            // }

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
                                "SELECT SUM(order_total) as daily_sales, DATE(order_date) as date
                                FROM orderdetails
                                    LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                                WHERE DATE(order_date) = ? AND user_id = ? and order_pick_place = '$branch'" . $date_filter
                            );
                            $stmt_daily->execute([$end_date, $current_user_id]);
                            $daily = $stmt_daily->fetch(PDO::FETCH_ASSOC);
                            $dailySales = $daily['daily_sales'] ?? 0;
                            $dailyDate = $daily['date'] ?? date('Y-m-d');

                            // Fetch weekly sales
                            $stmt_weekly = $DB_con->prepare(
                                "SELECT SUM(order_total) as weekly_sales
                                 FROM orderdetails 
                                    LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                                 WHERE (DATE(order_date) BETWEEN ? AND ?) AND user_id = ? and order_pick_place = '$branch' " . $date_filter
                            );
                            $stmt_weekly->execute([$start_date_weekly, $end_date, $current_user_id]);
                            $weekly = $stmt_weekly->fetch(PDO::FETCH_ASSOC);
                            $weeklySales = $weekly['weekly_sales'] ?? 0;
                            $weeklyStartDate = $start_date_weekly;
                            $weeklyEndDate = $end_date;

                            // Fetch monthly sales
                            $stmt_monthly = $DB_con->prepare(
                                "SELECT SUM(order_total) as monthly_sales
                                 FROM orderdetails 
                                    LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id
                                 WHERE (DATE(order_date) BETWEEN ? AND ?) AND user_id = ? and order_pick_place = '$branch' " . $date_filter
                            );
                            $stmt_monthly->execute([$start_date_monthly, $end_date, $current_user_id]);
                            $monthly = $stmt_monthly->fetch(PDO::FETCH_ASSOC);
                            $monthlySales = $monthly['monthly_sales'] ?? 0;
                            $monthlyStartDate = $start_date_monthly;
                            $monthlyEndDate = $end_date;
                        ?>

                        <!-- Daily Sales Container -->
                        <div class="sales-report-item" data-toggle="modal" data-target="#dailySales">
                            <div>
                                <h2>&#8369 <?php echo number_format($dailySales, 2); ?></h2>
                                <p>Daily Sales</p>
                                <p><?php echo date('F j, Y', strtotime($dailyDate)); ?></p>
                            </div>
                            <img src="../Admin/local_image/daily.jpeg" alt="Daily Sales">
                        </div>
                        
                        <!-- Weekly Sales Container -->
                        <div class="sales-report-item" data-toggle="modal" data-target="#weeklySales">
                            <div>
                                <h2>&#8369 <?php echo number_format($weeklySales, 2); ?></h2>
                                <p>Weekly Sales</p>
                                <p><?php echo date('F j, Y', strtotime($weeklyStartDate)) . ' - ' . date('F j, Y', strtotime($weeklyEndDate)); ?></p>
                            </div>
                            <img src="../Admin/local_image/weekly.jpeg" alt="Weekly Sales">
                        </div>

                        <!-- Monthly Sales Container -->
                        <div class="sales-report-item" data-toggle="modal" data-target="#monthlySales">
                            <div>
                                <h2>&#8369 <?php echo number_format($monthlySales, 2); ?></h2>
                                <p>Monthly Sales</p>
                                <p><?php echo date('F j, Y', strtotime($monthlyStartDate)) . ' - ' . date('F j, Y', strtotime($monthlyEndDate)); ?></p>
                            </div>
                            <img src="../Admin/local_image/monthly.jpeg" alt="Monthly Sales">
                        </div>
                    </div>
                </div>


                <div class="sales-report-transactions">

                    <div class="transactions-header hide-in-print">
                        <div class="transactions-tabs">
                        </div>


                    <form id="dateFilterForm" class="date-filter-form">
                        <div class="date-inputs">
                            <div class="date-input-group">
                                <input type="date" 
                                       id="end_date" 
                                       name="end_date" 
                                       min="<?php echo htmlspecialchars($min_date); ?>" 
                                       max="<?php echo date('Y-m-d'); ?>"
                                       value="<?php echo htmlspecialchars($end_date ?? ''); ?>"
                                       placeholder="End Date">
                            </div>
                            <button type="submit" class="action-btn btn btn-secondary">
                                <i class="fa fa-filter"></i> Filter
                            </button>
                            <?php 
                                    $start_date_c = $_GET['start_date'] ?? null; 
                                    $end_date_c = $_GET['end_date'] ?? null; 
                                    if ($start_date_c || $end_date_c): ?>
                                <a href="<?php echo "salesreport.php"; ?>" 
                                   class="action-btn btn btn-secondary">
                                    <i class="fa fa-times"></i>
                                </a>
                            <?php endif; ?>
                        </div>
                    </form>
                        <?php
                            $pdf_args = '';
                            if(isset($_GET['end_date'])) {
                                $pdf_args = "?end_date={$_GET['end_date']}";
                            }
                        ?>
                        <div class="transactions-actions">
                            <a href="generate_pdf.php<?php echo $pdf_args ?>" class="action-btn btn btn-primary">
                                <i class="fa fa-file-pdf-o"></i> Save PDF
                            </a>
                            <button type="button" style="margin: 10px auto;" class="action-btn btn btn-primary" onclick="printContent('page-wrapper')">
                                <i class="fa fa-print"></i> Print
                            </button>
                        </div>
                    </div>

                    <div class="transactions-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Transaction Date</th>
                                    <th>Invoice No.</th>
                                    <th>Products Ordered</th>
                                    <th>Quantity</th>
                                    <th>Total Amount</th>
                                    <th>Order Status</th>
                                </tr>
                            </thead>    
                            <tbody class="table-striped">
                                <?php

                                $stmt = $DB_con->prepare("
                                    SELECT 
                                        orderdetails.*
                                    FROM orderdetails 
                                    WHERE orderdetails.user_id = :current_user  and order_pick_place = '$branch' 
                                    ORDER BY orderdetails.order_date DESC
                                ");
                                $stmt->bindParam(':current_user', $current_user_id, PDO::PARAM_INT);
                                $stmt->execute();


                                if ($stmt->rowCount() > 0) {
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $orderDate = date('M d, Y', strtotime($row['order_date']));
                                        $statusClass = strtolower($row['order_status']) == 'completed' ? 'status-completed' : 
                                                     (strtolower($row['order_status']) == 'cancelled' ? 'status-cancelled' : 'status-pending');
                                        ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($orderDate); ?></td>
                                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                                            <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                                            <td>₱<?php echo number_format($row['order_total'], 2); ?></td>
                                            <td><span class="order-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No transactions found.</td>
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

        <br />
        <br />

    </div>
    <!-- /#wrapper -->


    <!-- Mediul Modal -->
    <?php require_once "./salesReportModal.php"; ?>
    <?php
        $sumqty_stmt = $DB_con->prepare("SELECT order_name, SUM(order_quantity) as sum_qty
        FROM orderdetails LEFT JOIN paymentform ON paymentform.id = orderdetails.payment_id
        WHERE orderdetails.user_id = :customer_user_id and order_pick_place = '$branch'
        GROUP BY order_name, order_price, gl ORDER BY sum_qty DESC LIMIT 5");
    $sumqty_stmt->bindParam(':customer_user_id', $current_user_id, PDO::PARAM_INT);
    $sumqty_stmt->execute();
    $sum_qty = $sumqty_stmt->fetchAll(PDO::FETCH_ASSOC);


    $top_requested_product = [];
    $top_requested_product_qty = [];
    for ($i = 0; $i < 5; ++$i) {
        $sum = $sum_qty[$i] ?? ['order_name' => '-', 'sum_qty' => 0];
        $top_requested_product[] = $sum['order_name'];
        $top_requested_product_qty[] = $sum['sum_qty'];
    }
    ?>

    <script type="text/javascript" charset="utf-8">
        document.addEventListener('DOMContentLoaded', () => {
            const ctx = document.getElementById('topRequestedProduct');

            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?= json_encode($top_requested_product) ?>,
                    datasets: [{
                        label: 'Most Requested Products',
                        data: <?= json_encode($top_requested_product_qty) ?>,
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)',  // Blue
                            'rgba(16, 185, 129, 0.8)',  // Green
                            'rgba(245, 158, 11, 0.8)',  // Orange
                            'rgba(236, 72, 153, 0.8)',  // Pink
                            'rgba(139, 92, 246, 0.8)'   // Purple
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(16, 185, 129, 1)',
                            'rgba(245, 158, 11, 1)',
                            'rgba(236, 72, 153, 1)',
                            'rgba(139, 92, 246, 1)'
                        ],
                        borderWidth: 2,
                        borderRadius: 6,
                        maxBarThickness: 40
                    }]
                },
                options: {
                    indexAxis: 'y',
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top',
                            labels: {
                                font: {
                                    size: 13,
                                    family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                                },
                                padding: 20
                            }
                        },
                        title: {
                            display: true,
                            text: 'Most Requested Products',
                            font: {
                                size: 16,
                                weight: 'bold',
                                family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                            },
                            padding: {
                                top: 10,
                                bottom: 20
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                display: true,
                                drawBorder: true,
                                drawOnChartArea: true,
                                drawTicks: true,
                                color: 'rgba(0, 0, 0, 0.05)'
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    size: 12
                                }
                            }
                        }
                    },
                    layout: {
                        padding: {
                            left: 20,
                            right: 20,
                            top: 0,
                            bottom: 10
                        }
                    }
                }
            });

        });
        function confirmEdit(itemName) {
            Swal.fire({
                title: 'Edit Item',
                text: `Are you sure you want to edit "${itemName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, edit it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'edititem.php?edit_id=' + encodeURIComponent(itemName);
                }
            });
            return false; // Prevent default link behavior
        }

        function confirmDelete(itemName) {
            Swal.fire({
                title: 'Remove Item',
                text: `Are you sure you want to remove "${itemName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel',
                dangerMode: true // Adds red color to the confirm button
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_id=' + encodeURIComponent(itemName);
                }
            });
            return false; // Prevent default link behavior
        }
        $(document).ready(function() {
            $('#example').dataTable();
        });

        $(document).ready(function() {
            $('#priceinput').keypress(function(event) {
                return isNumber(event, this)
            });
        });

        function isNumber(evt, element) {

            var charCode = (evt.which) ? evt.which : event.keyCode

            if (
                (charCode != 45 || $(element).val().indexOf('-') != -1) &&
                (charCode != 46 || $(element).val().indexOf('.') != -1) &&
                (charCode < 48 || charCode > 57))
                return false;

            return true;
        }

        function printContent(id) {
            document.querySelectorAll('.printable').forEach(e => {
                e.classList.remove('printable');
            });
            const ne = document.getElementById(id);
            ne.classList.add('printable');
            window.print();
            ne.classList.remove('printable');
        }
    </script>
</body>

</html>
