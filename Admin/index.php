<?php
session_start();

if(!$_SESSION['admin_username'])
{

    header("Location: ../index.php");
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
                    <li class="active"><a href="index.php"> &nbsp; &nbsp; &nbsp; Home</a></li>
                    <li><a href="orderdetails.php"> &nbsp; &nbsp; &nbsp; Admin Order Dashboard</a></li>
                    <li><a data-toggle="modal" data-target="#uploadModal"> &nbsp; &nbsp; &nbsp; Add Paint Products</a></li>
                    <li><a data-toggle="modal" data-target="#uploadItems"> &nbsp; &nbsp; &nbsp; Add Items</a></li>
                    <li><a href="items.php"> &nbsp; &nbsp; &nbsp; Item Management</a></li>
                    <li><a href="customers.php"> &nbsp; &nbsp; &nbsp; Customer Management</a></li>
                    <li><a href="salesreport.php"> &nbsp; &nbsp; &nbsp; Sales Report</a></li>
                    <li><a href="logout.php"> &nbsp; &nbsp; &nbsp; Logout</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right navbar-user">
                    <li class="dropdown messages-dropdown">
                        <a href="#"><i class="fa fa-calendar"></i>  <?php
                        $Today=date('y:m:d');
                        $new=date('l, F d, Y',strtotime($Today));
                        echo $new; ?></a>
                        
                    </li>
                    <li class="dropdown user-dropdown">
                        <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-user"></i> <?php   extract($_SESSION); echo $admin_username; ?><b class="caret"></b></a>
                        <ul class="dropdown-menu">

                            <li><a href="logout.php"><i class="fa fa-power-off"></i> Log Out</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </nav>

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
