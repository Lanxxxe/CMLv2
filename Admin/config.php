<?php
require '../vendor/autoload.php';
use Dotenv\Dotenv;
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$DB_HOST =$_ENV['DB_HOST']; 
$DB_USER =$_ENV['DB_USER']; 
$DB_PASS =$_ENV['DB_PASS']; 
$DB_NAME =$_ENV['DB_NAME']; 

try{
    $DB_con = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME}", $DB_USER, $DB_PASS);
    $DB_con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(PDOException $e){
    echo $e->getMessage();
}
    
