<?php
include '../scripts/connect.php';
session_start();

header('Content-Type: application/json');

if (!isset($_SESSION['shop_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Shop not identified']);
    exit();
}

if (!isset($_GET['id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID not provided']);
    exit();
}

$order_id = $_GET['id'];
$shop_id = $_SESSION['shop_id'];

try {
    // Fetch order details
    $stmt = $db->prepare("
        SELECT 
            o.id, 
            o.order_date, 
            o.status, 
            o.shipping_charge,
            IFNULL(p.payment_method, 'Cash on Delivery') as payment_method,
            u.first_name, 
            u.last_name, 
            u.email, 
            u.phone, 
            u.address, 
            u.pincode, 
            u.landmark
        FROM orders o
        JOIN users u ON o.user_id = u.id
        LEFT JOIN payments p ON o.payment_id = p.id
        WHERE o.id = :order_id AND o.shop_id = :shop_id
    ");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
    $stmt->execute();
    $order = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$order) {
        echo json_encode(['status' => 'error', 'message' => 'Order not found or unauthorized']);
        exit();
    }

    // Fetch order items
    $stmt = $db->prepare("
        SELECT 
            p.product_name, 
            oi.price, 
            oi.quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id
    ");
    $stmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
    $stmt->execute();
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'order' => [
                'order_date' => date('M j, Y g:i A', strtotime($order['order_date'])),
                'status' => ucfirst($order['status']),
                'payment_method' => $order['payment_method'],
                'shipping_charge' => $order['shipping_charge']
            ],
            'customer' => [
                'name' => $order['first_name'] . ' ' . $order['last_name'],
                'email' => $order['email'],
                'phone' => $order['phone'],
                'address' => $order['address'],
                'pincode' => $order['pincode'],
                'landmark' => $order['landmark']
            ],
            'items' => $items
        ]
    ]);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}
?>