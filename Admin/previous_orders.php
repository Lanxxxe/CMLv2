<?php
session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}
?>

<?php
error_reporting(~E_NOTICE);
require_once 'config.php';

if (isset($_GET['previous_id']) && !empty($_GET['previous_id'])) {
    $view_id = $_GET['previous_id'];
    // $payment_type = $_GET['payment_type'];

    $stmt_edit = $DB_con->prepare('SELECT users.*, orderdetails.* FROM users INNER JOIN orderdetails ON users.user_id = orderdetails.user_id WHERE orderdetails.payment_id = :previous_id');
    
    $stmt_edit->execute(array(':previous_id' => $view_id));
    $edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
    extract($edit_row);

    $_user_firstname = $edit_row['user_firstname'];
    $_user_lastname = $edit_row['user_lastname'];
    $_user_address = $edit_row['user_address'];
    $_user_email = $edit_row['user_email'];

} else {
    header("Location: customers.php");
    exit;
}

// Query to get the image from the paymentform table
$stmt_payment = $DB_con->prepare('SELECT * FROM paymentform WHERE order_id = :order_id AND payment_status = "verification"');
$stmt_payment->bindParam(':order_id', $view_id);
$stmt_payment->execute();
$payment_data = $stmt_payment->fetch(PDO::FETCH_ASSOC);

if ($payment_data) {
    $payment_image = $payment_data['payment_image_path'];
    $gcashname = $payment_data['gcash_name'];
    $gcashnumber = $payment_data['gcash_number'];
    $gamount = $payment_data['amount'];
} else {
    $payment_image = null;
    $gcashname = null;
    $gcashnumber = null;
    $gamount = null;
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MARCHPAINT SHOP</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
</head>
<body>
<div id="wrapper">
    <?php include('navigation.php'); ?>

    <div id="page-wrapper">
        <div class="alert alert-danger">
            <center><h3><strong>Customer Previous Item Ordered</strong></h3></center>
        </div>
        <br />
        <div class="table-responsive">
            <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Item</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Total</th>
                    <th>Date Ordered</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $stmt = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_id = :order_id AND (order_status = 'Verification' OR order_status = 'Pending')");
                $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
                $stmt->execute();

                if($stmt->rowCount() > 0) {
                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        ?>
                        <tr>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['order_name']; ?></td>
                            <td>&#8369; <?php echo $row['order_price']; ?> </td>
                            <td><?php echo $row['order_quantity']; ?></td>
                            <td>&#8369; <?php echo $row['order_total']; ?></td>
                            <td><?php echo $row['order_date']; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <div class="col-xs-12">
                        <div class="alert alert-warning">
                            <span class="glyphicon glyphicon-info-sign"></span> &nbsp; No ordered items yet...
                        </div>
                    </div>
                    <?php
                }
                echo "<tr>";
                echo "<td colspan='4' align='center' style='font-size:18px;'>"."Customer Name: <span style='color:red;'>{$_user_firstname} {$_user_lastname}</span> | Email: <span style='color:red;'>{$_user_email}</span> | Address: <span style='color:red;'>{$_user_address}</span>";
                echo "</td>";
                echo "<td><a class='btn btn-danger' href='customers.php'><span class='glyphicon glyphicon-backward'></span> Back</a></td>";
                echo "</tr>";
                echo "</tbody>";
                echo "</table>";
                echo "</div>";
                echo "<br />";
                echo '<div class="alert alert-default" style="background-color:#033c73;">
                        <p style="color:white;text-align:center;">
                        &copy;2024 CML Paint Trading Shop | All Rights Reserved
                        </p>
                      </div>';
                ?>
                </tbody>
            </table>
        </div>
        <?php
        if (!empty($payment_image)) {
            echo "<div class='alert alert-info'>";
            echo "<center><strong>Payment Verification Details</strong></center>";
            echo "<ul>";
            echo "<li><strong>Gcash Name:</strong> {$gcashname}</li>";
            echo "<li><strong>Gcash Number:</strong> {$gcashnumber}</li>";
            echo "<li><strong>Amount:</strong> {$gamount}</li>";
            echo "</ul>";
            echo "<center><img src='../Customers/{$payment_image}' alt='Payment Image' style='max-width:50%; height:auto;'></center>";
            echo "</div>";
        }
        ?>
    </div>
</div>

<!-- Mediul Modal -->
<div class="modal fade" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
    <div class="modal-dialog modal-md">
        <div style="color:white;background-color:#008CBA" class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h2 style="color:white" class="modal-title" id="myModalLabel">Upload Items</h2>
            </div>
            <div class="modal-body">
                <form enctype="multipart/form-data" method="post" action="additems.php">
                    <fieldset>
                        <p>Name of Item:</p>
                        <div class="form-group">
                            <input class="form-control" placeholder="Name of Item" name="item_name" type="text" required>
                        </div>
                        <p>Price:</p>
                        <div class="form-group">
                            <input id="priceinput" class="form-control" placeholder="Price" name="item_price" type="text" required>
                        </div>
                        <p>Choose Image:</p>
                        <div class="form-group">
                            <input class="form-control" type="file" name="item_image" accept="image/*" required/>
                        </div>
                    </fieldset>
                    <div class="modal-footer">
                        <button class="btn btn-success btn-md" name="item_save">Save</button>
                        <button type="button" class="btn btn-danger btn-md" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $('#example').dataTable();
    });

    $('#priceinput').keypress(function(event) {
        return isNumber(event, this);
    });

    function isNumber(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if ((charCode != 45 || $(element).val().indexOf('-') != -1) &&
            (charCode != 46 || $(element).val().indexOf('.') != -1) &&
            (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }
</script>
</body>
</html>
