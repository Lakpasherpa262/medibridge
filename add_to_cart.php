<?php
session_start();
include '../scripts/connect.php'; // Update the path as needed

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Please login to add items to cart']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 1;
    $userId = $_SESSION['id'];
    
    if (!$productId) {
        echo json_encode(['status' => 'error', 'message' => 'Invalid product']);
        exit();
    }
    
    try {
        // First get the shop_id from the product
        $productStmt = $db->prepare("SELECT shop_id FROM products WHERE id = ?");
        $productStmt->execute([$productId]);
        $product = $productStmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$product) {
            echo json_encode(['status' => 'error', 'message' => 'Product not found']);
            exit();
        }
        
        $shopId = $product['shop_id'];
        
        // Check if product already exists in cart
        $checkStmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $productId]);
        $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            $updateStmt = $db->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
            $updateStmt->execute([$newQuantity, $existingItem['id']]);
        } else {
            // Add new item
            $insertStmt = $db->prepare("INSERT INTO cart (user_id, product_id, shop_id, quantity) VALUES (?, ?, ?, ?)");
            $insertStmt->execute([$userId, $productId, $shopId, $quantity]);
        }
        
        // Get updated cart count
        $countStmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $countStmt->execute([$userId]);
        $cartData = $countStmt->fetch(PDO::FETCH_ASSOC);
        $cartCount = $cartData['total'] ?? 0;
        
        echo json_encode([
            'status' => 'success', 
            'message' => 'Item added to cart',
            'cartCount' => $cartCount
        ]);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
}
?>