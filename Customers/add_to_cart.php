<?php
session_start();

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
                    <li><a href="index.php"> &nbsp; <span class='glyphicon glyphicon-home'></span> Home</a></li>
                    <li class="active"><a href="shop.php?id=1"> &nbsp; <span class='glyphicon glyphicon-shopping-cart'></span> Shop Now</a></li>
                    <li><a href="paint-match.php"> &nbsp; <span class='glyphicon glyphicon-tint'></span> Paint Match</a></li>
                    <li><a href="color-change.php"> &nbsp; <span class='glyphicon glyphicon-glass'></span> Color Change</a></li>
                    <li><a href="cart_items.php"> &nbsp; <span class='fa fa-cart-plus'></span> Shopping Cart Lists</a></li>
                    <li><a href="orders.php"> &nbsp; <span class='glyphicon glyphicon-list-alt'></span> My Ordered Items</a></li>
                    <li><a href="view_purchased.php"> &nbsp; <span class='glyphicon glyphicon-eye-open'></span> Previous Items Ordered</a></li>
                    <li><a data-toggle="modal" data-target="#setAccount"> &nbsp; <span class='fa fa-gear'></span> Account Settings</a></li>
                    <li><a href="logout.php"> &nbsp; <span class='glyphicon glyphicon-off'></span> Logout</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right navbar-user">
                    <li class="dropdown messages-dropdown">
                        <a href="#"><i class="fa fa-calendar"></i>  <?php
                            $Today = date('y:m:d');
                            $new = date('l, F d, Y', strtotime($Today));
                            echo $new; ?></a>
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><span class='glyphicon glyphicon-shopping-cart'></span> Total Price Ordered: &#8369; <?php echo $total; ?> </b></a>
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo $user_email; ?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a data-toggle="modal" data-target="#setAccount"><i class="fa fa-gear"></i> Settings</a></li>
                            <li class="divider"></li>
                            <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

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
                        <td><input class="form-control" type="text" value="<?php echo $gl; ?>" disabled /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Image.</label></td>
                        <td><p><img class="img img-thumbnail" src="../Admin/item_images/<?php echo $item_image; ?>" style="height:250px;width:350px;" /></p></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Quantity.</label></td>
                        <td><input class="form-control" type="number" placeholder="Quantity" max="<?= $quantity ?>" name="order_quantity" value="1" onkeypress="return isNumber(event)" required /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Pick up date.</label></td>
                        <td><input class="form-control" type="datetime-local" name="order_pick_up" required /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Pick up place.</label></td>
                        <td>
                            <select name="order_pick_place" id="order_pick_place" class="form-control" required>
                                <option value="Caloocan">Caloocan</option>
                                <option value="Valenzuela">Valenzuela</option>
                                <option value="Quezon City">Quezon City</option>
                                <option value="San Jose de Monte">San Jose de Monte</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <button type="submit" name="order_save" class="btn btn-primary">
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
    </script>
</body>
</html>
