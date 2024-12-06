<?php
session_start();

require "config.php";
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email = :user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}


$stmt_edit = $DB_con->prepare("SELECT SUM(order_total) AS total FROM orderdetails WHERE user_id = :user_id AND order_status = 'Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/datatables.min.js"></script>
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

<body>
    <div id="wrapper">
        <?php require_once "navigation.php" ?>


        <div id="page-wrapper">
            <div class="alert alert-danger">

                <center>
                    <h3>
                        <strong>Inventory Items</strong>
                    </h3>
                </center>

            </div>

            <br />

            <div class="table-responsive">
                <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name of Item</th>
                            <th>Brand Name</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Expiration Date</th>
                            <th>Date Added</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("config.php");
                        $stmt = $DB_con->prepare('SELECT * FROM items where branch = :branch');
                        $stmt->bindParam(":branch", $_SESSION['current_branch']);
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($row);

                                $date = new DateTime($item_date);
                                $date1 = new DateTime($expiration_date);
                                $formattedDate = $date->format('F j, Y');
                                $formattedDate1 = $date1->format('F j, Y');
                        ?>
                                <tr 
                                    style="
                                    
                                    background: <?php 
                                    if ($quantity <= 30 && $quantity > 20 ) {
                                        echo '#e7dd51; color: #000;';
                                    }
                                    elseif ($quantity <= 20 && $quantity > 10) {
                                        echo '#f17d4f; color: #000;';
                                    } elseif ($quantity <= 10 || $quantity == 0 ) {
                                        echo '#d93434; color: #000;';
                                    } else {
                                        echo '#5be65b; color: #000;';
                                    }
                                ?>
                                ">

                                    <td>
                                        <center> <img src="../Admin/item_images/<?php echo $item_image; ?>" class="img img-rounded" width="50" height="50" /></center>
                                    </td>
                                    <td><?php echo $item_name . " (" . $gl . ")" ?></td>
                                    <td><?php echo $brand_name ?></td>
                                    <td>&#8369; <?php echo $item_price; ?></td>
                                    <td><?php echo $quantity . " " . $gl ?></td>
                                    <td><?php echo $formattedDate1 ?></td>
                                    <td><?php echo $formattedDate; ?></td>

                                </tr>

                            <?php
                            }
                            echo "</tbody>";
                            echo "</table>";
                            echo "</div>";
                            echo "<br />";
                            echo '<div class="alert alert-default" style="background-color:#033c73;">
                       <p style="color:white;text-align:center;">
                       &copy 2024 CML Paint Trading Shop | All Rights Reserved 

						</p>
                        
                    </div>
	</div>';

                            echo "</div>";
                        } else {
                            ?>


                            <div class="col-xs-12">
                                <div class="alert alert-warning">
                                    <span class="glyphicon glyphicon-info-sign"></span> &nbsp; No Data Found ...
                                </div>
                            </div>
                        <?php
                        }

                        ?>

            </div>
        </div>

        <br />
        <br />

    </div>
    </div>




    </div>



    </div>
    <!-- /#wrapper -->

    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $('#example').dataTable();
        });
    </script>

    <script>
        $(document).ready(function() {
            $('#priceinput').keypress(function(event) {
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
</body>

</html>