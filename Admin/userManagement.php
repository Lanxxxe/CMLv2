<?php
session_start();

if(!$_SESSION['admin_username'])
{

    header("Location: ../index.php");
}

require_once 'config.php';

// Display alert if exists
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo "<script>
        Swal.fire({
            icon: '" . $alert['type'] . "',
            title: '" . ucfirst($alert['type']) . "!',
            text: '" . htmlspecialchars($alert['message']) . "',
            timer: 2000,
            showConfirmButton: true
        });
    </script>";
    unset($_SESSION['alert']); // Clear the alert after displaying
}

if(isset($_GET['brand_id'])) {
    header('Content-Type: application/json');
    $stmt = $DB_con->prepare("SELECT type_id, type_name FROM product_type WHERE brand_id = ?");
    $stmt->execute([$_GET['brand_id']]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($types);
    exit;
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
    <link rel="stylesheet" type="text/css" href="css/salesreport.css" />
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-success">
                <center><h3><strong>Users Management</strong></h3></center>
            </div>

            <div class="users-container" width="100%">
                <div class="admin-container">
                    <h3>Admin</h3>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Admin ID</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Address</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>
                        </thead>    
                        <tbody>
                            <?php
                            $stmt = $DB_con->prepare('SELECT * FROM users WHERE type="Admin"');
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" style="color: #fff !important;"
                                                onclick="displayEditUser('<?php echo htmlspecialchars($row['user_id']) ?? ''; ?>', 
                                                '<?php echo htmlspecialchars($row['user_email']) ?? ''; ?>', 
                                                '<?php echo htmlspecialchars($row['user_password']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_firstname']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_lastname']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_address']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_mobile']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['type']) ?? ''; ?>');">
                                                Edit
                                            </button>                                    
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3">No transactions found.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="cashier-table">
                    <div style="display: flex; align-items: center; justify-content: start; gap: 7px;">
                        <h3>Cashier</h3>
                        <button type="button" class="btn btn-sm add-account" onclick="displayAddAccount()";>
                            Add Account
                        </button>
                    </div>    

                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>Cashier ID</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Address</th>
                                <th>Mobile</th>
                                <th>Action</th>
                            </tr>
                        </thead>    
                        <tbody>
                            <?php
                            $stmt = $DB_con->prepare('SELECT * FROM users WHERE type="Cashier"');
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>Null</td>
                                        <td>
                                            <button class="btn btn-sm btn-warning" style="color: #fff !important;"
                                                onclick="displayEditUser('<?php echo htmlspecialchars($row['user_id']) ?? ''; ?>', 
                                                '<?php echo htmlspecialchars($row['user_email']) ?? ''; ?>', 
                                                '<?php echo htmlspecialchars($row['user_password']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_firstname']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_lastname']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_address']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['user_mobile']) ?? ''; ?>',
                                                '<?php echo htmlspecialchars($row['type']) ?? ''; ?>');">
                                                Edit
                                            </button>      
                                            <button class="btn btn-danger" onclick="deleteAccount('<?php echo htmlspecialchars($row['user_id']) ?? ''; ?>');">Delete</button>   
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3">No transactions found.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

                <div class="customers-table">
                    <h3>Customers</h3>
                    <table class="users-table">
                        <thead>
                            <tr>
                                <th>User ID</th>
                                <th>Email</th>
                                <th>First Name</th>
                                <th>Last Name</th>
                                <th>Address</th>
                                <th>Mobile</th>
                            </tr>
                        </thead>    
                        <tbody>
                            <?php
                            $stmt = $DB_con->prepare('SELECT * FROM users WHERE type="Customer"');
                            $stmt->execute();

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_firstname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_lastname']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_address']); ?></td>
                                        <td><?php echo htmlspecialchars($row['user_mobile']); ?></td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="3">No transactions found.</td>
                                </tr>
                                <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="alert alert-default" style="background-color:#033c73; margin-top: 5rem;">
                <p style="color:white;text-align:center;">
                    &copy 2024 CML Paint Trading Shop | All Rights Reserved
                </p>
            </div>

        </div>

    </div>
<!-- /#wrapper -->


	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>
		
<script>
    let labelStyle = "sw-custom-label";
    let inputStyle = "sw-custom-input";
    let formControlStyle = "sw-custom-form-control";
    document.querySelector("#nav_user_management").className = "active";

    $(document).ready(function() {
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


    const displayEditUser = (userID, email, password, firstName, lastName, address, mobile, type) => {
        Swal.fire({
            title: `Edit ${type} Information`,
            html: `
                <div class="${formControlStyle}">
                    <label class="form-label ${labelStyle}" for="editBrandName">Email</label>
                    <input type="text" id="editUserEmail" class="swal2-input ${inputStyle}" value="${email}">
                    
                    <label class="form-label ${labelStyle}" for="editBrandName">Password</label>
                    <input type="password" id="editUserPassword" class="swal2-input ${inputStyle}" value="${password}">
                    
                    <label class="form-label ${labelStyle}" for="editBrandName">First Name</label>
                    <input type="text" id="editUserFirstName" class="swal2-input ${inputStyle}" value="${firstName}">
                    
                    <label class="form-label ${labelStyle}" for="editBrandName">Last Name</label>
                    <input type="text" id="editUserLastName" class="swal2-input ${inputStyle}" value="${lastName}">
                    
                    <label class="form-label ${labelStyle}" for="editBrandName">Address</label>
                    <input type="text" id="editUserAddress" class="swal2-input ${inputStyle}" value="${address}">
                    
                    <label class="form-label ${labelStyle}" for="editBrandName">Mobile Number</label>
                    <input type="text" id="editUserMobile" class="swal2-input ${inputStyle}" value="${mobile}">
                </div>
                
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const userEmail = document.querySelector('#editUserEmail').value;
                const userPassword = document.querySelector('#editUserPassword').value;
                const userFirstName = document.querySelector('#editUserFirstName').value;
                const userLastName = document.querySelector('#editUserLastName').value;
                const userAddress = document.querySelector('#editUserAddress').value;
                const userMobile = document.querySelector('#editUserMobile').value;
                
                if (!userEmail || !userPassword || !userFirstName || !userLastName || !userAddress || !userMobile) {
                    Swal.showValidationMessage('Please fill up all the information.');
                    return false;
                }
                
                return { userEmail, userPassword, userFirstName, userLastName, userAddress, userMobile };
            }
        }).then(( result ) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'edit_user');
                formData.append('user_id', userID);
                formData.append('user_email', result.value.userEmail);
                formData.append('user_password', result.value.userPassword);
                formData.append('user_firstName', result.value.userFirstName);
                formData.append('user_lastName', result.value.userLastName);
                formData.append('user_address', result.value.userAddress);
                formData.append('user_mobile', result.value.userMobile);


                fetch('manageUsers.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Admin information updated successfully!',
                        confirmButtonText: "Okay!"
                    }).then((result) => {
                        if (result.isConfirmed){
                            window.location.reload();
                        }
                    });
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to update admin information', 'error');
                });
            }
        });
    }

    const displayAddAccount = () => {
        Swal.fire({
            title: 'Add New Account',
            html: `
                <div class="${formControlStyle}">
                    <label class="form-label ${labelStyle}" for="newUserEmail">Email</label>
                    <input type="text" id="newUserEmail" class="swal2-input ${inputStyle}">

                    <label class="form-label ${labelStyle}" for="newUserPassword">Password</label>
                    <input type="password" id="newUserPassword" class="swal2-input ${inputStyle}">

                    <label class="form-label ${labelStyle}" for="newUserFirstName">First Name</label>
                    <input type="text" id="newUserFirstName" class="swal2-input ${inputStyle}">

                    <label class="form-label ${labelStyle}" for="newUserLastName">Last Name</label>
                    <input type="text" id="newUserLastName" class="swal2-input ${inputStyle}">

                    <label class="form-label ${labelStyle}" for="newUserAddress">Address</label>
                    <input type="text" id="newUserAddress" class="swal2-input ${inputStyle}">

                    <label class="form-label ${labelStyle}" for="newUserMobile">Mobile Number</label>
                    <input type="text" id="newUserMobile" class="swal2-input ${inputStyle}">
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Add Account',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const userEmail = document.querySelector('#newUserEmail').value;
                const userPassword = document.querySelector('#newUserPassword').value;
                const userFirstName = document.querySelector('#newUserFirstName').value;
                const userLastName = document.querySelector('#newUserLastName').value;
                const userAddress = document.querySelector('#newUserAddress').value;
                const userMobile = document.querySelector('#newUserMobile').value;

                if (!userEmail || !userPassword || !userFirstName || !userLastName || !userAddress || !userMobile) {
                    Swal.showValidationMessage('Please fill up all the information.');
                    return false;
                }

                return { userEmail, userPassword, userFirstName, userLastName, userAddress, userMobile };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'add_user');
                formData.append('user_email', result.value.userEmail);
                formData.append('user_password', result.value.userPassword);
                formData.append('user_firstName', result.value.userFirstName);
                formData.append('user_lastName', result.value.userLastName);
                formData.append('user_address', result.value.userAddress);
                formData.append('user_mobile', result.value.userMobile);

                fetch('manageUsers.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account added successfully!',
                        confirmButtonText: "Okay!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to add account', 'error');
                });
            }
        });
    }

    const deleteAccount = (userID) => {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will permanently delete the account.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'delete_user');
                formData.append('user_id', userID);

                fetch('manageUsers.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.text())
                .then(data => {
                    Swal.fire({
                        icon: 'success',
                        title: 'Account deleted successfully!',
                        confirmButtonText: "Okay!"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            window.location.reload();
                        }
                    });
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to delete account', 'error');
                });
            }
        });
    }


</script>
</body>
</html>
