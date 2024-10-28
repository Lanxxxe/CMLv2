<?php
session_start();

if (!$_SESSION['admin_username']) {
    header("Location: ../index.php");
    exit(); // Always exit after redirection
}

require_once 'config.php';

$error = false; // Flag to track errors

if (isset($_GET['edit_id']) && !empty($_GET['edit_id'])) {
    $id = $_GET['edit_id'];
    $stmt_edit = $DB_con->prepare('SELECT * FROM items WHERE item_id = :item_id');
    $stmt_edit->execute(array(':item_id' => $id));
    $edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
    extract($edit_row);
    // Initialize $gl to avoid undefined variable notice
    $gl = isset($type) ? $type : ''; // Assuming $type is where 'Gallon' or 'Liter' is stored
} else {
    header("Location: items.php");
    exit();
}

if (isset($_POST['btn_save_updates'])) {
    $item_name = $_POST['item_name'];
    $brand_name = $_POST['brand_name'];
    $item_price = $_POST['item_price'];
    $expiration_date = $_POST['expiration_date'];
    $type = $_POST['type'];
    $quantity = $_POST['quantity'];
    $gl = $_POST['gl'];

    $imgFile = $_FILES['item_image']['name'];
    $tmp_dir = $_FILES['item_image']['tmp_name'];
    $imgSize = $_FILES['item_image']['size'];

    if ($imgFile) {
        $upload_dir = 'item_images/';
        $imgExt = strtolower(pathinfo($imgFile, PATHINFO_EXTENSION));
        $valid_extensions = array('jpeg', 'jpg', 'png', 'gif');
        $itempic = rand(1000, 1000000) . "." . $imgExt;
        
        if (in_array($imgExt, $valid_extensions)) {
            if ($imgSize < 5000000) {
                unlink($upload_dir . $edit_row['item_image']);
                move_uploaded_file($tmp_dir, $upload_dir . $itempic);
            } else {
                $error = true;
                $errMSG = "Sorry, your file is too large. It should be less than 5MB.";
            }
        } else {
            $error = true;
            $errMSG = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
        }
    } else {
        $itempic = $edit_row['item_image'];
    }

    if (!$error) {
        $stmt = $DB_con->prepare('UPDATE items
                                     SET item_name=:item_name, 
                                            brand_name=:brand_name, 
                                         item_price=:item_price, 
                                         item_image=:item_image,
                                         expiration_date=:expiration_date,
                                         type=:type,
                                         quantity=:quantity,
                                         gl=:gl
                                   WHERE item_id=:item_id');
        $stmt->bindParam(':item_name', $item_name);
        $stmt->bindParam(':brand_name', $brand_name);
        $stmt->bindParam(':item_price', $item_price);
        $stmt->bindParam(':item_image', $itempic);
        $stmt->bindParam(':expiration_date', $expiration_date);
        $stmt->bindParam(':type', $type);
        $stmt->bindParam(':quantity', $quantity);
        $stmt->bindParam(':gl', $gl);
        $stmt->bindParam(':item_id', $id);

        if ($stmt->execute()) {
            // Success message is handled via JavaScript
            $success_message = "Successfully Updated!";
        } else {
            $errMSG = "Sorry, data could not be updated!";
        }
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
                <a class="navbar-brand" href="index.php">CML Paint Trading SHOP - Administrator Panel</a>
            </div>
            <div class="collapse navbar-collapse navbar-ex1-collapse">
                <ul class="nav navbar-nav side-nav">
                    <li><a href="index.php"> &nbsp; &nbsp; &nbsp; Home</a></li>
                    <li><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Admin Order Dashboard</a></li>
                    <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Add Paint Products</a></li>
                    <li><a data-toggle="modal" data-target="#uploadItems"> &nbsp; &nbsp; &nbsp; Add Items</a></li>                    
                    <li class="active"><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                    <li><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
                    <li><a href="salesreport.php"> &nbsp; &nbsp; &nbsp; Sales Report</a></li>
                    <li><a href="maintenance.php"> &nbsp; &nbsp; &nbsp; Maintenance</a></li>
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
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i>
                            <?php extract($_SESSION);
                            echo $admin_username; ?><b class="caret"></b></a>
                        <ul class="dropdown-menu">
                            <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

        <div id="page-wrapper">
            <div class="clearfix"></div>

            <form method="post" enctype="multipart/form-data" class="form-horizontal">

                <div class="alert alert-info">
                    <center>
                        <h3><strong>Update Item</strong> </h3>
                    </center>
                </div>

                <table class="table table-bordered table-responsive">
                    <tr>
                        <td><label class="control-label">Name of Item.</label></td>
                        <td><input class="form-control" type="text" name="item_name" value="<?php echo $item_name; ?>"
                                required /></td>
                    </tr>
                    <tr>
                        <td><label class="control-label">Brand Name.</label></td>
                        <td><input class="form-control" type="text" name="brand_name" value="<?php echo $brand_name; ?>"
                                required /></td>
                    </tr>

                    <tr>
                        <td><label class="control-label">Price.</label></td>
                        <td><input id="inputprice" class="form-control" type="text" name="item_price"
                                value="<?php echo $item_price; ?>" required /></td>
                    </tr>

                    <tr>
                        <td><label for="quantity" class="control-label">Quantity.</label></td>
                        <td><input type="number" class="form-control" name="quantity" min="1" value="<?php echo $quantity; ?>">
                        </td>
                    </tr>

                    <tr>
                        <td><label for="gl" class="control-label">Gallon/Liter.</label></td>
                        <td>
                            <select name="gl" id="gl" required class="form-control">
                                <option value="Gallon" <?php if ($gl == 'Gallon') echo 'selected'; ?>>Gallon</option>
                                <option value="Liter" <?php if ($gl == 'Liter') echo 'selected'; ?>>Liter</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><label for="quantity" class="control-label">Expiration Date</label></td>
                        <td><input type="date" class="form-control" name="expiration_date" value="<?php echo $expiration_date; ?>">
                        </td>
                    </tr>

                    <tr>
                        <td><label for="type" class="control-label">Type.</label></td>
                        <td>
                            <select name="type" id="" class="form-control">
                                <option value="" disabled selected>Select type</option>
                                <option value="Gloss" <?php if ($type == 'Gloss') echo 'selected'; ?>>Gloss</option>
                                <option value="Oil Paint" <?php if ($type == 'Oil Paint') echo 'selected'; ?>>Oil Paint</option>
                                <option value="Aluminum Paint" <?php if ($type == 'Aluminum Paint') echo 'selected'; ?>>Aluminum Paint</option>
                                <option value="Semi Gloss Paint" <?php if ($type == 'Semi Gloss Paint') echo 'selected'; ?>>Semi Gloss Paint</option>
                                <option value="Enamel" <?php if ($type == 'Enamel') echo 'selected'; ?>>Enamel</option>
                                <option value="Exterior Paint" <?php if ($type == 'Exterior Paint') echo 'selected'; ?>>Exterior Paint</option>
                                <option value="Interior Paint" <?php if ($type == 'Interior Paint') echo 'selected'; ?>>Interior Paint</option>
                                <option value="Emulsion" <?php if ($type == 'Emulsion') echo 'selected'; ?>>Emulsion</option>
                                <option value="Primer" <?php if ($type == 'Primer') echo 'selected'; ?>>Primer</option>
                                <option value="Acrylic" <?php if ($type == 'Acrylic') echo 'selected'; ?>>Acrylic</option>
                                <option value="Flat Paint" <?php if ($type == 'Flat Paint') echo 'selected'; ?>>Flat Paint</option>
                                <option value="Matte Finish" <?php if ($type == 'Matte Finish') echo 'selected'; ?>>Matte Finish</option>
                                <option value="Brush" <?php if ($type == 'Brush') echo 'selected'; ?>>Brush</option>
                                <option value="Tools" <?php if ($type == 'Tools') echo 'selected'; ?>>Tools</option>
                                <option value="Tape" <?php if ($type == 'Tape') echo 'selected'; ?>>Tape</option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <td><label class="control-label">Image.</label></td>
                        <td>
                            <p><img class="img img-thumbnail" src="item_images/<?php echo $item_image; ?>" height="150" width="150" /></p>
                            <input class="input-group" type="file" name="item_image" accept="image/*" />
                        </td>
                    </tr>

                    <tr>
                        <td colspan="2">
                            <button type="submit" name="btn_save_updates" class="btn btn-primary">
                                <span class="glyphicon glyphicon-save"></span> Update
                            </button>

                            <a class="btn btn-danger" href="items.php">
                                <span class="glyphicon glyphicon-backward"></span> Cancel
                            </a>
                        </td>
                    </tr>
                </table>
            </form>

            <br />

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy; 2024 CML Paint Trading Shop | All Rights Reserved
                </p>
            </div>
        </div>
    </div>

    <!-- Mediul Modal -->
    <?php include_once("uploadItems.php")?>

    <script>
        $(document).ready(function () {
            $('#priceinput').keypress(function (event) {
                return isNumber(event, this)
            });
        });

        function isNumber(evt, element) {
            var charCode = (evt.which) ? evt.which : event.keyCode;

            if (
                (charCode != 45 || $(element).val().indexOf('-') != -1) &&
                (charCode != 46 || $(element).val().indexOf('.') != -1) &&
                (charCode < 48 || charCode > 57)
            )
                return false;

            return true;
        }
    </script>

<script>
        <?php if (isset($errMSG)): ?>
            showErrorAlert('<?php echo $errMSG; ?>');
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            showSuccessAlert('<?php echo $success_message; ?>');
        <?php endif; ?>

        function showSuccessAlert(message) {
            Swal.fire({
                icon: 'success',
                title: 'Update Successful',
                text: message,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'items.php';
                }
            });
        }

        function showErrorAlert(message) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: message,
                confirmButtonText: 'OK'
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[name="quantity"]').forEach(input => {
                input.addEventListener('input', event => {
                    const min = +input.getAttribute('min');
                    if(+input.value < +min) {
                        input.value = min;
                    }
                });
            });
        })
    </script>
</body>
</html>

