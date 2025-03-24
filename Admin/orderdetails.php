<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

// Display alert if exists
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo "<script>
        Swal.fire({
            icon: '" . $alert['type'] . "',
            title: '" . ucfirst($alert['type']) . "!',
            text: '" . htmlspecialchars($alert['message']) . "',
            timer: 2000,
            showConfirmButton: true
        });
    </script>";
    unset($_SESSION['alert']); // Clear the alert after displaying
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

$branch_location = $_SESSION['current_branch'];

// Total orders (excluding Pending) filtered by branch
$stmt_total_orders = $DB_con->prepare(
    'SELECT COUNT(*) AS total 
     FROM orderdetails 
     WHERE order_status <> "Pending" AND order_pick_place = :branch_location'
);
$stmt_total_orders->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
$stmt_total_orders->execute();
$total_orders = $stmt_total_orders->fetch(PDO::FETCH_ASSOC)['total'];

// Confirmed orders filtered by branch
$stmt_confirmed = $DB_con->prepare(
    'SELECT COUNT(*) AS total, SUM(order_total) AS total_sum 
     FROM orderdetails 
     WHERE order_status = "Confirmed" AND order_pick_place = :branch_location'
);
$stmt_confirmed->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
$stmt_confirmed->execute();
$row_confirmed = $stmt_confirmed->fetch(PDO::FETCH_ASSOC);
$total_confirmed = $row_confirmed['total'];
$total_sum_confirmed = $row_confirmed['total_sum'];

// Orders under verification filtered by branch
$stmt_verification = $DB_con->prepare(
    'SELECT COUNT(*) AS total 
     FROM orderdetails 
     WHERE order_status = "Verification" AND order_pick_place = :branch_location'
);
$stmt_verification->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
$stmt_verification->execute();
$total_verification = $stmt_verification->fetch(PDO::FETCH_ASSOC)['total'];

// Returned items filtered by branch
$stmt_return = $DB_con->prepare(
    'SELECT COUNT(*) AS total 
     FROM orderdetails 
     WHERE order_status = "Returned" AND order_pick_place = :branch_location'
);
$stmt_return->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
$stmt_return->execute();
$returnItems = $stmt_return->fetch(PDO::FETCH_ASSOC)['total'];

// Rejected orders filtered by branch
$stmt_rejected = $DB_con->prepare(
    'SELECT COUNT(*) AS total 
     FROM orderdetails 
     WHERE order_status = "Rejected" AND order_pick_place = :branch_location'
);
$stmt_rejected->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
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
    $sql_cmd = "LCASE(orderdetails.order_status) = LCASE(:action)";
} else {
    $sql_cmd = "1=1"; // Fallback to include all records if no action is provided
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
        #loading {
          display: flex;
          position: fixed;
          top: 0;
          left: 0;
          z-index: 1000;
          width: 100vw;
          height: 100vh;
          background-color: rgba(192, 192, 192, 0.5);
          background-image: url("../ForgotPassword/images/loading.gif");
          background-repeat: no-repeat;
          background-position: center;
        }

        .hide {
          display: none !important;
        }

        .dashboard-circle {
            width: 170px;
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
                <div style="text-align:center">Cancelled<br><?php echo $total_rejected; ?></div>
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
                    <th>Actions</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <?php   
                    $stmt = $DB_con->prepare(
                        "SELECT
                            users.user_email,
                            if ((pf.firstname = '' AND pf.lastname = ''), users.user_firstname, pf.firstname) as user_firstname, 
                            if ((pf.firstname = '' AND pf.lastname = ''), users.user_lastname, pf.lastname) as user_lastname, 
                            users.user_address,
                            orderdetails.*,
                            order_pick_place
                        FROM users
                            INNER JOIN orderdetails ON users.user_id = orderdetails.user_id
                            INNER JOIN paymentform pf ON orderdetails.payment_id = pf.id
                        WHERE $sql_cmd AND order_pick_place = :branch_location
                        ORDER BY orderdetails.order_date DESC"
                    );
			   
                // Bind the parameters
                if (in_array($action, $actions)) {
                    $stmt->bindParam(':action', $action, PDO::PARAM_STR);
                }
                $stmt->bindParam(':branch_location', $branch_location, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $orderStatus = ucfirst($row['order_status']); 
                        if ($orderStatus != 'Pending'){
                        $date = new DateTime($row['order_pick_up']);
                        $formattedDate = $date->format('F j, Y');
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['order_date'])?></td>
                            <td><?php echo htmlspecialchars($row['user_firstname']) . ' ' . htmlspecialchars($row['user_lastname']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_name']); ?></td>
                            <td>&#8369; <?php echo number_format($row['order_price'], 2); ?></td>
                            <td><?php echo htmlspecialchars($row['order_quantity']); ?></td>
                            <td><?php echo $row['gl'] ? $row['gl'] : ''; ?></td>
                            <td><?php echo htmlspecialchars($formattedDate); ?></td>
                            <td><?php echo htmlspecialchars($row['order_pick_place']); ?></td>
                            <td>&#8369; <?php echo number_format($row['order_total'], 2); ?></td>
							<td><?php echo htmlspecialchars($row['order_status']) ?></td>
                            <td style="cursor: pointer;" 
                                <?php echo ($row['order_status'] != 'Pending' && $row['order_status'] != 'Verification' && $row['order_status'] != 'Returned') 
                                    ? "onclick=\"changeStatus('" . htmlspecialchars($row['user_id']) . "', '" . htmlspecialchars($row['order_id']) . "')\"" 
                                    : ''; 
                                ?>>                                
                                <?php
                                    $order_status = ucfirst($row['order_status']);
                                    $due = (new DateTime($row['order_date'])) < (new DateTime());
                                    $payment_id = $row['payment_id'];
                                    if ($due && $order_status === 'Confirmed') {
                                        echo 'To be picked up';
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

                                    if ($row['order_status'] != 'Pending' && $row['order_status'] != 'Verification' && $row['order_status'] != 'Returned') {
                                        echo ' <i class="fa fa-caret-square-o-down"></i>';
                                    }
                                ?> 
                            </td>
                        </tr>
                        <?php
                        }
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

<div id="loading" class="hide"></div>
<?php include_once("uploadItems.php") ?>
<?php include_once("insertBrandsModal.php"); ?>

<script>
    const setVisible = (elementOrSelector, visible) => {
      const element = document.querySelector(elementOrSelector);
      if (visible) {
        element.classList.remove("hide");
      } else {
        element.classList.add("hide");
      }
    };

    document.querySelector("#nav_dashboard").className = "active";

    $(document).ready(function () {
        $('#example').DataTable({
            "ordering": true, // Enable global ordering
            "paging": true,
            "info": true,
            "searching": true,
            "columnDefs": [
                { "orderable": true, "targets": [0, 1, 2] }, // Enable ordering for columns 1 to 3
                { "orderable": false, "targets": '_all' } // Disable ordering for all other columns
            ]
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

    function changeStatus(userID, order_id) {
        if (order_id){
            Swal.fire({
                title: 'Edit Product',
                html: `
                    <div> 
                        <select name="order_status" id="order_status" class="form-control" required>
                            <option value="" selected>Choose status</option>
                            <option value="Confirmed">Confirmed</option>
                            <option value="Rejected">Cancelled</option>
                            <option value="Returned">Return</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const statusSelect = document.getElementById('order_status');
                    const selectedStatus = statusSelect.value;

                    if (!selectedStatus) {
                        Swal.showValidationMessage('Please choose a status before saving.');
                        return false;
                    }

                    if (!order_id) {
                        Swal.showValidationMessage('The order is unpaid. Please contact the customer.');
                        return false;
                    }

                    return { userID, order_id, selectedStatus };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    setVisible('#loading', true);
                    const formData = new FormData();
                    formData.append('action', 'update_status');
                    formData.append('user_id', result.value.userID);
                    formData.append('order_id', result.value.order_id);
                    formData.append('status', result.value.selectedStatus);
                    console.log(result.value.userID);
                    console.log(result.value.order_id);
                    console.log(result.value.selectedStatus);
                    // console.log(result);
                    fetch('updateStatus.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        setVisible('#loading', false);
                        // data_obj = JSON.json_encode(data);
                        console.log(typeof(data));
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Order Status Updated',
                                text: `The status has been updated.`
                            }).then(() => {
                                // Optionally, reload the page or update the UI
                                location.reload();
                            });
                        } else if (data?.type === 'info'){
                            Swal.fire({
                                icon: 'info',
                                title: 'No changes were made!',
                                text: data.message || 'A warning occurred while updating the order status.'
                            });

                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Update Failed',
                                text: data.message || 'An error occurred while updating the order status.'
                            });
                        }
                    })
                    .catch(error => {
                        setVisible('#loading', false);
                        console.error('Error updating order status:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Request Failed',
                            text: 'There was an error processing your request. Please try again later.'
                        });
                    });
                }
            });
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Unpaid Order',
                text: 'Order unpaid, please contact the customer',
                confirmButtonText: 'OK'
            });
        }
    }
</script>
</body>
</html>
