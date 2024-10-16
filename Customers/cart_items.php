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
            align-self: end;
            width: 20px;
            height: 20px;
            margin-inline: 10px;
            position: relative;
            top: 4px;
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
                                    <span class="all-checkbtn">
                                        All
                                        <input id="checkAllBtn" type="checkbox" data-order="<?php echo $row['order_id'] . ':' . $row['order_price'] ?>">
                                    </span>
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
                                    <td><?php echo $order_name . " (" . $gl . ")"; ?></td>
                                    <td>&#8369; <?php echo $order_price; ?> </td>
                                    <td><?php echo $order_quantity . " " . $gl; ?></td>
                                    <td><?php echo $formattedDate; ?></td>
                                    <td><?php echo $order_pick_place; ?></td>
                                    <td>&#8369; <?php echo $order_total; ?> </td>
                                    <td>
                                        <div style="display: flex; justify-content: center; align-items: center;">
                                            <a style="flex: 1;" class="btn btn-danger" href="?delete_id=<?php echo $row['order_id']; ?>"
                                                title="click for delete"
                                                onclick="confirmDelete(event, <?php echo $row['order_id']; ?>)">
                                                <span class='glyphicon glyphicon-trash'></span> Remove Item
                                            </a>
                                        <input class="checkOrdered" style="width: 20px; height: 20px; margin-inline: 10px; margin-top: 0;" type="checkbox" data-order="<?php echo $row['order_id'] . ':' . $row['order_price'] ?>">
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
                                echo "<button id='unselectAllBtn' class='btn' style='background-color: transparent; visibility: hidden; color: gray;'><span class='glyphicon glyphicon-remove'></span></button>";
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
            if (order.checked) {
                total += +dataOrder[1];
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
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>

</html>
