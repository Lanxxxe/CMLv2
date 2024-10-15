<?php
require '../vendor/autoload.php';

use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$dbHost = $_ENV['DB_HOST'];
$dbName = $_ENV['DB_NAME'];
$dbUser = $_ENV['DB_USER'];
$dbPass = $_ENV['DB_PASS'];

$dbcon=mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);

if (!$dbcon) {
    die("Connection failed: " . mysqli_connect_error());
}

?>

