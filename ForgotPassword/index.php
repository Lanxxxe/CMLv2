<?php
try {
    session_start();
    unset($_SESSION['verification']);
    require_once './sweetAlert.php';
    
    if (isset($_SESSION['user_email'])) {
        $_SESSION['dest_email'] = $_SESSION['user_email'];
        header("Location: verify_user.php");
        exit;
    } else if (isset($_POST['verify_email']) && isset($_POST['email'])) {
        $email = trim(filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL));
    
        if (empty($email)) {
            sweetAlert("error", "Empty Email", "Email is required!", "OK", "./index.php");
            exit;
        }
    
        include_once '../db_conection.php';
        $stmt = mysqli_prepare($dbcon, "SELECT user_email FROM users WHERE user_email = ?");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
    
        $rows = mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        if (!$rows) {
            sweetAlert("error", "User not found!", "Please enter your email!", "OK", "./index.php");
            exit;
        }
    
        $_SESSION['dest_email'] = $email;
        header("Location: verify_user.php");
        exit;
    }
    
} catch (Exception $e) {
    sweetAlert("error", "Ooops! Something went wrong", "Please try again later or contact support.", "OK", "./index.php");
    include '../error_log.php';
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
    <link rel="stylesheet" href="./css/loading.css">
</head>

<body>

    <div class="page">

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
                <form class="submitFormLoading" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']) ?>" method="POST" enctype="multipart/form-data">
                    <div class="text-center" style="margin-bottom: 16px;">
                        <b style="font-size: 28px; color: #33333399"> Forgot Password </b>
                    </div>
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

    </div>
    <div id="loading"></div>

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
    <script src="./js/loading.js"></script>


</body>

</html>

