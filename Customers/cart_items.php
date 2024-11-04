<?php
session_start();

if (!$_SESSION['user_email']) {
    header("Location: ../index.php");
    exit();
}
?>

<?php
require "config.php";
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email = :user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
if ($edit_row) {
    extract($edit_row);
}
?>

<?php
require "config.php";
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
    <style>
        .checkOrdered {
            width: 1.4rem;
            height: 1.4rem; 
            position: relative;
            top: 1px;
            margin-right: 4px !important;
        }
        .last-column {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .all-checkbtn {
            justify-content: end;
            align-items: center;
        }
        .all-checkbtn #checkAllBtn {
            width: 1.4rem;
            height: 1.4rem;
            margin-right: 4px;
            position: relative;
            top: 1px;
        }
        #unselectAllBtn {
            background: none;
            border: none;
        }
        #unselectAllBtn:hover {
            opacity: 0.8;
        }

    </style>
</head>

<body>
    <div id="wrapper">
        <?php require_once "navigation.php" ?>
        <div id="page-wrapper">
            <div class="alert alert-default" style="color:white;background-color:#008CBA">
                <center>
                    <h3> <span class="fa fa-cart-plus"></span> Shopping Cart Lists</h3>
                </center>
            </div><br />
            <div class="table-responsive">
                <span class="all-checkbtn">
                    <input id="checkAllBtn" type="checkbox" data-order="<?php echo $row['order_id'] . ':' . $row['order_price'] . ':' . $row['order_quantity'] ?>">
                    All
                </span>
                <button id='unselectAllBtn' ><span class='glyphicon glyphicon-remove'></span> Unselect</button>
                <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Pick Up Date</th>
                            <th>Pick Up Place</th>
                            <th>Total</th>
                            <th>
                                <div class="last-column">
                                    <span>Actions</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        require "config.php";
                        $stmt = $DB_con->prepare("SELECT * FROM orderdetails WHERE order_status = 'Pending' AND user_id = :user_id");
                        $stmt->execute(array(':user_id' => $user_id));

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                extract($row);
                                $date = new DateTime($order_pick_up);
                                $formattedDate = $date->format('F j, Y');
                                ?>
                                <tr>
                                    <td>
                                        <input class="checkOrdered" type="checkbox" data-order="<?php echo $row['order_id'] . ':' . $row['order_price'] . ':' . $row['order_quantity'] ?>">
                                        <?php echo $order_name ?>
                                    </td>
                                    <td>&#8369; <?php echo $order_price; ?> </td>
                                    <td onclick="updateQuantity('<?php echo $order_quantity ?>', '<?php echo $order_id ?>', '<?php echo $order_price ?>');" style="cursor: pointer;"><span class='glyphicon glyphicon-pencil' style="margin-right: 7px;"></span> <?php echo $order_quantity . " " . $gl; ?></td>
                                    <td onclick="updatePickUpDate('<?php echo $formattedDate ?>', '<?php echo $order_id ?>');" style="cursor: pointer;"><span class='glyphicon glyphicon-pencil' style="margin-right: 7px;"></span> <?php echo $formattedDate; ?></td>
                                    <td onclick="updatePickUpPlace('<?php echo $order_pick_place ?>', '<?php echo $order_id ?>');" style="cursor: pointer;"><span class='glyphicon glyphicon-pencil' style="margin-right: 7px;"></span> <?php echo $order_pick_place; ?></td>
                                    <td>&#8369; <?php echo $order_total; ?> </td>
                                    <td>
                                        <div style="display: flex; justify-content: center; align-items: center;">
                                            <a style="flex: 1;" class="btn btn-danger" href="?delete_id=<?php echo $row['order_id']; ?>"
                                                title="click for delete"
                                                onclick="confirmDelete(event, <?php echo $row['order_id']; ?>)">
                                                <span class='glyphicon glyphicon-trash'></span> Remove Item
                                            </a>
                                        </div>
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
                                echo "<td>&#8369; " . "<span id=\"totalPrice\">$totalx</span>";
                                echo "</td>";
                                echo "<td style='display: flex; justify-content: center; align-items: center;'>";
                                // Query again to get the first pending order's order_id
                                $stmt_order_id = $DB_con->prepare("SELECT order_id FROM orderdetails WHERE user_id = :user_id AND order_status = 'Pending' LIMIT 1");
                                $stmt_order_id->execute(array(':user_id' => $user_id));
                                $row_order_id = $stmt_order_id->fetch(PDO::FETCH_ASSOC);
                                if ($row_order_id) {
                                    $order_id = $row_order_id['order_id'];
                                    echo "<button style='flex: 1;' class='btn btn-success flex' id='checkOutBtn'><span class='glyphicon glyphicon-'></span> CheckOut</button>";
                                }
                                echo "<button id='removeSelectedBtn' style='margin-inline: 7px;' class='btn btn-danger'><span class='glyphicon glyphicon-trash'></span></button>";
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

        function confirmDelete(event, orderId) {
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
                    window.location.href = '?delete_id=' + orderId;
                } else {
                    // Do nothing or handle cancel
                }
            });
        }   

        const totalPrice = document.getElementById('totalPrice');
        const checkAllBtn = document.getElementById('checkAllBtn');
        const unselectAllBtn = document.getElementById('unselectAllBtn');
        const removeSelectedBtn = document.getElementById('removeSelectedBtn');

        let formData = new FormData();

        function getTotalPriceByCheck() {
            const orders = document.querySelectorAll('.checkOrdered');
            let total = 0;
            let allBtnChecked = true;
            for (let key of formData.keys()) {
                formData.delete(key);
            }
            orders.forEach(order => {
                const dataOrder = order.getAttribute('data-order').split(':');
                console.log(dataOrder); 
                if (order.checked) {
                    sub = dataOrder[1] * dataOrder[2];
                    total += sub;
                    
                    formData.append('order_ids[]', dataOrder[0]);
                } else {
                    allBtnChecked = false;
                }
            });
            totalPrice.textContent = total;
            return allBtnChecked;
        }

        function unselectAllBtnF() {
            const orders = document.querySelectorAll('.checkOrdered');
            orders.forEach(order => {
                order.checked = false;
            });
            checkAllBtn.checked = false;
        }

        function checkAllCheckBtn() {
            if (checkAllBtn.checked) {
                const orders = document.querySelectorAll('.checkOrdered');
                orders.forEach(order => {
                    order.checked = true;
                });
            } else {
                unselectAllBtnF();
            }
        }

        function toggleBtns() {
            const orders = document.querySelectorAll('.checkOrdered');
            const anyCheked = Array.from(orders).some(order => order.checked);
            if (anyCheked) {
                unselectAllBtn.style.visibility = 'visible';
                removeSelectedBtn.disabled = false;
            } else {
                unselectAllBtn.style.visibility = 'hidden';
                removeSelectedBtn.disabled = true;
            }
        };
        toggleBtns();
        document.addEventListener('click', (event) => {
            const _unselectAllBtn = event.target.closest('#unselectAllBtn');
            if (_unselectAllBtn) {
                unselectAllBtnF();
            }

            const _checkAllBtn = event.target.closest('#checkAllBtn');
            if (_checkAllBtn) {
                checkAllCheckBtn();
                checkAllBtn.checked = getTotalPriceByCheck();
            }

            const checkOrdered = event.target.closest('.checkOrdered');
            if (checkOrdered) {
                checkAllBtn.checked = getTotalPriceByCheck();
            }

            toggleBtns();
        });


        document.getElementById('checkOutBtn').addEventListener('click', () => {
            fetch('./checkout.php', {
                method: 'post',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                        if (data.status === 'success') {
                            window.location.href = './payment_form.php';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Checkout Error!',
                                text: data.message,
                                confirmButtonText: 'OK'
                            });
                        }
                })
                .catch(console.error);
        });

        removeSelectedBtn.addEventListener('click', () => {
            for (const n of formData.values()) {
                console.log(n);
            }
            Swal.fire({
                icon: 'warning',
                title: 'Are you sure?',
                text: 'You are about to remove the selected items!',
                showCancelButton: true,
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, keep it'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('./delete_cart_item.php', {
                        method: 'post',
                        body: formData,
                    })
                    .then(response => response.json())
                    .then(data => {
                            if(data.status === 'success'){
                                window.location.reload();
                            }else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Unable to remove items!',
                                    text: data.message,
                                    confirmButtonText: 'OK'
                                });
                        }
                    })
                    .catch(console.error);
                }
            })
        });

        getTotalPriceByCheck();

        // Update Shopping Cart
        
        function updateQuantity(quantity, orderID, itemPrice){
            Swal.fire({
                title: 'Edit Quantity',
                html: `
                    <div class="form-group">
                        <label class="form-label" for="editQuantity">Quantity</label>
                        <input type="number" id="editQuantity" class="swal2-input" min='1' value="${quantity}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const newQuantity = document.getElementById('editQuantity').value;
                    if (!newQuantity || newQuantity <= 0) {
                        Swal.showValidationMessage('Please enter a valid quantity');
                        return false;
                    }
                    let newTotal = newQuantity * itemPrice;
                    return { quantity: newQuantity, total: newTotal};
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'update_quantity');
                    formData.append('order_id', orderID);
                    formData.append('quantity', result.value.quantity);
                    formData.append('total', result.value.total);

                    fetch('updateShoppingCart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                        icon: 'success',
                        title: 'Update!',
                        text: 'Quantity updated successfully',
                    }).then(() => {
                        window.location.reload();
                    });
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to update quantity', 'error');
                    });
                }
            });
        }

        function updatePickUpDate(pickUpDate, orderID){
            Swal.fire({
                title: 'Edit Pick Up Date',
                html: `
                    <div class="form-group">
                        <label class="form-label" for="editPickUpDate">Pick Up Date</label>
                        <input type="datetime-local" id="editPickUpDate" class="swal2-input" value="${pickUpDate}">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const newPickUpDate = document.getElementById('editPickUpDate').value;
                    if (!newPickUpDate) {
                        Swal.showValidationMessage('Please select a pick up date');
                        return false;
                    }
                    return { pickUpDate: newPickUpDate };
                },
                didOpen: () => {
                    calendarRestriction('editPickUpDate');
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'update_pickup_date');
                    formData.append('order_id', orderID);
                    formData.append('pickup_date', result.value.pickUpDate);

                    fetch('updateShoppingCart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                        icon: 'success',
                        title: 'Update!',
                        text: 'Pickup date updated successfully',
                    }).then(() => {
                        window.location.reload();
                    });
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to update pick up date', 'error');
                    });
                }
            });
        }

        function updatePickUpPlace(pickUpPlace, orderID){
            Swal.fire({
                title: 'Edit Pick Up Place',
                html: `
                    <div class="form-group">
                        <label class="form-label" for="editPickUpPlace">Pick Up Place</label>
                        <select name="editPickUpPlace" id="editPickUpPlace" class="form-control" required>
                            <option value="" selected>Select Location</option>
                            <option value="Caloocan">Caloocan</option>
                            <option value="Valenzuela">Valenzuela</option>
                            <option value="Quezon City">Quezon City</option>
                            <option value="San Jose de Monte">San Jose de Monte</option>
                        </select>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Save Changes',
                cancelButtonText: 'Cancel',
                preConfirm: () => {
                    const newPickUpPlace = document.getElementById('editPickUpPlace').value;
                    if (!newPickUpPlace) {
                        Swal.showValidationMessage('Please select a pick up place');
                        return false;
                    }
                    return { pickUpPlace: newPickUpPlace };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'update_pickup_place');
                    formData.append('order_id', orderID);
                    formData.append('pickup_place', result.value.pickUpPlace);

                    fetch('updateShoppingCart.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                        icon: 'success',
                        title: 'Update!',
                        text: 'Pickup place updated successfully',
                    }).then(() => {
                        window.location.reload();
                    });
                    })
                    .catch(error => {
                        Swal.fire('Error', 'Failed to update pick up place', 'error');
                    });
                }
            });
        }

        function calendarRestriction(editPickUpDate) {
            // Get the current date
            const today = new Date();
    
            // Set the date to tomorrow
            const tomorrow = new Date(today);
            tomorrow.setDate(today.getDate() + 1);
    
            // Format the date to YYYY-MM-DDTHH:MM
            const formattedTomorrow = tomorrow.toISOString().slice(0, 16);
            
            // Set the min attribute to tomorrow
            const dateInput = document.getElementById(editPickUpDate);
            dateInput.setAttribute('min', formattedTomorrow);
            dateInput.value = formattedTomorrow; // Set default to tomorrow
    
            // Optional: Add an event listener to reset the date if clicked
            dateInput.addEventListener('click', function () {
                dateInput.value = formattedTomorrow; // Reset to tomorrow when clicked
            });
        }

        
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
