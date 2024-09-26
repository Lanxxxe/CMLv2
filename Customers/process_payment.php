<?php
// Initialize variables
$error = "";
$message = "";
// Database connection parameters
$servername = "localhost";
$username = "u736664699_123";
$password = "Cmlpaint2024";
$dbname = "u736664699_123";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form is submitted via POST method
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and validate input fields
    $fullName = sanitizeInput($_POST['full_name']);
    $nameOnCard = sanitizeInput($_POST['name_on_card']);
    $email = sanitizeInput($_POST['email']);
    $address = sanitizeInput($_POST['address']);
    $city = sanitizeInput($_POST['city']);
    $dobDay = sanitizeInput($_POST['dob_day']);
    $dobMonth = sanitizeInput($_POST['dob_month']);
    $dobYear = sanitizeInput($_POST['dob_year']);
    $gender = sanitizeInput($_POST['gender']);
    $paymentMethod = sanitizeInput($_POST['pay']);
    $gcashNumber = sanitizeInput($_POST['card_number']);
    $gcashName = sanitizeInput($_POST['card_cvc']);
    $amount = floatval($_POST['amount']);

    // Validate inputs (example: basic checks, you can expand as needed)
    if (empty($fullName) || empty($email) || empty($address) || empty($city) || empty($dobDay) || empty($dobMonth) || empty($dobYear) || empty($gcashNumber) || empty($gcashName) || $amount <= 0) {
        $error = "Please fill in all required fields.";
    } else {
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
                $uploadFileDir = './uploaded_images/';

                // Check if the directory exists, if not, create it
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0777, true);
                }

                $dest_path = $uploadFileDir . $fileName;

                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $message = 'File is successfully uploaded.';
                    
                    // Insert form data into database
                    $sql = "INSERT INTO PaymentForm (full_name, name_on_card, email, address, city, dob_day, dob_month, dob_year, gender, payment_method, gcash_number, gcash_name, amount, payment_image_path) 
                            VALUES ('$fullName', '$nameOnCard', '$email', '$address', '$city', '$dobDay', '$dobMonth', '$dobYear', '$gender', '$paymentMethod', '$gcashNumber', '$gcashName', '$amount', '$dest_path')";

                    if ($conn->query($sql) === TRUE) {
                        $message .= ' Data is successfully inserted into the database.';
                    } else {
                        $error = 'Error: ' . $sql . '<br>' . $conn->error;
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
    }

    // Output response
    if (!empty($error)) {
        echo '<div style="color: red;">Error: ' . $error . '</div>';
    } elseif (!empty($message)) {
        echo '<div style="color: green;">' . $message . '</div>';
    }
}

// Function to sanitize input
function sanitizeInput($input) {
    return htmlspecialchars(trim($input));
}

// Close the database connection
$conn->close();
?>
