<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// Initialize variables
$error = "";
$message = "";
$receipt = "";
$paid = false;

// Database connection parameters
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$servername =$_ENV['DB_HOST']; 
$username =$_ENV['DB_USER']; 
$password =$_ENV['DB_PASS']; 
$dbname =$_ENV['DB_NAME']; 

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


// Retrieve order IDs from session
$order_ids = isset($_SESSION['order_ids']) ? $_SESSION['order_ids'] : [];

// Calculate total amount for all orders
$total_amount = 0;
foreach ($order_ids as $order_id) {
    $stmt = $conn->prepare("SELECT order_total FROM orderdetails WHERE order_id = ?");
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_amount += $row['order_total'];
    }
    $stmt->close();
}


// Check if form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input fields
    $firstName = isset($_SESSION['user_firstname']) ? $_SESSION['user_firstname'] : '';
    $lastName = isset($_SESSION['user_lastname']) ? $_SESSION['user_lastname'] : '';
    $mobile = isset($_SESSION['user_mobile']) ? $_SESSION['user_mobile'] : '';
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
    $address = isset($_SESSION['user_address']) ? $_SESSION['user_address'] : '';
    $amount = floatval($_POST['amount']);
    $order_stats = 'Confirmed';
    $paymentType = $_POST['paymentType'];
    $pay = $_POST['pay'];

    // Process file upload
    if (($_SESSION['user_type'] == 'Cashier') || isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] == 0) {
        if ($_SESSION['user_type'] != 'Cashier') {
            $fileTmpPath = $_FILES['payment_image']['tmp_name'];
            $fileName = $_FILES['payment_image']['name'];
            $fileSize = $_FILES['payment_image']['size'];
            $fileType = $_FILES['payment_image']['type'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));
    
            $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        } 
        if ($_SESSION['user_type'] == 'Cashier' || in_array($fileExtension, $allowedfileExtensions)) {
            if ($_SESSION['user_type'] != 'Cashier') {
                $uploadFileDir = './uploaded_images';
    
                // Check if the directory exists, if not, create it
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }
    
                $dest_path = $uploadFileDir . '/' . $fileName;
            } else {
                $dest_path = '';
            }

            if ($_SESSION['user_type'] == 'Cashier' || move_uploaded_file($fileTmpPath, $dest_path)) {
                if ($_SESSION['user_type'] != 'Cashier') {
                    $message = 'File is successfully uploaded.';
                }

                // Start transaction
                $conn->begin_transaction();

                try {
                    // Insert form data into database
                    if ($_SESSION['user_type'] == 'Cashier'){
                        $stats = "Confirmed";
                        $order_stats = 'Confirmed';
                        $stmt_insert = $conn->prepare("INSERT INTO paymentform (firstname, lastname, email, address, mobile, payment_method, payment_type, amount, payment_image_path, payment_status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt_insert->bind_param('sssssssdss', $firstName, $lastName, $email, $address, $mobile, $pay, $paymentType, $amount, $dest_path, $stats);
                    } else {
                        $order_stats = 'verification';
                        $stmt_insert = $conn->prepare("INSERT INTO paymentform (firstname, lastname, email, address, mobile, payment_method, payment_type, amount, payment_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt_insert->bind_param('sssssssds', $firstName, $lastName, $email, $address, $mobile, $pay, $paymentType, $amount, $dest_path);
                    }

                    if ($stmt_insert->execute()) {
                        $payment_id = $stmt_insert->insert_id;
                        $stmt_insert->close();

                        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

                        // Get Orders
                        $stmt_select = $conn->prepare(
                            "SELECT 
                                od.order_name AS name, 
                                SUM(od.order_quantity) AS qty, 
                                MAX(od.order_price) AS price, 
                                i.brand_name AS brand
                            FROM 
                                orderdetails od
                            JOIN 
                                items i ON od.product_id = i.item_id
                            WHERE 
                                od.order_id IN ($placeholders)
                            GROUP BY 
                                od.order_name, i.brand_name"
                        );
                        $types = str_repeat('i', count($order_ids));
                        $stmt_select->bind_param($types, ...$order_ids);
                        $stmt_select->execute();
                        
                        $order_list = $stmt_select->get_result()->fetch_all(MYSQLI_ASSOC);

                        // Update all selected orders
                        $update_sql = "UPDATE orderdetails SET payment_id = ?, order_status = ? WHERE order_id IN ($placeholders)";
                        $stmt_update = $conn->prepare($update_sql);
                        $params = array_merge([$payment_id, $order_stats], $order_ids);
                        $types = 'is' . str_repeat('i', count($order_ids));
                        $stmt_update->bind_param($types, ...$params);
                        $stmt_select->close();


                        if ($stmt_update->execute()) {
                            date_default_timezone_set('Asia/Manila');
                            $currentDateTime = new DateTime();
                            $formattedDateTime = $currentDateTime->format('F j, Y - h:ia');

                            $message .= ' Payment Document Successfully Submitted for all selected orders.';
                            // Generate receipt HTML
                            $receipt .= "<div id=\"cmlReciept\" class=\"receipt\">";
                            $receipt .= "<h2 style='font-size: 26; font-weight: 900; border: none; color: #044C92; padding: 2px;'>CML Paint Trading</h2>";
                            $receipt .= "<h2>Payment Receipt</h2>";
                            $receipt .= "<p><strong>Date Ordered:</strong> $formattedDateTime</p>";
                            // $receipt .= "<p><strong>Order ID/s:</strong> " . implode(', ', $order_ids) . "</p>";
                            $receipt .= "<p><strong>Transaction ID:</strong> " . $payment_id . "</p>";
                            $receipt .= "<p><strong>Name:</strong> $firstName $lastName</p>";
                            $receipt .= "<p><strong>Email:</strong> $email</p>";
                            $receipt .= "<p><strong>Address:</strong> $address</p>";
                            $receipt .= "<p><strong>Mobile Number:</strong> $mobile</p>";
                            $displayType = ($paymentType === "Down Payment") ? "Partial Payment" : $paymentType;
                            $receipt .= "<p style=\"padding-bottom: 16px; border-bottom: 1px solid #6c757d;\"><strong>Payment Type:</strong> $pay ($displayType)</p>";
                            // $receipt .= "<p style=\"padding-bottom: 16px; border-bottom: 1px solid #6c757d;\"><strong>Payment Type:</strong> $pay ($paymentType)</p>";

                            $receipt .= "<table style=\"width: 100%; margin-bottom: 16px;\">";
                            $receipt .= "<thead>";
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Item </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Brand </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Quantity </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Price </th>";
                                $receipt .= "</tr>";
                            $receipt .= "</thead>";
                            $receipt .= "<tbody>";
                            $_total_amount = 0;
                            foreach ($order_list as $order_data) {
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<td style=\"padding: 4px 0;\">{$order_data['name']} </td>";
                                    $receipt .= "<td style=\"padding: 4px 0;\">{$order_data['brand']} </td>";
                                    $receipt .= "<td style=\"padding: 4px 0;\">{$order_data['qty']} </td>";
                                    $receipt .= "<td style=\"padding: 4px 0;\">₱" . number_format($order_data['price'], 2) ."</td>";
                                $receipt .= "</tr>";
                                $_total_amount += $order_data['price'] * $order_data['qty'];
                            }
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<th colspan=\"3\" style=\"padding: 4px 0;\"> Total Amount </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\">₱" . number_format($_total_amount, 2) . "</th>";
                                $receipt .= "</tr>";
                            $receipt .= "</tbody>";
                            $receipt .= "</table>";
                            // $receipt .= "<p><strong>Amount:</strong> $amount</p>";
                            // $receipt .= "<p><strong>Order IDs:</strong> " . implode(', ', $order_ids) . "</p>";
                            $receipt .= "<p><strong>Amount Paid:</strong> ₱" . number_format($amount, 2) . "</p>";
                            $receipt .= "<p><strong>Remaining Balance:</strong> ₱" .   number_format($_total_amount - $amount, 2) . "</p>";

                            $receipt .= "<p><strong>Payment Status:</strong> $order_stats</p>";
                            // if ($_SESSION['user_type'] != 'Cashier'){
                            //     $receipt .= "<p><strong>Payment Image:</strong> <img src=\"$dest_path\" style=\"width: 50px; height: 50px; object-fit: cover;\" alt=\"Proof of Payment\"></p>";
                            // }
                            $receipt .= "</div>";
                            // Add the "Shop" button after the receipt
                            $receipt .= "<div class=\"input_group\">";
                            $receipt .= "<div class=\"input_box\">";
                            $receipt .= "<button style=\"margin: 10px auto;\" onclick=\"window.print()\" type=\"button\" href=\"shop.php\" class=\"w-100 btn btn-primary\">Print</button>";
                            $receipt .= "<button style=\"margin-bottom: 10px;\"  onclick=\"saveAsPDF()\" type=\"button\" href=\"shop.php\" class=\"w-100 btn btn-primary\">Save as PDF</button>";
                            $receipt .= "<a href=\"shop.php\" class=\"w-100 btn btn-primary\">Shop</a>";
                            $receipt .= "</div>";
                            $receipt .= "</div>";
                        } else {
                            throw new Exception('Error updating order status: ' . $conn->error);
                        }
                        $stmt_update->close();
                    } else {
                        throw new Exception('Error: ' . $stmt_insert->error);
                    }

                    // If we've made it this far without exceptions, commit the transaction
                    $paid = $conn->commit();
                } catch (Exception $e) {
                    // An error occurred; rollback the transaction
                    $conn->rollback();
                    $error = $e->getMessage();
                }
            } else {
                $error = 'There was some error moving the file to upload directory.';
            }
        } else {
            $error = 'Upload failed. Allowed file types: ' . implode(',', $allowedfileExtensions);
        }
    } else if ($_SESSION['user_type'] == 'Cashier') {

    } 
    else {
        $error = 'There is some error in the file upload. Please check the following error.<br>';
        $error .= 'Error:' . $_FILES['payment_image']['error'];
    }

    // Output response
    if (!empty($error)) {
        $sError = json_encode($error);
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: $sError
                    });
                });
              </script>";
    } elseif (!empty($message)) {
        $sMessage = json_encode($message);
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: $sMessage
                    }).then(function() {
                        document.getElementById('receipt').innerHTML = '$receipt';
                    });
                });
              </script>";
    }
}



// Function to sanitize input
function sanitizeInput($input)
{
    return htmlspecialchars(trim($input));
}

// Close the database connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Form</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/font-awesome/4.2.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js" integrity="sha512-GsLlZN/3F2ErC5ifS5QtgpiJtWd43JWSuIgh7mbzZ8zBps+dvLusV+eNQATqgA/HdeKFVgA5v3S/cIrLF7QnIg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <style>
        .center-content {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .center-content img {
            margin-right: 10px;
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

            #receipt, #receipt * {
                visibility: visible !important;
            }

            #receipt {
                position: absolute !important;
                left: 0 !important;
                top: 0 !important;
                width: 100% !important;
                padding: 0.75rem !important;
                margin: 0 !important;
            }

            /* Typography for receipt printing - 2.5x bigger */
            #receipt h2 {
                font-size: 3rem !important;  /* Increased from 1.2rem */
                margin-bottom: 1.25rem !important;
                line-height: 1.2 !important;
            }

            #receipt p {
                font-size: 2.2rem !important;  /* Increased from 0.875rem */
                margin-bottom: 0.75rem !important;
                line-height: 1.4 !important;
            }

            #receipt table {
                font-size: 2.2rem !important;  /* Increased from 0.875rem */
                width: 100% !important;
                margin: 1.25rem 0 !important;
            }

            #receipt th, 
            #receipt td {
                padding: 0.625rem !important;
                font-size: 2.2rem !important;  /* Increased from 0.875rem */
            }

            /* Company name styling */
            #receipt h2:first-child {
                font-size: 3.5rem !important;  /* Increased from 1.4rem */
                font-weight: 900 !important;
                color: #044C92 !important;
                padding: 0.3125rem !important;
                margin-bottom: 1.875rem !important;
            }

            /* Increase spacing between sections */
            #receipt > * {
                margin-bottom: 1.25rem !important;
            }

            /* Hide print and shop buttons when printing */
            #receipt .input_group,
            #receipt .btn {
                display: none !important;
            }

            /* Make strong tags (labels) stand out more */
            #receipt strong {
                font-size: 2.2rem !important;
                font-weight: 700 !important;
            }

            /* Add more spacing between table rows */
            #receipt tr {
                margin-bottom: 0.625rem !important;
            }

            /* Ensure the payment image scales appropriately */
            #receipt img {
                width: 125px !important;  /* Increased from 50px */
                height: 125px !important;  /* Increased from 50px */
            }
        }
    </style>

</head>
<body>
    <div class="wrapper">

    <?php if (empty($receipt)) : ?>
        <h2>Payment Form</h2>
        <form action="payment_form.php" method="post" enctype="multipart/form-data">
            <?php foreach ($order_ids as $order_id): ?>
                <input type="hidden" name="order_ids[]" value="<?php echo htmlspecialchars($order_id); ?>">
                <?php endforeach; ?>
                
                <!-- Payment Details Start -->
                <div class="input_group">
                    <div class="input_box">
                        <h4>Payment Details</h4>
                        <?php 
                        if ($_SESSION['user_type'] != 'Cashier'){
                            ?>
                            <input type="hidden" name="pay" id="bc1" value="Gcash" checked>
                            <label for="bc1"><span><i class="fa fa-cc-visa"></i> Gcash</span></label>
                        <?php
                        }
                        else {
                        ?>
                            <input type="hidden" name="pay" id="bc2" value="Walk In" checked>
                            <label for="bc2"><span><i class="fa fa-cc-paypal"></i> Cash Payment</span></label>                
                        <?php
                        }
                    ?>
                </div>
            </div>
          
            <div class="input_group center-content">
                <?php 
                if ($_SESSION['user_type'] != 'Cashier'){
                ?>
                <div class="input_box" style="display: inline-flex; align-items: center;">
                    <img src="gcash.png" alt="gcash qr">
                    <span style="margin: 0 10px;">OR</span>
                    <p>Jericson Oghayon 09207652366</p>
                </div>
                <?php 
                }
                ?>
            </div>
            <label class="form-label">Type of Payment<span style="color: red;">*</span></label>
            <div class="input_group">
                <div class="input_box">
                    <select name="paymentType" required id="paymentType" class="name">
                        <option value="Full Payment">Full Payment</option>
                        <?php 
                        if ($_SESSION['user_type'] != 'Cashier'){
                            ?>
                            <option value="Down Payment">Partial Payment</option>
                            <!-- <option value="Installment">Installment</option> -->
                        <?php
                        }
                        ?>
                    </select>
                    <i class="fa fa-credit-card icon"></i>
                </div>
            </div>
            <label class="form-label">Amount<span style="color: red;">*</span></label>
            <div class="input_box" id="amountInput">
                <input type="number" name="amount" value="<?php echo number_format($total_amount, 2); ?>" step="0.01" readonly class="name" min="0.00" max="<?php echo $total_amount; ?>">
                <i class="fa fa-money icon" aria-hidden="true"></i>
            </div>

            <?php
                if ($_SESSION['user_type'] != 'Cashier'){
            ?>
                <!-- Image Upload Start -->
                <h4>Proof Of Payment<span style="color: red;">*</span></h4>
                <div class="input_group">
                    <div class="input_box">
                        <input type="file" name="payment_image" required class="name">
                        <i class="fa fa-upload icon"></i>
                    </div>
                </div>
            <?php
            }
            ?>
            <!-- Image Upload End -->

            <!-- Payment Details End -->
            <div class="input_group">
                <div class="input_box">
                    <button type="submit">PAY NOW</button>
                </div>
            </div>
            <div class="input_group">
                <div class="input_box">
                    <a href="cart_items.php" class="w-100 btn btn-secondary">CANCEL</a>
                </div>
            </div>
        </form>
    <?php else: ?>
        <!-- Receipt display area -->
        <div id="receipt">
            <?php echo $receipt; ?>
        </div>
    <?php endif; ?>
    </div>

    <script>
        function saveAsPDF() {
            const cmlReciept = document.querySelector('#cmlReciept');
            const opts = {
              margin:       0.55,
              filename:     'reciept.pdf',
              image:        { type: 'jpeg', quality: 0.98 },
              html2canvas:  { scale: 2 },
              jsPDF:        { unit: 'in', format: 'letter', orientation: 'portrait' }
            };
            html2pdf().set(opts).from(cmlReciept).save();
        }

        document.getElementById('paymentType').addEventListener('change', function() {
            var paymentType = this.value;
            var amountInput = document.getElementById('amountInput');
            var amountField = document.querySelector('input[name="amount"]');
            if (paymentType === 'Full Payment') {
                amountField.readOnly = true;
                amountField.value = (<?php echo $total_amount; ?>).toFixed(2);
            } else if (paymentType === 'Down Payment') {
                amountField.readOnly = true;
                amountField.value = (<?php echo $total_amount * 0.5; ?>).toFixed(2);
            } else {
                amountField.readOnly = false;
                amountField.value = '';
            }
            amountInput.style.display = 'block';
        });


        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('input[type="number"]').forEach(input => {
                input.addEventListener('input', event => {
                    const min = +input.getAttribute('min');
                    const max = +input.getAttribute('max') * 0.30;
                    if(+input.value < min) {
                        input.value = min;
                    }
                    if(+input.value > max) {
                        input.value = max;
                    }
                });
            });
        });
    </script>
</body>
</html>
