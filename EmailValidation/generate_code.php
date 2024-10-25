<?php
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require '../vendor/autoload.php';
require '../db_conection.php';

function generateVerificationCode($length = 6)
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}


function verificationMessage($code) {
    return '
        <div class="verificationLetter" style="border: 1px solid #5555555f; font-family: Arial;">
          <div class="eHeader" style="background: #044C92; color: white; padding: 20px; font-weight: bold;">
            Account Identity Verification
          </div>
          
          <div class="eBody" style="padding: 12px;">
            <div>
              <strong>Dear User:</strong>
            </div>
            <p>
              You are in the process of verifying your identity. The verification code is: 
              <span style="color: #044C92; font-weight: bold; font-size: 1rem;">' . $code . '</span>
            </p>
            <p>
              It will be valid for 5 minutes. Please do not share it with anyone. If you did not request this verification, you can safely ignore this email.
            </p>
          </div>
          
          <div class="eFooter" style="border-top: 1px solid #5555555f; padding: 5px 10px; color: gray;">
            This is an automated email, Please do not reply.
          </div>
        </div>
    ';
}

$response = array();
$email = filter_input(INPUT_POST, 'ruser_email', FILTER_VALIDATE_EMAIL);

$mail = new PHPMailer(true);


try {
    $user_email = $_POST['ruser_email'];
    $check_user = "SELECT * FROM users WHERE user_email='$user_email'";
    $run_query = mysqli_query($dbcon, $check_user);

    if (mysqli_num_rows($run_query) > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Customer already exists, please try another one!';
        die(json_encode($response));
    } 


    include 'gmail_con.php';
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
    $mail->setFrom(EMAIL_ADDRESS, "CML Paint Trading");
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


    $mail->Body = verificationMessage($verification['code']);
    
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
    $dateTime = date('Y-m-d H:i:s');

    // Prepare the error log message
    $errorMessage = sprintf(
        "%s [%d] - %s in %s:%d",
        $dateTime,
        $e->getCode(),
        $e->getMessage(),
        $e->getFile(),
        $e->getLine()
    ) . PHP_EOL;
    error_log($errorMessage, 3, '../error.log');
} finally {
    echo json_encode($response);
}

