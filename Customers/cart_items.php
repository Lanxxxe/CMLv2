<?php
session_start();

if (!$_SESSION['user_email']) {
    header("Location: ../index.php");
    exit();
}
?>

<?php
include ("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email = :user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}
?>

<?php
include ("config.php");
$stmt_edit = $DB_con->prepare("SELECT SUM(order_total) AS total FROM orderdetails WHERE user_id = :user_id AND order_status = 'Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}
?>

<?php
require_once 'config.php';
if (isset($_GET['delete_id'])) {
    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id = :order_id');
    $stmt_delete->bindParam(':order_id', $_GET['delete_id']);
    $stmt_delete->execute();


    $productQuantity = $_GET['quantity'];
    $updateStock = $DB_con->prepare("UPDATE items SET quantity = quantity + '$productQuantity' WHERE item_id = :product_id");

    $updateStock->bindParam(':product_id', $_GET['product_id']);
    $updateStock->execute();

    header("Location: cart_items.php");
    exit();
}
?>

<?php
require_once 'config.php';

if (isset($_GET['update_id'])) {
    $stmt_delete = $DB_con->prepare('UPDATE orderdetails SET order_status = "Ordered" WHERE order_status = "Pending" AND user_id = :user_id');
    $stmt_delete->bindParam(':user_id', $_GET['update_id']);
    $stmt_delete->execute();
    // Display SweetAlert2 instead of alert
    echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Items successfully ordered!',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'orders.php';
                }
            });
          </script>";

    header("Location: orders.php");
    exit();
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
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
</head>

<body>
    <div id="wrapper">
        <?php include_once ("navigation.php") ?>
        <div id="page-wrapper">
            <div class="alert alert-default" style="color:white;background-color:#008CBA">
                <center>
                    <h3> <span class="fa fa-cart-plus"></span> Shopping Cart Lists</h3>
                </center>
            </div><br />
            <div class="table-responsive">
                <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Pick Up Date</th>
                            <th>Pick Up Place</th>
                            <th>Total</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include ("config.php");
                        $stmt = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_status = 'Pending' AND user_id = :user_id");
                        $stmt->execute(array(':user_id' => $user_id));

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($row);
                                $date = new DateTime($order_pick_up);
                                $formattedDate = $date->format('F j, Y');
                                ?>
                                <tr>
                                    <td><?php echo $order_name . " (" . $gl . ")"; ?></td>
                                    <td>&#8369; <?php echo $order_price; ?> </td>
                                    <td><?php echo $order_quantity . " " . $gl; ?></td>
                                    <td><?php echo $formattedDate; ?></td>
                                    <td><?php echo $order_pick_place; ?></td>
                                    <td>&#8369; <?php echo $order_total; ?> </td>
                                    <td>
                                        <a class="btn btn-block btn-danger" href="?delete_id=<?php echo $row['order_id']; ?>"
                                            title="click for delete"
                                            onclick="confirmDelete(event, <?php echo $row['order_id']; ?>, <?php echo $row['product_id'] ?>, <?php echo $row['order_quantity'] ?>)">
                                            <span class='glyphicon glyphicon-trash'></span> Remove Item
                                        </a>
                                        <!-- <a class="btn btn-block btn-danger" href="?delete_id=<?php echo $row['order_id']; ?>" title="click for delete" onclick="return confirm('Are you sure to remove this item?')"><span class='glyphicon glyphicon-trash'></span> Remove Item</a> -->
                                    </td>
                                </tr>
                                <?php
                            }
                            $stmt_edit = $DB_con->prepare("SELECT SUM(order_total) AS totalx FROM orderdetails WHERE user_id = :user_id AND order_status = 'Pending'");
                            $stmt_edit->execute(array(':user_id' => $user_id));
                            $edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
                            if ($edit_row) {
                                extract($edit_row);
                                echo "<tr>";
                                echo "<td colspan='5' align='right'>Total Price:";
                                echo "</td>";
                                echo "<td>&#8369; " . $totalx;
                                echo "</td>";
                                echo "<td>";
                                // Query again to get the first pending order's order_id
                                $stmt_order_id = $DB_con->prepare("SELECT order_id FROM orderdetails WHERE user_id = :user_id AND order_status = 'Pending' LIMIT 1");
                                $stmt_order_id->execute(array(':user_id' => $user_id));
                                $row_order_id = $stmt_order_id->fetch(PDO::FETCH_ASSOC);
                                if ($row_order_id) {
                                    $order_id = $row_order_id['order_id'];
                                    echo "<a class='btn btn-block btn-success' href='checkout.php?order_id=" . $order_id . "'><span class='glyphicon glyphicon-'></span> CheckOut</a>";
                                }
                                echo "</td>";
                                echo "</tr>";
                            }
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                            echo "<br />";
                            echo '<div class="alert alert-default" style="background-color:#033c73;">
                                <p style="color:white;text-align:center;">
                                &copy 2024 CML PAINT TRADING Shop| All Rights Reserved 
                                </p>
                            </div>';
                            echo "</div>";
                        } else {
                            ?>
                            <div class="col-xs-12">
                                <div class="alert alert-warning">
                                    <span class="glyphicon glyphicon-info-sign"></span> &nbsp; No Item Found ...
                                </div>
                            </div>
                            <?php
                        }
                        ?>
            </div>
        </div>
    </div>
    </div>
    <!-- /#wrapper -->
    <!-- Mediul Modal -->
    <div class="modal fade" id="setAccount" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h2 style="color:white" class="modal-title" id="myModalLabel">Account Settings</h2>
                </div>
                <div class="modal-body">
                    <form enctype="multipart/form-data" method="post" action="settings.php">
                        <fieldset>
                            <p>Firstname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text"
                                    value="<?php echo $user_firstname; ?>" required>
                            </div>
                            <p>Lastname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text"
                                    value="<?php echo $user_lastname; ?>" required>
                            </div>
                            <p>Address:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Address" name="user_address" type="text"
                                    value="<?php echo $user_address; ?>" required>
                            </div>
                            <p>Password:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="user_password" type="password"
                                    value="<?php echo $user_password; ?>" required>
                            </div>

                            <div class="form-group">
                                <input class="form-control hide" name="user_id" type="text"
                                    value="<?php echo $user_id; ?>" required>
                            </div>
                        </fieldset>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-block btn-success btn-md" name="user_save">Save</button>
                    <button type="button" class="btn btn-block btn-danger btn-md" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        $(document).ready(function () {
            $('#priceinput').keypress(function (event) {
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
    <script>
    function confirmDelete(event, orderId, product_id, quantity) {
        event.preventDefault();
        
        Swal.fire({
            icon: 'warning',
            title: 'Are you sure?',
            text: 'You are about to remove this item!',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, keep it'
        }).then((result) => {
            if (result.isConfirmed) {
                // Redirect or perform delete action
                window.location.href = '?delete_id=' + orderId + '&product_id=' + product_id + '&quantity=' + quantity;
                console.log(orderId);
            } else {
                // Do nothing or handle cancel
            }
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>