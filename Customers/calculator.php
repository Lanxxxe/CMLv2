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
                                <input class="form-control" placeholder="Enter number of coats" id="coatsNumber" type="number">
                            </div>
                            <div class="form-group col-md-6" style="font-size: 20px;">
                                <label class="form-label">Gallons in Total: </label>
                                <input class="form-control" id="liters" type="text">
                            </div>
                        </div>
                        <div class="form-group" style="font-size: 20px;">
                            <label class="form-label">Paint Type: </label>
                            <select id="paint" class="form-control">
                                <option value="Gloss">Gloss</option>
                                <option value="Oil Paint">Oil Paint</option>
                                <option value="Aluminum Paint">Aluminum Paint</option>
                                <option value="Semi Gloss Paint">Semi Gloss Paint</option>
                                <option value="Enamel">Enamel</option>
                                <option value="Exterior Paint">Exterior Paint</option>
                                <option value="Interior Paint">Interior Paint</option>
                                <option value="Emulsion">Emulsion</option>
                                <option value="Primer">Primer</option>
                                <option value="Acrylic">Acrylic</option>
                                <option value="Flat Paint">Flat Paint</option>
                                <option value="Matte Finish">Matte Finish</option>
                            </select>
                        </div>
                        <div class="form-group" style="font-size: 20px;">
                            <label class="form-label">Total Price: </label>
                            <input class="form-control" id="totalPrice" value="0" type="text">
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

    // Function to compute surface area
    const updateSurfaceArea = () => {
        var width = parseFloat(document.getElementById("width").value);
        var height = parseFloat(document.getElementById("height").value);
        var surfaceArea = 0; // Default value
        if (!isNaN(width) && !isNaN(height)) {
            surfaceArea = (width * height).toFixed(2);
        }
        document.getElementById("surfaceArea").value = surfaceArea;
    }

    const updateTotalGallons = () => {
        var surfaceArea = parseFloat(document.getElementById("surfaceArea").value);
        var coatsNumber = parseFloat(document.getElementById("coatsNumber").value);
        var totalLiters = 0; // Default value
        var pricePerLiter = 200; // Price per liter
        var paintType = document.getElementById("paint").value;

        switch (paintType) {
            case "Gloss":
                pricePerLiter += 0; // No additional charge for Gloss paint
                break;
            case "Oil Paint":
                pricePerLiter += 12; // No additional charge for Gloss paint
                break;
            case "Aluminum Paint":
                pricePerLiter += 20; // No additional charge for Gloss paint
                break;
            case "Semi Gloss Paint":
                pricePerLiter += 27; // No additional charge for Gloss paint
                break;
            case "Enamel":
                pricePerLiter += 32; // No additional charge for Gloss paint
                break;
            case "Exterior Paint":
                pricePerLiter += 36; // No additional charge for Gloss paint
                break;
            case "Exterior Paint":
                pricePerLiter += 42; // No additional charge for Gloss paint
                break;
            case "Emulsion":
                pricePerLiter += 46; // No additional charge for Gloss paint
                break;
            case "Primer":
                pricePerLiter += 49; // No additional charge for Gloss paint
                break;
            case "Acrylic":
                pricePerLiter += 21; // No additional charge for Gloss paint
                break;
            case "Flat Paint":
                pricePerLiter += 13; // No additional charge for Gloss paint
                break;
            case "Matte Finish":
                pricePerLiter += 60; // No additional charge for Gloss paint
                break;
            default:
                pricePerLiter += 10; // Add 10 pesos for other paint types
                break;
        }
        if (!isNaN(surfaceArea) && !isNaN(coatsNumber) && coatsNumber > 0) {
            totalLiters = (surfaceArea * coatsNumber).toFixed(2);

            var totalGallons = (totalLiters * 0.264172).toFixed(2);

            var totalPrice = (totalLiters * pricePerLiter).toFixed(2);
            
            document.getElementById("liters").value = totalGallons;
            document.getElementById("totalPrice").value = totalPrice;
        }
    }

    // Attach event listeners to width and height input fields
    document.getElementById("width").addEventListener("input", updateSurfaceArea);
    document.getElementById("height").addEventListener("input", updateSurfaceArea);
    document.getElementById("coatsNumber").addEventListener("input", updateTotalGallons);
    document.getElementById("paint").addEventListener("change", updateTotalGallons);

    // Initialize color on page load
    updateSurfaceArea();
    updateTotalGallons();
</script>
</body>
</html>
