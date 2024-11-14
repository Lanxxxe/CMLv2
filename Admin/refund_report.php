<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (!isset($_SESSION['admin_username'])) {
    header("Location: ../index.php");
    exit;
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
                <center><h3><strong>Refund Report</strong></h3></center>
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
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title mb-0">Refund Report</h3>
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
        document.querySelector("#nav_refund_report").className = "active";
        

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
