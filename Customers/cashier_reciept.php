<?php
session_start();
require '../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Database connection settings
$servername = $_ENV['DB_HOST'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];
$dbname = $_ENV['DB_NAME'];

try {
    // Create a new MySQLi connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    // Check connection
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Invalid request method.');
    }

    // Get item IDs and quantities from POST data
    $item_ids = $_POST['item_ids'] ?? [];
    $qtys = $_POST['qtys'] ?? [];
    $payment_id = $_SESSION['payment_id'] ?? '';

    // Validate input
    if (!is_array($item_ids) || !is_array($qtys) || empty($item_ids) || count($item_ids) !== count($qtys)) {
        throw new Exception('Invalid item IDs or quantities.');
    }

    // Prepare SQL query to retrieve items
    $placeholders = implode(',', array_fill(0, count($item_ids), '?'));
    $select_sql = "SELECT * FROM items WHERE item_id IN ($placeholders)";
    $stmt_select = $conn->prepare($select_sql);

    if (!$stmt_select) {
        throw new Exception('Query preparation failed: ' . $conn->error);
    }

    // Bind parameters and execute the statement
    $stmt_select->bind_param(str_repeat('i', count($item_ids)), ...$item_ids);
    $stmt_select->execute();
    $items = $stmt_select->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_select->close();

    // Check if items were found
    if (empty($items)) {
        throw new Exception('No items found for the given IDs.');
    }

    // Initialize TCPDF with custom parameters for receipt-like dimensions
    $pdf = new TCPDF('P', 'mm', array(80, 180)); // Standard receipt width of 80mm
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('CML Paint Trading');
    $pdf->SetTitle('Payment Receipt');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Set margins (left, top, right) - narrow margins typical for receipts
    $pdf->SetMargins(5, 5, 5);
    $pdf->SetAutoPageBreak(TRUE, 5);
    
    // Add page
    $pdf->AddPage();

    // Store Logo and Header
    $pdf->SetFont('helvetica', 'B', 12);
    $pdf->Cell(0, 5, 'CML Paint Trading', 0, 1, 'C');
    $pdf->SetFont('helvetica', '', 8);
    // $pdf->Cell(0, 3, '123 Paint Street, Color City', 0, 1, 'C');
    // $pdf->Cell(0, 3, 'Tel: (123) 456-7890', 0, 1, 'C');
    // $pdf->Cell(0, 3, 'VAT Reg TIN: 123-456-789-000', 0, 1, 'C');

    // Add separator line
    $pdf->Cell(0, 2, '', 0, 1);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Cell(0, 2, '', 0, 1);

    // Receipt Details
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 3, 'Receipt No: ' . str_pad($payment_id, 8, '0', STR_PAD_LEFT), 0, 1);
    $pdf->Cell(0, 3, 'Date: ' . date('Y-m-d H:i:s'), 0, 1);
    if (isset($_SESSION['customer_name'])) {
        $pdf->Cell(0, 3, 'Customer Name: ' . $_SESSION['customer_name'], 0, 1);
    }
    if (isset($_SESSION['customer_contact_no'])) {
        $pdf->Cell(0, 3, 'Contact No.: ' . $_SESSION['customer_contact_no'], 0, 1);
    }

    // Add separator line
    $pdf->Cell(0, 2, '', 0, 1);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Cell(0, 2, '', 0, 1);

    // Column Headers
    $pdf->SetFont('DejaVuSans', 'B', 8);
    $pdf->Cell(25, 3, 'Item', 0, 0);
    $pdf->Cell(12, 3, 'Qty', 0, 0, 'R');
    $pdf->Cell(15, 3, 'Price', 0, 0, 'R');
    $pdf->Cell(18, 3, 'Total', 0, 1, 'R');

    // Add separator line
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Cell(0, 2, '', 0, 1);

    // Order details
    $pdf->SetFont('DejaVuSans', '', 8);
    $totalPrice = 0;

    foreach ($items as $index => $item) {
        $itemQuantity = $qtys[$index];
        $itemTotalPrice = $item['item_price'] * $itemQuantity;
        $totalPrice += $itemTotalPrice;
        $gl = $item['gl']? ' ' . $item['gl'] : ' pc';

        // Item name - wrapped if too long
        $pdf->MultiCell(25, 3, htmlspecialchars($item['item_name']), 0, 'L', false, 0);
        $pdf->Cell(12, 3, $itemQuantity . $gl, 0, 0, 'R');
        $pdf->Cell(15, 3, '₱' . number_format($item['item_price'], 2), 0, 0, 'R');
        $pdf->Cell(18, 3, '₱' . number_format($itemTotalPrice, 2), 0, 1, 'R');
    }

    // Add separator line
    $pdf->Cell(0, 2, '', 0, 1);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Cell(0, 2, '', 0, 1);

    // Totals
    $pdf->SetFont('DejaVuSans', 'B', 8);
    $pdf->Cell(45, 3, 'TOTAL:', 0, 0);
    $pdf->Cell(25, 3, '₱' . number_format($totalPrice, 2), 0, 1, 'R');
    $pdf->SetFont('helvetica', 'B', 8);
    
    // $vat = $totalPrice * 0.12; // 12% VAT
    // $pdf->Cell(45, 3, 'VAT (12%):', 0, 0);
    // $pdf->Cell(25, 3, number_format($vat, 2), 0, 1, 'R');
    // 
    // $grandTotal = $totalPrice + $vat;
    // $pdf->SetFont('helvetica', 'B', 9);
    // $pdf->Cell(45, 4, 'TOTAL:', 0, 0);
    // $pdf->Cell(25, 4, number_format($grandTotal, 2), 0, 1, 'R');

    // Add separator line
    $pdf->Cell(0, 2, '', 0, 1);
    $pdf->Cell(0, 0, '', 'T', 1);
    $pdf->Cell(0, 2, '', 0, 1);

    // // Payment Method
    // $pdf->SetFont('helvetica', '', 8);
    // $pdf->Cell(45, 3, 'Payment Method:', 0, 0);
    // $pdf->Cell(25, 3, 'Online Payment', 0, 1, 'R');

    // Footer
    $pdf->Cell(0, 5, '', 0, 1);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Cell(0, 3, 'Thank you for choosing CML Paint Trading!', 0, 1, 'C');
    $pdf->Cell(0, 3, 'Please keep this receipt for your records.', 0, 1, 'C');
    // $pdf->SetFont('helvetica', '', 7);

    // Add QR Code at the bottom
    // $pdf->Cell(0, 5, '', 0, 1);
    // $style = array(
    //     'border' => false,
    //     'vpadding' => 'auto',
    //     'hpadding' => 'auto',
    //     'fgcolor' => array(0, 0, 0),
    //     'bgcolor' => false,
    //     'module_width' => 1,
    //     'module_height' => 1
    // );
    // $qrContent = "RN:{$payment_id}|DT:" . date('YmdHis') . "|TL:" . number_format($grandTotal, 2);
    // $pdf->write2DBarcode($qrContent, 'QRCODE,L', 25, $pdf->GetY(), 30, 30, $style);
    
    // Output PDF
    $pdf->Output('receipt.pdf', 'I');

} catch (Exception $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}
