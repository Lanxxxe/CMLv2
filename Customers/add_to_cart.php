<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 0);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");

if (!$_SESSION['user_email']) {
    header("Location: ../index.php");
    exit();
}

include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email = :user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

$stmt_edit = $DB_con->prepare("SELECT SUM(order_total) AS total FROM orderdetails WHERE user_id = :user_id AND order_status = 'Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

if (isset($_GET['cart']) && !empty($_GET['cart'])) {
    $id = $_GET['cart'];
    $stmt_edit = $DB_con->prepare('SELECT * FROM items WHERE item_id = :item_id');
    $stmt_edit->execute(array(':item_id' => $id));
    $edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
    extract($edit_row);
} else {
    header("Location: shop.php");
    exit();
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div id="wrapper">
        <?php 
            include_once("navigation.php");
        ?>

        <div id="page-wrapper">
            <?php if (isset($_SESSION['order_success'])): ?>
                <script>
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '<?php echo $_SESSION['order_success']; ?>',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.href = 'index.php';
                        }
                    });
                </script>
                <?php unset($_SESSION['order_success']); ?>
            <?php endif; ?>

            <form role="form" method="post" action="save_order.php">
                <?php if (isset($errMSG)): ?>
                    <div class="alert alert-danger">
                        <?php echo $errMSG; ?>
                    </div>
                <?php endif; ?>

                <div class="alert alert-default" style="color:white;background-color:#008CBA">
                    <center><h3><span class="glyphicon glyphicon-info-sign"></span> Order Details</h3></center>
                </div>

                <input class="form-control" type="hidden" name="order_name" value="<?php echo $item_name; ?>" />
                <input class="form-control" type="hidden" name="gl" value="<?php echo $gl; ?>" />
                <input class="form-control" type="hidden" name="order_price" value="<?php echo $item_price; ?>" />
                <input class="form-control" type="hidden" name="user_id" value="<?php echo $user_id; ?>" />
                <input class="form-control" type="hidden" name="cart" value="<?php echo $id; ?>" />
                <input class="form-control" type="hidden" name="product_id" value="<?php echo $item_id; ?>" />


                <table class="table table-bordered table-responsive">
                    <tr>
                        <td><label class="control-label">Name of Item.</label></td>
                        <td><input class="form-control" type="text" value="<?php echo $item_name . " (" . $gl . ")"; ?>" disabled /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Available Stocks.</label></td>
                        <td><input class="form-control" type="number" value="<?php echo $quantity; ?>" disabled /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Price.</label></td>
                        <td><input class="form-control" type="text" value="<?php echo $item_price; ?>" disabled /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Gallon/Liter.</label></td>
                        <td><input class="form-control" type="text" value="<?php echo $gl; ?>" /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Image.</label></td>
                        <td><p><img class="img img-thumbnail" src="../Admin/item_images/<?php echo $item_image; ?>" style="height:250px;width:350px;" /></p></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Quantity.</label></td>
                        <td><input class="form-control" type="number" placeholder="Quantity" min="1" max="<?= $quantity ?>" name="order_quantity" value="1" onkeypress="return isNumber(event)" required /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Pick up date.</label></td>
                        <td><input class="form-control" type="datetime-local" id="order_pick_up" name="order_pick_up" required /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Pick up place.</label></td>
                        <td>
                            <input name="order_pick_place" id="order_pick_place" class="form-control" value="<?php echo $edit_row['branch']; ?>" readonly />
        
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="submit" value="submit" name="order_save" class="btn btn-primary" <?= ($quantity <= 0)? 'disabled': '' ?> >
                                <span class="glyphicon glyphicon-shopping-cart"></span> OK
                            </button>
                            <a class="btn btn-danger" href="shop.php?id=1"><span class="glyphicon glyphicon-backward"></span> Cancel</a>
                        </td>
                    </tr>
                </table>
            </form>
            <br />
            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">© 2024 CML PAINT TRADING Shop | All Rights Reserved</p>
            </div>
        </div>
    </div>

    <div class="modal fade" id="setAccount" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h2 style="color:white" class="modal-title" id="myModalLabel">Account Settings</h2>
                </div>
                <div class="modal-body">
                    <form enctype="multipart/form-data" method="post" action="settings.php">
                        <fieldset>
                            <p>Firstname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text" value="<?php echo $user_firstname; ?>" required>
                            </div>
                            <p>Lastname:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text" value="<?php echo $user_lastname; ?>" required>
                            </div>
                            <p>Address:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Address" name="user_address" type="text" value="<?php echo $user_address; ?>" required>
                            </div>
                            <p>Password:</p>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="user_password" type="password" value="<?php echo $user_password; ?>" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control hide" name="user_id" type="text" value="<?php echo $user_id; ?>" required>
                            </div>
                        </fieldset>
                    </form>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-block btn-success btn-md" name="user_save">Save</button>
                    <button type="button" class="btn btn-block btn-danger btn-md" data-dismiss="modal">Cancel</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[name="order_quantity"]').forEach(input => {
                input.addEventListener('input', event => {
                    const min = +input.getAttribute('min');
                    const max = +input.getAttribute('max');
                    if(+input.value < min) {
                        input.value = min;
                    }
                    if(+input.value > max) {
                        input.value = max;
                    }
                });
            });
        });

        function isNumber(evt) {
            evt = (evt) ? evt : window.event;
            var charCode = (evt.which) ? evt.which : evt.keyCode;
            if (charCode > 31 && (charCode < 48 || charCode > 57)) {
                return false;
            }
            return true;
        }

        function confirmOrder(event) {
            event.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "Do you want to proceed with the order?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, place order!'
            }).then((result) => {
                if (result.isConfirmed) {
                    event.target.submit();
                }
            });
        }

        function calendarRestriction() {
            // Get the current date
            const today = new Date();
    
            // Set the date to tomorrow
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
    
            // Format the date to YYYY-MM-DDTHH:MM
            const formattedTomorrow = tomorrow.toISOString().slice(0, 16);
            
            // Set the min attribute to tomorrow
            const dateInput = document.getElementById('order_pick_up');
            dateInput.setAttribute('min', formattedTomorrow);
            dateInput.value = formattedTomorrow; // Set default to tomorrow
    
            // Optional: Add an event listener to reset the date if clicked
            dateInput.addEventListener('click', function () {
                dateInput.value = formattedTomorrow; // Reset to tomorrow when clicked
            });
        }


        calendarRestriction();
    </script>
</body>
</html>
