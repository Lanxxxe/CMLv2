<?php
// process.php
session_start();
include 'config.php'; // Include your database connection file

// Function to redirect with message
function redirectWithMessage($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
    header("Location: maintenance.php");
    exit();
}

try {
    // Add Brand
    if (isset($_POST['action']) && $_POST['action'] == 'add_brand') {
        $brand_name = trim($_POST['brand_name']);
    
        if (empty($brand_name)) {
            throw new Exception("Brand name cannot be empty");
        }
        
        // Handle image upload
        if (!isset($_FILES['brand_image']) || $_FILES['brand_image']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid image");
        }
        
        $file_temp = $_FILES['brand_image']['tmp_name'];
        $file_name = time() . '_' . $_FILES['brand_image']['name'];
        $target_path = 'local_image/' . $file_name;
        
        // Create directory if it doesn't exist
        if (!file_exists('local_image/')) {
            mkdir('local_image/', 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file_temp, $target_path)) {
            throw new Exception("Failed to upload image");
        }
        
        // Insert into database
        $stmt = $DB_con->prepare("INSERT INTO brands (brand_name, brand_img) VALUES (?, ?)");
        $stmt->execute([$brand_name, $target_path]);
        
        redirectWithMessage('success', 'Brand added successfully!');
    }
    
    // Edit Brand
    else if (isset($_POST['action']) && $_POST['action'] == 'edit_brand') {
        $brand_id = $_POST['brand_id'];
        $brand_name = trim($_POST['brand_name']);
        
        if (empty($brand_name)) {
            throw new Exception("Brand name cannot be empty");
        }
        
        // Handle image upload if a new image was provided
        $image_url = null;
        if (isset($_FILES['brand_image']) && $_FILES['brand_image']['error'] === UPLOAD_ERR_OK) {
            $file_temp = $_FILES['brand_image']['tmp_name'];
            $file_name = time() . '_' . $_FILES['brand_image']['name'];
            $target_path = 'local_image/' . $file_name;
            
            // Create directory if it doesn't exist
            if (!file_exists('local_image/')) {
                mkdir('local_image/', 0777, true);
            }

            // Move uploaded file
            if (move_uploaded_file($file_temp, $target_path)) {
                $image_url = $target_path;
                
                // Delete old image if exists
                $stmt = $DB_con->prepare("SELECT brand_img FROM brands WHERE brand_id = ?");
                $stmt->execute([$brand_id]);
                $old_image = $stmt->fetchColumn();
                
                if ($old_image && file_exists($old_image)) {
                    unlink($old_image);
                }
            } else {
                throw new Exception("Failed to upload image");
            }
        }
        
        // Update database
        if ($image_url) {
            $stmt = $DB_con->prepare("UPDATE brands SET brand_name = ?, brand_img = ? WHERE brand_id = ?");
            $stmt->execute([$brand_name, $image_url, $brand_id]);
        } else {
            $stmt = $DB_con->prepare("UPDATE brands SET brand_name = ? WHERE brand_id = ?");
            $stmt->execute([$brand_name, $brand_id]);
        }
        
        redirectWithMessage('success', 'Brand updated successfully!');
    }

    // Add Tool
    else if (isset($_POST['action']) && ($_POST['action'] == 'add_tool' || $_POST['action'] == 'add_pallet')) {
        if ($_POST['action'] == 'add_tool') {
            $brand_id = $_POST['brand_id'];
            $tool_name = trim($_POST['tool_name']);
            $prod_type = trim($_POST['prod_type']);
        
            if (empty($tool_name)) {
                throw new Exception("Product name cannot be empty");
            }
            
            if (empty($prod_type)) {
                throw new Exception("Product type cannot be empty");
            }
            
            $stmt = $DB_con->prepare("INSERT INTO product_type (brand_id, type_name, prod_type) VALUES (?, ?, ?)");
            $stmt->execute([$brand_id, $tool_name, $prod_type]);
            
            redirectWithMessage('success', 'Tool added successfully!');
        }

        else if ($_POST['action'] == 'add_pallet') {
            $pallet_code = $_POST['pallet_code'];
            $pallet_name = $_POST['pallet_name'];
            $pallet_rgb = $_POST['pallet_rgb'];

            if (empty($pallet_code)) {
                throw new Exception("Product Code cannot be empty");
            }
            if (empty($pallet_name)) {
                throw new Exception("Product Name cannot be empty");
            }
            if (empty($pallet_rgb)) {
                throw new Exception("Product RGB cannot be empty");
            }

            $stmt = $DB_con->prepare("INSERT INTO pallets (code, name, rgb) VALUES (?, ?, ?)");
            $stmt->execute([$pallet_code, $pallet_name, $pallet_rgb]);

            redirectWithMessage('success', 'New Pallet added successfully!');
        }
    }
    
    // Edit Tool
    else if (isset($_POST['action']) && $_POST['action'] == 'edit_tool') {
        $tool_id = $_POST['tool_id'];
        $tool_name = trim($_POST['tool_name']);
        $prod_type = trim($_POST['prod_type']);
        
        if (empty($tool_name)) {
            throw new Exception("Product name cannot be empty");
        }
        
        if (empty($prod_type)) {
            throw new Exception("Product type cannot be empty");
        }
        
        $stmt = $DB_con->prepare("UPDATE product_type SET type_name = ?, prod_type = ? WHERE type_id = ?");
        $stmt->execute([$tool_name, $prod_type, $tool_id]);
        
        redirectWithMessage('success', 'Product updated successfully!');
    }


    // Delete TOOL || PALLET || BRAND
    else if (isset($_POST['action']) && ($_POST['action'] == 'delete_tool' || $_POST['action'] == 'delete_pallet' || $_POST['action'] == 'delete_brand')) {
        // DELETE TOOL
        if ($_POST['action'] == 'delete_tool'){
            
            $tool_id = $_POST['tool_id'];
            
            $stmt = $DB_con->prepare("DELETE FROM product_type WHERE type_id = ?");
            $stmt->execute([$tool_id]);
            
            redirectWithMessage('success', 'Tool deleted successfully!');
        } 

        // DELETE PALLET
        else if ($_POST['action'] == 'delete_pallet') {
            $pallet_id = $_POST['pallet_id'];

            $stmt = $DB_con->prepare("DELETE FROM pallets WHERE pallet_id = ?");
            $stmt->execute([$pallet_id]);
            
            redirectWithMessage('success', 'Pallet deleted successfully!');
        }

        // DELETE BRAND
        else if ($_POST['action'] == 'delete_brand'){
            $brand_id = $_POST['brand_id'];
        
            // Start transaction to ensure both queries succeed or fail together
            $DB_con->beginTransaction();
            
            // Delete associated tools first (due to foreign key constraint)
            // $stmt = $DB_con->prepare("DELETE FROM product_type WHERE brand_id = ?");
            // $stmt->execute([$brand_id]);
            
            // Delete the brand
            $stmt = $DB_con->prepare("DELETE FROM brands WHERE brand_id = ?");
            $stmt->execute([$brand_id]);
            
            // Commit the transaction
            $DB_con->commit();
            
            redirectWithMessage('success', 'Brand and associated tools deleted successfully!');
        }
    }
    
    else {
        throw new Exception("Invalid action specified");
    }
    
} catch (Exception $e) {
    // Roll back transaction if one is active
    if ($DB_con->inTransaction()) {
        $DB_con->rollBack();
    }
    redirectWithMessage('error', $e->getMessage());
}
?>
