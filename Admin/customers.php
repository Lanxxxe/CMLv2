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


if (isset($_GET['confirm_return_id'])) {
    $return_id = $_GET['confirm_return_id'];

    $stmt_confirmed = $DB_con->prepare('UPDATE returnitems SET status = "Confirmed" WHERE return_id = :return_id');
    $stmt_confirmed->bindParam(':return_id', $return_id);
    if ($stmt_confirmed->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Confirmed',
                text: 'The return has been successfully confirmed.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error confirming return: " . $stmt_confirmed->errorInfo()[2];
    }
}

if (isset($_GET['reject_return_id'])) {
    $return_id = $_GET['reject_return_id'];

    $stmt_rejected = $DB_con->prepare('UPDATE returnitems SET status = "Rejected" WHERE return_id = :return_id');
    $stmt_rejected->bindParam(':return_id', $return_id);
    if ($stmt_rejected->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Rejected',
                text: 'The return has been rejected.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error rejecting return: " . $stmt_rejected->errorInfo()[2];
    }
}

if (isset($_GET['delete_return_id'])) {
    $return_id = $_GET['delete_return_id'];

    $stmt_delete = $DB_con->prepare('DELETE FROM returnitems WHERE return_id = :return_id');
    $stmt_delete->bindParam(':return_id', $return_id);
    if ($stmt_delete->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Deleted',
                text: 'The return has been successfully deleted.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error deleting return: " . $stmt_delete->errorInfo()[2];
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
    #modal-product-im, #modal-receipt-image{
        max-width: 150px;
        max-height: 150px;
    }
    </style>
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
                <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Add Paint Products</a></li>
                <li><a data-toggle="modal" data-target="#uploadItems"> &nbsp; &nbsp; &nbsp; Add Items</a></li>                
                <li><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                <li class="active"><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
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
            <center><h3><strong>Customer Management</strong></h3></center>
        </div>
        <br />
        <div class="table-responsive" style="margin-bottom: 50px;">
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

    <center><h4><strong>Manage Return Items</strong></h4></center>
    <div class="table-responsive">
        <table class="display table table-bordered" id="returnsTable" cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>Return ID</th>
                <th>Product Name</th>
                <th>User Email</th>
                <th>Quantity</th>
                <th>Reason</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
            </thead>
            <tbody>
            <?php
            $stmt = $DB_con->prepare('SELECT returnitems.*,
                (SELECT users.user_email FROM users WHERE users.user_id = returnitems.user_id) as user_email
            FROM returnitems');
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['return_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($row['reason']); ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                          <td>
                                <?php if ($row['status'] !== 'Confirmed' && $row['status'] !== 'Rejected'): ?>
                                    <button class="btn btn-success btn-sm confirm-return" data-id="<?php echo htmlspecialchars($row['return_id']); ?>">Confirm</button>
                                    <button class="btn btn-warning btn-sm reject-return" data-id="<?php echo htmlspecialchars($row['return_id']); ?>">Reject</button>
                                <?php endif; ?>
                                <button class="btn btn-primary btn-sm preview-return" data-id="<?php echo htmlspecialchars($row['return_id']); ?>" 
                                    data-product="<?php echo htmlspecialchars($row['product_name']); ?>"
                                    data-email="<?php echo htmlspecialchars($row['user_email']); ?>"
                                    data-quantity="<?php echo htmlspecialchars($row['quantity']); ?>"
                                    data-reason="<?php echo htmlspecialchars($row['reason']); ?>"
                                    data-status="<?php echo htmlspecialchars($row['status']); ?>"
                                    data-image="<?php echo  '../Customers/' . ($row['product_image']); ?>"
                                    data-receipt="<?php echo  '../Customers/' . ($row['receipt_image']); ?>">
                                    Preview
                                </button>
                                <button class="btn btn-danger btn-sm delete-return" data-id="<?php echo htmlspecialchars($row['return_id']); ?>">Delete</button>
                            </td>
                    </tr>
                    <?php
                }
            } else {
                ?>
                <tr>
                    <td colspan="7" class="text-center">No return items found</td>
                </tr>
                <?php
            }
            ?>
            </tbody>
        </table>
    </div>
        <div class="alert alert-default" style="background-color:#033c73;">
            <p style="color:white;text-align:center;">&copy 2024 CML Paint Trading Shop | All Rights Reserved</p>
        </div>
    </div>
</div>

    <div class="modal fade" id="previewModal" tabindex="-1" role="dialog" aria-labelledby="previewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="previewModalLabel">Return Item Preview</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Return ID:</strong> <span id="modal-return-id"></span></p>
                            <p><strong>Product Name:</strong> <span id="modal-product-name"></span></p>
                            <p><strong>User Email:</strong> <span id="modal-user-email"></span></p>
                            <p><strong>Quantity:</strong> <span id="modal-quantity"></span></p>
                            <p><strong>Reason:</strong> <span id="modal-reason"></span></p>
                            <p><strong>Status:</strong> <span id="modal-status"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Product Image:</strong></p>
                            <img id="modal-product-image" src="" alt="Product Image">
                            <p><strong>Receipt Image:</strong></p>
                            <img id="modal-receipt-image" src="" alt="Receipt Image">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>	
<script>
    $(document).ready(function() {
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
        $(document).ready(function() {
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

            $('.confirm-return').click(function() {
                var returnId = $(this).data('id');
                confirmReturn(returnId);
            });

            $('.reject-return').click(function() {
                var returnId = $(this).data('id');
                rejectReturn(returnId);
            });

            $('.preview-return').click(function() {
                var returnId = $(this).data('id');
                var productName = $(this).data('product');
                var userEmail = $(this).data('email');
                var quantity = $(this).data('quantity');
                var reason = $(this).data('reason');
                var status = $(this).data('status');
                var productImage = $(this).data('image');
                var receiptImage = $(this).data('receipt');

                $('#modal-return-id').text(returnId);
                $('#modal-product-name').text(productName);
                $('#modal-user-email').text(userEmail);
                $('#modal-quantity').text(quantity);
                $('#modal-reason').text(reason);
                $('#modal-status').text(status);
                $('#modal-product-image').attr('src', productImage);
                $('#modal-receipt-image').attr('src', receiptImage);

                $('#previewModal').modal('show');
            });

            $('.delete-return').click(function() {
                var returnId = $(this).data('id');
                deleteReturn(returnId);
            });

            function confirmReturn(returnId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to confirm this return!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, confirm it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get('confirm_return.php', { return_id: returnId }, function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire('Confirmed!', result.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        });
                    }
                });
            }

            function rejectReturn(returnId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "You are about to reject this return!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, reject it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get('reject_return.php', { return_id: returnId }, function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire('Rejected!', result.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        });
                    }
                });
            }

            function deleteReturn(returnId) {
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action will permanently delete the return. This cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'No, cancel!',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.get('delete_return.php', { return_id: returnId }, function(response) {
                            var result = JSON.parse(response);
                            if (result.success) {
                                Swal.fire('Deleted!', result.message, 'success').then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', result.message, 'error');
                            }
                        });
                    }
                });
            }
        });

    });
</script>

</body>
</html>
