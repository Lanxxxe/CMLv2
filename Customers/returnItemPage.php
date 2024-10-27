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
  <link rel="stylesheet" type="text/css" href="css/returnItem.css" />

  <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
  <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>

</head>

<body>
  <div id="wrapper">
    <?php include_once("navigation.php") ?>

    <div id="page-wrapper">

        <h1 class="return-item-text">Return Item Form</h1>

        <div class="return-item-form">
          <form action="returnItem.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <label for="reason">Reason for Return:</label>
              <select class="form-control" id="reason" name="reason" required>
                <option value="" selected>Select a reason</option>
                <option value="Product damaged">Product damaged</option>
                <option value="Product not as described">Product not as described</option>
                <option value="Incorrect Item">Incorrect Item</option>
                <option value="Expired Product">Expired Product</option>
                <option value="Wrong Paint Color">Wrong Paint Color</option>
                <option value="Change of Mind">Change of Mind</option>
                <option value="Other">Other</option>
              </select>
            </div>
            <div class="form-group">
              <label for="productName">Product Name</label>
              <input type="text" class="form-control" id="productName" name="productName" required>
            </div>
            <div class="form-group">
              <label for="orderNumber">Quantity:</label>
              <input type="number" class="form-control" id="quantity" name="quantity" min="1" required>
            </div>
            <div class="form-group">
              <label for="image1">Product Image:</label>
              <input type="file" class="form-control" id="productImage" name="productImage" required>
            </div>
            <div class="form-group">
              <label for="image2">Receipt:</label>
              <input type="file" class="form-control" id="receipt" name="receipt" required>
            </div>
            <button type="submit" class="btn btn-primary" name="returnItem">Submit Return Request</button>
          </form>
        </div>


        <div class="a">
            <h3>Recent Return Items</h3>

        <div class="table-responsive">
          <table class="display table table-bordered" id="return-items-table" cellspacing="0" width="100%">
            <thead>
              <tr>
                <th>Product Name</th>
                <th>Reason of Return</th>
                <th>Status</th>
              </tr>
            </thead>
              <?php
                  include_once 'config.php'; // Database configuration

                  // Check if the user is logged in
                  if (isset($_SESSION['user_id'])) {
                      $userID = $_SESSION['user_id'];

                      // Prepare the query to retrieve returned items based on the logged-in user
                      $query = "SELECT product_name, reason, status FROM returnitems WHERE user_id = :user_id";

                      // Prepare and execute the query using PDO
                      try {
                          $stmt = $DB_con->prepare($query);
                          $stmt->bindParam(':user_id', $userID, PDO::PARAM_INT);
                          $stmt->execute();

                          // Fetch all the rows as an associative array
                          $returnedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);

                      } catch (PDOException $e) {
                          echo "Error: " . $e->getMessage();
                      }
                  } else {
                      // If the user is not logged in, redirect them to the login page
                      header("Location: login.php");
                      exit();
                  }
              ?>
            <tbody>
                <?php if (!empty($returnedItems)): ?>
                    <?php foreach ($returnedItems as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                            <td><?php echo htmlspecialchars($item['reason']); ?></td>
                            <td><?php echo htmlspecialchars($item['status']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="text-center text-danger">No returned items found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
          </table>
        </div>
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

        document.querySelectorAll('input[name="quantity"]').forEach(input => {
            input.addEventListener('input', event => {
                const min = +input.getAttribute('min');
                if(+input.value < +min) {
                    input.value = min;
                }
            });
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
