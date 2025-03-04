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


    <script src="bootstrap/js/bootstrap.min.js"></script>
    <script src="js/datatables.min.js"></script>

    <!-- Include SweetAlert2 CSS and JS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <style>
        .custom-btns {
            display: flex;
            justify-content: end;
            gap: 10px;
            margin-bottom: 20px;
        }

        @media print {
            @page {
                size: 800px auto;
                margin: 0;
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }

            body > *:not(#printable-inventory) {
                display: none !important;
            }
            #printable-inventory {
                display: block !important;
                position: fixed;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: white;
                z-index: 9999;
                padding: 20px;
            }

            #printable-inventory .header {
                text-align: right;
                margin-bottom: 30px;
                padding-bottom: 20px;
                border-bottom: 2px solid #333;
                position: relative;
            }
            
            #printable-inventory .company-name {
                position: absolute;
                left: 0;
                top: 0;
                font-size: 24px;
                font-weight: bold;
            }
            
            #printable-inventory .report-title {
                font-size: 20px;
                font-weight: bold;
                margin-bottom: 10px;
            }
            
            #printable-inventory .report-info {
                margin-top: 38px;
                font-size: 12px;
                text-align: right;
                line-height: 13px;
            }
            
            #printable-inventory table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
                font-size: 12px;
            }
            
            #printable-inventory th {
                background-color: #2c3e50 !important;
                color: white !important;
                padding: 10px;
                text-align: left;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            #printable-inventory td {
                padding: 8px;
                border-bottom: 1px solid #ddd;
            }
            
            #printable-inventory tr:nth-child(even) {
                background-color: #f9f9f9;
            }

            #printable-inventory .status-low {
                background-color: #df6540 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            #printable-inventory .status-medium {
                background-color: #df9440 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            #printable-inventory .status-warning {
                background-color: #d8df40 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            #printable-inventory .status-good {
                background-color: #70df40 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            #printable-inventory .page-number {
                position: fixed;
                bottom: 20px;
                right: 20px;
                font-size: 12px;
                text-wrap: nowrap;
            }

            /* Hide all other elements when printing */
            body > *:not(#printable-inventory) {
                display: none !important;
            }
        }
        </style>


</head>

<body>
    <div id="printable-inventory" style="display: none;">
        <div class="header">
            <div class="company-name">CML Paint Trading</div>
            <div class="report-info">
                <div class="report-title">Inventory Report</div>
                Date Printed: <?php echo date('F d, Y h:i A'); ?><br>
                Printed by: <?php echo htmlspecialchars($_SESSION['admin_username']); ?>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Name of Item</th>
                    <th>Brand Name</th>
                    <th>Product Type</th>
                    <th>Price</th>
                    <th>Quantity</th>
                    <th>Expiration Date</th>
                    <th>Date Added</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $branch = $_SESSION['current_branch']; 
                $stmt = $DB_con->prepare('SELECT * FROM items where branch = :branch');
                $stmt->bindParam(':branch', $branch);
                $stmt->execute();

                if ($stmt->rowCount() > 0) {
                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $date = new DateTime($row['item_date']);
                        $date1 = new DateTime($row['expiration_date']);
                        $formattedDate = $date->format('F j, Y');
                        $formattedDate1 = $date1->format('F j, Y');
                        
                        ?>
                        <tr>
                            <td><?php echo $row['item_name'] . ($row['gl'] ? " (" . $row['gl'] . ")" : ""); ?></td>
                            <td><?php echo htmlspecialchars($row['brand_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['type']); ?></td>
                            <td>₱<?php echo number_format($row['item_price'], 2); ?></td>
                            <td><?php echo $row['quantity'] . " " . $row['gl']; ?></td>
                            <td><?php echo $formattedDate1; ?></td>
                            <td><?php echo $formattedDate; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No items found.</td>
                    </tr>
                    <?php
                }
                ?>
            </tbody>
        </table>
        <div class="page-number"></div>
    </div>

    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-danger">
                <center>
                    <h3><strong>Inventory</strong> </h3>
                </center>
            </div>

            <div class="table-responsive mt-5" style="margin-top: 50px;">
                    <div class="custom-btns">
                        <a href="generate_inventory_pdf.php" class="action-btn btn btn-primary">
                            <i class="fa fa-file-pdf-o"></i> Save PDF
                        </a>
                        <button type="button" class="action-btn btn btn-primary" onclick="printContent()">
                            <i class="fa fa-print"></i> Print
                        </button>
                    </div>
                <br />

                <div id="trpContainer">
                    <canvas id="quantityChart" height="600px"></canvas>
                </div>
            
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
                            <th class="hide-in-print">Actions</th>

                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include("config.php");
                        $stmt = $DB_con->prepare('SELECT * FROM items WHERE branch = :branch');
                        $stmt->bindParam(':branch', $branch);
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
                                    <td>&#8369; <?php echo number_format($row['item_price'], 2); ?></td>
                                    <td><?php echo $quantity . " " . $gl ?></td>
                                    <td><?php echo $formattedDate1 ?></td>
                                    <td><?php echo $formattedDate; ?></td>

                                    <td class="hide-in-print">


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
            $getProduct = $DB_con->prepare('SELECT * FROM items WHERE branch = :branch');
            $getProduct->bindParam(':branch', $branch);
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

    <script type="text/javascript" charset="utf-8">
        $(document).ready(function() {
            const table = $('#example').dataTable();
        });
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

function printContent() {
    window.print();
}

// Add this to handle page numbers
window.onbeforeprint = function() {
    let pageHeight = window.innerHeight;
    let tableRows = document.querySelectorAll('#printable-inventory tbody tr');
    let currentPage = 1;
    let currentHeight = document.querySelector('#printable-inventory .header').offsetHeight;
    
    // Remove existing page number elements
    document.querySelectorAll('.page-number').forEach(el => el.remove());
    
    let count = 0;
    tableRows.forEach((row, index) => {
        count += 1;
        currentHeight += row.offsetHeight;
        
        if (currentHeight > pageHeight - 100) { // 100px buffer for page margins
            // Add page number
            let pageNum = document.createElement('div');
            pageNum.className = 'page-number';
            pageNum.textContent = `Page ${currentPage}`;

            let totalpage = document.createElement('span');
            totalpage.className = 'total-page';
            pageNum.appendChild(totalpage);

            row.parentNode.insertBefore(pageNum, row);
            
            currentHeight = row.offsetHeight;
            currentPage++;
        }
        
        if (index === tableRows.length - 1) {
            // Add final page number
            let pageNum = document.createElement('div');
            pageNum.className = 'page-number';
            pageNum.textContent = `Page ${currentPage}`;

            let totalpage = document.createElement('span');
            totalpage.className = 'total-page';
            pageNum.appendChild(totalpage);

            row.parentNode.appendChild(pageNum);
        }
    });
            document.querySelectorAll('.total-page').forEach(element => {
                element.textContent = ' of ' + count;
            });
};
    </script>
</body>

</html>
