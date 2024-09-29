<?php



// $dbcon=mysqli_connect("localhost","u736664699_123","Cmlpaint2024");

// mysqli_select_db($dbcon,"u736664699_123");

$dbcon=mysqli_connect("localhost","root","", "cml_paint_db");

if (!$dbcon) {
    die("Connection failed: " . mysqli_connect_error());
}

?>