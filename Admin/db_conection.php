<?php



// $dbcon=mysqli_connect("127.0.0.1","u736664699_123","Cmlpaint2024");

// mysqli_select_db($dbcon,"u736664699_123");

$dbcon=mysqli_connect("localhost","root","", "cml_paint_db");

if (!$dbcon) {
    die("Connection failed: " . mysqli_connect_error());
}
echo "Connected successfully";

?>