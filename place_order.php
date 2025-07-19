<?php
session_start();
include 'scripts/connect.php';

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['error' => 'User not logged in']));
}

$userId = $_SESSION['user_id'];
$paymentMethod = $_POST['payment_method'];

// Get cart items
$cartKey = 'cart_'.$userId;
$cartItems = isset($_COOKIE[$cartKey]) ? json_decode($_COOKIE[$cartKey], true) : [];

if (empty($cartItems)) {
    die(json_encode(['error' => 'Cart is empty']));
}

// Get product details
$placeholders = implode(',', array_fill(0, count($cartItems), '?'));
$query = "SELECT id, price, shop_id FROM products WHERE id IN ($placeholders)";
$stmt = $db->prepare($query);
$stmt->execute(array_keys($cartItems));
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total
$subtotal = 0;
$deliveryFee = 99;
foreach ($products as $product) {
    $subtotal += $product['price'] * $cartItems[$product['id']];
}
$total = $subtotal + $deliveryFee;

// Start transaction
$db->beginTransaction();

try {
    // Create order
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, delivery_fee, payment_method, status) 
                          VALUES (?, ?, ?, ?, 'pending')");
    $stmt->execute([$userId, $total, $deliveryFee, $paymentMethod]);
    $orderId = $db->lastInsertId();
    
    // Add order items
    $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, shop_id, quantity, price) 
                          VALUES (?, ?, ?, ?, ?)");
    
    foreach ($products as $product) {
        $stmt->execute([
            $orderId,
            $product['id'],
            $product['shop_id'],
            $cartItems[$product['id']],
            $product['price']
        ]);
    }
    
    // Clear cart
    setcookie($cartKey, '', time() - 3600, "/");
    
    $db->commit();
    echo 'success';
} catch (Exception $e) {
    $db->rollBack();
    die(json_encode(['error' => $e->getMessage()]));
}
?>