<?php
session_start();

if (!$_SESSION['admin_username']) {

    header("Location: ../index.php");
}

?>

<?php

require_once 'config.php';

if (isset($_GET['delete_id'])) {

    $stmt_select = $DB_con->prepare('SELECT item_image FROM items WHERE item_id =:item_id');
    $stmt_select->execute(array(':item_id' => $_GET['delete_id']));
    $imgRow = $stmt_select->fetch(PDO::FETCH_ASSOC);
    unlink("item_images/" . $imgRow['item_image']);


    $stmt_delete = $DB_con->prepare('DELETE FROM items WHERE item_id =:item_id');
    $stmt_delete->bindParam(':item_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: items.php");
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
    <script src="../assets/js/chart.umd.min.js"></script>


    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/datatables.min.js"></script>

    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



</head>

<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-danger">
                <center>
                    <h3><strong>Inventory</strong> </h3>
                </center>
            </div>
            <br />

            <div id="trpContainer">
                <canvas id="quantityChart" height="600px"></canvas>
            </div>
            
            <div class="table-responsive mt-5" style="margin-top: 50px;">
                <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                    <thead>
                        <tr>
                            <th>Image</th>
                            <th>Name of Item</th>
                            <th>Brand Name</th>
                            <th>Product Type</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Expiration Date</th>
                            <th>Date Added</th>
                            <th>Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("config.php");
                        $stmt = $DB_con->prepare('SELECT * FROM items');
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
                                        echo '#d8df40; color: #000;';
                                    }
                                    elseif ($quantity <= 20 && $quantity > 10) {
                                        echo '#df9440; color: #000;';
                                    } elseif ($quantity <= 10 || $quantity == 0 ) {
                                        echo '#df6540; color: #000;';
                                    } else {
                                        echo '#70df40; color: #000;';
                                    }
                                ?>
                                ">

                                    <td>
                                        <center> <img src="item_images/<?php echo $item_image; ?>" class="img img-rounded" width="50" height="50" /></center>
                                    </td>
                                    <td><?php echo $item_name . (($gl)? " (" . $gl . ")" : "") ?></td>
                                    <td><?php echo $brand_name ?></td>
                                    <td><?php echo $type ?></td>
                                    <td>&#8369; <?php echo $item_price; ?></td>
                                    <td><?php echo $quantity . " " . $gl ?></td>
                                    <td><?php echo $formattedDate1 ?></td>
                                    <td><?php echo $formattedDate; ?></td>

                                    <td>


                                        <a class="btn btn-info" href="javascript:void(0)" title="Edit Item" onclick="return confirmEdit('<?php echo $row['item_id']; ?>')">
                                            <span class='glyphicon glyphicon-pencil'></span> Update
                                        </a>

                                        <a class="btn btn-danger" href="javascript:void(0)" title="Remove Item" onclick="return confirmDelete('<?php echo $row['item_id']; ?>')">
                                            <span class='glyphicon glyphicon-trash'></span> Remove Item
                                        </a>
                                        <!-- <a class="btn btn-info" href="edititem.php?edit_id=<?php echo $row['item_id']; ?>" title="click for edit" onclick="return confirm('Are you sure edit this item?')"><span class='glyphicon glyphicon-pencil'></span> Edit Item</a> 
				
                                        <a class="btn btn-danger" href="?delete_id=<?php echo $row['item_id']; ?>" title="click for delete" onclick="return confirm('Are you sure to remove this item?')"><span class='glyphicon glyphicon-trash'></span> Remove Item</a>
                                        -->
                                    </td>
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

    <!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>

    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            $('#example').dataTable();

            // Define arrays to store data
            const itemNames = [];
            const itemQuantities = [];

            // Fetch item data from the PHP loop
            <?php
            // Reset the statement to ensure we can fetch the data again
            $getProduct = $DB_con->prepare('SELECT * FROM items');
            $getProduct->execute();
            while ($row = $getProduct->fetch(PDO::FETCH_ASSOC)) {
                extract($row);
                // Pass item names and quantities to JavaScript
                ?>
                itemNames.push("<?php echo $item_name . (($gl) ? " (" . $gl . ")" : "") ?>");
                itemQuantities.push(<?php echo $quantity; ?>);
                console.log("<?php echo $item_name . (($gl) ? " (" . $gl . ")" : "") ?>");
            <?php } ?>

            // Initialize Chart.js
            const ctx = document.getElementById('quantityChart');
            new Chart(ctx, {
            type: 'bar',
            data: {
                labels: itemNames,
                datasets: [{
                    label: 'Inventory Products',
                    data: itemQuantities,
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',  // Blue
                        'rgba(16, 185, 129, 0.8)',  // Green
                        'rgba(245, 158, 11, 0.8)',  // Orange
                        'rgba(236, 72, 153, 0.8)',  // Pink
                        'rgba(139, 92, 246, 0.8)'   // Purple
                    ],
                    borderColor: [
                        'rgba(59, 130, 246, 1)',
                        'rgba(16, 185, 129, 1)',
                        'rgba(245, 158, 11, 1)',
                        'rgba(236, 72, 153, 1)',
                        'rgba(139, 92, 246, 1)'
                    ],
                    borderWidth: 2,
                    borderRadius: 6,
                    maxBarThickness: 40
                }]
            },
            options: {
                indexAxis: 'y',
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            font: {
                                size: 13,
                                family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                            },
                            padding: 20
                        }
                    },
                    title: {
                        display: true,
                        text: 'Inventory',
                        font: {
                            size: 16,
                            weight: 'bold',
                            family: "'Helvetica Neue', 'Helvetica', 'Arial', sans-serif"
                        },
                        padding: {
                            top: 10,
                            bottom: 20
                        }
                    }
                },
                scales: {
                    x: {
                        title: {
                            display: true,
                            text: 'Quantity'
                        },
                        beginAtZero: true,
                        grid: {
                            display: true,
                            drawBorder: true,
                            drawOnChartArea: true,
                            drawTicks: true,
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    },
                    y: {title: {
                            display: true,
                            text: 'Item'
                        },
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 12
                            }
                        }
                    }
                },
                layout: {
                    padding: {
                        left: 20,
                        right: 20,
                        top: 0,
                        bottom: 10
                    }
                },
            }
        });

        });
    </script>

    <script>
        document.querySelector("#nav_item_management").className = "active";

        function confirmEdit(itemName) {
            Swal.fire({
                title: 'Edit Item',
                text: `Are you sure you want to edit "${itemName}"?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Yes, edit it',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'edititem.php?edit_id=' + encodeURIComponent(itemName);
                }
            });
            return false; // Prevent default link behavior
        }


        function confirmDelete(itemName) {
            Swal.fire({
                title: 'Remove Item',
                text: `Are you sure you want to remove "${itemName}"?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, remove it',
                cancelButtonText: 'Cancel',
                dangerMode: true // Adds red color to the confirm button
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?delete_id=' + encodeURIComponent(itemName);
                }
            });
            return false; // Prevent default link behavior
        }
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
