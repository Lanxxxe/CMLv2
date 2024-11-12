<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();


define('HOST', $_ENV['MAILER_HOST']);
define('EMAIL_ADDRESS', $_ENV['MAILER_EMAIL']);
define('EMAIL_PASSWORD', $_ENV['MAILER_PASS']);


if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
}

include './approve_email.php';

function sendEmailApprovedOrder($payment_id) {
    include './config.php';
    try {
        $paymentformQ = $DB_con->prepare('SELECT email, payment_type FROM paymentform WHERE id = :id');
        $paymentformQ->execute([':id' => $payment_id]);
        $paymentformFetch = $paymentformQ->fetch(PDO::FETCH_ASSOC);
        $email = $paymentformFetch['email'];
        $payment_type = $paymentformFetch['payment_type'];

        $amountCalculation = $DB_con->prepare("
            SELECT 
                pf.id AS payment_id,
                pf.firstname,
                pf.lastname,
                pf.payment_type,
                pf.payment_status,
                COALESCE(
                    (
                        SELECT pt.amount 
                        FROM payment_track pt 
                        WHERE pt.payment_id = pf.id 
                        ORDER BY pt.date_tracked DESC 
                        LIMIT 1
                    ),
                    pf.amount
                ) AS latest_payment_amount,
                COALESCE(
                    (
                        SELECT pt.date_tracked 
                        FROM payment_track pt 
                        WHERE pt.payment_id = pf.id 
                        ORDER BY pt.date_tracked DESC 
                        LIMIT 1
                    ),
                    pf.created_at
                ) AS payment_date,
                (
                    COALESCE(pf.amount, 0) + 
                    CASE 
                        WHEN pf.payment_type = 'Installment' THEN 
                            COALESCE(
                                (
                                    SELECT SUM(pt.amount) 
                                    FROM payment_track pt 
                                    WHERE pt.payment_id = pf.id 
                                    AND pt.status = 'Confirmed'
                                ),
                                0
                            )
                        ELSE 0
                    END
                ) AS total_amount_paid,
                (
                    SELECT SUM(od.order_total) 
                    FROM orderdetails od 
                    WHERE od.payment_id = pf.id
                ) AS total_order_amount,
                (
                    SELECT SUM(od.order_total) 
                    FROM orderdetails od 
                    WHERE od.payment_id = pf.id
                ) - 
                (
                    COALESCE(pf.amount, 0) + 
                    CASE 
                        WHEN pf.payment_type = 'Installment' THEN 
                            COALESCE(
                                (
                                    SELECT SUM(pt.amount) 
                                    FROM payment_track pt 
                                    WHERE pt.payment_id = pf.id 
                                    AND pt.status = 'Confirmed'
                                ),
                                0
                            )
                        ELSE 0
                    END
                ) AS remaining_balance
            FROM 
                paymentform pf
            WHERE
                pf.id = :pfid
            ORDER BY 
                payment_date DESC;
            ");
        $amountCalculation->execute([':pfid' => $payment_id]);
        $amountCalculationFetch = $amountCalculation->fetch(PDO::FETCH_ASSOC);
        $latest_payment_amount = $amountCalculationFetch['latest_payment_amount'] ?? 0;
        $total_amount_paid = $amountCalculationFetch['total_amount_paid'] ?? 0;
        $remaining_balance = $amountCalculationFetch['remaining_balance'] ?? 0;



        $orderDetails = $DB_con->prepare('SELECT orderdetails.*, users.user_email, users.user_firstname, users.user_lastname, users.user_mobile FROM orderdetails JOIN users ON orderdetails.user_id = users.user_id WHERE payment_id = :payment_id');
        $orderDetails->execute([':payment_id' => $payment_id]);
        $orders = $orderDetails->fetchAll(PDO::FETCH_ASSOC);

        $messageBody = composedMessage($orders, $payment_type, $latest_payment_amount, $total_amount_paid, $remaining_balance);

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
        $mail->Subject = 'Order Approved';
        $mail->Body = $messageBody;
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

require_once 'config.php';

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" />
    <link rel="stylesheet" type="text/css" href="css/local.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.3/css/jquery.dataTables.min.css">
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>
    
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        /* Add any custom styles here */
        .table {
            vertical-align: middle;
        }
    </style>

</head>
<body>

    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-danger">
                <center><h3><strong>Invoice Return Items</strong></h3></center>
            </div>

            <?php
                // Fetch return payments from database
                $stmt = $DB_con->prepare("SELECT 
                    rp.return_payment_id,
                    rp.user_id,
                    rp.return_status,
                    rp.proof_of_payment,
                    rp.amount_return,
                    CONCAT(u.user_firstname, ' ', u.user_lastname) as customer_name
                    FROM return_payments rp
                    LEFT JOIN users u ON rp.user_id = u.user_id
                    ORDER BY rp.return_payment_id DESC");
                $stmt->execute();
                $return_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                ?>

                <!-- Return Payments Table -->
                <!-- Return Payments Table -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Return Payments History</h3>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Return ID</th>
                                        <th>Customer Name</th>
                                        <th>Status</th>
                                        <th>Amount Returned</th>
                                        <th>Proof of Return</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($return_payments): ?>
                                        <?php foreach ($return_payments as $payment): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($payment['return_payment_id']); ?></td>
                                                <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $payment['return_status'] === 'Refunded' ? 'success' : 'warning'; ?>">
                                                        <?php echo htmlspecialchars($payment['return_status']); ?>
                                                    </span>
                                                </td>
                                                <td>₱<?php echo number_format($payment['amount_return'], 2); ?></td>
                                                <td>
                                                    <a role="button" class="btn btn-sm btn-info view-proof" data-toggle="modal" data-target="#pop" data-image="refunds/<?php echo htmlspecialchars($payment['proof_of_payment']); ?>">View Proof</a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center">No return payments found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal fade" id="pop" tabindex="-1" role="dialog" aria-labelledby="myMediulModalLabel">
                    <div class="modal-dialog modal-md">
                        <div style="color:#000;" class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                                <h2 class="modal-title" id="myModalLabel">Proof of Return</h2>
                            </div>
                            <div class="modal-body">
                                <img id="proofImage" alt="Proof of Return" style="max-width: 100%; height: auto;">
                            </div>
                        </div>
                    </div>
                </div>

        </div>

	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>	

    <script>
        document.querySelector("#nav_invoice_report").className = "active";
        

        document.addEventListener('DOMContentLoaded', function() {
            const viewProofButtons = document.querySelectorAll('.view-proof');
            viewProofButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const imagePath = this.getAttribute('data-image');
                    document.getElementById('proofImage').src = `./${imagePath}`;
                });
            });
        });

    </script>

</body>
</html>