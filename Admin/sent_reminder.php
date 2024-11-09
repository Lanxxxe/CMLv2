<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';
require '../vendor/autoload.php';
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

define('HOST', $_ENV['MAILER_HOST']);
define('EMAIL_ADDRESS', $_ENV['MAILER_EMAIL']);
define('EMAIL_PASSWORD', $_ENV['MAILER_PASS']);

include 'email_format.php';

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_id'])) {
        $payment_id = $_POST['payment_id'];

        // Send the reminder
        $result = sendPaymentReminder($payment_id, $DB_con);

        // Return response
        header('Content-Type: application/json');
        if ($result) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Failed to send reminder']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);
    }
} catch (Exception $error) {
    echo json_encode(['success' => false, 'message' => $error->getMessage()]);
}
