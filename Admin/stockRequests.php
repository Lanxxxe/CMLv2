<?php
session_start();

if(!$_SESSION['admin_username'])
{

    header("Location: ../index.php");
}

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


include_once 'config.php';

// Handle Confirm or Decline actions
if (isset($_POST['action']) && in_array($_POST['action'], ['confirm', 'decline'])) {
    try {
        $requestId = intval($_POST['request_id']);
        $status = ($_POST['action'] == 'confirm') ? 'Confirmed' : 'Declined';

        // Update the status of the request
        $stmt = $DB_con->prepare('UPDATE product_requests SET status = :status WHERE request_id = :request_id');
        $stmt->bindParam(':status', $status);
        $stmt->bindParam(':request_id', $requestId);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Request status updated successfully!']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to update request status.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
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

        <div id="page-wrapper" style="height: 100dvh;">
            <div class="request-stock-container" width="100%">
                <div style="display: flex; align-items: center; justify-content: start; gap: 7px;">
                    <h3>Stock Requests</h3>
                    <?php 
                        if ($_SESSION['current_branch'] != 'Caloocan') {
                    ?>
                        <button type="button" class="btn btn-sm add-account" onclick="requestProduct('<?php echo $_SESSION['current_branch'] ?>')";>
                            Request
                        </button>
                    <?php 
                        } else {
                            ?>
                                <button type="button" class="btn btn-sm btn-primary" onclick="generate_report();">
                                    Generate Report
                                </button>
                            <?php
                        }
                    ?>
                </div>  
                
                <table class="request-stock-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Requested Product</th>
                            <th>Product Type</th>
                            <th>Quantity</th>
                            <th>Requested by</th>
                            <th>Status</th>
                            <?php 
                            if ($_SESSION['current_branch'] == 'Caloocan') {
                            ?>
                                <th>Action</th>
                            <?php 
                                }
                            ?>
                        </tr>
                    </thead>    
                    <tbody>
                        <?php
                        if ($_SESSION['current_branch'] == 'Caloocan') {
                            $stmt = $DB_con->prepare('SELECT * FROM product_requests ORDER BY requested_at DESC');
                        } else {
                            $stmt = $DB_con->prepare('SELECT * FROM product_requests where requesting_branch = :branch ORDER BY requested_at DESC');
                            $stmt->bindParam(':branch', $_SESSION['current_branch']);
                        }
                        $stmt->execute();

                        if ($stmt->rowCount() > 0) {
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['request_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['product_brand']); ?></td>
                                    <td><?php echo htmlspecialchars($row['quantity']); ?></td>
                                    <td><?php echo htmlspecialchars($row['requesting_branch']); ?></td>
                                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                                    <?php 
                                        if ($_SESSION['current_branch'] == 'Caloocan') {
                                            if ($row['status'] == 'Pending') {
                                        ?>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-success" 
                                                    onclick="updateRequestStatus(<?php echo $row['request_id']; ?>, 'confirm')">Confirm</button>
                                                <button type="button" class="btn btn-sm btn-danger" 
                                                    onclick="updateRequestStatus(<?php echo $row['request_id']; ?>, 'decline')">Decline</button>
                                            </td>
                                        <?php 
                                            } else {
                                                ?>
                                                <td>
                                                    <?php 
                                                        if (!empty($row['approved_date'])) {
                                                            $approvedDate = new DateTime($row['approved_date']);
                                                            echo $approvedDate->format('F j, Y'); // Example: December 21, 2024
                                                        } else {
                                                            echo 'N/A'; // Display N/A if approved_date is null or empty
                                                        }
                                                    ?>
                                                </td>
                                                <?php
                                            }
                                        }
                                    ?>
                                    
                                </tr>
                                <?php
                            }
                        } else {
                            ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No requested products.</td>
                                </tr>
                            <?php
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="alert alert-default" style="background-color:#033c73;">
        <p style="color:white;text-align:center;">
            &copy 2024 CML Paint Trading Shop | All Rights Reserved
        </p>

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
    document.querySelector("#nav_request_stock").className = "active";
    
    const generate_report = () => {
        window.location.href = "generate_stockrq_pdf.php";
    }

    function capitalizeWords(phrase) {
        return phrase
            .split(' ') // Split the phrase into an array of words
            .map(word => word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()) // Capitalize the first letter
            .join(' '); // Join the words back into a single string
    }

    // Handle "Request Product" button click
    const requestProduct = (requested_by) => {
        Swal.fire({
                title: `Request Product`,
                html: `
                    <div class="${formControlStyle}">
                        <label class="${labelStyle}" for="product">Product Name:</label>
                        <input type="text" id="product" class="swal2-input ${inputStyle}" placeholder="Specific product name">
                        
                        <label class="${labelStyle}" for="product">Product Brand:</label>
                        <input type="text" id="brand" class="swal2-input ${inputStyle}" placeholder="Product Brand">

                        <label class="${labelStyle}" for="quantity">Quantity:</label>
                        <input type="number" id="quantity" min="1" class="swal2-input ${inputStyle}" placeholder="Enter quantity">

                        <label class="${labelStyle}" for="branch">Requesting Branch:</label>
                        <input type="text" id="branch" class="swal2-input ${inputStyle}" value="${requested_by}" readonly>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Request',
                preConfirm: () => {
                    const product = document.getElementById('product').value.trim();
                    const brand = document.getElementById('brand').value.trim();
                    const quantity = document.getElementById('quantity').value.trim();

                    if (!product || !quantity || !brand) {
                        Swal.showValidationMessage('All fields are required.');
                        return false;
                    }

                    propProductName = capitalizeWords(product);
                    propBrandName = capitalizeWords(brand);

                    return { propProductName, propBrandName, quantity, requested_by };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('action', 'request_product');
                    formData.append('product_name', result.value.propProductName);
                    formData.append('brand_name', result.value.propBrandName);
                    formData.append('quantity', result.value.quantity);
                    formData.append('requested_by', requested_by);

                    fetch('requestProduct.php', {
                    method: 'POST',
                    body: formData,
                    })
                    .then(response => response.text())
                    .then(data => {
                        Swal.fire({
                            icon: 'success',
                            title: 'Product Request Sent!',
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

    function updateRequestStatus(requestId, action) {
        Swal.fire({
            title: `Are you sure you want to ${action} this request?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
        }).then((result) => {
            if (result.isConfirmed) {
                // Send the action to the server
                const formData = new FormData();
                formData.append('action', action);
                formData.append('request_id', requestId);

                fetch('stockRequests.php', {
                    method: 'POST',
                    body: formData,
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Success', data.message, 'success').then(() => {
                            // Reload the page to reflect the changes
                            window.location.reload();
                        });
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'An error occurred while processing the request.', 'error');
                });
            }
        });
    }

    $(document).ready(function() {
        $('#quantity').keypress(function (event) {
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
