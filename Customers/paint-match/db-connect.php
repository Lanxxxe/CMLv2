<?php
error_reporting(E_ALL);
ini_set("display_errors", 0);
function customErrorHandler($errno, $errstr, $errfile, $errline) {
    $date = date('Y-m-d H:i:s');
    $message = "($date) Error: [$errno] $errstr - $errfile:$errline" . PHP_EOL;
    error_log($message, 3, '../error.log');
}

set_error_handler("customErrorHandler");


try  {
    include '../../vendor/autoload.php';
} catch (Exception $e) {
    if ($e->getCode() === 10) {
        include '../vendor/autoload.php';
    }
}
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../../');
$dotenv->load();

$DB_HOST =$_ENV['DB_HOST']; 
$DB_USER =$_ENV['DB_USER']; 
$DB_PASS =$_ENV['DB_PASS']; 
$DB_NAME =$_ENV['DB_NAME']; 


try{
    $DB_con = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}",$DB_USER,$DB_PASS);
    $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    echo $e->getMessage();
}
