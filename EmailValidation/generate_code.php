<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';
require 'gmail_con.php';

function generateVerificationCode($length = 6)
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

$response = array();
$email = filter_input(INPUT_POST, 'ruser_email', FILTER_VALIDATE_EMAIL);

$mail = new PHPMailer(true);

try {
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
    ];

    //Content
    $verification = $_SESSION['verification'];
    $mail->isHTML(true);                                  //Set email format to HTML
    $mail->Subject = 'Verification Code';
    $mail->Body    = "<p>Your verification code is: <b>{$verification['code']}</b></p>";
    if ($mail->send()) {
        $response['verification_generated'] = true;
        $response['message'] = 'Message has been sent';
    } else {
        $response['verification_generated'] = false;
        $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }


} catch (Exception $e) {
    $response['verification_code'] = false;
    $response['message'] = "Message could not be sent. Internal Server Error!";
    // $response['message'] = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    include '../error_log.php';
} finally {
    echo json_encode($response);
}

