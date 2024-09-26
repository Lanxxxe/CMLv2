<?php
session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'config.php';

if (isset($_GET['order_id'])) {
    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id = :order_id');
    $stmt_delete->bindParam(':order_id', $_GET['order_id']);
    if ($stmt_delete->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Deleted',
                text: 'The order has been successfully deleted.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error deleting order: " . $stmt_delete->errorInfo()[2];
    }
}

if (isset($_GET['reset_order_id'])) {
    $DB_con->beginTransaction();
    try {
        $stmt_reset = $DB_con->prepare('UPDATE orderdetails SET order_status = "rejected" WHERE order_id = :order_id');
        $stmt_reset->bindParam(':order_id', $_GET['reset_order_id']);
        $stmt_reset->execute();

        $stmt_update_payment = $DB_con->prepare('UPDATE paymentform SET payment_status = "failed" WHERE order_id = :order_id');
        $stmt_update_payment->bindParam(':order_id', $_GET['reset_order_id']);
        $stmt_update_payment->execute();

        $DB_con->commit();

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Reset',
                text: 'The order has been rejected due to wrong payment.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } catch (Exception $e) {
        $DB_con->rollBack();
        echo "Error resetting order: " . $e->getMessage();
    }
}

if (isset($_GET['confirm_order_id'])) {
    $order_id = $_GET['confirm_order_id'];

    $stmt_confirmed = $DB_con->prepare('UPDATE orderdetails SET order_status = "Confirmed" WHERE order_id = :order_id');
    $stmt_confirmed->bindParam(':order_id', $order_id);
    if ($stmt_confirmed->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Confirmed',
                text: 'The order has been successfully confirmed.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error confirming order: " . $stmt_confirmed->errorInfo()[2];
    }
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
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
            <a class="navbar-brand" href="index.php">CML Paint Trading SHOP - Administrator Panel</a>
        </div>
        <div class="collapse navbar-collapse navbar-ex1-collapse">
            <ul class="nav navbar-nav side-nav">
                <li><a href="index.php"> &nbsp; &nbsp; &nbsp; Home</a></li>
                <li><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Admin Order Dashboard</a></li>
                <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Upload Items</a></li>
                <li><a data-toggle="modal" data-target="#addBrandsModal"> &nbsp; &nbsp; &nbsp; Add Brands</a></li>
                <li><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                <li class="active"><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
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
            <center><h3><strong>Customer Management</strong></h3></center>
        </div>
        <br />
        <div class="table-responsive">
            <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Customer Email</th>
                    <th>Order ID</th>
                    <th>Order Status</th>
                    <th>Order Total</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $DB_con->prepare('SELECT users.user_email, users.user_firstname, users.user_lastname, users.user_address, orderdetails.* FROM users
                INNER JOIN orderdetails ON users.user_id = orderdetails.user_id WHERE orderdetails.order_status != "Confirmed"');
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_status']); ?></td>
                            <td><?php echo htmlspecialchars($row['order_total']); ?></td>
                            <td>
                                <a class="btn btn-success" href="javascript:confirmOrder('<?php echo htmlspecialchars($row['order_id']); ?>');"><span class='glyphicon glyphicon-shopping-cart'></span> Confirm Order</a>
                                <a class="btn btn-warning" href="javascript:resetOrder('<?php echo htmlspecialchars($row['order_id']); ?>');" title="click for reset"><span class='glyphicon glyphicon-ban-circle'></span> Reject Order</a>
                                <a class="btn btn-primary" href="previous_orders.php?previous_id=<?php echo htmlspecialchars($row['order_id']); ?>"><span class='glyphicon glyphicon-eye-open'></span> Previous Items Ordered</a>
                                <a class="btn btn-danger" href="javascript:deleteUser('<?php echo htmlspecialchars($row['order_id']); ?>');" title="click for delete"><span class='glyphicon glyphicon-trash'></span> Remove Order</a>
                            </td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="5" class="text-center">No orders found</td>
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

	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>
		

<script>
    $(document).ready(function () {
        $('#example').DataTable();
    });

    function confirmOrder(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, confirm it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'customers.php?confirm_order_id=' + orderId;
            }
        });
    }

    function resetOrder(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to reject this order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, reset it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'customers.php?reset_order_id=' + orderId;
            }
        });
    }

    function deleteUser(orderId) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will permanently delete the order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'customers.php?order_id=' + orderId;
            }
        });
    }
</script>
</body>
</html>
