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
    $order_stats = 'Verification';
    $paymentType = $_POST['paymentType'];
    $pay = $_POST['pay'];

    // Process file upload
    if (isset($_FILES['payment_image']) && $_FILES['payment_image']['error'] == 0) {
        $fileTmpPath = $_FILES['payment_image']['tmp_name'];
        $fileName = $_FILES['payment_image']['name'];
        $fileSize = $_FILES['payment_image']['size'];
        $fileType = $_FILES['payment_image']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $allowedfileExtensions = array('jpg', 'gif', 'png', 'jpeg');
        if (in_array($fileExtension, $allowedfileExtensions)) {
            $uploadFileDir = './uploaded_images';

            // Check if the directory exists, if not, create it
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);
            }

            $dest_path = $uploadFileDir . '/' . $fileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $message = 'File is successfully uploaded.';

                // Start transaction
                $conn->begin_transaction();

                try {
                    // Insert form data into database
                    $stmt_insert = $conn->prepare("INSERT INTO paymentform (firstname, lastname, email, address, mobile, payment_method, payment_type, amount, payment_image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param('sssssssds', $firstName, $lastName, $email, $address, $mobile, $pay, $paymentType, $amount, $dest_path);

                    if ($stmt_insert->execute()) {
                        $payment_id = $stmt_insert->insert_id;
                        $stmt_insert->close();

                        $placeholders = implode(',', array_fill(0, count($order_ids), '?'));

                        // Get Orders
                        $stmt_select = $conn->prepare(
                            "SELECT order_name as name, SUM(order_quantity) as qty, MAX(order_price) as price
                            FROM orderdetails WHERE order_id IN ($placeholders) GROUP BY order_name"
                        );
                        $types = str_repeat('i', count($order_ids));
                        $stmt_select->bind_param($types, ...$order_ids);
                        $stmt_select->execute();
                        $order_list = $stmt_select->get_result()->fetch_all(MYSQLI_ASSOC);

                        // Update all selected orders
                        $update_sql = "UPDATE orderdetails SET payment_id = ?, order_status = ? WHERE order_id IN ($placeholders)";
                        $stmt_update = $conn->prepare($update_sql);
                        $params = array_merge([$payment_id, $order_stats], $order_ids);
                        $types = str_repeat('i', count($params));
                        $stmt_update->bind_param($types, ...$params);
                        $stmt_select->close();


                        if ($stmt_update->execute()) {
                            date_default_timezone_set('Asia/Manila');
                            $currentDateTime = new DateTime();
                            $formattedDateTime = $currentDateTime->format('F j, Y - h:ia');

                            $message .= ' Payment Document Successfully Submitted for all selected orders.';
                            // Generate receipt HTML
                            $receipt .= "<div id=\"cmlReciept\" class=\"receipt\">";
                            $receipt .= "<h2>Payment Receipt</h2>";
                            $receipt .= "<p><strong>Date Ordered:</strong> $formattedDateTime</p>";
                            $receipt .= "<p><strong>Name:</strong> $firstName $lastName</p>";
                            $receipt .= "<p><strong>Email:</strong> $email</p>";
                            $receipt .= "<p><strong>Address:</strong> $address</p>";
                            $receipt .= "<p><strong>Gcash Number:</strong> $mobile</p>";
                            $receipt .= "<p style=\"padding-bottom: 16px; border-bottom: 1px solid #6c757d;\"><strong>Payment Type:</strong> $pay ($paymentType)</p>";

                            $receipt .= "<table style=\"width: 100%; margin-bottom: 16px;\">";
                            $receipt .= "<thead>";
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Item </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Quantity </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> Price </th>";
                                $receipt .= "</tr>";
                            $receipt .= "</thead>";
                            $receipt .= "<tbody>";
                            foreach ($order_list as $order_data) {
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<td style=\"padding: 4px 0;\"> {$order_data['name']} </td>";
                                    $receipt .= "<td style=\"padding: 4px 0;\"> {$order_data['price']} </td>";
                                    $receipt .= "<td style=\"padding: 4px 0;\"> {$order_data['qty']} </td>";
                                $receipt .= "</tr>";
                            }
                                $receipt .= "<tr style=\"border-bottom: 1px solid #6c757d; padding: 1px 4px;\">";
                                    $receipt .= "<th colspan=\"2\" style=\"padding: 4px 0;\"> Total Amount </th>";
                                    $receipt .= "<th style=\"padding: 4px 0;\"> $amount </th>";
                                $receipt .= "</tr>";
                            $receipt .= "</tbody>";
                            $receipt .= "</table>";
                            // $receipt .= "<p><strong>Amount:</strong> $amount</p>";
                            // $receipt .= "<p><strong>Order IDs:</strong> " . implode(', ', $order_ids) . "</p>";
                            $receipt .= "<p><strong>Payment Status:</strong> $order_stats</p>";
                            $receipt .= "<p><strong>Payment Image:</strong> <img src=\"$dest_path\" style=\"width: 50px; height: 50px; object-fit: cover;\" alt=\"Proof of Payment\"></p>";
                            $receipt .= "</div>";
                            // Add the "Shop" button after the receipt
                            $receipt .= "<div class=\"input_group\">";
                            $receipt .= "<div class=\"input_box\">";
                            $receipt .= "<button style=\"margin: 10px auto;\" onclick=\"saveAsPDF()\" type=\"button\" href=\"shop.php\" class=\"w-100 btn btn-primary\">Save as PDF</button>";
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
    } else {
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
            padding: 20px;
            margin-top: 20px;
        }

        .receipt h2 {
            margin-bottom: 10px;
        }

        .receipt p {
            margin-bottom: 5px;
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
                            <input type="radio" name="pay" class="radio" id="bc1" value="Gcash" checked>
                            <label for="bc1"><span><i class="fa fa-cc-visa"></i> Gcash</span></label>
                        <?php
                        }
                    ?>
                    <input type="radio" name="pay" class="radio" id="bc2" value="Walk In">
                    <label for="bc2"><span><i class="fa fa-cc-paypal"></i> Walk In</span></label>
                </div>
            </div>
            <div class="input_group center-content">
                <div class="input_box" style="display: inline-flex; align-items: center;">
                    <img src="gcash.png" alt="gcash qr">
                    <span style="margin: 0 10px;">OR</span>
                    <p>Jericson Oghayon 09207652366</p>
                </div>
            </div>
            <div class="input_group">
                <div class="input_box">
                    <select name="paymentType" required id="paymentType" class="name">
                        <option value="Full Payment">Full Payment</option>
                        <option value="Down Payment">Down Payment</option>
                        <option value="Installment">Installment</option>
                    </select>
                    <i class="fa fa-credit-card icon"></i>
                </div>
            </div>
            <div class="input_box" id="amountInput">
                <input type="number" name="amount" value="<?php echo $total_amount; ?>" readonly class="name">
                <i class="fa fa-money icon" aria-hidden="true"></i>
            </div>

            <!-- Image Upload Start -->
            <h4>Proof Of Payment</h4>
            <div class="input_group">
                <div class="input_box">
                    <input type="file" name="payment_image" required class="name">
                    <i class="fa fa-upload icon"></i>
                </div>
            </div>
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
                amountField.value = <?php echo $total_amount; ?>;
            } else {
                amountField.readOnly = false;
                amountField.value = '';
            }
            amountInput.style.display = 'block';
        });
    </script>
</body>
</html>
