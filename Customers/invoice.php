<?php
session_start();;
error_reporting(E_ALL);
ini_set("display_errors", 1);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");

if (!$_SESSION['user_email']) {
    echo '<script type="text/javascript">
            Swal.fire({
                icon: "warning",
                title: "Unauthorized Access",
                text: "You need to log in to access this page.",
                confirmButtonText: "OK"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "../index.php";
                }
            });
          </script>';
    exit();
}

include("config.php");
$stmt_installment = $DB_con->prepare("
        SELECT paymentform.*, 
               (SELECT SUM(orderdetails.order_total) 
                FROM orderdetails 
                WHERE orderdetails.payment_id = paymentform.id) AS total_amount 
        FROM paymentform 
        WHERE email = :email AND payment_type = 'Installment'");
$stmt_installment->execute(array(':email' => $_SESSION['user_email']));
$installment_row = $stmt_installment->fetchAll(PDO::FETCH_ASSOC);

$stmt_downpayment = $DB_con->prepare("
        SELECT paymentform.*, 
               (SELECT SUM(orderdetails.order_total) 
                FROM orderdetails 
                WHERE orderdetails.payment_id = paymentform.id) AS total_amount 
        FROM paymentform 
        WHERE email = :email AND payment_type = 'Down payment'");
$stmt_downpayment->execute(array(':email' => $_SESSION['user_email']));
$downpayment_row = $stmt_downpayment->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CML Paint Trading</title>
    <link rel="shortcut icon" href="../assets/img/logo.png" type="image/x-icon" />
    <link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link rel="stylesheet" type="text/css" href="font-awesome/css/font-awesome.min.css" /> <link rel="stylesheet" type="text/css" href="css/local.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
    <script type="text/javascript" src="bootstrap/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script type="text/javascript" src="jquery.fancybox.js?v=2.1.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox.css?v=2.1.5" media="screen" />
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-buttons.css?v=1.0.5" />
    <script type="text/javascript" src="jquery.fancybox-buttons.js?v=1.0.5"></script>
    <link rel="stylesheet" type="text/css" href="jquery.fancybox-thumbs.css?v=1.0.7" />
    <script type="text/javascript" src="jquery.fancybox-thumbs.js?v=1.0.7"></script>
    <script type="text/javascript" src="jquery.fancybox-media.js?v=1.0.6"></script>
</head>

<body>
    <div id="wrapper">
        <?php include_once("navigation.php") ?>
        <div id="page-wrapper">
            <div class="row">
                <div class="col-lg-12">
                    <h1 class="page-header">Invoice Management</h1>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-body">
                            <!-- Nav tabs -->
                            <ul class="nav nav-tabs" role="tablist">
                                <li role="presentation" class="active">
                                    <a href="#downpayment" aria-controls="downpayment" role="tab" data-toggle="tab">
                                        <i class="fa fa-money"></i> Downpayment
                                    </a>
                                </li>
                                <li role="presentation">
                                    <a href="#installment" aria-controls="installment" role="tab" data-toggle="tab">
                                        <i class="fa fa-calendar"></i> Installment
                                    </a>
                                </li>
                            </ul>

                            <!-- Tab panes -->
                            <div class="tab-content">
                                <!-- Downpayment Tab -->
                                <div role="tabpanel" class="tab-pane active" id="downpayment">
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-lg-12">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Customer Name</th>
                                                        <th>Order Date</th>
                                                        <th>Total Amount</th>
                                                        <th>Remaining Balance</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($downpayment_row as $row): 
                                                        $stat = $row['payment_status'];
                                                        $paid = $stat === 'Confirm' && $row['total_amount'] <= $row['amount'];
                                                        ?>
                                                        <tr>
                                                            <td><?= $row['id'] ?></td>
                                                            <td><?= $row['firstname'] . ' ' . $row['lastname'] ?></td>
                                                            <td><?= $row['created_at'] ?></td>
                                                            <td>₱<?= $row['total_amount'] ?></td>
                                                            <td>₱<?= $row['total_amount'] - $row['amount'] ?></td>
                                                            <td><?php
                                                                if ($paid) {
                                                                   echo '<span class="label label-success">Paid</span>';
                                                                } else {
                                                                   echo '<span class="label label-secondary">Payment Pending</span>';
                                                                }
                                                            ?></td>
                                                            <td>
                                                                <button class="btn btn-primary btn-sm">
                                                                    <i class="fa fa-eye"></i> View
                                                                </button>
                                                                <?php if ($paid): ?>
                                                                    <button class="btn btn-success btn-sm" disabled>
                                                                        <i class="fa fa-check"></i> Process Payment
                                                                    </button>
                                                                <?php else: ?>
                                                                    <button class="btn btn-success btn-sm">
                                                                        <i class="fa fa-check"></i> Process Payment
                                                                    </button>
                                                                <?php endif; ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Installment Tab -->
                                <div role="tabpanel" class="tab-pane" id="installment">
                                    <div class="row" style="margin-top: 20px;">
                                        <div class="col-lg-12">
                                            <table class="table table-striped table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>Order ID</th>
                                                        <th>Customer Name</th>
                                                        <th>Total Amount</th>
                                                        <th>Monthly Payment</th>
                                                        <th>Due Date</th>
                                                        <th>Payments Made</th>
                                                        <th>Remaining Balance</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                        <?php foreach ($installment_row as $row): 
                                                            $amount = $row['total_amount'] - $row['amount'];
                                                            $monthly_payment = $amount / 12;
                                                            $added_day = 30 * ($row['months_paid'] + 1);
                                                            $due_date = date('Y-m-d', strtotime("+$added_day day", strtotime($row['created_at']))); 
                                                            $remaining_balance =  $amount - ($row['months_paid'] + 1) * $monthly_payment;
                                                        ?>
                                                        <tr>
                                                            <td><?= $row['id'] ?></td>
                                                            <td><?= $row['firstname'] . ' ' . $row['lastname'] ?></td>
                                                            <td>₱<?= $row['total_amount'] ?></td>
                                                            <td>₱<?= number_format($monthly_payment, 2) ?></td>
                                                            <td><?= $due_date ?></td>
                                                            <td><?= $row['months_paid']+1 ?>&sol;12 </td>
                                                            <td>₱<?= number_format($remaining_balance, 2) ?></td>
                                                            <td>
                                                                <?php if ($remaining_balance <= 0): ?>
                                                                    <span class="label label-primary">Complete</span>
                                                                <?php elseif(new DateTime($due_date) < new DateTime()): ?>
                                                                    <span class="label label-danger">Overdue</span>
                                                                <?php else: ?>
                                                                    <span class="label label-success">Current</span>
                                                                <?php endif; ?>
                                                            </td>
                                                            <td>
                                                                <button class="btn btn-primary btn-sm">
                                                                    <i class="fa fa-eye"></i> View
                                                                </button>
                                                                <button class="btn btn-success btn-sm" <?php echo ($remaining_balance <= 0)? 'disabled': ''?>>
                                                                    <i class="fa fa-money"></i> Record Payment
                                                                </button>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /#wrapper -->
    <!-- View Payment Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Payment Details</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Order ID:</strong> <span id="view-order-id"></span></p>
                            <p><strong>Customer:</strong> <span id="view-customer"></span></p>
                            <p><strong>Email:</strong> <span id="view-email"></span></p>
                            <p><strong>Phone:</strong> <span id="view-phone"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Total Amount:</strong> ₱<span id="view-total"></span></p>
                            <p><strong>Amount Paid:</strong> ₱<span id="view-paid"></span></p>
                            <p><strong>Remaining Balance:</strong> ₱<span id="view-balance"></span></p>
                            <p><strong>Payment Type:</strong> <span id="view-type"></span></p>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <h4>Ordered Items</h4>
                            <p id="view-items"></p>
                        </div>
                    </div>
                    <div id="installment-details" style="display: none;">
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Monthly Payment:</strong> ₱<span id="view-monthly"></span></p>
                                <p><strong>Payments Made:</strong> <span id="view-payments-made"></span>/12</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Next Due Date:</strong> <span id="view-due-date"></span></p>
                                <p><strong>Status:</strong> <span id="view-status"></span></p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

        <!-- Update the Process Payment Modal in invoice.php -->
        <div class="modal fade" id="processPaymentModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Process Payment</h4>
                    </div>
                    <div class="modal-body">
                        <form id="paymentForm" enctype="multipart/form-data" method="post">
                            <input type="hidden" id="payment-id">
                            <input type="hidden" id="payment-type">
                            <div class="form-group" id="payment-amount-group">
                                <label>Payment Amount (₱)</label>
                                <input type="number" class="form-control" id="payment-amount" step="0.01" required>
                                <small class="text-muted payment-amount-help"></small>
                            </div>
                            <div class="form-group">
                                <label>Payment Proof</label>
                                <input type="file" class="form-control" id="payment-image" accept="image/*" required>
                                <small class="text-muted">Please upload proof of payment (image file)</small>
                            </div>
                            <div class="form-group">
                                <label>Remaining Balance:</label>
                                <p>₱<span id="remaining-balance"></span></p>
                            </div>
                            <!-- Add this to show payment proof in the View Modal -->
                            <div class="row" id="payment-proof-section">
                                <div class="col-md-12">
                                    <h4>Payment Proof</h4>
                                    <img id="view-payment-proof" src="" alt="Payment Proof" class="img-responsive" style="max-width: 300px;">
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-primary" id="submitPayment">Submit Payment</button>
                    </div>
                </div>
            </div>
        </div>
        <script>
            $(document).ready(function() {
                // View payment details handler
                function viewPaymentDetails(paymentId) {
                    $.post('process_invoice.php', {
                        action: 'get_payment_details',
                        payment_id: paymentId
                    }, function(response) {
                        const data = JSON.parse(response);
                        if (data.status === 'success') {
                            const payment = data.data;
                            const remaining = payment.total_amount - payment.amount;
                            
                            $('#view-order-id').text(payment.id);
                            $('#view-customer').text(payment.firstname + ' ' + payment.lastname);
                            $('#view-email').text(payment.email);
                            $('#view-phone').text(payment.mobile);
                            $('#view-total').text(payment.total_amount);
                            $('#view-paid').text(payment.amount);
                            $('#view-balance').text(remaining);
                            $('#view-type').text(payment.payment_type);
                            $('#view-items').text(payment.items);
                            
                            if (payment.payment_image_path) {
                                $('#view-payment-proof').attr('src', './uploaded_images/' + payment.payment_image_path);
                                $('#payment-proof-section').show();
                            } else {
                                $('#payment-proof-section').hide();
                            }
                            
                            if (payment.payment_type === 'Installment') {
                                $('#installment-details').show();
                                $('#view-monthly').text(payment.monthly_payment.toFixed(2));
                                $('#view-payments-made').text(payment.months_paid);
                                $('#view-due-date').text(payment.next_due);
                                
                                const now = new Date();
                                const dueDate = new Date(payment.next_due);
                                let status = 'Current';
                                if (payment.months_paid >= 12) {
                                    status = 'Complete';
                                } else if (now > dueDate) {
                                    status = 'Overdue';
                                }
                                $('#view-status').text(status);
                            } else {
                                $('#installment-details').hide();
                            }
                            
                            $('#viewModal').modal('show');
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: data.message
                            });
                        }
                    });
                }

                // Process payment handler - Modified to handle different payment types
                function showPaymentModal(paymentId, paymentType, remainingBalance, monthlyPayment = null) {
                    // $('#processPaymentModal').data('paymentId', paymentId);
                    $('#payment-id').val(paymentId);
                    $('#payment-type').val(paymentType);
                    $('#remaining-balance').text(remainingBalance.toFixed(2));
                    
                    // Show/hide and configure amount input based on payment type
                    if (paymentType === 'installment') {
                        $('#payment-amount').val(monthlyPayment.toFixed(2));
                        $('#payment-amount').prop('readonly', true);
                        $('#payment-amount-group').show();
                        $('.payment-amount-help').text('Monthly installment amount (fixed)');
                    } else {
                        $('#payment-amount').val('');
                        $('#payment-amount').prop('readonly', false);
                        $('#payment-amount-group').show();
                        $('#payment-amount').attr({
                            'min': remainingBalance * 0.2,  // Minimum 20% of remaining
                            'max': remainingBalance
                        });
                        $('.payment-amount-help').text('Enter payment amount (min: ₱' + (remainingBalance * 0.2).toFixed(2) + ', max: ₱' + remainingBalance.toFixed(2) + ')');
                    }

                    $('#processPaymentModal').modal('show');
                }

                // Attach view button handlers
                $('.btn-primary').click(function() {
                    const paymentId = $(this).closest('tr').find('td:first').text().trim();
                    viewPaymentDetails(paymentId);
                });

                // Attach process payment button handlers
                $('.btn-success').click(function() {
                    const row = $(this).closest('tr');
                    const paymentId = row.find('td:first').text().trim();
                    const totalAmount = parseFloat(row.find('td:contains("₱")').text().replace('₱', ''));
                    const paidAmount = totalAmount - parseFloat(row.find('td:contains("₱")').eq(1).text().replace('₱', ''));
                    const remainingBalance = totalAmount - paidAmount;
                    const paymentType = $(this).closest('.tab-pane').attr('id');
                    
                    if (paymentType === 'installment') {
                        const monthlyPayment = parseFloat(row.find('td:eq(3)').text().replace('₱', ''));
                        showPaymentModal(paymentId, paymentType, remainingBalance, monthlyPayment);
                    } else {
                        showPaymentModal(paymentId, paymentType, remainingBalance);
                    }
                });

                // Handle payment amount validation for downpayment
                $('#payment-amount').on('input', function() {
                    if ($('#payment-type').val() === 'downpayment') {
                        const amount = parseFloat($(this).val());
                        const remaining = parseFloat($('#remaining-balance').text());
                        const minAmount = remaining * 0.2;
                        
                        if (amount < minAmount) {
                            $(this).val(minAmount.toFixed(2));
                        } else if (amount > remaining) {
                            $(this).val(remaining.toFixed(2));
                        }
                    }
                });


                // Handle payment submission
                $('#submitPayment').click(function(event) {
                    const paymentId = $('#payment-id').val();
                    const amount = parseFloat($('#payment-amount').val());
                    const paymentType = $('#payment-type').val();
                    const remaining = parseFloat($('#remaining-balance').text());
                    
                    if (!$('#payment-image')[0].files[0]) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Missing Payment Proof',
                            text: 'Please upload proof of payment'
                        });
                        return;
                    }

                    // Show loading state
                    Swal.fire({
                        title: 'Processing Payment',
                        text: 'Please wait...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    const formData = new FormData();
                    formData.append('action', paymentType === 'downpayment' ? 'process_downpayment' : 'process_installment');
                    formData.append('payment_id', paymentId);
                    formData.append('amount', amount);
                    formData.append('payment_image', $('#payment-image')[0].files[0]);
                    
                    $.ajax({
                        url: 'process_invoice.php',
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            const data = JSON.parse(response);
                            
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: data.message,
                                    showConfirmButton: true
                                }).then((result) => {
                                    if (result.isConfirmed) {
                                        location.reload();
                                    }
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: data.message
                                });
                            }
                            
                            $('#processPaymentModal').modal('hide');
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to process payment. Please try again.'
                            });
                        }
                    });
                });

                // Add file type validation for payment image
                $('#payment-image').change(function() {
                    const file = this.files[0];
                    const validTypes = ['image/jpeg', 'image/png', 'image/gif'];
                    
                    if (file && !validTypes.includes(file.type)) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid File Type',
                            text: 'Please upload an image file (JPEG, PNG, or GIF)'
                        });
                        $(this).val('');
                    }
                });
            });
        </script>
</body>

</html>
