<?php
require 'vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();


define('HOST', $_ENV['MAILER_HOST']);
define('EMAIL_ADDRESS', $_ENV['MAILER_EMAIL']);
define('EMAIL_PASSWORD', $_ENV['MAILER_PASS']);

function verifyCode()
{
    global $response;

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $response['status'] = 'error';
        $response['message'] = 'Invalid Request!';
        return false;
    }

    $verification = $_SESSION['verification'] ?? null;

    if (empty($verification)) {
        $response['status'] = 'error';
        $response['message'] = 'Try again! No verification code generated!';
        return false;
    }

    if ($verification['expiration'] <= time()) {
        $response['status'] = 'error';
        $response['message'] = 'Verification code expired!';
        return false;
    }

    return $verification['code'] === $_POST['verification_code'];
}

function congratulationsMessage($username, $redirect) {
    return '
        <div class="congratulationsLetter" style="border: 1px solid #5555555f; font-family: Arial;">
            <div class="eHeader" style="background: #044C92; color: white; padding: 20px; font-weight: bold;">
                Welcome to CML Paint Trading!
            </div>
            
            <div class="eBody" style="padding: 12px;">
                <div>
                    <strong>Dear ' . $username . ',</strong>
                </div>
                <p>
                    Thank you for registering with CML Paint Trading! Your account has been successfully created.
                </p>
                <p>
                    With your new account, you can:
                </p>
                <ul style="color: #044C92; margin: 15px 0;">
                    <li>Browse our extensive collection of quality paints and supplies</li>
                    <li>Save your favorite items to your wishlist</li>
                    <li>Track your orders</li>
                    <li>Access your purchase history and reorder easily</li>
                    <li>Manage your delivery addresses</li>
                </ul>
                <p>
                    To get started, simply log in to your account using your registered email address.
                </p>
                <div style="background: #f5f5f5; padding: 15px; margin: 20px 0; border-radius: 5px;">
                    <p style="margin: 0;">
                        If you have any questions or need assistance, our support team is here to help!
                    </p>
                </div>
            </div>
            
            <div class="eCta" style="padding: 0 12px 20px 12px; text-align: center;">
                <a href="' . $redirect . '" style="
                    background: #044C92;
                    color: white;
                    padding: 12px 25px;
                    text-decoration: none;
                    border-radius: 5px;
                    display: inline-block;
                    font-weight: bold;">
                    Visit Our Homepage
                </a>
            </div>
            
            <div class="eFooter" style="border-top: 1px solid #5555555f; padding: 5px 10px; color: gray;">
                This is an automated email, Please do not reply.
            </div>
        </div>
    ';
}

function sendEmail($email, $username, $redirect) {
    try {
        $mail = new PHPMailer(true);
        //Server settings
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = HOST;                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = EMAIL_ADDRESS;                     //SMTP username
        $mail->Password   = EMAIL_PASSWORD;                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;            //Enable implicit TLS encryption
        $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

        //Recipients
        $mail->setFrom(EMAIL_ADDRESS, "CML Paint Trading");
        //Add a recipient (Name is optional)
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);                                     // Set email format to HTML
        $mail->Subject = 'Account Registration Success';
        $mail->Body    = congratulationsMessage($username, $redirect);

        $mail->send();
        return json_encode([
            "status" => "error",
            "message" => "message sent",
        ]);
    } catch (Exception $e) {
        return json_encode([
            "status" => "error",
            "message" => $e->getMessage(),
        ]);
    }
}

try {
    session_start();
    require "db_conection.php";
    $response = array();

    $redirect = $_ENV['WEBSITE'];
    $user_email = $_POST['ruser_email'];
    $user_password = $_POST['ruser_password'];
    $user_firstname = $_POST['ruser_firstname'];
    $user_lastname = $_POST['ruser_lastname'];
    $user_address = $_POST['ruser_address'];
    $user_mobile = $_POST['ruser_mobile'];

    $check_user = "SELECT * FROM users WHERE user_email='$user_email'";
    $run_query = mysqli_query($dbcon, $check_user);

    if (mysqli_num_rows($run_query) > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Customer already exists, please try another one!';
        die(json_encode($response));
    } else if (verifyCode()) {
        // Prepare and execute the insert statement
        $stmt = $dbcon->prepare("INSERT INTO users (user_email, user_password, user_firstname, user_lastname, user_address, user_mobile, type) VALUES (?, ?, ?, ?, ?, ?, 'Customer')");
        $stmt->bind_param("ssssss", $user_email, $user_password, $user_firstname, $user_lastname, $user_address, $user_mobile);


        if ($stmt->execute()) {
            sendEmail($user_email, $user_firstname, $redirect);
            $response['status'] = 'success';
            $response['message'] = 'Data successfully saved, you may now login!';
        } else {
            throw new Exception('Registration failed, please try again!');
        }
    } else {
        throw new Exception('Invalid code, please try again!');
    }
} catch (Exception $e) {
    $response['status'] = 'error';
    $response['message'] = $e->getMessage();
    include './error_log.php';
}

echo json_encode($response);
