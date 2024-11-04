<?php
session_start();

if(empty($_SESSION['user_email']))
{
    echo "
    <script>
        alert('Sign in first!'); 
        window.location.href='../index.php';
    </script>";
    exit();
}

error_reporting(E_ALL);
ini_set("display_errors", 0);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");

try {
 include("config.php");
 extract($_SESSION); 
		  $stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
		$stmt_edit->execute(array(':user_email'=>$user_email));
		$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
		extract($edit_row);
		
		?>
		
		<?php
 include("config.php");
		  $stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
		$stmt_edit->execute(array(':user_id'=>$user_id));
		$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
		extract($edit_row);
} catch(Exception $e) {
    include '../error_log.php';
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
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div id="wrapper">
        <?php include_once("navigation.php") ?>

		<div id="page-wrapper">
            <div class="alert alert-default" style="color:white;background-color:#008CBA">
                <center>
                    <h3> <span class="glyphicon glyphicon-edit"></span> Paint Paint Calculator</h3>
                </center>
            </div>

            <br />

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <div class="form-group" style="font-size: 20px;">
                            <label class="form-label" for="brandSelect">Brand:</label>
                            <select name="brand_id" id="brandSelect" class="form-control" required>
                                <option value="">Select Brand</option>
                                <?php
                                // Fetch all brands from database
                                $stmt = $DB_con->prepare("SELECT DISTINCT b.brand_id, b.brand_name 
                                    FROM brands b
                                    JOIN product_type pt ON b.brand_id = pt.brand_id
                                    WHERE pt.prod_type = 'Paint'
                                    ORDER BY b.brand_name");
                                $stmt->execute();
                                $brands = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                
                                foreach ($brands as $brand) {
                                    ?>
                                    <option value='<?php echo htmlspecialchars($brand['brand_id']) ?>'>
                                        <?php echo htmlspecialchars($brand['brand_name']) ?></option>";
                                    <?php
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group" style="font-size: 20px;">
                            <label for="typeSelect">Type:</label>
                            <select name="type_id" id="typeSelect" class="form-control" required disabled>
                                <option value="">Select Brand First</option>
                            </select>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6" style="font-size: 20px;">
                                <label class="form-label">Width (m): </label>
                                <input class="form-control" placeholder="Enter width of wall" id="width" type="text">
                            </div>
                            <div class="form-group col-md-6" style="font-size: 20px;">
                                <label class="form-label">Height (m): </label>
                                <input class="form-control" placeholder="Enter height of wall" id="height" type="text">
                            </div>
                        </div>


                        <div class="form-group" style="font-size: 20px;">
                            <label class="form-label">Surface Area (m2): </label>
                            <input class="form-control" id="surfaceArea" type="text">
                        </div>
                        
                        <div class="row">
                            <div class="form-group col-md-6" style="font-size: 20px;">
                                <label class="form-label">No. of coats: </label>
                                <input class="form-control" placeholder="Enter number of coats" id="coatsNumber" value="1" min="1" onkeypress="return isNumber(event)" type="number">
                            </div>
                            <div class="form-group col-md-6" style="font-size: 20px;">
                                <label class="form-label">Gallons in Total: </label>
                                <input class="form-control" id="totalGallons" type="text">
                            </div>
                        </div>

                        <div class="form-group" style="font-size: 20px;">
                            <label class="form-label">Total Price: </label>
                            <input class="form-control" id="totalPrice" value="0" type="text">
                        </div>
                    </div>

                    <div class="item-preview-container" style="display: none;">
                        <h3>Item Preview</h3>

                        <div style="font-size: 20px; font-weight: bold;">
                            <img id="paint_item_image" src="" width="300px" height="250px" style="display: block; margin: 0 auto;" alt="">
                            <p>Brand: <span id="paint_brand_name"></span></p>
                            <p>Item: <span id="paint_item_name"></span></p>
                            <p>Price: <span id="paint_item_price"></span></p>

                            <div id="item-preview-pallet" style="display: flex; gap: 7px">
                                <p style="display: block;">Pallet Color: <span id="pallet_item_code"></span></p>
                                <div id="pallet-container" style="width: 500px; height: 20px; margin-top: 6px; border-radius: 7px"></div>
                            </div>
                        </div>
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
                <form enctype="multipart/form-data" method="post" action="settings.php">
                    <div class="modal-body">
                        <fieldset>                        
                            <p>Firstname:</p>
                            <div class="form-group">          
                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text" value="<?php  echo $user_firstname; ?>" required>
                            </div> 
                            <p>Lastname:</p>
                            <div class="form-group"> 
                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text" value="<?php  echo $user_lastname; ?>" required>          
                            </div>         
                            <p>Address:</p>
                            <div class="form-group">      
                                <input class="form-control" placeholder="Address" name="user_address" type="text" value="<?php  echo $user_address; ?>" required>
                            </div>
                            <p>Password:</p>
                            <div class="form-group">          
                                <input class="form-control" placeholder="Password" name="user_password" type="password" value="<?php  echo $user_password; ?>" required>
                            </div>         
                            <div class="form-group">         
                                <input class="form-control hide" name="user_id" type="text" value="<?php  echo $user_id; ?>" required>
                            </div>
                        </fieldset>
                    </div>

                    <div class="modal-footer">
                        <button class="btn btn-block btn-success btn-md" name="user_save">Save</button>
                        <button type="button" class="btn btn-block btn-danger btn-md" data-dismiss="modal">Cancel</button>
                </div>
                </form>
            </div>
          </div>
        </div>
        
<script>

    // Function to restrict input to numbers, decimals, and hyphen for negative numbers
    function isNumber(evt) {
        evt = (evt) ? evt : window.event;
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        if (charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }
        return true;
    }

    // Function to calculate the surface area
    function calculateSurfaceArea() {
        const width = parseFloat(document.getElementById("width").value) || 0;
        const height = parseFloat(document.getElementById("height").value) || 0;
        const surfaceArea = width * height;
        document.getElementById("surfaceArea").value = surfaceArea.toFixed(2);
    }

    function updateTotalPrice() {
        const surfaceArea = parseFloat(document.getElementById("surfaceArea").value) || 0;
        const coatsNumber = parseFloat(document.getElementById("coatsNumber").value) || 0;
        
        // Retrieve the price per gallon from the selected option's data attribute
        const selectedOption = document.getElementById("typeSelect").selectedOptions[0];
        const pricePerGallon = selectedOption ? parseFloat(selectedOption.getAttribute("data-item-price")) : 0;

        // Calculate total gallons and total price
        const totalGallons = (surfaceArea * coatsNumber) / 10; // Example calculation, adjust if needed
        const totalPrice = totalGallons * pricePerGallon;

        // Update the display fields
        document.getElementById("totalGallons").value = totalGallons.toFixed(2);
        document.getElementById("totalPrice").value = `P${totalPrice.toFixed(2)}`;
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Restrict input to numbers only for price
        document.getElementById("totalPrice").addEventListener("keypress", function(event) {
            return isNumber(event, this);
        });


        // Set up event listeners for brand and type select elements
        document.getElementById("brandSelect").addEventListener("change", function() {
            const brandId = this.value;
            console.log(brandId);
            const typeSelect = document.getElementById("typeSelect");

            // Fetch available types for selected brand
            if (brandId) {
                const xhr = new XMLHttpRequest();
                xhr.open("GET", `getPaintTypes.php?brand_id=${brandId}`, false);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        const types = JSON.parse(xhr.responseText);
                        console.log(types);

                        // Populate typeSelect with available types in gallons only
                        typeSelect.innerHTML = '<option value="">Select Type</option>';
                        types.forEach(type => {
                            if (type.gl === "Gallon") {
                                typeSelect.innerHTML += `<option value="${type.type}" 
                                    data-brand-name="${type.brand_name}" 
                                    data-item-name="${type.item_name}" 
                                    data-item-price="${type.item_price}"
                                    data-item-image="${type.item_image}"
                                    data-item-color="${type.rgb}">
                                    ${type.type} - ${type.item_price} per ${type.gl} - ${type.name}
                                </option>`;
                            }
                        });
                        typeSelect.disabled = false;
                    } else {
                        console.error("Error fetching paint types");
                    }
                };
                xhr.send();
            } else {
                typeSelect.innerHTML = '<option value="">Select Brand First</option>';
                typeSelect.disabled = true;
            }
        });
        
        // Update the preview when a type is selected
        document.getElementById("typeSelect").addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];

            if (selectedOption.value) {
                document.querySelector('.item-preview-container').style.display = 'block';
                // Get details from the selected option's data attributes
                const brandName = selectedOption.getAttribute("data-brand-name");
                const itemName = selectedOption.getAttribute("data-item-name");
                const itemPrice = selectedOption.getAttribute("data-item-price");
                const itemImage = selectedOption.getAttribute("data-item-image");
                const itemColor = selectedOption.getAttribute("data-item-color");
                
                // Update the preview container
                document.querySelector("#paint_brand_name").textContent = brandName;
                document.querySelector("#paint_item_name").textContent = itemName;
                document.querySelector("#paint_item_price").textContent = itemPrice;
                document.querySelector("#paint_item_image").src = `../Admin/item_images/${itemImage}`;
                document.querySelector("#paint_item_image").alt = `${itemName}`;
                document.querySelector("#pallet-container").style.backgroundColor = `${itemColor}`;
            } else {
                document.querySelector('.item-preview-container').style.display = 'none';
                // Clear the preview if no type is selected
                document.querySelector("#paint_brand_name").textContent = "";
                document.querySelector("#paint_item_name").textContent = "";
                document.querySelector("#paint_item_price").textContent = "";
            }
        });

        // Other event listeners for updates
        document.getElementById("typeSelect").addEventListener("change", updateTotalPrice);
        document.getElementById("width").addEventListener("input", calculateSurfaceArea);
        document.getElementById("height").addEventListener("input", calculateSurfaceArea);
        document.getElementById("coatsNumber").addEventListener("input", updateTotalPrice);
    });

</script>

</body>
</html>
