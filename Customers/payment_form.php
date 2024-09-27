<?php
session_start();

// Initialize variables
$error = "";
$message = "";
$receipt = "";

// Database connection parameters
// $servername = "127.0.0.1:3306";
// $username = "u473175646_cmlpaint";
// $password = "6Vk~LBYc";
// $dbname = "u473175646_edgedata";

// $servername = "localhost";
// $username = "u736664699_123";
// $password = "Cmlpaint2024";
// $dbname = "u736664699_123";

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cml_paint_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input fields
    $firstName = isset($_SESSION['user_firstname']) ? $_SESSION['user_firstname'] : '';
    $lastName = isset($_SESSION['user_lastname']) ? $_SESSION['user_lastname'] : '';
    $mobile = isset($_SESSION['user_mobile']) ? $_SESSION['user_mobile'] : '';
    $email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '';
    $address = isset($_SESSION['user_address']) ? $_SESSION['user_address'] : '';
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : NULL;
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : (isset($_SESSION['order_id']) ? intval($_SESSION['order_id']) : 0);
    $order_stats = 'Verification';
    $paymentType = $_POST['paymentType'];
    $pay = $_POST['pay'];

    // Check if order_id exists in orderdetails table
    $orderCheckQuery = "SELECT * FROM orderdetails WHERE order_id = ?";
    $stmt = $conn->prepare($orderCheckQuery);
    $stmt->bind_param("i", $order_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
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
                $uploadFileDir = 'uploaded_images/';

                // Check if the directory exists, if not, create it
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }

                $dest_path = $uploadFileDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $message = 'File is successfully uploaded.';

                    // Insert form data into database
                    $stmt_insert = $conn->prepare("INSERT INTO paymentform (firstname, lastname, email, address, mobile, payment_method, payment_type, amount, payment_image_path, order_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                    $stmt_insert->bind_param('sssssssdsi', $firstName, $lastName, $email, $address, $mobile, $pay, $paymentType, $amount, $dest_path, $order_id);

                    if ($stmt_insert->execute()) {
                        // Get the inserted payment ID
                        $payment_id = $stmt_insert->insert_id;
                        $stmt_insert->close();

                        // Update the order details with the payment ID and status
                        $update_sql = "UPDATE orderdetails SET payment_id = ?, order_status = ? WHERE order_id = ?";
                        $stmt_update = $conn->prepare($update_sql);
                        $stmt_update->bind_param('isi', $payment_id, $order_stats, $order_id);

                        if ($stmt_update->execute()) {
                            $message .= ' Payment Document Successfully Submitted.';
                            // Generate receipt HTML
                            $receipt .= "<div class='receipt'>";
                            $receipt .= "<h2>Payment Receipt</h2>";
                            $receipt .= "<p><strong>Name:</strong> $firstName $lastName</p>";
                            $receipt .= "<p><strong>Email:</strong> $email</p>";
                            $receipt .= "<p><strong>Address:</strong> $address</p>";
                            $receipt .= "<p><strong>Gcash Number:</strong> $mobile</p>";
                            $receipt .= "<p><strong>Payment Type:</strong> $pay ($paymentType)</p>";

                            if ($amount != NULL) {
                                $receipt .= "<p><strong>Amount:</strong> $amount</p>";
                            }
                            $receipt .= "<p><strong>Order ID:</strong> $order_id</p>";
                            $receipt .= "<p><strong>Payment Status:</strong> $order_stats</p>";
                            $receipt .= "<p><strong>Payment Image:</strong> <img src='$dest_path' style='width: 50px; height: 50px; object-fit: cover;' alt='Proof of Payment'></p>";
                            $receipt .= "</div>";
                            // Add the "Shop" button after the receipt
                            $receipt .= "<div class='input_group'>";
                            $receipt .= "<div class='input_box'>";
                            $receipt .= "<a href='shop.php' class='w-100 btn btn-primary'>Shop</a>";
                            $receipt .= "</div>";
                            $receipt .= "</div>";
                        } else {
                            $error = 'Error updating order status: ' . $conn->error;
                        }
                        $stmt_update->close();
                    } else {
                        $error = 'Error: ' . $stmt_insert->error;
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
    } else {
        $error = "Error: order_id does not exist in orderdetails table.";
    }

    $stmt->close();

    // Output response
    if (!empty($error)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: '$error'
                    });
                });
              </script>";
    } elseif (!empty($message)) {
        echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success',
                        text: '$message'
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
        <h2>Payment Form</h2>
        <form action="payment_form.php" method="post" enctype="multipart/form-data">
            <!-- Hidden input for order ID -->
            <input type="hidden" name="order_id" value="<?php echo isset($_SESSION['order_id']) ? htmlspecialchars($_SESSION['order_id']) : ''; ?>">
            <!-- Payment Details Start -->
            <div class="input_group">
                <div class="input_box">
                    <h4>Payment Details</h4>
                    <input type="radio" name="pay" class="radio" id="bc1" value="Gcash" checked>
                    <label for="bc1"><span><i class="fa fa-cc-visa"></i> Gcash</span></label>
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
                        <option value="" selected>Type of Payment</option>
                        <option value="Full Payment">Full Payment</option>
                        <option value="Down Payment">Down Payment</option>
                        <option value="Installment">Installment</option>
                    </select>
                    <i class="fa fa-credit-card icon"></i>
                </div>
            </div>
            <div class="input_box" id="amountInput" style="display: none;">
                <input type="number" id="amount" name="amount" placeholder="Enter Amount" class="name" required>
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

        <!-- Receipt display area -->
        <div id="receipt">
            <?php echo $receipt; ?>
        </div>
    </div>

    <script>
        document.getElementById('paymentType').addEventListener('change', function() {
            var paymentType = this.value;
            var amountInput = document.getElementById('amountInput');
            var amount = document.querySelector('#amount');
            if (paymentType === 'Full Payment') {
                amountInput.style.display = 'none';
                amount.removeAttribute('required');
            } else {
                amountInput.style.display = 'block';
            }
        });
    </script>
</body>

</html>