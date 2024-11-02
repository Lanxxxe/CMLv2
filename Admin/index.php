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
         <div id="my-carousel" class="carousel slide hero-slide hidden-xs" data-ride="carousel">
            <!-- Indicators -->
            <ol class="carousel-indicators">
                <li data-target="#my-carousel" data-slide-to="0" class="active"></li>
                <li data-target="#my-carousel" data-slide-to="1"></li>
                <li data-target="#my-carousel" data-slide-to="2"></li>
                <li data-target="#my-carousel" data-slide-to="3"></li>
                <li data-target="#my-carousel" data-slide-to="4"></li>
                <li data-target="#my-carousel" data-slide-to="5"></li>
            </ol>

            <!-- Wrapper for slides -->
            <div class="carousel-inner" role="listbox">
                <div class="item active">

                    <img src="../assets/img/1-slide.jpg" alt="Hero Slide" style="width:100%;height:500px;">

                    <div class="carousel-caption">
                        <h1 style="font-family:Century Gothic"><b></b></h1>

                        <h2></h2>
                    </div>
                </div>
                <div class="item">
                    <img src="../assets/img/Slide2.jpg" alt="..." style="width:100%;height:500px;">

                    <div class="carousel-caption">

                    </div>
                </div>
                <div class="item">
                    <img src="../assets/img/Slide3.jpg" alt="..." style="width:100%;height:500px;">

                    <div class="carousel-caption">


                        <p></p>
                    </div>
                </div>

                <div class="item">
                    <img src="../assets/img/Slide4.jpg" alt="..." style="width:100%;height:500px;">

                    <div class="carousel-caption">


                        <p></p>
                    </div>
                </div>

                <div class="item">
                    <img src="../assets/img/Slide5.jpg" alt="..." style="width:100%;height:500px;">

                    <div class="carousel-caption">


                        <p></p>
                    </div>
                </div>

                <div class="item">
                    <img src="../assets/img/Slide6.jpg" alt="..." style="width:100%;height:500px;">

                    <div class="carousel-caption">


                        <p></p>
                    </div>
                </div>
            </div>

            <!-- Controls -->
            <a class="left carousel-control" href="#my-carousel" role="button" data-slide="prev">

              <span class="icon-prev"></span>

          </a>
          <a class="right carousel-control" href="#my-carousel" role="button" data-slide="next">

             <span class="icon-next"></span>
         </a>

         <!-- #my-carousel-->

     </div>


     <br />	
     <div class="alert alert-danger">

        &nbsp; &nbsp; THANK YOU FOR CHOOSING CML PAINT TRADING SHOP!
    </div>
    <br />

    <div class="alert alert-default" style="background-color:#033c73;">
     <p style="color:white;text-align:center;">
         &copy 2024 CML Paint Trading Shop | All Rights Reserved
     </p>

 </div>

</div>
</div>


</div>



</div>
<!-- /#wrapper -->


	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>
		
<script>
    document.querySelector("#nav_home").className = "active";

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
</script>
</body>
</html>
