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

include_once 'config.php';
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
    <script src="../assets/js/chart.umd.min.js"></script>

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
                // Initialize filter parameters
                $filter_date = $_GET['filter_date'] ?? null;
                $filter_type = $_GET['filter_type'] ?? null;


                // Get the current branch from the session
                $branch = $_SESSION['current_branch'] ?? null;
                // Base query
                $query = "SELECT 
                            rp.return_payment_id,
                            rp.user_id,
                            rp.return_status,
                            rp.proof_of_payment,
                            rp.amount_return,
                            rp.quantity,
                            rp.date as date_only ,
                            CONCAT(u.user_firstname, ' ', u.user_lastname) as customer_name
                        FROM return_payments rp
                        LEFT JOIN users u ON rp.user_id = u.user_id";

                // Conditions for filters
                $conditions = [];
                $params = [];

                // Filter by branch
                if (!empty($branch)) {
                    $conditions[] = "rp.branch = :branch";
                    $params[':branch'] = $branch;
                }


                // Filter by specific date
                if (!empty($filter_date)) {
                    $conditions[] = "DATE(rp.date) = :filter_date";
                    $params[':filter_date'] = $filter_date;
                }

                // Filter by daily, weekly, or monthly
                if ($filter_type === 'daily') {
                    $conditions[] = "DATE(rp.date) = CURDATE()";
                } elseif ($filter_type === 'weekly') {
                    $conditions[] = "YEARWEEK(rp.date, 1) = YEARWEEK(CURDATE(), 1)";
                } elseif ($filter_type === 'monthly') {
                    $conditions[] = "MONTH(rp.date) = MONTH(CURDATE()) AND YEAR(rp.date) = YEAR(CURDATE())";
                }

                // Append conditions to query
                if (!empty($conditions)) {
                    $query .= " WHERE " . implode(" AND ", $conditions);
                }

                $query .= " ORDER BY rp.return_payment_id DESC";

                // Execute query
                $stmt = $DB_con->prepare($query);
                $stmt->execute($params);
                $return_payments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            ?>

            <div class="d-flex">
                <form method="GET" id="filterForm" class="mb-4">
                    <div class="row">
                        <div class="col-md-3">
                            <input type="date" name="filter_date" class="form-control" placeholder="Select Date">
                        </div>
                        <div class="col-md-3">
                            <select name="filter_type" class="form-control">
                                <option value="">Select Filter</option>
                                <option value="daily">Daily</option>
                                <option value="weekly">Weekly</option>
                                <option value="monthly">Monthly</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary">Filter</button>
                        </div>
                    </div>
                </form>
                
                <div class="d-flex" style="display: flex; gap: 20px; flex-wrap: wrap;">
                    <form method="GET" action="generate_refund_report.php" style="margin-top: 15px">
                        <input type="hidden" name="filter_date" value="<?php echo htmlspecialchars($filter_date); ?>">
                        <input type="hidden" name="filter_type" value="<?php echo htmlspecialchars($filter_type); ?>">
                        <button type="submit" class="btn btn-success">Save as PDF</button>
                    </form>
    
                    <button type="button" class="btn btn-primary" style="margin-top: 15px" onclick="printContent()">
                        <i class="fa fa-print"></i> Print
                    </button>
                </div>
            </div>

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
                                    <th>Quantity</th>
                                    <th>Amount Returned</th>
                                    <th>Date Returned</th>
                                    <th>Proof of Return</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($return_payments): 
                                    // Prepare data arrays for Chart.js
                                    $dates = [];
                                    $amounts = [];

                                    // Populate the arrays with the filtered results
                                    foreach ($return_payments as $payment) {
                                        $dates[] = $payment['date_only'];  // Use the extracted date
                                        $amounts[] = $payment['amount_return'];
                                    }
                                    ?>
                                    
                                    <?php foreach ($return_payments as $payment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($payment['return_payment_id']); ?></td>
                                            <td><?php echo htmlspecialchars($payment['customer_name']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $payment['return_status'] === 'Refunded' ? 'success' : 'warning'; ?>">
                                                    <?php echo htmlspecialchars($payment['return_status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo $payment['quantity']; ?></td>
                                            <td>₱<?php echo number_format($payment['amount_return'], 2); ?></td>
                                            <td><?php echo $payment['date_only']; ?></td>
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

            <div>
                <canvas id="refundChart" width="400" height="200"></canvas>
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
        function printContent() {
            window.print();
        }
        document.querySelector("#nav_invoice_report").className = "active";
                                    

        document.addEventListener('DOMContentLoaded', function() {
            const viewProofButtons = document.querySelectorAll('.view-proof');
            viewProofButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const imagePath = this.getAttribute('data-image');
                    document.getElementById('proofImage').src = `./${imagePath}`;
                });
            });

            // Example data for the chart, fetch dynamically based on query results if needed
            const labels = <?php echo json_encode($dates); ?>;
            const data = <?php echo json_encode($amounts); ?>;

            const ctx = document.getElementById('refundChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Refund Amount (₱)',
                        data: data,
                        backgroundColor: 'rgba(54, 162, 235, 0.5)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                // Custom tooltip to show only date without time
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) {
                                        label += ': ';
                                    }
                                    label += '₱' + context.raw.toFixed(2);
                                    return label;
                                }
                            }
                        }
                    }
                }
            });
        });

    </script>

</body>
</html>
