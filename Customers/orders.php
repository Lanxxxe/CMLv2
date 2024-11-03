<?php
session_start();

if (!$_SESSION['user_email']) {

  header("Location: ../index.php");
}

?>

<?php
include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>

<?php
include("config.php");
$stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

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

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


</head>

<body>
  <div id="wrapper">
    <?php include_once("navigation.php") ?>

    <div id="page-wrapper">


      <div class="alert alert-default" style="color:white;background-color:#008CBA">
        <center>
          <h3> <span class="glyphicon glyphicon-list-alt"></span> My Ordered Items</h3>
        </center>
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
              <th>Pick Up Date</th>
              <th>Pick Up Place</th>
              <th>Total</th>
              <th>Order Status</th>
            </tr>
          </thead>
          <tbody>
            <?php
            include("config.php");

            $stmt = $DB_con->prepare("SELECT * FROM orderdetails where user_id='$user_id' ORDER BY order_pick_up DESC");
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
              while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                $date = new DateTime($order_pick_up);
                $formattedDate = $date->format('F j, Y');
            ?>
                <tr>

                  <td><?php echo $order_id; ?></td>
                  <td><?php echo $order_name . " (" . $gl . ")"; ?></td>
                  <td>&#8369; <?php echo $order_price; ?> </td>
                  <td><?php echo $order_quantity . " " . $gl; ?></td>
                  <td><?php echo $formattedDate; ?></td>
                  <td><?php echo $order_pick_place; ?></td>
                  <td>&#8369; <?php echo $order_total; ?> </td>
                  <td><?php echo $order_status; ?></td>
                </tr>


              <?php
              }
              include("config.php");
              $stmt_edit = $DB_con->prepare("select sum(order_total) as totalx from orderdetails where user_id=:user_id");
              $stmt_edit->execute(array(':user_id' => $user_id));
              $edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
              extract($edit_row);
              echo "<tr>";
              echo "<td colspan='2' align='right'><b>Total Price Ordered:</b>";
              echo "</td>";
              echo "<td colspan='5' align='right'><b>" . $totalx;
              echo "</b></td>";
              echo "</tr>";
              echo "</tbody>";
              echo "</table>";
              echo "</div>";
              echo "<br />";
              echo '<div class="alert alert-default" style="background-color:#033c73;">
                       <p style="color:white;text-align:center;">
                       © 2024 CML PAINT TRADING Shop | All Rights Reserved

						</p>
                        
                    </div>
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
