<?php
session_start();

$response = array('status' => '', 'message' => '');

// Ensure session is active
if(!isset($_SESSION['admin_username'])) {
    $response['status'] = 'error';
    $response['message'] = 'Unauthorized access';
    echo json_encode($response);
    exit();
}

include("db_conection.php");

// Check if brand exists
$query = "SELECT COUNT(*) FROM brands WHERE brand_name = ?";

if ($statement = mysqli_prepare($dbcon, $query)) {
    // Bind the brandName parameter
    mysqli_stmt_bind_param($statement, 's', $brandName);
    $brandName = $_POST['brandName'];

    // Execute the statement
    mysqli_stmt_execute($statement);

    // Bind result variable
    mysqli_stmt_bind_result($statement, $count);
    mysqli_stmt_fetch($statement);

    // Close the select statement
    mysqli_stmt_close($statement);

    // If the brand already exists
    if ($count > 0) {
        $response['status'] = 'error';
        $response['message'] = 'Brand name already exists';
    } else {
        // Insert new brand into the database
        $insertQuery = "INSERT INTO brands (brand_name, brand_logo) VALUES (?, ?)";

        if ($statement = mysqli_prepare($dbcon, $insertQuery)) {
            mysqli_stmt_bind_param($statement, 'ss', $brandName, $brandURL);

            $brandURL = $_POST['trademark'];

            // Execute the insert query
            mysqli_stmt_execute($statement);

            if (mysqli_stmt_affected_rows($statement) > 0) {
                $response['status'] = 'success';
                $response['message'] = 'Brand added successfully';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to add the brand';
            }

            // Close the insert statement
            mysqli_stmt_close($statement);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error preparing insert query';
        }
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'Error preparing select query';
}

// Close the database connection
mysqli_close($dbcon);

// Output the response as JSON
echo json_encode($response);
?>