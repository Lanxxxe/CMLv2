<?php
session_start();

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon" />

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" />
    <link href="assets/css/flexslider.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="navbar navbar-inverse navbar-fixed-top" id="menu">
        <div class="container">
            <div class="navbar-header">
                <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                    <span class="icon-bar"></span>
                </button>
                <a class="navbar-brand" href="#"><img class="" src="assets/img/logo.png" alt=""
                        style="height: 50px;" /></a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-center"
                    style="flex-grow: 1; display: flex; justify-content: center; margin-left: 200px;">
                    <li><a href="#home">HOME</a></li>
                    <li><a href="#testimonials-sec">BRANCHES</a></li>
                    <li><a href="#faculty-sec">MANAGERS</a></li>
                    <li><a href="#brand-sec">BRANDS</a></li>
                    <li><a href="#course-sec">ABOUT US</a></li>
                </ul>
                <ul class="nav navbar-nav navbar-right">
                    <li><a href="#features-sec" class="btn btn-warning btn-lg" data-toggle="modal" data-target="#ln"
                            style="height: auto; padding: 10px 20px; margin-top: 10px; margin-right: 10px;">SIGN IN</a>
                    </li>
                    <li><a href="#features-sec" class="btn btn-success btn-lg" data-toggle="modal" data-target="#su"
                            style="height: auto; padding: 10px 20px; margin-top: 10px;">SIGN UP</a></li>
                </ul>
            </div>
        </div>
    </div>



    <div class="home-sec" id="home">
        <div class="overlay">
            <div class="container">
                <div class="row text-center ">

                    <div class="col-lg-12  col-md-12 col-sm-12">

                        <div class="flexslider set-flexi" id="main-section">
                            <ul class="slides move-me">
                                <!-- Slider 01 -->
                                <li>
                                    <h3>HIGH QUALITY PAINT PRODUCTS</h3>
                                    <h1>WHAT ARE YOU WAITING FOR? SHOP NOW!</h1>
                                    <a id="shopNowBtn1" href="Customers" class=" shopNowBtn btn btn-danger btn-lg"
                                        style="font-size: 40px; border-radius: 90px; padding: 30px; font-family: Comic Sans MS;">SHOP
                                        NOW</a>
                                </li>
                                <!-- End Slider 01 -->

                                <!-- Slider 02 -->
                                <li>
                                    <h3>HIGH QUALITY PAINT PRODUCTS</h3>
                                    <h1>WE HAVE WHAT YOU NEED</h1>

                                    <a id="shopNowBtn2" href="Customers" class="shopNowBtn btn btn-danger btn-lg"
                                        style="font-size: 40px; border-radius: 90px; padding: 30px; font-family: Comic Sans MS;">SHOP
                                        NOW</a>
                                      




                                </li>
                                <!-- End Slider 02 -->

                                <!-- Slider 03 -->
                                <li>
                                    <h3>HIGH QUALITY PAINT PRODUCTS</h3>
                                    <h1>WE HAVE GOOD BRANDS</h1>
                                    <a id="shopNowBtn3" href="Customers" class="shopNowBtn btn btn-danger btn-lg"
                                        style="font-size: 40px; border-radius: 90px; padding: 30px; font-family: Comic Sans MS;">SHOP
                                        NOW</a>
                                </li>
                                <!-- End Slider 03 -->
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <!--HOME SECTION END-->
    <div class="tag-line">
        <div class="container">
            <div class="row  text-center">

                <div class="col-lg-12  col-md-12 col-sm-12">

                    <h2 data-scroll-reveal="enter from the bottom after 0.1s"><i class="fa fa-circle-o-notch"></i>
                        WELCOME TO CML PAINT TRADING <i class="fa fa-circle-o-notch"></i> </h2>
                </div>
            </div>
        </div>

    </div>
    <!--HOME SECTION TAG LINE END-->
    <div id="testimonials-sec" class="container set-pad">
        <div class="row text-center">
            <div class="col-lg-8 col-lg-offset-2 col-md-8 col-sm-8 col-md-offset-2 col-sm-offset-2">
                <h1 data-scroll-reveal="enter from the bottom after 0.2s" class="header-line">BRANCHES </h1>
                <p data-scroll-reveal="enter from the bottom after 0.3s">
                    Have a look of Our Paint Trading Branches
                </p>
            </div>

        </div>
        <!--/.HEADER LINE END-->


        <div class="row">


            <div class="col-lg-3  col-md-3 col-sm-3" data-scroll-reveal="enter from the bottom after 0.4s">
                <div class="about-div">
                    <center> <img src="assets/img/CML.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>CML PAINT TRADING</h3>
                        <hr />
                        <hr />
                        <p>
                            Address: CML Paint Trading, General Luis, 165 Bagbaguin, Caloocan, Metro Manila
                        </p>
                    </center>

                </div>
            </div>


            <div class="col-lg-3  col-md-3 col-sm-3" data-scroll-reveal="enter from the bottom after 0.4s">
                <div class="about-div">
                    <center> <img src="assets/img/TWIST.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>TWIST MIX <br />PAINT STATION</h3>
                        <hr />
                        <hr />
                        <p>
                            Address: Paso De Blas, Valenzuela City
                        </p>
                </div>
            </div>


            <div class="col-lg-3  col-md-3 col-sm-3" data-scroll-reveal="enter from the bottom after 0.4s">
                <div class="about-div">
                    <center> <img src="assets/img/MIRONY.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>MIRONY <br /> PAINT STATION</h3>
                        <hr />
                        <hr />
                        <p>
                            Address: RDM Building kingspoint Subdivision Cor, King henry St, Bagbag Novaliches, Quezon
                            City
                        </p>
                    </center>

                </div>
            </div>

            <div class="col-lg-3  col-md-3 col-sm-3" data-scroll-reveal="enter from the bottom after 0.4s">
                <div class="about-div">
                    <center> <img src="assets/img/CRRG.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>CRRG <br />PAINT TRADING</h3>
                        <hr />
                        <hr />
                        <p>Address: Stall 1, Lot H, Mt. View Subd. Brgy. Muzon City of San jose del Monte, Bulacan</p>

                    </center>
                    <br />

                </div>
            </div>


        </div>
    </div>
    <!-- FEATURES SECTION END-->
    <div id="faculty-sec">
        <div class="container set-pad">
            <div class="row text-center">
                <div class="col-lg-8 col-lg-offset-2 col-md-8 col-sm-8 col-md-offset-2 col-sm-offset-2">
                    <h1 data-scroll-reveal="enter from the bottom after 0.1s" class="header-line">BRANCH MANAGERS</h1>
                    <p data-scroll-reveal="enter from the bottom after 0.3s">

                    </p>
                </div>

            </div>
            <!--/.HEADER LINE END-->

            <div class="row">


                <div class="col-lg-6  col-md-6 col-sm-6" data-scroll-reveal="enter from the bottom after 0.4s">
                    <div class="faculty-div">
                        <img src="assets/img/CAL.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <img src="assets/img/VAL2.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>CALOOCAN / VALENZUELA</h3>
                        <hr />
                        <h4>Lhyne Gajopo Pacao / Michael Camposano</h4>
                        <p>

                        </p>
                    </div>
                </div>
                <div class="col-lg-6  col-md-6 col-sm-6" data-scroll-reveal="enter from the bottom after 0.5s">
                    <div class="faculty-div">
                        <img src="assets/img/QC2.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <img src="assets/img/SJDM.jpg" class="img-rounded" style="width:230px;height:200px;" />
                        <h3>QUEZON CITY / San Jose Del Monte </h3>
                        <hr />
                        <h4>Chona Gajopo / Charlone Gajopo</h4>
                        <p>

                        </p>
                    </div>
                </div>


            </div>
        </div>
    </div>

    <div id="brand-sec" class="container set-pad">
        <div class="row text-center">
            <div class="col-lg-8 col-lg-offset-2 col-md-8 col-sm-8 col-md-offset-2 col-sm-offset-2">
                <h1 data-scroll-reveal="enter from the bottom after 0.2s" class="header-line">Brands</h1>
                <p data-scroll-reveal="enter from the bottom after 0.3s">

                </p><br />


            </div>

        </div>
        <!--/.HEADER LINE END-->

        <div class="container">
            <div class="row">



                <img class="img img-rounded img-responsive" src="assets/img/Brands.png"
                    style="width:2000px;height:500px;" />



                <div class="col-lg-12">


                </div>

            </div>
        </div>
    </div>
    <br />
    <!-- FACULTY SECTION END-->
    <div id="course-sec" class="container set-pad">
        <div class="row text-center">
            <div class="col-lg-8 col-lg-offset-2 col-md-8 col-sm-8 col-md-offset-2 col-sm-offset-2">
                <h1 data-scroll-reveal="enter from the bottom after 0.1s" class="header-line">ABOUT US</h1>
                <p data-scroll-reveal="enter from the bottom after 0.3s">
                    CML Paint Trading is a paint retailing company providing high-quality products and targeted to have
                    exceptional customer service by selling paint products to consumers or other businesses.
                    <br />For more details. See the contact information below.
                </p>
            </div>

        </div>
        <!--/.HEADER LINE END-->

        <br />

        <div class="container">
            <div class="row set-row-pad">
                <div class="col-lg-4 col-md-4 col-sm-4   col-lg-offset-1 col-md-offset-1 col-sm-offset-1 "
                    data-scroll-reveal="enter from the bottom after 0.4s">

                    <h2><strong>Our Location </strong></h2>
                    <hr />
                    <div>
                        <h4>CML Paint Trading, General Luis, 165 Bagbaguin, Caloocan, Metro Manila</h4>
                        <h4>Philippines.</h4>
                        <h4>Zip code 1400</h4>

                    </div>


                </div>
                <div class=" col-lg-4 col-md-4 col-sm-4 col-lg-offset-1 col-md-offset-1 col-sm-offset-1"
                    data-scroll-reveal="enter from the bottom after 0.4s">

                    <h2><strong>Contact Us </strong></h2>
                    <hr />
                    <div>
                        <h4><strong>Call:</strong> 09156946074 </h4>
                        <h4><strong>Call:</strong> 09985801884 </h4>
                        <h4><strong>Gmail: </strong>cmlpainttrading@gmail.com</h4>
                    </div>
                </div>


            </div>
        </div>


    </div>
    </div>
    <!-- COURSES SECTION END-->
    <!-- Registration Modal -->
    <div class="modal fade" id="su" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">Customer Registration Form</h4>
                </div>
                <div class="modal-body">
                    <form id="registerForm" role="form" method="post">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Firstname" name="ruser_firstname" type="text"
                                    required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Lastname" name="ruser_lastname" type="text"
                                    required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Address" name="ruser_address" type="text"
                                    required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Email" name="ruser_email" type="email"
                                    required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="ruser_password" type="password"
                                    required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Gcash Number" name="ruser_mobile" type="number"
                                    required>
                            </div>
                        </fieldset>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-md btn-warning btn-block" name="register">Sign Up</button>
                    <button type="button" class="btn btn-md btn-success btn-block" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Script -->


    <!-- Your existing HTML content -->
    <div class="modal fade" id="ln" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 style="color:white" class="modal-title" id="myModalLabel">Login</h4>
                </div>
                <div class="modal-body">
                    <form id="loginForm" role="form" method="post">
                        <fieldset>
                            <div class="form-group">
                                <input class="form-control" placeholder="Email" name="user_email" type="email" required>
                            </div>
                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="user_password" type="password"
                                    required>
                            </div>
                        </fieldset>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-md btn-warning btn-block" name="user_login">Sign In</button>
                    <button type="button" class="btn btn-md btn-success btn-block" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="an" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">&times;</span></button>
                    <h4 style="color:white" class="modal-title" id="myModalLabel">Administrator Credentials</h4>
                </div>
                <div class="modal-body">


                    <form role="form" method="post" action="adminlogin.php">
                        <fieldset>


                            <div class="form-group">
                                <input class="form-control" placeholder="Username" name="admin_username" type="text"
                                    required>
                            </div>

                            <div class="form-group">
                                <input class="form-control" placeholder="Password" name="admin_password" type="password"
                                    required>
                            </div>

                        </fieldset>


                </div>
                <div class="modal-footer">

                    <button class="btn btn-md btn-warning btn-block" name="admin_login">Login</button>

                    <button type="button" class="btn btn-md btn-success btn-block" data-dismiss="modal">Cancel</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <br />
    <br />
    <br />
    <!-- Script -->
    <!-- CONTACT SECTION END-->
    <div id="footer">
        &copy 2024 CML Paint Trading Shop | All Rights Reserved <a style="color: #fff" target="_blank"></a>
    </div>
    <!-- FOOTER SECTION END-->


    <script>
        document.getElementById('loginForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('userlogin.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    console.log(data)
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Login Successful',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = data.redirect;
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Login Failed',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>

    <script>
        // Registration form submission
        document.getElementById('registerForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('register.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Registration Successful',
                            text: data.message,
                            confirmButtonText: 'OK'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'index.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Registration Failed',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var shopNowBtns = document.getElementsByClassName('shopNowBtn'); // Get all elements with class 'shopNowBtn'

        // Loop through each button in the collection
        Array.from(shopNowBtns).forEach(function (shopNowBtn) {
            shopNowBtn.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default link behavior

                var isLoggedIn = <?php echo isset($_SESSION['admin_username']) ? 'true' : 'false'; ?>;

                if (!isLoggedIn) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Sign In Required',
                        text: 'Please sign in to access the shop.',
                        confirmButtonText: 'OK'
                    });
                } else {
                    var href = shopNowBtn.getAttribute('href');
                    if (href) {
                        window.location.href = href;
                    } else {
                        console.error('No href attribute found on the SHOP NOW button.');
                    }
                }
            });
        });
    });
</script>



    <!--  Jquery Core Script -->
    <script src="assets/js/jquery-1.10.2.js"></script>
    <!--  Core Bootstrap Script -->
    <script src="assets/js/bootstrap.js"></script>
    <!--  Flexslider Scripts -->
    <script src="assets/js/jquery.flexslider.js"></script>
    <!--  Scrolling Reveal Script -->
    <script src="assets/js/scrollReveal.js"></script>
    <!--  Scroll Scripts -->
    <script src="assets/js/jquery.easing.min.js"></script>
    <!--  Custom Scripts -->
    <script src="assets/js/custom.js"></script>



</body>

</html>