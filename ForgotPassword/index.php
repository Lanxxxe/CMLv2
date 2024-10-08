<?php
session_start();
unset($_SESSION['verification']);

if (isset($_SESSION['user_email'])) {
    $_SESSION['dest_email'] = $_SESSION['user_email'];
    header("Location: verify_user.php");
    exit;
} else if (isset($_POST['verify_email']) && isset($_POST['email'])) {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    if (empty($email)) {
        echo "<script>
        alert('$msg');
        window.location.href = './index.php';
        </script>";
        exit;
    }
    $_SESSION['dest_email'] = $email;
    header("Location: verify_user.php");
    exit;
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />

    <link href="./css/forgot-password.css" rel="stylesheet"/>
    <link href="../assets/css/bootstrap.css" rel="stylesheet" />
    <link href="../assets/css/font-awesome.min.css" rel="stylesheet" />
    <link href="../assets/css/flexslider.css" rel="stylesheet" />
    <link href="../assets/css/style.css" rel="stylesheet" />
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
                <a class="navbar-brand" href="#"><img class="" src="../assets/img/logo.png" alt=""
                        style="height: 50px;" /></a>
            </div>
            <div class="navbar-collapse collapse">
                <ul class="nav navbar-nav navbar-center"
                    style="flex-grow: 1; display: flex; justify-content: center; margin-left: 200px;">
                    <li><a href="../index.php#home">HOME</a></li>
                    <li><a href="../index.php#testimonials-sec">BRANCHES</a></li>
                    <li><a href="../index.php#faculty-sec">MANAGERS</a></li>
                    <li><a href="../index.php#brand-sec">BRANDS</a></li>
                    <li><a href="../index.php#course-sec">ABOUT US</a></li>
                </ul>
            </div>
        </div>
    </div>

    <div class="forgot-password-form">
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
            <div class="form-group">
              <label for="userEmail">Enter Email:</label>
              <input type="email" class="form-control" id="userEmail" name="email" required="">
            </div>
            <button type="submit" class="btn btn-primary" name="verify_email">Verify Email</button>
        </form>
    </div>

    <!-- CONTACT SECTION END-->
    <div id="footer" style="position: fixed; bottom: 0; width: 100%;">
        &copy 2024 CML Paint Trading Shop | All Rights Reserved <a style="color: #fff" target="_blank"></a>
    </div>
    <!-- FOOTER SECTION END-->

    <!--  Jquery Core Script -->
    <script src="../assets/js/jquery-1.10.2.js"></script>
    <!--  Core Bootstrap Script -->
    <script src="../assets/js/bootstrap.js"></script>
    <!--  Flexslider Scripts -->
    <script src="../assets/js/jquery.flexslider.js"></script>
    <!--  Scrolling Reveal Script -->
    <script src="../assets/js/scrollReveal.js"></script>
    <!--  Scroll Scripts -->
    <script src="../assets/js/jquery.easing.min.js"></script>
    <!--  Custom Scripts -->
    <script src="../assets/js/custom.js"></script>



</body>

</html>

