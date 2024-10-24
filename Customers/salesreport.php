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
    
    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #saveAsPDFBtn {
            position: fixed !important;
            top: 60px !important;
            right: 10px !important;
        }
    </style>

</head>

<body>
    <div id="wrapper">
        <a type="button" class="btn btn-primary" id="saveAsPDFBtn" href="../Admin/generate_pdf.php">Save as PDF</a>
        <?php require_once "navigation.php" ?>

        <div id="page-wrapper">
            <div class="sales-report-container">
                <h1>Sales Report <?php echo $_SESSION['user_firstname'] ?></h1>

                <div class="sales-report-content">
                    <?php
                    $current_user_id = $_SESSION['user_id'];
                    // Fetch daily sales
                    $stmt_daily = $DB_con->prepare(
                        'SELECT SUM(order_total) as daily_sales, 
                        DATE(order_pick_up) as date 
                        FROM orderdetails 
                        WHERE DATE(order_date) = CURDATE()
                        AND user_id = ?');
                    $stmt_daily->bindParam(1, $current_user_id, PDO::PARAM_INT);
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
                         WHERE DATE(order_date) BETWEEN 
                               DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) 
                               AND DATE_ADD(DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY), INTERVAL 6 DAY)
                               AND user_id = ?');
                    $stmt_weekly->bindParam(1, $current_user_id, PDO::PARAM_INT);
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
                         WHERE DATE(order_date) BETWEEN 
                               DATE_FORMAT(CURDATE(), "%Y-%m-01") 
                               AND LAST_DAY(CURDATE())
                               AND user_id = ?');
                    $stmt_monthly->bindParam(1, $current_user_id, PDO::PARAM_INT);
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


                <div class="sales-report-transactions">
                    <h2>Transactions</h2>
                    <div class="transactions-table">
                        <table>
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
                                $current_user = $_SESSION['user_id'];
                                $stmt = $DB_con->prepare('SELECT users.user_email, users.user_firstname, users.user_lastname, users.user_address, orderdetails.* 
                                FROM users 
                                INNER JOIN orderdetails 
                                ON users.user_id = orderdetails.user_id 
                                WHERE orderdetails.user_id = :current_user 
                                ORDER BY orderdetails.order_pick_up DESC');
                                $stmt->bindParam(':current_user', $current_user, PDO::PARAM_INT);
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
                                            <td><?php echo htmlspecialchars($totalBill); ?></td>
                                            <td><?php echo htmlspecialchars($orderStatus); ?></td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="3">No transactions found.</td>
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

    <script type="text/javascript" charset="utf-8">
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
    </script>
</body>

</html>
