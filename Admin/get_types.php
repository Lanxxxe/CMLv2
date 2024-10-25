<?php
include 'config.php';

try {
    
    if(isset($_GET['brand_id'])) {
        header('Content-Type: application/json');
        $stmt = $DB_con->prepare("SELECT type_id, type_name FROM product_type WHERE brand_id = ?");
        $stmt->execute([$_GET['brand_id']]);
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($types);
    }
} catch(PDOException $e) {
    header('Content-Type: application/json');
    echo json_encode(['error' => $e->getMessage()]);
}
?>