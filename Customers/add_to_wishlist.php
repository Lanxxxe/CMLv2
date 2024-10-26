<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 0);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");

if (!$_SESSION['user_email']) {
    header("Location: ../index.php");
    exit();
}

$item_id = filter_input(INPUT_GET, 'item', FILTER_VALIDATE_INT);
$user_id = filter_input(INPUT_GET, 'user', FILTER_VALIDATE_INT);

include("config.php");
$stmt_add = $DB_con->prepare('INSERT INTO wishlist (item_id, user_id) VALUES (:item_id, :user_id)');
if ($stmt_add->execute([':item_id' => $item_id, ':user_id' => $user_id])) {
    header("Location: wishlist.php?id=1");
} else {
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'failed to add wishlist',
            text: <?php echo json_encode($stmt_add->errorInfo()) ?>,
            confirmButtonText: 'OK'
        });
    </script>
</body>
</html>

<?php
}