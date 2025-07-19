<?php
session_start();
require_once '../scripts/connect.php';

if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

$action = $_POST['action'] ?? '';
$userId = $_SESSION['id'];

try {
    $db->beginTransaction();
    
    if ($action === 'update') {
        $productId = $_POST['product_id'];
        $quantity = $_POST['quantity'];
        
        // Update quantity in cart
        $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$quantity, $userId, $productId]);
        
        // Get updated cart data
        $cartData = getCartData($db, $userId);
        
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total'],
            'cartCount' => $cartData['cartCount'],
            'itemPrice' => $cartData['itemPrices'][$productId]
        ]);
        
    } elseif ($action === 'remove') {
        $productIds = $_POST['product_ids'];
        
        // Convert single ID to array for uniform processing
        if (!is_array($productIds)) {
            $productIds = [$productIds];
        }
        
        // Prepare placeholders for IN clause
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        
        // Delete items from cart
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND product_id IN ($placeholders)");
        $stmt->execute(array_merge([$userId], $productIds));
        
        // Get updated cart data
        $cartData = getCartData($db, $userId);
        
        $db->commit();
        
        echo json_encode([
            'status' => 'success',
            'subtotal' => $cartData['subtotal'],
            'total' => $cartData['total'],
            'cartCount' => $cartData['cartCount']
        ]);
        
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid action']);
    }
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

function getCartData($db, $userId) {
    // Get cart items with prices
    $stmt = $db->prepare("
        SELECT c.product_id, c.quantity, p.price 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $stmt->execute([$userId]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate subtotal and item prices
    $subtotal = 0;
    $itemPrices = [];
    foreach ($items as $item) {
        $itemTotal = $item['price'] * $item['quantity'];
        $subtotal += $itemTotal;
        $itemPrices[$item['product_id']] = $item['price'];
    }
    
    // Get cart count
    $stmt = $db->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
    $stmt->execute([$userId]);
    $cartCount = $stmt->fetchColumn();
    
    // Calculate total with shipping
    $shippingCharge = 100;
    $total = $subtotal + $shippingCharge;
    
    return [
        'subtotal' => $subtotal,
        'total' => $total,
        'cartCount' => $cartCount,
        'itemPrices' => $itemPrices
    ];
}
?>