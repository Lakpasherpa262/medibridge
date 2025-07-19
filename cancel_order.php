<?php
// scripts/cancel_order.php
session_start();

if (!isset($_SESSION['id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['order_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

include 'connect.php';

$orderId = $_POST['order_id'];
$userId = $_SESSION['id'];

try {
    // Check if order belongs to user and get payment method and current statuses
    $stmt = $db->prepare("SELECT id, payment_method, status, delivery_status FROM orders WHERE id = ? AND user_id = ?");
    $stmt->execute([$orderId, $userId]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'Order not found or does not belong to you'
        ]);
        exit();
    }
    
    // Check if order is already cancelled in either status field
    if (strtolower($order['status']) === 'cancelled' || strtolower($order['delivery_status']) === 'cancelled') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'Order is already cancelled'
        ]);
        exit();
    }
    
    // For COD orders, allow cancellation unless delivered
    $isCod = strtolower($order['payment_method']) === 'cod';
    $currentStatus = strtolower($order['status']);
    $currentDeliveryStatus = strtolower($order['delivery_status']);
    
    // Check cancellation eligibility
    if (!$isCod && !in_array($currentStatus, ['pending', 'processing'])) {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'Non-COD orders can only be cancelled when in pending or processing status'
        ]);
        exit();
    }
    
    // Check if COD order is already delivered
    if ($isCod && $currentDeliveryStatus === 'delivered') {
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error', 
            'message' => 'COD orders cannot be cancelled after delivery'
        ]);
        exit();
    }
    
    // Begin transaction
    $db->beginTransaction();
    
    // First, get all order items to restore quantities
    $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $itemsStmt->execute([$orderId]);
    $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Restore product quantities
    foreach ($orderItems as $item) {
        $restoreStmt = $db->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $restoreStmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Update both status fields
    $updateStmt = $db->prepare("UPDATE orders SET status = 'cancelled', delivery_status = 'cancelled', updated_at = NOW() WHERE id = ?");
    $updateSuccess = $updateStmt->execute([$orderId]);
    
    if (!$updateSuccess) {
        throw new Exception("Failed to update order status");
    }
    
    // Update order items status if they have a status column
    try {
        $updateItemsStmt = $db->prepare("UPDATE order_items SET status = 'cancelled' WHERE order_id = ?");
        $updateItemsStmt->execute([$orderId]);
    } catch (PDOException $e) {
        // If order_items doesn't have a status column, just continue
        error_log("Note: order_items table may not have status column: " . $e->getMessage());
    }
    
    // Commit transaction
    $db->commit();
    
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'success', 
        'message' => 'Order cancelled successfully',
        'debug' => [
            'order_id' => $orderId,
            'previous_status' => $order['status'],
            'previous_delivery_status' => $order['delivery_status']
        ]
    ]);
    
} catch (PDOException $e) {
    // Roll back transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => 'Database error',
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
} catch (Exception $e) {
    // Roll back transaction on error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}