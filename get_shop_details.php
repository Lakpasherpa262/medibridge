<?php
include '../scripts/connect.php';
header('Content-Type: application/json');

// Verify admin access
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'Access denied. Please login first.']);
    exit;
}

if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'message' => 'Shop ID not provided']);
    exit;
}

$shopId = $_POST['id'];

try {
    $stmt = $db->prepare("SELECT 
        s.id, 
        s.shop_name, 
        s.email, 
        s.shop_number, 
        s.address, 
        s.district, 
        s.state, 
        s.pincode, 
        s.trade_license, 
        s.retail_drug_license, 
        s.shop_image, 
        s.owner_signature,
        CONCAT(u.first_name, ' ', COALESCE(u.middle_name, ''), ' ', u.last_name) AS owner_name
        FROM shopdetails s
        JOIN users u ON s.shopOwner_id = u.id
        WHERE s.id = :id");
    
    $stmt->bindParam(':id', $shopId);
    $stmt->execute();
    
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($shop) {
        echo json_encode([
            'success' => true,
            'data' => $shop
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Shop not found'
        ]);
    }
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>