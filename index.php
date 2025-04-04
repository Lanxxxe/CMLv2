﻿<?php
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
    <style>
        #loading {
          display: flex;
          position: fixed;
          top: 0;
          left: 0;
          z-index: 1000;
          width: 100vw;
          height: 100vh;
          background-color: rgba(192, 192, 192, 0.5);
          background-image: url("ForgotPassword/images/loading.gif");
          background-repeat: no-repeat;
          background-position: center;
        }

        .hide {
          display: none !important;
        }

        #footer {
            background: #333;
            color: #fff;
            padding: 15px 0;
        }
        #footer a {
            color: #fff;
            text-decoration: none;
            margin: 0 10px; /* Adds space between links */
        }
        #footer a:hover {
            text-decoration: underline;
            color: rgb(100, 121, 244);
        }

    </style>
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
                    style="flex-grow: 1; display: flex; justify-content: center; margin-left: 250px;">
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
                <form id="registerForm" role="form" method="post">
                    <div class="modal-body">
                            <fieldset>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Firstname" name="ruser_firstname" type="text"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Lastname" name="ruser_lastname" type="text"
                                        required>
                                </div>
                                <!-- <div class="form-group">
                                    <input class="form-control" placeholder="Address" name="ruser_address" type="text"
                                        required>
                                </div> -->
                                <div class="form-group">
                                    <input class="form-control" placeholder="Brgy/Subdivision" name="ruser_brgy" type="text"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Municipality" name="ruser_municipality" type="text"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="City/Province" name="ruser_CProvince" type="text"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Email" name="ruser_email" type="email"
                                        required>
                                </div>
                                <div class="form-group">
                                    <input class="form-control" placeholder="Password" name="ruser_password" type="password"
                                        required onkeyup="checkPassword(this)">
                                    <small id="passwordFeedback" class="form-text text-white"></small>
                                </div>
                                <div class="form-group">
                                    <input class="form-control mobile-number-input" placeholder="Mobile Number" name="ruser_mobile" type="text" minlength="11" maxlength="11" pattern="09[0-9]{9}"
                                        required>
                                </div>
                            </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-md btn-warning btn-block" name="register">Sign Up</button>
                        <button type="button" class="btn btn-md btn-success btn-block" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Script -->

    <div class="modal fade" id="verificationModal" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
        <div class="modal-dialog modal-sm">
            <div style="color:white;background-color:#008CBA" class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                    <h4 class="modal-title" id="myModalLabel">Email Verification</h4>
                </div>
                <form id="verificationForm" role="form" method="post">
                    <div class="modal-body">
                            <p class="form-label text-center" for="vCode">Enter the code we sent to you email account.</p>
                            <fieldset>
                                <div class="form-group">
                                    <input id="vCode" class="form-control" placeholder="Enter Verification Code" name="verification_code" maxlength="6" minlength="6" type="text"
                                        required>
                                </div>
                            </fieldset>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-md btn-warning btn-block" name="verify">Submit</button>
                        <button type="button" class="btn btn-md btn-success btn-block" data-dismiss="modal">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                    <p class="text-center">
                        <a style="color: white;" href="ForgotPassword/index.php">Forgot Password</a>
                    </p>
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
                <form role="form" method="post" action="adminlogin.php">
                    <div class="modal-body">


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
                    </div>
                </form>
            </div>
        </div>
    </div>
    <br />
    <br />
    <br />

    <footer id="footer" class="bg-dark text-white">
        <div class="container">
            <div class="text-center">
                <a href="terms.php" class="text-white">Terms and Conditions</a>
                <a href="privacy.php" class="text-white">Privacy Policy</a>
            </div>
            <div class="text-center">
                &copy; 2024 CML Paint Trading Shop | All Rights Reserved
            </div>
        </div>
    </footer>

    <div id="loading" class="hide"></div>
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




    <script>
        function checkPassword(input) {
            // Get the password value
            var password = input.value;

            // Regular expression to check for at least one uppercase letter, one lowercase letter, and one number
            var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$/;
            
            // Check if the password matches the regex
            if (passwordRegex.test(password)) {
                // Password is valid
                input.classList.remove("is-invalid");
                input.classList.add("is-valid");
                document.getElementById("passwordFeedback").textContent = "Password is valid!";
            } else {
                // Password is invalid
                input.classList.remove("is-valid");
                input.classList.add("is-invalid");
                document.getElementById("passwordFeedback").textContent = "* Password must contain at least one uppercase letter, one lowercase letter, and one number, and be at least 8 characters long.";
            }
        }


        document.querySelectorAll('.mobile-number-input').forEach(element => {
            element.addEventListener('input', event => {
                let txt = "";
                for (const c of element.value) {
                    if ("0123456789".includes(c)) {
                        txt += c;
                    }
                }
                element.value = txt;
            });
        });


    document.addEventListener('DOMContentLoaded', function () {
        const setVisible = (elementOrSelector, visible) => {
          const element = document.querySelector(elementOrSelector);
          if (visible) {
            element.classList.remove("hide");
          } else {
            element.classList.add("hide");
          }
        };

        function hideModal(modalID) {
          document.getElementById(modalID).classList.remove('show');
          document.getElementById(modalID).style.display = 'none';
          document.querySelector('body').classList.remove('modal-open');
          document.querySelector('.modal-backdrop').remove();
        }

        document.getElementById('loginForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const formData = new FormData(this);

            fetch('userlogin.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
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

        let formData = null;
        function registerUser() {
            setVisible("#loading", true);
            fetch('register.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    setVisible("#loading", false);
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
                .catch(error => {
                    console.error('Error:', error)
                });
        }

        // Registration form submission
        document.getElementById('registerForm').addEventListener('submit', function (event) {
            event.preventDefault();
            formData = new FormData(this);
            setVisible("#loading", true);
            hideModal('su');
            fetch('./EmailValidation/generate_code.php', {
                method: 'POST',
                body: formData,
            })
                .then(response => response.json())
                .then(data => {
                    if (!data.verification_generated) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Verification Failed',
                            text: data.message,
                            confirmButtonText: 'OK'
                        });
                    } else {
                        $('#verificationModal').modal('show');
                    }
                    setVisible("#loading", false);
                })
                .catch(console.error);
        });

        document.getElementById('verificationForm').addEventListener('submit', function (event) {
            event.preventDefault();
            const vcode = this.querySelector('#vCode').value;
            formData.append('verification_code', vcode);
            registerUser();
        });
        var shopNowBtns = document.getElementsByClassName('shopNowBtn'); // Get all elements with class 'shopNowBtn'

        // Loop through each button in the collection
        Array.from(shopNowBtns).forEach(function (shopNowBtn) {
            shopNowBtn.addEventListener('click', function (event) {
                event.preventDefault(); // Prevent default link behavior

                window.location.href = 'viewshop.php'
                // var isLoggedIn = <?php echo isset($_SESSION['admin_username']) ? 'true' : 'false'; ?>;

                // if (!isLoggedIn) {
                //     Swal.fire({
                //         icon: 'info',
                //         title: 'Sign In Required',
                //         text: 'Please sign in to access the shop.',
                //         confirmButtonText: 'OK'
                //     });
                // } else {
                //     var href = shopNowBtn.getAttribute('href');
                //     if (href) {
                //         window.location.href = href;
                //     } else {
                //         console.error('No href attribute found on the SHOP NOW button.');
                //     }
                // }
            });
        });
    });
</script>



</body>

</html>
