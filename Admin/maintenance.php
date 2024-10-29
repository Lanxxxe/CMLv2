<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'config.php';

// Handle delete request
if (isset($_GET['delete_id'])) {
    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id = :order_id');
    $stmt_delete->bindParam(':order_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: orderdetails.php");
    exit;
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

if(isset($_GET['brand_id'])) {
    header('Content-Type: application/json');
    $stmt = $DB_con->prepare("SELECT type_id, type_name FROM product_type WHERE brand_id = ?");
    $stmt->execute([$_GET['brand_id']]);
    $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($types);
    exit;
}

// Fetch data for the dashboard
$stmt_total_orders = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails');
$stmt_total_orders->execute();
$total_orders = $stmt_total_orders->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_confirmed = $DB_con->prepare('SELECT COUNT(*) AS total, SUM(order_total) AS total_sum FROM orderdetails WHERE order_status = "Confirmed"');
$stmt_confirmed->execute();
$row_confirmed = $stmt_confirmed->fetch(PDO::FETCH_ASSOC);
$total_confirmed = $row_confirmed['total'];
$total_sum_confirmed = $row_confirmed['total_sum'];

$stmt_verification = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Verification"');
$stmt_verification->execute();
$total_verification = $stmt_verification->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_return = $DB_con->prepare('SELECT COUNT(*) AS total FROM  returnitems WHERE status = "Confirmed"');
$stmt_return->execute();
$returnItems = $stmt_return->fetch(PDO::FETCH_ASSOC)['total'];

$stmt_rejected = $DB_con->prepare('SELECT COUNT(*) AS total FROM orderdetails WHERE order_status = "Rejected"');
$stmt_rejected->execute();
$total_rejected = $stmt_rejected->fetch(PDO::FETCH_ASSOC)['total'];

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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    
    <style>
        .dashboard-circle {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background-color: #f1f1f1;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2em;
            margin: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
        }
        .dashboard-circle:hover {
            transform: scale(1.05);
        }
        .dashboard-circle.success {
            background-color: #28a745;
            color: white;
        }
        .dashboard-circle.warning {
            background-color: #ffc107;
            color: white;
        }
        .dashboard-circle.danger {
            background-color: #dc3545;
            color: white;
        }
        .dashboard-circle.primary {
            background-color: #007bff;
            color: white;
        }
        .dashboard-container {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }


        .brand-container {
            margin: 20px;
            max-width: 800px;
        }
        
        .brand-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .brand-header {
            background-color: #f8f9fa;
            padding: 15px;
            border-bottom: 1px solid #ddd;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .brand-title {
            margin: 0;
            color: #333;
            font-size: 1.2em;
        }
        
        .tools-list {
            list-style: none;
            padding: 15px;
            margin: 0;
        }
        
        .tool-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #eee;
        }
        
        .tool-item:last-child {
            border-bottom: none;
        }
        
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 0.9em;
            margin-left: 5px;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-warning {
            background-color: #ffc107;
            color: black;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .add-brand-btn {
            margin: 20px;
            padding: 10px 20px;
            background-color: #28a745;
            color: white;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            border-radius: 8px;
            width: 80%;
            max-width: 500px;
        }

        .close {
            float: right;
            cursor: pointer;
            font-size: 1.5em;
        }

        .custom-input {
            width: 100%;                
            box-sizing: border-box;    
            margin-top: 10px;           
        }

    </style>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

    <script src="bootstrap/js/bootstrap.min.js"></script>


    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
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
                <li><a href="index.php"> &nbsp; &nbsp; &nbsp; Home</a></li>
                <li><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Admin Order Dashboard</a></li>
                <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Add Paint Products</a></li>
                <li><a data-toggle="modal" data-target="#uploadItems"> &nbsp; &nbsp; &nbsp; Add Tools Products</a></li>
                <li><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                <li><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
                <li><a href="manage_return.php"> &nbsp; &nbsp; &nbsp; Manage Return Items</a></li>
                <li><a href="salesreport.php"> &nbsp; &nbsp; &nbsp; Sales Report</a></li>
                <li class="active"><a href="maintenance.php"> &nbsp; &nbsp; &nbsp; Maintenance</a></li>
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
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php echo htmlspecialchars($_SESSION['admin_username']); ?><b class="caret"></b></a>
                    <ul class="dropdown-menu">
                        <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                    </ul>
                </li>
            </ul>
        </div>
    </nav>

    <div id="page-wrapper">
        <div class="alert alert-danger">
            <center><h3><strong>Maintenance</strong></h3></center>
        </div>
        <div class="brand-container">
            <!-- Add Brand Button -->
            <button class="btn add-brand-btn" onclick="showAddBrandModal()">Add New Brand</button>

            <?php
            // SQL query to fetch brands and their product types
            $sql = "
            SELECT b.brand_id, b.brand_name, b.brand_img, pt.type_id, pt.type_name
            FROM brands b
            LEFT JOIN product_type pt ON b.brand_id = pt.brand_id
            ORDER BY b.brand_name, pt.type_name
            ";

            $stmt = $DB_con->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (count($result) > 0) {
                $currentBrand = null;
                $brandTools = [];

                // Group tools by brand
                foreach ($result as $row) {
                    if (!isset($brandTools[$row['brand_id']])) {
                        $brandTools[$row['brand_id']] = [
                            'name' => $row['brand_name'],
                            'img' => $row['brand_img'],
                            'tools' => []
                        ];
                    }
                    if (!empty($row['type_name'])) {
                        $brandTools[$row['brand_id']]['tools'][] = [
                            'id' => $row['type_id'],
                            'name' => $row['type_name']
                        ];
                    }
                }

                // Display brands and their tools
                foreach ($brandTools as $brandId => $brand) {
                    ?>
                    <div class="brand-card">
                        <div class="brand-header">
                            <img src="<?php echo $brand["img"]; ?>" style="width:50px; height:50px;" alt="">
                            <h3 class="brand-title"><?php echo htmlspecialchars($brand['name']); ?></h3>
                            <div class="brand-actions">
                                <button class="btn btn-primary" onclick="showAddToolModal(<?php echo $brandId; ?>)">Add Product</button>
                                <button class="btn btn-warning" style="color: #fff; !important" onclick="showEditBrandModal(<?php echo $brandId; ?>, '<?php echo htmlspecialchars($brand['name']); ?>', '<?php echo htmlspecialchars($brand['img'] ?? ''); ?>')">Edit Brand</button>
                                <button class="btn btn-danger" onclick="confirmDeleteBrand(<?php echo $brandId; ?>)">Delete Brand</button>
                            </div>
                        </div>
                        <ul class="tools-list">
                            <?php foreach ($brand['tools'] as $tool) { ?>
                                <li class="tool-item">
                                    <span><?php echo htmlspecialchars($tool['name']); ?></span>
                                    <div>
                                        <button class="btn btn-warning" style="color: #fff; !important" onclick="showEditToolModal(<?php echo $tool['id']; ?>, '<?php echo htmlspecialchars($tool['name']); ?>')">Edit</button>
                                        <button class="btn btn-danger" onclick="confirmDeleteTool(<?php echo $tool['id']; ?>)">Delete</button>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                    <?php
                }
            } else {
                echo "<p>No brands or product types found.</p>";
            }
            ?>
        </div>
        <div class="alert alert-default" style="background-color:#033c73;">
            <p style="color:white;text-align:center;">&copy 2024 CML Paint Trading Shop | All Rights Reserved</p>
        </div>
    </div>


</div>

<?php include_once("uploadItems.php") ?>
<?php include_once("insertBrandsModal.php"); ?>

<script>
    let labelStyle = "font-size: 14px; margin-left: 2.5rem; margin-top: 1.5rem;";
    let inputStyle = "width: 80%;";

    $(document).ready(function () {
        $('#example').DataTable();
    });

    $(document).ready(function () {
        $('#priceinput').keypress(function (event) {
            return isNumber(event, this);
        });
    });

    function isNumber(evt, element) {
        var charCode = (evt.which) ? evt.which : event.keyCode;
        if (
            (charCode != 45 || $(element).val().indexOf('-') != -1) &&
            (charCode != 46 || $(element).val().indexOf('.') != -1) &&
            (charCode < 48 || charCode > 57))
            return false;

        return true;
    }

    // Modal functions remain the same
    function showModal(modalId) {
        document.getElementById(modalId).style.display = 'block';
    }

    function closeModal(modalId) {
        document.getElementById(modalId).style.display = 'none';
    }

    // Add Brand
    function showAddBrandModal() {
        Swal.fire({
            title: 'Add New Brand',
            html: `
                <div style="display: flex; flex-direction: column; align-items: start;"> 
                    <label for="brandImage" style="${labelStyle}">Brand Name</label>
                    <input 
                        type="text" 
                        id="brandName" 
                        class="swal2-input" 
                        style="width: 80%;" 
                        placeholder="Enter brand name">
                    
                    <label for="brandImage" style="${labelStyle}">Brand Image</label>
                    <input 
                        type="file" 
                        id="brandImage" 
                        class="swal2-input" 
                        style="width: 80%;" 
                        accept="image/*">

                    <div id="imagePreview" class="mb-3" style="display: none; width: 100%;">
                        <img id="previewImg" src="" alt="Image Preview" style="max-width: 100px; margin: 1rem auto">
                    </div>
                </div>
            `,
            // width: '600px',
            showCancelButton: true,
            confirmButtonText: 'Add',
            cancelButtonText: 'Cancel',
            didOpen: () => {
                // Add image preview functionality
                const imageInput = document.getElementById('brandImage');
                const imagePreview = document.getElementById('imagePreview');
                const previewImg = document.getElementById('previewImg');
                
                imageInput.addEventListener('change', function(e) {
                    if (e.target.files && e.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            previewImg.src = e.target.result;
                            imagePreview.style.display = 'block';
                        }
                        reader.readAsDataURL(e.target.files[0]);
                    }
                });
            },
            preConfirm: () => {
                const brandName = document.getElementById('brandName').value;
                const brandImage = document.getElementById('brandImage').files[0];
                
                if (!brandName) {
                    Swal.showValidationMessage('Please enter a brand name');
                    return false;
                }
                
                if (!brandImage) {
                    Swal.showValidationMessage('Please upload a brand image');
                    return false;
                }
                
                return { brandName, brandImage };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'add_brand');
                formData.append('brand_name', result.value.brandName);
                formData.append('brand_image', result.value.brandImage);
                
                fetch('maintenanceprocess.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    window.location.reload();
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to add brand', 'error');
                    console.error('Error:', error);
                });
            }
        });
    }

    // Edit Brand
    function showEditBrandModal(brandId, brandName, currentImage) {
        Swal.fire({
            title: 'Edit Brand',
            html: `
                <div style="display: flex; flex-direction: column; align-items: start;">
                        <label class="form-label" for="editBrandName" style="${labelStyle}">Brand Name</label>
                        <input type="text" id="editBrandName" style="${inputStyle}" class="swal2-input" value="${brandName}">
                        <label class="form-label" for="editBrandImage" style="${labelStyle}">Brand Image</label>
                        <input type="file" id="editBrandImage" style="${inputStyle}" class="swal2-file" accept="image/*">                
                </div>
                ${currentImage ? `
                    <div class="mb-3">
                        <img src="${currentImage}" alt="Current Image" style="max-width: 100px; margin-top: 2rem;">
                    </div>` : ''}
            `,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            preConfirm: () => {
                const brandName = document.getElementById('editBrandName').value;
                const brandImage = document.getElementById('editBrandImage').files[0];
                
                if (!brandName) {
                    Swal.showValidationMessage('Please enter a brand name');
                    return false;
                }
                
                return { brandName, brandImage };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const formData = new FormData();
                formData.append('action', 'edit_brand');
                formData.append('brand_id', brandId);
                formData.append('brand_name', result.value.brandName);
                if (result.value.brandImage) {
                    formData.append('brand_image', result.value.brandImage);
                }
                
                // Using fetch to send FormData
                fetch('maintenanceprocess.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(data => {
                    window.location.reload();
                })
                .catch(error => {
                    Swal.fire('Error', 'Failed to update brand', 'error');
                });
            }
        });
    }

    // Delete Brand
    function confirmDeleteBrand(brandId) {
        Swal.fire({
            title: 'Delete Brand',
            text: 'Are you sure you want to delete this brand? This will also delete all associated tools.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `maintenanceprocess.php?action=delete_brand&id=${brandId}`;
            }
        });
    }

    // Add Tool
    function showAddToolModal(brandId) {
        Swal.fire({
            title: 'Add New Product',
            input: 'text',
            inputLabel: 'Tool Name',
            inputPlaceholder: 'Enter product name',
            showCancelButton: true,
            confirmButtonText: 'Add',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a tool name';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'maintenanceprocess.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'add_tool';
                
                const brandIdInput = document.createElement('input');
                brandIdInput.type = 'hidden';
                brandIdInput.name = 'brand_id';
                brandIdInput.value = brandId;
                
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'tool_name';
                nameInput.value = result.value;
                
                form.appendChild(actionInput);
                form.appendChild(brandIdInput);
                form.appendChild(nameInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Edit Tool
    function showEditToolModal(toolId, toolName) {
        Swal.fire({
            title: 'Edit Product',
            input: 'text',
            inputLabel: 'Tool Name',
            inputValue: toolName,
            showCancelButton: true,
            confirmButtonText: 'Save Changes',
            cancelButtonText: 'Cancel',
            inputValidator: (value) => {
                if (!value) {
                    return 'Please enter a tool name';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'maintenanceprocess.php';
                
                const actionInput = document.createElement('input');
                actionInput.type = 'hidden';
                actionInput.name = 'action';
                actionInput.value = 'edit_tool';
                
                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'tool_id';
                idInput.value = toolId;
                
                const nameInput = document.createElement('input');
                nameInput.type = 'hidden';
                nameInput.name = 'tool_name';
                nameInput.value = result.value;
                
                form.appendChild(actionInput);
                form.appendChild(idInput);
                form.appendChild(nameInput);
                document.body.appendChild(form);
                form.submit();
            }
        });
    }

    // Delete Tool
    function confirmDeleteTool(toolId) {
        Swal.fire({
            title: 'Delete Product',
            text: 'Are you sure you want to delete this tool?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it',
            cancelButtonText: 'Cancel',
            confirmButtonColor: '#dc3545'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = `maintenanceprocess.php?action=delete_tool&id=${toolId}`;
            }
        });
    }

    // Add success/error message handler
    function showMessage(type, message) {
        Swal.fire({
            icon: type,
            title: type === 'success' ? 'Success!' : 'Error!',
            text: message,
            timer: 2000,
            showConfirmButton: false
        });
    }
</script>

<?php
// Add this at the end of your PHP processing files (add_brand.php, edit_brand.php, etc.)
if ($success) {
    echo "<script>showMessage('success', 'Operation completed successfully!');</script>";
} else if (isset($error)) {
    echo "<script>showMessage('error', '" . htmlspecialchars($error) . "');</script>";
}
?>
</body>
</html>
