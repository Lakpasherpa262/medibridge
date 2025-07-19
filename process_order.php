<?php
session_start();
include '../scripts/connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit();
}

try {
    $db->beginTransaction();

    // 1. First check all items are in stock
    $cartStmt = $db->prepare("
        SELECT c.product_id, c.quantity, p.quantity as available_quantity, p.shop_id 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        WHERE c.user_id = ?
    ");
    $cartStmt->execute([$_SESSION['id']]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Check stock availability
    foreach ($cartItems as $item) {
        if ($item['quantity'] > $item['available_quantity']) {
            throw new Exception("Product ID {$item['product_id']} only has {$item['available_quantity']} items available, but you requested {$item['quantity']}");
        }
    }

    // Get the primary shop_id (we'll use the first one found)
    $shop_id = $cartItems[0]['shop_id'] ?? null;

    // 2. Create the order with payment method and status
    $orderStmt = $db->prepare("
        INSERT INTO orders 
        (user_id, order_date, status, shop_id, payment_method, payment_status, total_amount, shipping_charge) 
        VALUES (?, NOW(), 'pending', ?, ?, ?, ?, ?)
    ");
    
    $orderStmt->execute([
        $_SESSION['id'],
        $shop_id,
        $_POST['payment_method'],
        $_POST['payment_status'],
        $_POST['total_amount'],
        $_POST['shipping_charge']
    ]);
    
    $order_id = $db->lastInsertId();

    // 3. Create payment record if not COD
    $payment_id = null;
    if ($_POST['payment_method'] !== 'cod') {
        $paymentStmt = $db->prepare("
            INSERT INTO payments 
            (user_id, order_id, amount, payment_method, card_last4, transaction_date, status) 
            VALUES (?, ?, ?, ?, ?, NOW(), 'completed')
        ");
        
        $card_last4 = substr($_POST['card_number'], -4);
        $paymentStmt->execute([
            $_SESSION['id'],
            $order_id,
            $_POST['total_amount'],
            $_POST['payment_method'],
            $card_last4
        ]);
        
        $payment_id = $db->lastInsertId();
    }

    // 4. Move cart items to order_items and update product quantities
    foreach ($cartItems as $item) {
        // Insert order item
        $orderItemStmt = $db->prepare("
            INSERT INTO order_items 
            (order_id, product_id, quantity, price) 
            VALUES (?, ?, ?, (SELECT price FROM products WHERE id = ?))
        ");
        $orderItemStmt->execute([
            $order_id,
            $item['product_id'],
            $item['quantity'],
            $item['product_id']
        ]);
        
        // Update product quantity
        $updateProductStmt = $db->prepare("
            UPDATE products 
            SET quantity = quantity - ? 
            WHERE id = ?
        ");
        $updateProductStmt->execute([
            $item['quantity'],
            $item['product_id']
        ]);
    }
    
    // 5. Clear the cart
    $clearCartStmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
    $clearCartStmt->execute([$_SESSION['id']]);
    
    // 6. Create notification for the shop
    if ($shop_id) {
        // Get user details for notification
        $userStmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
        $userStmt->execute([$_SESSION['id']]);
        $user = $userStmt->fetch(PDO::FETCH_ASSOC);
        
        $userName = trim($user['first_name'] . ' ' . $user['last_name']);
        $message = "New order #$order_id from $userName";
        
        $notificationStmt = $db->prepare("
            INSERT INTO notifications 
            (pharmacy_id, user_id, order_id, message, is_read) 
            VALUES (?, ?, ?, ?, 0)
        ");
        $notificationStmt->execute([
            $shop_id,
            $_SESSION['id'],
            $order_id,
            $message
        ]);
    }
    
    $db->commit();
    
    echo json_encode(['success' => true, 'message' => 'Order placed successfully', 'order_id' => $order_id]);
    
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}