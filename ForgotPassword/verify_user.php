<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    require_once './sweetAlert.php';

    session_start();
    $email = $_SESSION['dest_email'] ?? null;

    if (empty($email) && empty($_SESSION['verification'])) {
        sweetAlert("error", "Invalid Session!",  "Please try again.", "OK", "./index.php");
    }

    require '../vendor/autoload.php';
    require '../EmailValidation/gmail_con.php';

    if (isset($_SESSION['verification']['expiration']) && $_SESSION['verification']['expiration'] <= time()) {
        sweetAlert("info", "Verification Code Expired!",  "Please Check your email and try again.", "OK");
        $email = $_SESSION['verification']['email'];
    }
} catch (Exception $e) {
    sweetAlert("error", "Ooops! Something went wrong", "Please try again later or contact support.", "OK", "./index.php");
    include '../error_log.php';
}

if (!empty($email)) {
    function generateVerificationCode($length = 6)
    {
        return substr(bin2hex(random_bytes($length)), 0, $length);
    }

    try {
        $response = array();
        $mail = new PHPMailer(true);
        //Server settings
        // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = HOST;                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = EMAIL_ADDRESS;                     //SMTP username
        $mail->Password   = EMAIL_PASSWORD;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom(EMAIL_ADDRESS);
        //Add a recipient (Name is optional)
        $mail->addAddress($email);

        $_SESSION['verification'] = [
            'code' => generateVerificationCode(),
            'expiration' => time() + 5 * 60,
            'email' => $email,
        ];

        //Content
        $verification = $_SESSION['verification'];
        $mail->isHTML(true);                                  //Set email format to HTML
        $mail->Subject = 'Verification Code';
        $mail->Body    = "<p>Your verification code is: <b>{$verification['code']}</b></p>";
        $mail->send();
        unset($_SESSION['dest_email']);
    } catch (Exception $e) {
        sweetAlert("error", "Message could not be sent!", "Mail Error: {$mail->ErrorInfo}", "OK", "./index.php");
    }

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
            <form action="./new_password.php" method="POST" enctype="multipart/form-data">
                <div class="text-center" style="margin-bottom: 16px;">
                    <b style="font-size: 28px; color: #33333399"> Forgot Password </b>
                </div>
                <div class="text-center" style="margin-bottom: 16px;">
                    <i class="fa fa-user" style="font-size: 22px;"></i>
                    <b style="font-size: 14px;"> <?php echo $_SESSION['verification']['email'] ?> </b>
                </div>
                <div class="form-group">
                        <label for="verificationCode">Enter the code we sent to you email account:</label>
                  <input type="text" minlength="6" maxlength="6" class="form-control" id="verificationCode" name="verification_code" placeholder="Verification Code" required="">
                </div>
                <button type="submit" class="btn btn-primary" name="submit" value="Submit">Submit</button>
                <button style="margin-top: 10px;" class="btn btn-danger" onclick="window.location.href='../index.php'">Cancel</button>
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

