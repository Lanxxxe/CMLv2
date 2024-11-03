<?php

$valid_steps = ["1", "2", "2a", "2b", "2c", "2d", "3", "4"];
$step = filter_input(INPUT_GET, "step", FILTER_SANITIZE_SPECIAL_CHARS);

if (!empty($step) && !in_array($step, $valid_steps)) {
    header("Location: HTTP/1.0 404 Not Found");
}

session_start();

if (!$_SESSION['user_email']) {

    header("Location: ../index.php");
}

?>

<?php
include("config.php");
extract($_SESSION);
$stmt_edit = $DB_con->prepare('SELECT * FROM users WHERE user_email =:user_email');
$stmt_edit->execute(array(':user_email' => $user_email));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>

<?php
include("config.php");
$stmt_edit = $DB_con->prepare("select sum(order_total) as total from orderdetails where user_id=:user_id and order_status='Ordered'");
$stmt_edit->execute(array(':user_id' => $user_id));
$edit_row = $stmt_edit->fetch(PDO::FETCH_ASSOC);
extract($edit_row);

?>

<?php

require_once 'config.php';

if (isset($_GET['delete_id'])) {
    
    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE order_id =:order_id');
    $stmt_delete->bindParam(':order_id', $_GET['delete_id']);
    $stmt_delete->execute();

    header("Location: cart_items.php");
}

?>
<?php

require_once 'config.php';

if (isset($_GET['update_id'])) {

    $stmt_delete = $DB_con->prepare('update orderdetails set order_status="Ordered" WHERE order_status="Pending" and user_id =:user_id');
    $stmt_delete->bindParam(':user_id', $_GET['update_id']);
    $stmt_delete->execute();
    echo "<script>alert('Item/s successfully ordered!')</script>";

    header("Location: orders.php");
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="./bootstrap/css/bootstrap.min.css" />
    <!-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"> -->
    <link rel="stylesheet" type="text/css" href="./font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="./css/local.css" />

    <!-- HTMX -->
    <script src="https://unpkg.com/htmx.org@1.9.12" integrity="sha384-ujb1lZYygJmzgSwoxRggbCHcjc0rB2XoQrxeTUQyRjrOnlCoYta87iKBWq3EsdM2" crossorigin="anonymous"></script>
    
    <!-- Step 3 -> Object/Image -->
    <script type="text/javascript" src="./paint-mixer-files/script-file/objects.js" defer></script>
    
    <!-- step1 and step 2 -->
    <link rel="stylesheet" type="text/css" href="./paint-mixer-files/css-files/steps-styles.css">
    <script type="text/javascript" src="./paint-mixer-files/script-file/paint-mixer-script.js" defer></script>
    <!-- <script type="text/javascript" src="./paint-mixer-files/script-file/searchFunction.js" ></script> -->
    
    <!-- step3 -->
    <link rel="stylesheet" type="text/css" href="./paint-mixer-files/css-files/step3.css">
    <script type="text/javascript" src="./paint-mixer-files/script-file/step3.js" defer></script>

    <!-- step4 -->
    <link rel="stylesheet" type="text/css" href="./paint-mixer-files/css-files/step4.css">
    <script type="text/javascript" src="./paint-mixer-files/script-file/step4.js" defer></script>

    <script type="text/javascript" src="./js/jquery-1.10.2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script type="text/javascript" src="./bootstrap/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        #result-color {
            width: 100%;
            height: 700px;
            background-color: red;
            border: 1px solid black;
            margin-bottom: 10px;
            position: relative;
        }

        .slidecontainer {
            width: 100%;
        }

        .slider {
            -webkit-appearance: none;
            width: 100%;
            height: 15px;
            border-radius: 5px;
            outline: none;
            opacity: 0.7;
            -webkit-transition: .2s;
            transition: opacity .2s;
        }

        .red {
            background-color: #FF0003;
        }

        .red::-webkit-slider-thumb {
            background-color: #600709;
        }

        .green {
            background-color: #00FF12;
        }

        .green::-webkit-slider-thumb {
            background-color: #0D7B15;
        }

        .blue {
            background-color: #0009FF;
        }

        .blue::-webkit-slider-thumb {
            background-color: #0F1366;
        }

        .slider:hover {
            opacity: 1;
        }

        .slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            cursor: pointer;
        }

        .slider::-moz-range-thumb {
            width: 25px;
            height: 25px;
            border-radius: 50%;
            background: #04AA6D;
            cursor: pointer;
        }

        .color-slider {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
        }

        .color-list {
            width: 30%;
            height: 30px;
            text-align: center;
            padding: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        .color-switcher {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .paint-btns {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: absolute;
            top: 20px;
            right: 40px;
        }
    </style>

</head>

<body>
    <div id="wrapper">
        <?php include './navigation.php' ?>

        <div id="page-wrapper">
        
            <div class="container-fluid">
                <?php
                    $step = filter_input(INPUT_GET, "step", FILTER_SANITIZE_SPECIAL_CHARS);

                    if (empty($step) || $step === "1") {
                        include_once("./paint-mixer-files/step1.php");
                    } else if ($step === "2") {
                        include_once("./paint-mixer-files/step2.php");
                    } else if ($step === "2a") {
                        include_once("./paint-mixer-files/step2-browse-color.php");
                    } else if ($step === "2b") {
                        include_once("./paint-mixer-files/step2-curated-color.php");
                    } else if ($step === "2c") {
                        include_once("./paint-mixer-files/step2-curate-choice.php");
                    } else if ($step === "2d") {
                        include_once("./paint-mixer-files/step2-search.php");
                    } else if ($step === "3") {
                        include_once("./paint-mixer-files/step3.php");
                    } else if ($step === "4") {
                        include_once("./paint-mixer-files/step4.php");
                    }
                ?>
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
                <div class="modal-body">




                    <form enctype="multipart/form-data" method="post" action="settings.php">
                        <fieldset>


                            <p>Firstname:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Firstname" name="user_firstname" type="text" value="<?php echo $user_firstname; ?>" required>


                            </div>


                            <p>Lastname:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Lastname" name="user_lastname" type="text" value="<?php echo $user_lastname; ?>" required>


                            </div>

                            <p>Address:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Address" name="user_address" type="text" value="<?php echo $user_address; ?>" required>


                            </div>

                            <p>Password:</p>
                            <div class="form-group">

                                <input class="form-control" placeholder="Password" name="user_password" type="password" value="<?php echo $user_password; ?>" required>


                            </div>

                            <div class="form-group">

                                <input class="form-control hide" name="user_id" type="text" value="<?php echo $user_id; ?>" required>


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

    <script>
        function updateColor() {
            var redValue = document.getElementById("redRange").value;
            var greenValue = document.getElementById("greenRange").value;
            var blueValue = document.getElementById("blueRange").value;

            var resultColor = document.getElementById("result-color");
            resultColor.style.backgroundColor = "rgb(" + redValue + ", " + greenValue + ", " + blueValue + ")";

            var resultText = document.getElementById("resultText");
            resultText.value = "#" + toHex(redValue) + toHex(greenValue) + toHex(blueValue);

            var redText = document.getElementById("valueBlue");
            redText.innerHTML = redValue;

            var greenText = document.getElementById("valueGreen");
            greenText.innerHTML = greenValue;

            var blueText = document.getElementById("valueRed");
            blueText.innerHTML = blueValue;

            // Compute surface area if width and height are provided
            updateSurfaceArea();
            updateTotalLiters();
        }

        // Function to compute surface area
        function updateSurfaceArea() {
            var width = parseFloat(document.getElementById("width").value);
            var height = parseFloat(document.getElementById("height").value);
            var surfaceArea = 0; // Default value
            if (!isNaN(width) && !isNaN(height)) {
                surfaceArea = (width * height).toFixed(2);
            }
            document.getElementById("surfaceArea").value = surfaceArea;
        }

        function updateTotalLiters() {
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
                var totalPrice = (totalLiters * pricePerLiter).toFixed(2);
                document.getElementById("liters").value = totalLiters;
                document.getElementById("totalPrice").value = totalPrice;
            }
        }


        function resetColor() {
            document.getElementById("redRange").value = 125;
            document.getElementById("greenRange").value = 125;
            document.getElementById("blueRange").value = 125;
            updateColor();
        }

        function downloadColor() {
            // Get the color-result box element
            var colorResultBox = document.getElementById("result-color");

            // Create a canvas element
            var canvas = document.createElement("canvas");
            canvas.width = colorResultBox.offsetWidth;
            canvas.height = colorResultBox.offsetHeight;

            // Get the canvas context
            var ctx = canvas.getContext("2d");

            // Draw the color-result box onto the canvas
            ctx.fillStyle = window.getComputedStyle(colorResultBox).backgroundColor;
            ctx.fillRect(0, 0, canvas.width, canvas.height);

            // Create a data URL for the canvas image
            var dataURL = canvas.toDataURL("image/png");

            // Create a download link
            var downloadLink = document.createElement("a");
            downloadLink.href = dataURL;
            downloadLink.download = "color_result.png";
            downloadLink.click();
        }


        function toHex(c) {
            var hex = parseInt(c).toString(16);
            return hex.length == 1 ? "0" + hex : hex;
        }

        // Attach event listeners to width and height input fields
        document.getElementById("width").addEventListener("input", updateSurfaceArea);
        document.getElementById("height").addEventListener("input", updateSurfaceArea);
        document.getElementById("coatsNumber").addEventListener("input", updateTotalLiters);
        document.getElementById("paint").addEventListener("change", updateTotalLiters);

        // Initialize color on page load
        updateSurfaceArea();
        updateTotalLiters();
    </script>
</body>

</html>
