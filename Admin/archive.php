<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Check admin authentication
if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

require_once 'config.php';

// Handle restore actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['restore_brand'])) {
        $delete_id = intval($_POST['delete_id']);
        
        // Determine restore type
        try {
            // Prepare and execute the stored procedure
            $stmt = $DB_con->prepare("CALL restore_brand(?)");
            $stmt->bindParam(1, $delete_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Success message
            $_SESSION['success_message'] = "Brand restored successfully!";
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header("Location: archive.php");
        exit;
    }
    
    if (isset($_POST['restore_product'])) {
        $delete_id = intval($_POST['delete_id']);
        
        try {
            // Prepare and execute the stored procedure
            $stmt = $DB_con->prepare("CALL restore_product_type(?)");
            $stmt->bindParam(1, $delete_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Success message
            $_SESSION['success_message'] = "Product restored successfully!";
            
        } catch (Exception $e) {
            if ($e->getCode() == 23000) {
                $_SESSION['error_message'] = 'no brand error';
                $_SESSION['POST_DATA'] = $_POST;
            } else {
                $_SESSION['error_message'] = $e->getMessage();
            }
        }
        
        // Redirect to prevent form resubmission
        header("Location: archive.php");
        exit;
    }

    if (isset($_POST['restore_product_with_brand'])) {
        $delete_id = intval($_POST['delete_id']);
        
        try {
            // Prepare and execute the stored procedure
            $stmt = $DB_con->prepare("CALL restore_product_with_brand(?)");
            $stmt->bindParam(1, $delete_id, PDO::PARAM_INT);
            $stmt->execute();
            
            // Success message
            $_SESSION['success_message'] = "Product restored successfully!";
            
        } catch (Exception $e) {
            $_SESSION['error_message'] = $e->getMessage();
        }
        
        // Redirect to prevent form resubmission
        header("Location: archive.php");
        exit;
    }
}

// Date filtering logic
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : null;
$custom_from = isset($_GET['custom_from']) ? $_GET['custom_from'] : null;
$custom_to = isset($_GET['custom_to']) ? $_GET['custom_to'] : null;

// Construct date filter query
$date_condition = "";
$current_time = date('Y-m-d H:i:s');

switch($date_filter) {
    case '1hour':
        $date_condition = "AND ba.deleted_at >= DATE_SUB('$current_time', INTERVAL 1 HOUR)";
        break;
    case '1day':
        $date_condition = "AND ba.deleted_at >= DATE_SUB('$current_time', INTERVAL 1 DAY)";
        break;
    case '1week':
        $date_condition = "AND ba.deleted_at >= DATE_SUB('$current_time', INTERVAL 1 WEEK)";
        break;
    case '1month':
        $date_condition = "AND ba.deleted_at >= DATE_SUB('$current_time', INTERVAL 1 MONTH)";
        break;
    case 'custom':
        if ($custom_from && $custom_to) {
            $date_condition = "AND ba.deleted_at BETWEEN '$custom_from' AND '$custom_to'";
        }
        break;
}

// Fetch archived brands with date filtering
$brands_query = "SELECT * FROM brands_archive ba WHERE 1=1 $date_condition ORDER BY delete_id DESC";
$brands_result = $DB_con->query($brands_query);

$date_condition = str_replace("ba", "pa", $date_condition);
// Fetch archived products with date filtering
$products_query = "SELECT pa.*, ba.brand_id, ba.brand_name, b.brand_name as brand_name_fallbak FROM product_type_archive pa
                    LEFT JOIN brands_archive ba ON ba.brand_id = pa.brand_id
                    LEFT JOIN brands b ON b.brand_id = pa.brand_id
                    WHERE 1=1 $date_condition ORDER BY delete_id DESC";
$products_result = $DB_con->query($products_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading - Archive</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        .archive-section {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        .table-responsive {
            max-height: 400px;
            overflow-y: auto;
        }
        .nav-tabs {
            margin-bottom: 20px;
        }
        #custom-date-fields {
            display: none;
        }
        .tab-pane:not(.active) {
            display: none !important;
        }
    </style>
</head>
<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>
        
        <div id="page-wrapper">
            <div class="container-fluid">
                <div class="page-header">
                    <h2><i class="fa fa-archive"></i> Archive Management</h2>
                </div>
                

                <?php
                    if (isset($_SESSION['error_message']) && $_SESSION['error_message'] === 'no brand error') {
                        $POST_DATA = $_SESSION['POST_DATA'];
                    ?>
                    <form method="post" class="d-inline restoreProductWithBrand"
                        data-product-type="<?= $POST_DATA['product_type'] ?>"
                        data-brand-name="<?= $POST_DATA['brand_name'] ?>"
                    >
                        <input type="hidden" name="delete_id" value="<?= $POST_DATA['delete_id'] ?>">
                        <input type="hidden" name="restore_product_with_brand" value="1">
                    </form>
                    <script>
                        const productType = $('.restoreProductWithBrand').data('product-type');
                        const brandName = $('.restoreProductWithBrand').data('brand-name');
                        Swal.fire({
                            title: `Would you like restore ${brandName}?`,
                            text: `You can't restore ${productType} without ${brandName}.`,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'Yes, restore it!'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                $('.restoreProductWithBrand').submit();
                            }
                        });
                    </script>
                <?php
                    unset($_SESSION['error_message']);
                    unset($_SESSION['POST_DATA']);
                }
                // Display success or error messages
                if (isset($_SESSION['success_message'])) {
                    echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                    unset($_SESSION['success_message']);
                }
                if (isset($_SESSION['error_message'])) {
                    echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                    unset($_SESSION['error_message']);
                }
                ?>
                
                <!-- Date Filter Form -->
                <form method="get" class="mb-3">
                    <div class="row">
                        <div class="col-md-3">
                            <select name="date_filter" id="date_filter" class="form-control">
                                <option value="">Select Date Filter</option>
                                <option value="1hour" <?= $date_filter == '1hour' ? 'selected' : '' ?>>Last 1 Hour</option>
                                <option value="1day" <?= $date_filter == '1day' ? 'selected' : '' ?>>Last 1 Day</option>
                                <option value="1week" <?= $date_filter == '1week' ? 'selected' : '' ?>>Last 1 Week</option>
                                <option value="1month" <?= $date_filter == '1month' ? 'selected' : '' ?>>Last 1 Month</option>
                                <option value="custom" <?= $date_filter == 'custom' ? 'selected' : '' ?>>Custom Date</option>
                            </select>
                        </div>
                        <div id="custom-date-fields" class="col-md-6">
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="datetime-local" name="custom_from" class="form-control" value="<?= htmlspecialchars($custom_from ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <input type="datetime-local" name="custom_to" class="form-control" value="<?= htmlspecialchars($custom_to ?? '') ?>">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">Apply Filter</button>
                            <a href="archive.php" class="btn btn-secondary">Reset</a>
                        </div>
                    </div>
                </form>
                
                <!-- Tabs -->
                <ul class="nav nav-tabs" role="tablist">
                    <li class="nav-item active">
                        <a class="nav-link" data-toggle="tab" href="#brands">Brands</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#products">Products</a>
                    </li>
                </ul>

                <!-- Tab content -->
                <div class="tab-content">
                    <!-- Brands Tab -->
                    <div id="brands" class="tab-pane fade show active in">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered archive-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Brand Name</th>
                                        <th>Deleted At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($brand = $brands_result->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($brand['brand_name']) ?></td>
                                            <td><?= htmlspecialchars($brand['deleted_at']) ?></td>
                                            <td>
                                                <form method="post" class="d-inline restoreBrand" data-name="<?= $brand['brand_name'] ?>">
                                                    <input type="hidden" name="delete_id" value="<?= $brand['delete_id'] ?>">
                                                    <input type="hidden" name="restore_brand" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm restore-btn">
                                                        <i class="fa fa-undo"></i> Restore
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Products Tab -->
                    <div id="products" class="tab-pane fade">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered archive-table">
                                <thead class="thead-dark">
                                    <tr>
                                        <th>Product</th>
                                        <th>Brand</th>
                                        <th>Product Category</th>
                                        <th>Deleted At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($product = $products_result->fetch(PDO::FETCH_ASSOC)): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($product['type_name']) ?></td>
                                            <td>
                                                <span class="badge badge-primary brand-badge">
                                                    <?= htmlspecialchars($product['brand_name'] ?? $product['brand_name_fallbak']) ?>
                                                </span>
                                            </td>
                                            <td><?= htmlspecialchars($product['prod_type']) ?></td>
                                            <td><?= htmlspecialchars($product['deleted_at']) ?></td>
                                            <td>
                                                <form method="post" class="d-inline restoreProduct" data-name="<?= $product['prod_type']?>">
                                                    <input type="hidden" name="product_type" value="<?= $product['prod_type']?>">
                                                    <input type="hidden" name="brand_name" value="<?= $product['brand_name']?>">
                                                    <input type="hidden" name="delete_id" value="<?= $product['delete_id'] ?>">
                                                    <input type="hidden" name="restore_product" value="1">
                                                    <button type="submit" class="btn btn-primary btn-sm restore-btn">
                                                        <i class="fa fa-undo"></i> Restore
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>               

            </div>

            </div>

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">
                    &copy; 2024 CML Paint Trading Shop | All Rights Reserved
                </p>
            </div>
        </div>
    </div>

    <script>
    $(document).ready(function() {
        // Show/hide custom date fields
        $('#date_filter').change(function() {
            if ($(this).val() === 'custom') {
                $('#custom-date-fields').show();
            } else {
                $('#custom-date-fields').hide();
            }
        });

        // Trigger change on page load to handle case of pre-selected custom filter
        $('#date_filter').trigger('change');

        // Optional: Add confirmation dialogs for restore actions
        $('.restoreBrand').on('submit', function(e) {
            var $form = $(this);
            const brand = $(this).data('name');
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to restore ' + brand + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.unbind('submit').submit();
                }
            });
        });

        $('.restoreProduct').on('submit', function(e) {
            var $form = $(this);
            const brand = $(this).data('name');
            e.preventDefault();
            
            Swal.fire({
                title: 'Are you sure?',
                text: 'Do you want to restore ' + brand + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, restore it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $form.unbind('submit').submit();
                }
            });
        });
    });
    </script>
</body>
</html>
