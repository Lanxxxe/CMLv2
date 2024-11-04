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


        $stmt = $DB_con->prepare("SELECT 
                i.item_id, 
                i.item_name, 
                i.brand_name, 
                i.item_image, 
                i.type, 
                i.item_price, 
                i.gl, 
                i.pallet_id,
                p.rgb,
                p.name,
                p.code
            FROM 
                items i
            JOIN 
                pallets p ON i.pallet_id = p.pallet_id
            WHERE 
                i.brand_name = ? 
                AND i.gl = 'Gallon'");
        $stmt->execute([$brand_name]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode($types);
    }
} catch (PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}

?>