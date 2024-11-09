<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

if (!$_SESSION['admin_username']) {

    header("Location: ../index.php");
}

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

$order_type = $_GET['order_type'] ?? null;
if ($order_type !== 'walk_in' && $order_type !== 'online' && $order_type !== 'gcash') {
    $order_type = null;
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
    <link rel="stylesheet" type="text/css" href="css/salesreport.css" />
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


    </style>

</head>

<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper" class="printable">
            <div class="sales-report-container">
                <h1 class="pageTitle">Sales Report</h1>

            <div id="analyticReports">
                <div id="trpContainer">
                    <canvas id="topRequestedProduct"></canvas>
                </div>

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
                         WHERE (DATE(order_date) BETWEEN 
                               DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                               AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY))' . $order_type_str
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
                    <div class="sales-report-item" data-toggle="modal" data-target="#dailySales">
                        <div>
                            <h2>&#8369 <?php echo number_format($dailySales, 2); ?></h2>
                            <p>Daily Sales</p>
                            <p><?php echo date('F j, Y', strtotime($dailyDate)); ?></p>
                        </div>
                        <img src="./local_image/daily.jpeg" alt="Daily Sales">
                    </div>
                    
                    <!-- Weekly Sales Container -->
                    <div class="sales-report-item" data-toggle="modal" data-target="#weeklySales">
                        <div>
                            <h2>&#8369 <?php echo number_format($weeklySales, 2); ?></h2>
                            <p>Weekly Sales</p>
                            <p><?php echo date('F j, Y', strtotime($weeklyStartDate)) . ' - ' . date('F j, Y', strtotime($weeklyEndDate)); ?></p>
                        </div>
                        <img src="./local_image/weekly.jpeg" alt="Weekly Sales">
                    </div>

                    <!-- Monthly Sales Container -->
                    <div class="sales-report-item" data-toggle="modal" data-target="#monthlySales">
                        <div>
                            <h2>&#8369 <?php echo number_format($monthlySales, 2); ?></h2>
                            <p>Monthly Sales</p>
                            <p><?php echo date('F j, Y', strtotime($monthlyStartDate)) . ' - ' . date('F j, Y', strtotime($monthlyEndDate)); ?></p>
                        </div>
                        <img src="./local_image/monthly.jpeg" alt="Monthly Sales">
                    </div>
                </div>

            </div>

            <div class="sales-report-transactions">

                <div class="transactions-header hide-in-print">
                    <div class="transactions-tabs">
                        <a href="./salesreport.php" class="tab-btn <?php echo (!$order_type ? 'active' : ''); ?>">
                            All Transactions
                        </a>
                        <a href="./salesreport.php?order_type=walk_in" class="tab-btn <?php echo ($order_type === 'walk_in' ? 'active' : ''); ?>">
                            Walk In Transactions
                        </a>
                        <a href="./salesreport.php?order_type=gcash" class="tab-btn <?php echo ($order_type === 'gcash' ? 'active' : ''); ?>">
                            GCash Transactions
                        </a>
                    </div>
                    <div class="transactions-actions">
                        <!-- <a href="generate_pdf.php<?php echo ($order_type ? "?order_type=$order_type" : ''); ?>" class="action-btn btn btn-primary"> -->
                        <!--     <i class="fa fa-file-pdf-o"></i> Save PDF -->
                        <!-- </a> -->
                        <button type="button" class="action-btn btn btn-primary" onclick="printContent('page-wrapper')">
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
                                <th>Customer</th>
                                <th>Products Ordered</th>
                                <th>Quantity</th>
                                <th>Total Amount</th>
                                <th>Payment Method</th>
                                <th>Order Status</th>
                            </tr>
                        </thead>    
                        <tbody class="table-striped">
                            <?php
                            $order_type_str_r = str_replace(" AND", "WHERE", $order_type_str);

                            $stmt = $DB_con->prepare('
                                SELECT 
                                    users.user_email, 
                                    users.user_firstname, 
                                    users.user_lastname, 
                                    users.user_address, 
                                    orderdetails.*,
                                    paymentform.payment_method
                                FROM users 
                                INNER JOIN orderdetails ON users.user_id = orderdetails.user_id 
                                LEFT JOIN paymentform ON orderdetails.payment_id = paymentform.id '
                                 . $order_type_str_r .
                                'ORDER BY orderdetails.order_date DESC
                            ');
                            $stmt->execute();


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
                                        <td><span class="order-status <?php echo $statusClass; ?>"><?php echo htmlspecialchars($row['order_status']); ?></span></td>
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
                </div>
            </div>


            </div>

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy 2024 CML Paint Trading Shop | All Rights Reserved
                </p>
            </div>
        </div>

    </div>

    <!-- Mediul Modal -->
    <?php require_once "uploadItems.php"; ?>
    <?php require_once "insertBrandsModal.php"; ?>
    <?php require_once "salesReportModal.php"; ?>

    <?php
        $sumqty_stmt = $DB_con->prepare('SELECT order_name, SUM(order_quantity) as sum_qty
        FROM orderdetails LEFT JOIN paymentform ON paymentform.id = orderdetails.payment_id ' . $order_type_str_r . ' GROUP BY order_name, order_price, gl ORDER BY sum_qty DESC LIMIT 5');
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
                text: 'Top Requested Products',
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

        document.querySelector("#nav_sales_report").className = "active";

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
