<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'config.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id = :order_id');
    $stmt_delete->bindParam(':order_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: orderdetails.php");
    exit;
}

// Fetch data for the dashboard
$stmt_total_orders = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails');
$stmt_total_orders->execute();
$total_orders = $stmt_total_orders->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_confirmed = $DB_con->prepare('SELECT COUNT(*) AS total, SUM(order_total) AS total_sum FROM orderdetails WHERE order_status = "Confirmed"');
$stmt_confirmed->execute();
$row_confirmed = $stmt_confirmed->fetch(PDO::FETCH_ASSOC);
$total_confirmed = $row_confirmed['total'];
$total_sum_confirmed = $row_confirmed['total_sum'];

$stmt_verification = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Verification"');
$stmt_verification->execute();
$total_verification = $stmt_verification->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_return = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Returned"'); 
$stmt_return->execute();
$returnItems = $stmt_return->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_rejected = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Rejected"');
$stmt_rejected->execute();
$total_rejected = $stmt_rejected->fetch(PDO::FETCH_ASSOC)['total'];

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS);
$q = filter_input(INPUT_GET, 'q', FILTER_VALIDATE_INT);
$activatedCircle = '';
if ($q > 0 && $q < 6) {
    $activatedCircle = $q;
}
$sql_cmd = '';
$actions = [
    'confirmed',
    'verification',
    'rejected',
    'returned',
];
if (in_array($action, $actions)) {
    $sql_cmd = "WHERE LCASE(orderdetails.order_status) = LCASE('$action')";
}

function isActivated($s) {
    global $activatedCircle;
    echo ($activatedCircle == $s)? 'activated': '';
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    <style>
        .dashboard-circle {
            width: 180px;
            height: 90px;
            border-radius: 1rem;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-circle:hover {
            transform: scale(1.05);
        }
        .dashboard-circle.success {
            background-color: #28a745;
            color: white;
        }
        .dashboard-circle.warning {
            background-color: #ffc107;
            color: white;
        }
        .dashboard-circle.danger {
            background-color: #dc3545;
            color: white;
        }
        .dashboard-circle.primary {
            background-color: #007bff;
            color: white;
        }
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .dashboard-circle.activated {
            border: 3px solid #000;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

    <script src="bootstrap/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
<div id="wrapper">
    <?php include("navigation.php"); ?>

    <div id="page-wrapper">
        <div class="alert alert-danger">
            <center><h3><strong>Order Details Dashboard</strong></h3></center>
        </div>
        <br />

        <div class="dashboard-container">
            <div class="dashboard-circle primary" onclick="window.location.href='./orderdetails.php'">
                <div style="text-align:center">Total Orders<br><?php echo $total_orders; ?></div>
            </div>
                <div class="dashboard-circle success <?php isActivated('1') ?>" onclick="window.location.href='./orderdetails.php?action=confirmed&q=1'">
                <div style="text-align:center">Confirmed<br><?php echo $total_confirmed; ?></div>
            </div>
            <div class="dashboard-circle warning <?php isActivated('2') ?>" onclick="window.location.href='./orderdetails.php?action=verification&q=2'">
                <div style="text-align:center">Verification<br><?php echo $total_verification; ?></div>
            </div>
            <div class="dashboard-circle danger <?php isActivated('3') ?>" onclick="window.location.href='./orderdetails.php?action=rejected&q=3'">
                <div style="text-align:center">Rejected<br><?php echo $total_rejected; ?></div>
            </div>
            <div class="dashboard-circle primary <?php isActivated('4') ?>" onclick="window.location.href='./orderdetails.php?action=returned&q=4'">
                <div style="text-align:center">Returned Items<br><?php echo $returnItems; ?></div>
            </div>
            <div class="dashboard-circle success <?php isActivated('5') ?>" onclick="window.location.href='./orderdetails.php?action=confirmed&q=5'">
                <div style="text-align:center">Total Confirmed Sum<br>&#8369; <?php echo number_format($total_sum_confirmed ?? 0, 2); ?></div>
            </div>
        </div>

        <br />

        <div class="table-responsive">
            <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Date Ordered</th>
                    <th>Customer Name</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Gallon/Liter</th>
                    <th>Pick Up Date</th>
                    <th>Pick Up Place</th>
                    <th>Total</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                    $stmt = $DB_con->prepare(
                            "SELECT
                                users.user_email,
                                users.user_firstname,
                                users.user_lastname,
                                users.user_address,
                                orderdetails.*
                            FROM users
                                INNER JOIN orderdetails ON users.user_id = orderdetails.user_id $sql_cmd
                            ORDER BY orderdetails.order_date DESC");
			   $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $date = new DateTime($row['order_pick_up']);
                        $formattedDate = $date->format('F j, Y');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_date']); ?></td>
                            <td><?php echo htmlspecialchars($row['user_firstname']) . ' ' . htmlspecialchars($row['user_lastname']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                            <td>&#8369; <?php echo htmlspecialchars($row['order_price']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                            <td><?php echo htmlspecialchars($row['gl']); ?></td>
                            <td><?php echo htmlspecialchars($formattedDate); ?></td>
                            <td><?php echo htmlspecialchars($row['order_pick_place']); ?></td>
                            <td>&#8369; <?php echo htmlspecialchars($row['order_total']); ?></td>
                            <td><?php
                                $order_status = ucfirst($row['order_status']);
                                $due = (new DateTime($row['order_date'])) < (new DateTime());

                                if ($due && $order_status === 'Confirmed') {
                                    echo 'Picked up';
                                } elseif (!$due && $order_status === 'Confirmed') {
                                    echo 'Waiting';
                                } elseif ($order_status === 'Pending' || $order_status === 'Verification') {
                                    echo 'Processing';  // Shows the order is being processed but not yet confirmed
                                } elseif ($order_status === 'Rejected') {
                                    echo 'Cancelled';   // Indicates the order was not accepted/cancelled
                                } elseif ($order_status === 'Returned') {
                                    echo 'Refunded';    // Shows the order was returned and likely refunded
                                } else {
                                    echo 'N/A';
                                }
                            ?></td>
							<td><?php echo htmlspecialchars($row['order_status']); ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="10" class="text-center">No Data Found ...</td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
        <br />
        <div class="alert alert-default" style="background-color:#033c73;">
            <p style="color:white;text-align:center;">&copy 2024 CML Paint Trading Shop | All Rights Reserved</p>
        </div>
    </div>
</div>

<?php include_once("uploadItems.php") ?>
<?php include_once("insertBrandsModal.php"); ?>

<script>
    document.querySelector("#nav_dashboard").className = "active";

    $(document).ready(function () {
        $('#example').DataTable({
            "ordering": false, // Disable all sorting
            "paging": true,
            "info": true,
            "searching": true,
        });
    });

    $(document).ready(function () {
        $('#priceinput').keypress(function (event) {
            return isNumber(event, this);
        });
    });

    function isNumber(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
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
