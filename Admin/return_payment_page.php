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
        .return-form {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .return-card {
            max-width: 500px;
            width: 100%;
            padding: 20px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .form-control {
            margin-bottom: 15px;
        }
        .process-button {
            width: 100%;
        }
</style>

</head>
<body>
    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-danger">
                <center><h3><strong>Return Payment Form</strong></h3></center>
            </div>
            
            <div class="return-form">
                <div class="return-card">
                    <?php 
                    if (isset($_GET['reset_payment_id']) && isset($_GET['payment_type'])) {
                        $cancel_payment_id = $_GET['reset_payment_id'];
                        $cancel_payment_type = $_GET['payment_type'];

                        $getPaymentInformation = $DB_con->prepare("
                            SELECT DISTINCT p.*, o.*
                            FROM paymentform p
                            JOIN orderdetails o ON p.id = o.payment_id
                            WHERE p.id = ?
                        ");
                        $getPaymentInformation->execute([$cancel_payment_id]);

                        $payment_information = $getPaymentInformation->fetchAll(PDO::FETCH_ASSOC);

                        // Check if data is available
                        if (count($payment_information) > 0) {
                            foreach ($payment_information as $info) {
                                ?>
                                <form id="refundForm" action="" method="POST" enctype="multipart/form-data">
                                    <!-- Display Payment Information -->
                                    <p>Payment ID: <strong><?php echo htmlspecialchars($info['id']); ?></strong></p>
                                    <p>Customer Name: <strong><?php echo htmlspecialchars($info['firstname'] . ' ' . $info['lastname']); ?></strong></p>
                                    <p>Payment Type: <strong><?php echo htmlspecialchars($info['payment_type']); ?></strong></p>
                                    <p>Amount Paid: <strong>₱<?php echo htmlspecialchars($info['amount']); ?></strong></p>
                                    <p>Mobile Number: <strong><?php echo htmlspecialchars($info['mobile']); ?></strong></p>
                                    
                                    <!-- Display Payment Proof Image -->
                                    <div class="pop-container">
                                        <label for="proof-of-payment">Proof of Payment:</label>
                                        <img id="proof-of-payment" src="../Customers/<?php echo htmlspecialchars($info['payment_image_path']); ?>" 
                                            alt="Payment Image" width="200">
                                    </div>

                                    <hr>
                                    
                                    <!-- Input for the amount to return -->
                                    <div class="form-group">
                                        <label for="return_amount">Amount to Refund:</label>
                                        <input type="number" name="return_amount" id="return_amount" required class="form-control" 
                                            placeholder="Enter amount to return" min="1" max="<?php echo htmlspecialchars($info['amount']); ?>">
                                        <small class="text-muted">Please enter the exact amount refunded: ₱<?php echo htmlspecialchars($info['amount']); ?></small>
                                    </div>

                                    <!-- Input to upload proof of return -->
                                    <div class="form-group">
                                        <label for="return_image">Upload Proof of Refund:</label>
                                        <input type="file" name="return_image" id="return_image" required class="form-control" min="1">
                                        <small class="text-muted">Please upload a screenshot or photo of the refund transaction.</small>
                                    </div>


                                    <!-- Hidden inputs for passing payment ID and user ID -->
                                    <input type="hidden" name="payment_id" value="<?php echo htmlspecialchars($info['id']); ?>">
                                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($info['user_id']); ?>">
                                    <input type="hidden" name="order_quantity" value="<?php echo htmlspecialchars($info['order_quantity']); ?>">

                                    <!-- Submit button -->
                                    <button type="submit" id="confirmRefundBtn" class="btn btn-primary process-button">Process Refund</button>
                                </form>
                                <?php
                            }
                        } else {
                            echo "<p>No payment information found for the specified ID and type.</p>";
                        }
                    }
                    ?>
                </div>
            </div>

        </div>

	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>	
<script>
    document.getElementById('confirmRefundBtn').addEventListener('click', function (e) {
        e.preventDefault();
        const form = document.getElementById('refundForm');
        const formData = new FormData(form);

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to process a refund. This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, refund it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('return_payment.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    console.log(data);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: data.message
                        }).then(() => window.location.href = 'invoice_report.php');
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Request Failed',
                        text: `An error occurred while processing the refund. ${error}`
                    });
                });
            }
        });
    });

</script>

</body>
</html>
