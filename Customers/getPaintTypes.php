<?php
require_once 'config.php';

try {
    
    if(isset($_GET['brand_id'])){
        header('Content-Type: application/json');
        $brand_id = $_GET['brand_id'] ?? '';

        // Prepare the SQL statement
        $stmt = $DB_con->prepare("SELECT brand_name FROM brands WHERE brand_id = ?");
        $stmt->execute([$brand_id]);
        $brand_row = $stmt->fetch(PDO::FETCH_ASSOC);
        $brand_name = $brand_row ? $brand_row['brand_name'] : null;


        $stmt = $DB_con->prepare("SELECT type, item_price, gl FROM items WHERE brand_name = ? AND gl = 'Gallon'");
        $stmt->execute([$brand_name]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($types);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

?>