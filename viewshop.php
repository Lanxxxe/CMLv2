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
    <title>CML Paint Trading - Shop</title>
    <link rel="shortcut icon" href="assets/img/logo.png" type="image/x-icon" />

    <link href="assets/css/bootstrap.css" rel="stylesheet" />
    <link href="assets/css/font-awesome.min.css" rel="stylesheet" />
    <link href="assets/css/flexslider.css" rel="stylesheet" />
    <link href="assets/css/style.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="Customers/css/local.css" />
    <link rel="stylesheet" type="text/css" href="Customers/bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="Customers/font-awesome/css/font-awesome.min.css" />
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

        body {
            background-color: #fff;
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
                    style="flex-grow: 1; display: flex; justify-content: center; margin-left: 200px;">
                    <li><a href="index.php">HOME</a></li>
                    <li><a href="index.php#testimonials-sec">BRANCHES</a></li>
                    <li><a href="index.php#faculty-sec">MANAGERS</a></li>
                    <li><a href="index.php#brand-sec">BRANDS</a></li>
                    <li><a href="index.php#course-sec">ABOUT US</a></li>
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



    <div class="shop-sec" id="viewshop">
        <div id="page-wrapper">
                <div class="alert alert-default" style="color:white;background-color:#008CBA;margin-top: 7rem;">
                    <center>
                        <h3> <span class="glyphicon glyphicon-shopping-cart"></span> This is our Paint & Brush stocks, Shop now!</h3>
                    </center>
                </div>

                <br />
                <label for="filter">Filter</label>
                <input class="form-control" type="text" id="search" placeholder="Search items..." oninput="filterText()" style="width: 30%; margin-bottom: 10px;">

                <div style="display: flex; width: 100%; align-items: center; gap: 10px;">
                    <div class="form-group">
                        <select id="filter1" class="form-control" onchange="updateFilterOptions()">
                            <option value="All">All</option>
                            <option value="Wall">Wall</option>
                            <option value="Wood">Wood</option>
                            <option value="Metal">Metal</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select id="filter2" class="form-control" onchange="updateFilterOptions()">
                            <option value="Interior">Interior</option>
                            <option value="Exterior">Exterior</option>
                            <option value="Tools">Tools</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <select id="filter3" class="form-control" onchange="filterItems()">
                            <option value="Flat/Matte">Flat/Matte</option>
                            <option value="Gloss">Gloss</option>
                            <option value="Primer">Primer</option>
                            <option value="Acrytex">Acrytex</option>
                            <option value="QDE">QDE</option>
                            <option value="Oil Paint">Oil Paint</option>
                            <option value="Enamel">Enamel</option>
                            <option value="Alkyds">Alkyds</option>
                            <option value="Acrylic">Acrylic</option>
                            <option value="Latex">Latex</option>
                            <option value="Brush">Brush</option>
                            <option value="Tape">Tape</option>
                        </select>
                    </div>
                </div>


                <?php
                require 'vendor/autoload.php';

                use Dotenv\Dotenv;

                $dotenv = Dotenv::createImmutable(__DIR__ . '/../');
                $dotenv->load();

                $dbHost = $_ENV['DB_HOST'];
                $dbName = $_ENV['DB_NAME'];
                $dbUser = $_ENV['DB_USER'];
                $dbPass = $_ENV['DB_PASS'];

                $conn=mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

                if (!$conn) {
                    die("Connection failed: " . mysqli_connect_error());
                }

                $start = 0;
                $limit = 8;
                $id = 1; // Initialize $id with a default value

                if (isset($_GET['id']) && !empty($_GET['id'])) {
                    $id = intval($_GET['id']);
                    $start = ($id - 1) * $limit;
                }

                $query = mysqli_query($conn, "SELECT * FROM items LIMIT $start, $limit");

                while ($query2 = mysqli_fetch_assoc($query)) {
                    echo "<div class='col-sm-3 panel-item' data-type='" . $query2['type'] . "' data-brand='" . $query2['brand_name'] . "'>
                <div class='panel panel-default' style='border-color:#008CBA;'>
                    <div class='panel-heading' style='color:white;background-color: #033c73;'>
                        <center> 
                            <textarea style='text-align:center;background-color: white;' class='form-control' rows='1' disabled>" . $query2['brand_name'] . "</textarea>
                        </center>
                    </div>
                    <div class='panel-body'>
                        <a class='fancybox-buttons' href='Admin/item_images/" . $query2['item_image'] . "' data-fancybox-group='button' title='Page " . $id . "- " . $query2['item_name'] . "'>
                            <img src='Admin/item_images/" . $query2['item_image'] . "' class='img img-thumbnail' style='width:350px;height:150px;' />
                        </a>
                        <center><h4> Item Name: " . $query2['item_name'] . " (" . $query2['gl'] . ") </h4></center>
                        <center><h4> Price: &#8369; " . $query2['item_price'] . " </h4></center>
                        <a class='addToCart btn btn-block btn-danger' href='add_to_cart.php?cart=" . $query2['item_id'] . "'><span class='glyphicon glyphicon-shopping-cart'></span> Add to cart</a>
                    </div>
                </div>
            </div>";
                }

                echo "<div class='container'></div>";

                $rows = mysqli_num_rows(mysqli_query($conn, "SELECT * FROM items"));
                $total = ceil($rows / $limit);
                echo "<br /><ul class='pager'>";
                if ($id > 1) {
                    echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id - 1) . "'>Previous Page</a><li>";
                }
                if ($id != $total) {
                    echo "<li><a style='color:white;background-color : #033c73;' href='?id=" . ($id + 1) . "' class='pager'>Next Page</a></li>";
                }
                echo "</ul>";

                echo "<center><ul class='pagination pagination-lg'>";
                for ($i = 1; $i <= $total; $i++) {
                    if ($i == $id) {
                        echo "<li class='pagination active'><a style='color:white;background-color : #033c73;'>" . $i . "</a></li>";
                    } else {
                        echo "<li><a href='?id=" . $i . "'>" . $i . "</a></li>";
                    }
                }
                echo "</ul></center>";
                ?>

                <br />
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
                                    <input class="form-control" placeholder="Mobile Number" name="ruser_mobile" type="number"
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
    <!-- Script -->
    <!-- CONTACT SECTION END-->
    <div id="footer">
        &copy 2024 CML Paint Trading Shop | All Rights Reserved <a style="color: #fff" target="_blank"></a>
    </div>
    <!-- FOOTER SECTION END-->

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




    <script>function updateFilterOptions() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3Select = document.getElementById('filter3');

            var filter3Options = [];

            if (filter1 === 'wall') {
                if (filter2 === 'interior') {
                    filter3Options = ['Flat/Matte', 'Gloss', 'Primer'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrytex', 'Primer'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'wood') {
                if (filter2 === 'interior') {
                    filter3Options = ['QDE', 'Oil Paint'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Enamel'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            } else if (filter1 === 'metal') {
                if (filter2 === 'interior') {
                    filter3Options = ['Alkyds'];
                } else if (filter2 === 'exterior') {
                    filter3Options = ['Acrylic', 'Latex'];
                } else if (filter2 === 'tools') {
                    filter3Options = ['Brush', 'Tape'];
                }
            }

            // Update filter3 options
            filter3Select.innerHTML = ''; // Clear current options

            for (var i = 0; i < filter3Options.length; i++) {
                var option = document.createElement('option');
                option.value = filter3Options[i].toLowerCase();
                option.textContent = filter3Options[i];
                filter3Select.appendChild(option);
            }

            filterItems();
        }

        // Function to filter items based on filter1, filter2, and filter3 selections
        function filterItems() {
            var filter1 = document.getElementById('filter1').value.toLowerCase();
            var filter2 = document.getElementById('filter2').value.toLowerCase();
            var filter3 = document.getElementById('filter3').value.toLowerCase();
            var panels = document.getElementsByClassName('panel-item');

            Array.from(panels).forEach(panel => {
                const itemType = panel.getAttribute("data-type").toLowerCase();
                if (itemType.includes(filter1) || itemType.includes(filter2) || itemType.includes(filter3)) {
                    panel.style.display = "";
                } else {
                    panel.style.display = "none";
                }
            });
        }

        // Initialize filter2 options based on filter1 on page load
        // document.addEventListener('DOMContentLoaded', updateFilterOptions);
        document.addEventListener('DOMContentLoaded', function () {
            updateFilterOptions();

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
            var addToCartBtns = document.getElementsByClassName('addToCart'); // Get all elements with class 'shopNowBtn'

            // Loop through each button in the collection
            Array.from(addToCartBtns).forEach(function (shopNowBtn) {
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



</body>

</html>
