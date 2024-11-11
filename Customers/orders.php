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
              <th>Action</th>
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
                  <td><?php echo $order_status ?></td>
                  <td>
                    <button class="btn btn-primary" onclick="viewReceipt('<?php echo htmlspecialchars($order_id); ?>', '<?php echo htmlspecialchars($payment_id); ?>')">View Receipt</button>
                  </td>
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
              echo "<td colspan='5' align='right'><b>&#8369; " . $totalx;
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


<!-- Receipt Modal -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="receiptModalLabel">Order Receipt</h2>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <h3 class='text-center' id="receipt-error">Receipt not avaible. Please checkout the item first.</h3>
                
                <div id="receipt-body">
                  <p><strong>Order ID:</strong> <span id="receiptOrderId"></span></p>
                  <p><strong>Pick Up Date:</strong> <span id="receiptPickUpDate"></span></p>
                  <p><strong>Pick Up Place:</strong> <span id="receiptPickUpPlace"></span></p>
                  <p><strong>Order Status:</strong> <span id="receiptOrderStatus"></span></p>
  
                  <!-- Payment Proof Image -->
                  <div class="payment-proof mt-3">
                      <h5>Proof of Payment</h5>
                      <img id="paymentProofImage" src="" alt="Payment Proof" style="max-width: 30%; height: auto; border: 1px solid #ddd;">
                  </div>
  
                  <!-- Order Items Table -->
                  <!-- <h5 class="mt-3">Order Items</h5> -->
                  <table class="table table-bordered" style="margin-top: 20px;">
                      <thead>
                          <tr>
                              <th>Item</th>
                              <th>Price</th>
                              <th>Quantity</th>
                              <!-- <th>Total</th> -->
                          </tr>
                      </thead>
                      <tbody id="orderItems">
                          <tr>
                            <td id="item_name"></td>
                            <td id="item_price"></td>
                            <td id="item_quantity"></td>
                          </tr>
                      </tbody>
                  </table>
                  <p><strong>Total Price:</strong> &#8369;<span id="receiptTotal"></span></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
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

      function formatDateTime(dateTimeString) {
        const options = { 
            year: 'numeric', 
            month: 'long', 
            day: '2-digit',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true 
        };
        
        const dateTime = new Date(dateTimeString);
        return dateTime.toLocaleString('en-US', options);
    }
    
    let currentUser = `'<?php echo $_SESSION['user_type'] ?>'`;
    console.log(currentUser);

    function viewReceipt(orderId, paymentId) {
      // console.log('Function called with:', orderId, paymentId); // Debug log
      document.getElementById("receipt-error").style.display = "none";
      document.getElementById("receipt-body").style.display = "block";
      
      fetch(`getOrderDetails.php?order_id=${orderId}&payment_id=${paymentId}&userType=${currentUser}`)
          .then(response => {
              // console.log('Raw response:', response); // Debug log
              return response.json();
          })
          .then(data => {
              // console.log('Received data:', data); // Debug log
              if (data.error) {
                  console.error('Server returned error:', data.error);
                  document.getElementById("receipt-error").style.display = "block";
                  document.getElementById("receipt-body").style.display = "none";

                  // Show the modal
                  $('#receiptModal').modal('show');
                  return;
              }

              const { orderDetails, paymentDetails } = data;

              // Populate modal fields with order details
              document.getElementById("receiptOrderId").textContent = orderDetails.order_id;
              document.getElementById("receiptPickUpDate").textContent =  formatDateTime(orderDetails.order_pick_up);
              document.getElementById("receiptPickUpPlace").textContent = orderDetails.order_pick_place;
              document.getElementById("receiptOrderStatus").textContent = orderDetails.order_status;
              document.getElementById("receiptTotal").textContent = orderDetails.order_total;

              // Set payment proof image
              if (paymentDetails && paymentDetails.payment_image_path) {
                  document.getElementById("paymentProofImage").src = `${paymentDetails.payment_image_path}`;
              }
              const orderItemsBody = document.getElementById("orderItems");
              if (orderItemsBody) {
                  orderItemsBody.innerHTML = `
                      <tr>
                          <td>${orderDetails['order_name']} (${orderDetails['gl']})</td>
                          <td>₱ ${orderDetails['order_price']}</td>
                          <td>${orderDetails['order_quantity']} ${orderDetails['gl']}</td>
                      </tr>
                  `;
              } else {
                  console.error('orderItems tbody not found!');
              }
              // Show the modal
              $('#receiptModal').modal('show');
          })
          .catch(error => {
              console.error("Error:", error);
              alert('Error loading receipt details. Please try again.');
          });
    }
  </script>
</body>

</html>
