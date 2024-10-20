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

$stmt_rejected = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Rejected"');
$stmt_rejected->execute();
$total_rejected = $stmt_rejected->fetch(PDO::FETCH_ASSOC)['total'];
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
            width: 150px;
            height: 150px;
            border-radius: 50%;
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
            gap: 20px;
            margin-bottom: 20px;
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
    <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <img class="logo-custom" src="../assets/img/logo.png" alt="" style="height: 40px; margin-left: 15px;" />
        </div>
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav side-nav">
                <li><a href="index.php"> &nbsp; &nbsp; &nbsp; Home</a></li>
                <li class="active"><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Admin Order Dashboard</a></li>
                <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Upload Items</a></li>
                <li><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                <li><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
                <li><a href="salesreport.php"> &nbsp; &nbsp; &nbsp; Sales Report</a></li>
                <li><a href="logout.php"> &nbsp; &nbsp; &nbsp; Logout</a></li>
            </ul>
            <ul class="nav navbar-nav navbar-right navbar-user">
                <li class="dropdown messages-dropdown">
                    <a href="#"><i class="fa fa-calendar"></i> <?php
                        $Today = date('y:m:d');
                        $new = date('l, F d, Y', strtotime($Today));
                        echo $new; ?></a>
                </li>
                <li class="dropdown user-dropdown">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?><b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <div id="page-wrapper">
        <div class="alert alert-danger">
            <center><h3><strong>Order Details Dashboard</strong></h3></center>
        </div>
        <br />

        <div class="dashboard-container">
            <div class="dashboard-circle primary">
                <div style="text-align:center">Total Orders<br><?php echo $total_orders; ?></div>
            </div>
            <div class="dashboard-circle success">
                <div style="text-align:center">Confirmed<br><?php echo $total_confirmed; ?></div>
            </div>
            <div class="dashboard-circle warning">
                <div style="text-align:center">Verification<br><?php echo $total_verification; ?></div>
            </div>
            <div class="dashboard-circle danger">
                <div style="text-align:center">Rejected<br><?php echo $total_rejected; ?></div>
            </div>
            <div class="dashboard-circle success">
                <div style="text-align:center">Total Confirmed Sum<br>&#8369; <?php echo number_format($total_sum_confirmed, 2); ?></div>
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
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
               $stmt = $DB_con->prepare('SELECT users.user_email, users.user_firstname, users.user_lastname, users.user_address, orderdetails.* FROM users INNER JOIN orderdetails ON users.user_id = orderdetails.user_id ORDER BY orderdetails.order_pick_up DESC');
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
    $(document).ready(function () {
        $('#example').DataTable();
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
