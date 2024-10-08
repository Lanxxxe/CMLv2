<?php
session_start();

function alertError($msg)
{
    echo "<script>
    alert('$msg');
    window.location.href = './verify_user.php';
    </script>";
    exit;
}

function verifyCode()
{
    $verification = $_SESSION['verification'] ?? null;

    if (empty($verification)) {
        alertError('Try again! No verification code generated!');
    }

    if ($verification['expiration'] <= time() ) {
        alertError('Verification code expired!');
    }

    return $verification['code'] === $_POST['verification_code'];
}

function generateCsrfToken()
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function generateRandString($length = 4)
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

$s = generateRandString();
$q = null;
if (!verifyCode()) {
    alertError("Invalid Verificaton Code! Please try again.");
} else {
    $verification = $_SESSION['verification'] ?? null;
    $_SESSION['input_verification_code'] = $_POST['verification_code'];
    $q = $s . md5($s . $_SESSION['verification']['email']);
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
            <form action="change_password.php" method="post">
                <input type="hidden" name="csrf_token" value="<?php echo generateCsrfToken() ?>">
                <input type="hidden" name="q" value="<?php echo $q ?>">
            <div class="form-group">
              <label for="newPassword">Enter New Password:</label>
              <input type="password" class="form-control" id="newPassword" name="password" required placeholder="Enter New Password">
            </div>
            <div class="form-group">
              <label for="confirmPassword">Confirm New Password:</label>
              <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required placeholder="Confirm New Password">
            </div>
            <button type="submit" class="btn btn-primary">Update Password</button>
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
