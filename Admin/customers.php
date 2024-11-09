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
#modal-product-im, #modal-receipt-image{
    max-width: 150px;
    max-height: 150px;
}

.recieptPar {
    font-size: 1.4rem !important;
}

.receipt {
    border: 1px solid #ccc;
    padding: 1.25rem;
    margin-top: 1.25rem;
}

.receipt h2 {
    margin-bottom: 0.625rem;
    font-size: 1.5rem;
}

.receipt p {
    margin-bottom: 0.3125rem;
    font-size: 1rem;
}

@media print {
    @page {
        size: 80mm auto;
        margin: 0;
    }

    body * {
        visibility: hidden !important;
    }

    #cmlReciept {
        border: none;
    }

    #cmlReciept, #cmlReciept * {
        visibility: visible !important;
    }

    #cmlReciept {
        position: absolute !important;
        left: 0 !important;
        top: 0 !important;
        width: 100% !important;
        padding: 0.75rem !important;
        margin: 0 !important;
    }

    /* Typography for receipt printing - 2.5x bigger */
    #cmlReciept h2 {
        font-size: 3rem !important;
        margin-bottom: 1.25rem !important;
        line-height: 1.2 !important;
    }

    #cmlReciept p {
        font-size: 2.2rem !important;
        margin-bottom: 0.75rem !important;
        line-height: 1.4 !important;
    }

    #cmlReciept table {
        font-size: 2.2rem !important;
        width: 100% !important;
        margin: 1.25rem 0 !important;
    }

    #cmlReciept th, 
    #cmlReciept td {
        padding: 0.625rem !important;
        font-size: 2.2rem !important;
    }

    /* Company name styling */
    #cmlReciept h2:first-child {
        font-size: 3.5rem !important;
        font-weight: 900 !important;
        color: #044C92 !important;
        padding: 0.3125rem !important;
        margin-bottom: 1.875rem !important;
    }

    /* Increase spacing between sections */
    #cmlReciept > * {
        margin-bottom: 1.25rem !important;
    }

    /* Hide modal elements when printing */
    .modal-header,
    .modal-footer,
    .close,
    .btn {
        display: none !important;
    }

    /* Make strong tags (labels) stand out more */
    #cmlReciept strong {
        font-size: 2.2rem !important;
        font-weight: 700 !important;
    }

    /* Add more spacing between table rows */
    #cmlReciept tr {
        margin-bottom: 0.625rem !important;
    }

    /* Ensure the payment image scales appropriately */
    #cmlReciept img {
        width: 125px !important;
        height: 125px !important;
    }
}
</style>

</head>
<body>
<?php
if (isset($_GET['delete_payment_id'])) {
    $payment_id = $_GET['delete_payment_id'];
    $payment_type = $_GET['payment_type'];
    if ($payment_type === 'Installment' || strcasecmp($payment_type, 'Down Payment') === 0) {
        $stmt_reject = $DB_con->prepare("DELETE FROM payment_track WHERE payment_id = :payment_id");
        $stmt_reject->execute([':payment_id' => $payment_id]);
    }

    $stmt_delete = $DB_con->prepare('DELETE FROM orderdetails WHERE payment_id = :payment_id');
    $stmt_delete->bindParam(':payment_id', $payment_id);
    $delete_orders = $stmt_delete->execute();

    $stmt_delete = $DB_con->prepare('DELETE FROM paymentform WHERE id = :payment_id');
    $stmt_delete->bindParam(':payment_id', $payment_id);
    $delete_payment = $stmt_delete->execute();

    if ($delete_orders && $delete_payment) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Deleted',
                text: 'The order has been successfully deleted.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        $error = json_encode($stmt_delete->errorInfo()[0]);
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Order Deleted Error',
                text: $error,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    }
}

if (isset($_GET['reset_payment_id'])) {
    $DB_con->beginTransaction();
    try {
        $payment_id = $_GET['reset_payment_id'];
        $payment_type = $_GET['payment_type'];
        if($payment_type == 'Installment' || strcasecmp($payment_type, 'Down Payment') === 0) {
            $stmt_reject = $DB_con->prepare("UPDATE payment_track SET status = 'Rejected' WHERE payment_id = :payment_id");
            $stmt_reject->execute([':payment_id' => $payment_id]);
        }
        $stmt_reset = $DB_con->prepare('UPDATE orderdetails SET order_status = "Rejected" WHERE payment_id = :payment_id');
        $stmt_reset->bindParam(':payment_id', $payment_id);
        $stmt_reset->execute();

        $stmt_update_payment = $DB_con->prepare('UPDATE paymentform SET payment_status = "failed" WHERE id = :payment_id');
        $stmt_update_payment->bindParam(':payment_id', $payment_id);
        $stmt_update_payment->execute();

        $DB_con->commit();

        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Reset',
                text: 'The order has been rejected due to wrong payment.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } catch (Exception $e) {
        $DB_con->rollBack();
        $error = json_encode($e->getMessage());
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Order Reset Failed',
                text: $error,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    }
}

if (isset($_GET['confirm_payment_id'])) {
    $payment_id = $_GET['confirm_payment_id'];
    $payment_type = $_GET['payment_type'];
    
    if ($payment_type == 'Installment' || strcasecmp($payment_type, 'Down Payment') === 0) {

        $stmt_track = $DB_con->prepare("SELECT track_id
            FROM payment_track
            WHERE payment_id = :payment_id 
            ORDER BY track_id DESC
            LIMIT 1");
        $stmt_track->execute([':payment_id' => $payment_id]);

        if ($stmt_track->rowCount() > 0) {
            $track_id = $stmt_track->fetch(PDO::FETCH_NUM)[0];
            // Prepare and execute the stored procedure
            $stmt_confirmed = $DB_con->prepare("CALL confirm_payment(:track_id)");
            $stmt_confirmed->bindParam(':track_id', $track_id);
            $stmt_confirmed->execute();
            // Close the cursor to free up resources
            $stmt_confirmed->closeCursor();
        }
    }

    // Prepare and execute the update statements
    $stmt_order = $DB_con->prepare('UPDATE orderdetails SET order_status = "Confirmed" WHERE payment_id = :payment_id');
    $stmt_order->bindParam(':payment_id', $payment_id);
    $order_confirmed = $stmt_order->execute();

    // Close the cursor
    $stmt_order->closeCursor();

    $stmt_payment = $DB_con->prepare('UPDATE paymentform SET payment_status = "Confirmed" WHERE id = :payment_id');
    $stmt_payment->bindParam(':payment_id', $payment_id);
    $payment_confirmed = $stmt_payment->execute();

    // Close the cursor
    $stmt_payment->closeCursor();

    // Final confirmation status
    $confirmed = $payment_confirmed && $order_confirmed;
    if ($confirmed) {
        // sendEmailApprovedOrder($payment_id);
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Order Confirmed',
                text: 'The order has been successfully confirmed.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        $error = json_encode($stmt_confirmed->errorInfo()[2]);
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Order Reset Failed',
                text: $error,
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    }
}


if (isset($_GET['confirm_return_id'])) {
    $return_id = $_GET['confirm_return_id'];

    $stmt_confirmed = $DB_con->prepare('UPDATE returnitems SET status = "Confirmed" WHERE return_id = :return_id');
    $stmt_confirmed->bindParam(':return_id', $return_id);
    if ($stmt_confirmed->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Confirmed',
                text: 'The return has been successfully confirmed.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error confirming return: " . $stmt_confirmed->errorInfo()[2];
    }
}

if (isset($_GET['reject_return_id'])) {
    $return_id = $_GET['reject_return_id'];

    $stmt_rejected = $DB_con->prepare('UPDATE returnitems SET status = "Rejected" WHERE return_id = :return_id');
    $stmt_rejected->bindParam(':return_id', $return_id);
    if ($stmt_rejected->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Rejected',
                text: 'The return has been rejected.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error rejecting return: " . $stmt_rejected->errorInfo()[2];
    }
}

if (isset($_GET['delete_return_id'])) {
    $return_id = $_GET['delete_return_id'];

    $stmt_delete = $DB_con->prepare('DELETE FROM returnitems WHERE return_id = :return_id');
    $stmt_delete->bindParam(':return_id', $return_id);
    if ($stmt_delete->execute()) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Return Deleted',
                text: 'The return has been successfully deleted.',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'customers.php';
                }
            });
        </script>";
    } else {
        echo "Error deleting return: " . $stmt_delete->errorInfo()[2];
    }
}

?>
    <div id="wrapper">
        <?php include("navigation.php"); ?>

        <div id="page-wrapper">
            <div class="alert alert-danger">
                <center><h3><strong>Customer Management</strong></h3></center>
            </div>
            <br />
            <div class="table-responsive" style="margin-bottom: 50px;">
                <table class="display table table-bordered" id="example" cellspacing="0" width="100%">
                    <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer Email</th>
                        <th>Payment Type</th>
                        <th>Order Status</th>
                        <th>Order Total</th>
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    // $stmt = $DB_con->prepare('SELECT users.user_email, users.user_firstname, users.user_lastname, users.user_address, orderdetails.* FROM users
                    // INNER JOIN orderdetails ON users.user_id = orderdetails.user_id WHERE orderdetails.order_status NOT IN ("Confirmed", "Returned", "rejected")');
                    $stmt = $DB_con->prepare("
                        SELECT 
                            u.user_email,
                            u.user_firstname,
                            u.user_lastname,
                            u.user_address,
                            pf.id AS order_id,
                            pf.id AS payment_id,
                            if(pf.payment_type IN ('Down Payment', 'Installment') AND pf.payment_status = 'verification', 'Requested', pf.payment_status) AS order_status,
                            pf.payment_type,
                            pf.payment_image_path,
                            SUM(od.order_total) AS order_total
                        FROM 
                            users u
                            INNER JOIN orderdetails od ON u.user_id = od.user_id
                            INNER JOIN paymentform pf ON od.payment_id = pf.id
                        WHERE 
                            (pf.payment_status NOT IN ('Confirmed', 'Returned', 'failed')) AND (pf.payment_type NOT IN ('Down Payment', 'Installment') OR pf.payment_status = 'verification')
                        GROUP BY 
                            u.user_email,
                            u.user_firstname,
                            u.user_lastname,
                            u.user_address,
                            pf.id,
                            pf.payment_status;
                    ");
                    $stmt->execute();
                    $payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    $stmt = $DB_con->prepare("
                        SELECT 
                            u.user_email,
                            u.user_firstname,
                            u.user_lastname,
                            u.user_address,
                            pt.track_id AS order_id,
                            pt.status AS order_status,
                            pf.payment_type,
                            pf.payment_image_path,
                            pf.id as payment_id,
                            pt.amount AS order_total
                        FROM 
                            users u
                            INNER JOIN orderdetails od ON u.user_id = od.user_id
                            INNER JOIN paymentform pf ON od.payment_id = pf.id
                            LEFT JOIN payment_track pt ON pt.payment_id = pf.id
                        WHERE 
                        pt.status = 'Requested'
                        GROUP BY 
                            u.user_email,
                            u.user_firstname,
                            u.user_lastname,
                            u.user_address,
                            pf.id,
                            pf.payment_status;
                    ");
                    $stmt->execute();
                    $tracks = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $data = array_merge($payments, $tracks);

                    if (count($data) > 0) {
                        foreach ($data as $row) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['payment_id']); ?></td>
                                <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                                <td><?php echo htmlspecialchars($row['payment_type']); ?></td>
                                <td><?php echo htmlspecialchars($row['order_status']); ?></td>
                                <td>₱<?php echo htmlspecialchars($row['order_total']); ?></td>
                                <td>
                                    <a class="btn btn-success" href="javascript:confirmOrder('<?php echo htmlspecialchars($row['payment_id']); ?>', '<?php echo htmlspecialchars($row['payment_type']); ?>', '<?php echo htmlspecialchars($row['user_email']); ?>', '<?php echo htmlspecialchars($row['order_status']); ?>', '<?php echo htmlspecialchars($row['order_total']); ?>', '<?php echo htmlspecialchars($row['payment_image_path']); ?>');">
                                        <span class='glyphicon glyphicon-shopping-cart'></span> Confirm Order
                                    </a>                                
                                    <a class="btn btn-warning" href="javascript:cancelOrder('<?php echo htmlspecialchars($row['payment_id']); ?>', '<?php echo htmlspecialchars($row['payment_type']); ?>');" title="click for reset"><span class='glyphicon glyphicon-ban-circle'></span> Cancel Order</a>
                                    <a class="btn btn-primary" href="previous_orders.php?previous_id=<?php echo htmlspecialchars($row['payment_id']); ?>"><span class='glyphicon glyphicon-eye-open'></span> Previous Items Ordered</a>
                                </td>
                            </tr>
                            <?php
                        }
                    } else {
                        ?>
                        <tr>
                            <td colspan="5" class="text-center">No orders found</td>
                        </tr>
                        <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>

            <div class="alert alert-default" style="background-color:#033c73;">
                <p style="color:white;text-align:center;">&copy 2024 CML Paint Trading Shop | All Rights Reserved</p>
            </div>
        </div>
    </div>  
    <!-- Receipt Modal -->
    <div class="modal fade" id="receiptModal" tabindex="-1" aria-labelledby="receiptModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title" id="receiptModalLabel">Order Receipt</h2>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="cmlReciept" class="receipt">
                        <h2 style="font-size: 26px; font-weight: 900; border: none; color: #044C92; padding: 2px;">CML Paint Trading</h2>
                        <h2>Order Receipt</h2>
                        <p class="recieptPar"><strong>Date:</strong> <span id="receiptDate"></span></p>
                        <p class="recieptPar"><strong>Order ID:</strong> <span id="receiptOrderId"></span></p>
                        <p class="recieptPar"><strong>Customer Email:</strong> <span id="receiptEmail"></span></p>
                        <p class="recieptPar" style="padding-bottom: 16px; border-bottom: 1px solid #6c757d;">
                            <strong>Payment Type:</strong> <span id="receiptPaymentType"></span>
                        </p>

                        <table style="width: 100%; margin-bottom: 16px;">
                            <thead>
                                <tr style="border-bottom: 1px solid #6c757d;">
                                    <th style="padding: 4px 0;">Item</th>
                                    <th style="padding: 4px 0;">Quantity</th>
                                    <th style="padding: 4px 0;">Price</th>
                                    <th style="padding: 4px 0;">Total</th>
                                </tr>
                            </thead>
                            <tbody id="orderItems">
                                <!-- Items will be populated here dynamically -->
                            </tbody>
                            <tfoot>
                            </tfoot>
                        </table>

                        <p><strong>Order Status:</strong> <span id="receiptStatus"></span></p>
                        <div class="payment-proof mt-3">
                            <p><strong>Proof of Payment:</strong></p>
                            <img id="paymentProofImage" src="" alt="Payment Proof" style="width: 50px; height: 50px; object-fit: cover;">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" onclick="window.print()">Print</button>
                    <button type="button" class="btn btn-success" id="confirmOrderButton">Confirm Order</button>
                </div>
            </div>
        </div>
    </div>

	<!-- Mediul Modal -->
    <?php include_once("uploadItems.php"); ?>
    <?php include_once("insertBrandsModal.php"); ?>	
<script>
document.querySelector("#nav_order_request").className = "active";

function confirmOrder(orderId, paymentType, email, status, total, proofOfPayment) {
    // Populate the receipt modal with order details
    document.getElementById("receiptOrderId").textContent = orderId;
    document.getElementById("receiptEmail").textContent = email;
    document.getElementById("receiptPaymentType").textContent = paymentType;
    document.getElementById("receiptStatus").textContent = status;

    // Set the current date and time
    const now = new Date();
    const options = { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: 'numeric', 
        minute: 'numeric',
        hour12: true 
    };
    document.getElementById("receiptDate").textContent = now.toLocaleDateString('en-US', options);
    
    // Set payment proof image
    const paymentProofImage = document.getElementById("paymentProofImage");
    paymentProofImage.src = `../Customers/${proofOfPayment}`;
    paymentProofImage.onerror = function() {
        this.style.display = 'none';
        this.previousElementSibling.style.display = 'none';
    };
    paymentProofImage.onload = function() {
        this.style.display = 'block';
        this.previousElementSibling.style.display = 'block';
    };

    // Clear previous items
    const orderItemsBody = document.getElementById("orderItems");
    orderItemsBody.innerHTML = "";

    // Fetch and populate order items
    fetch(`getOrderItems.php?payment_id=${orderId}`)
        .then(response => response.json())
        .then(items => {
            console.log(items);
            let totalAmount = 0;
            
            items.forEach(item => {
                const itemTotal = parseFloat(item.order_total);
                totalAmount += itemTotal;
                
                const row = document.createElement("tr");
                row.style.borderBottom = "1px solid #6c757d";
                row.innerHTML = `
                    <td style="padding: 4px 0;">${item.order_name}</td>
                    <td style="padding: 4px 0;">${item.order_quantity}</td>
                    <td style="padding: 4px 0;">₱${parseFloat(item.order_price).toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                    <td style="padding: 4px 0;">₱${itemTotal.toLocaleString('en-US', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    })}</td>
                `;
                orderItemsBody.appendChild(row);
            });

            // Add total row
            const totalRow = document.createElement("tr");
            totalRow.style.borderTop = "1px solid #6c757d";
            totalRow.innerHTML = `
                <th colspan="3" style="padding: 4px 0; text-align: left;">Total Amount:</th>
                <th style="padding: 4px 0;">₱${totalAmount.toLocaleString('en-US', {
                    minimumFractionDigits: 2,
                    maximumFractionDigits: 2
                })}</th>
            `;
            orderItemsBody.appendChild(totalRow);
        })
        .catch(error => {
            console.error("Error fetching order items:", error);
            orderItemsBody.innerHTML = `
                <tr>
                    <td colspan="4" class="text-center">Error loading order items</td>
                </tr>
            `;
        });

    // Show the receipt modal
    $('#receiptModal').modal('show');

    // Set up the Confirm Order button
    document.getElementById("confirmOrderButton").onclick = function() {
        Swal.fire({
            title: 'Are you sure?',
            text: "You won't be able to revert this!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, confirm it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'customers.php?confirm_payment_id=' + orderId + '&payment_type=' + encodeURIComponent(paymentType);
            }
        });
    };
}


    function cancelOrder(orderId, paymentType) {
        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to cancel this order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, cancel it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'return_payment_page.php?reset_payment_id=' + orderId + '&payment_type=' + encodeURIComponent(paymentType);
            }
        });
    }

    function deleteUser(orderId, paymentType) {
        Swal.fire({
            title: 'Are you sure?',
            text: "This action will permanently delete the order!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'No, cancel!',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = 'customers.php?delete_payment_id=' + orderId + '&payment_type=' + encodeURIComponent(paymentType);
            }
        });
    }
</script>

</body>
</html>
