<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include 'scripts/connect.php';

// Get order ID from URL
$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if ($order_id <= 0) {
    header("Location: user.php");
    exit();
}

// Fetch order details
try {
    $stmt = $db->prepare("
        SELECT o.*, s.shop_name, p.payment_method, p.card_last4
        FROM orders o
        JOIN shopdetails s ON o.shop_id = s.id
        LEFT JOIN payments p ON o.id = p.id
        WHERE o.id = ? AND o.user_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['id']]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$order) {
        throw new Exception("Order not found");
    }
    
    // Fetch order items
    $stmt = $db->prepare("
        SELECT oi.*, p.product_img
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Calculate total
    $subtotal = array_reduce($items, fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);
    $total = $subtotal + $order['shipping_charge'];
    
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation | MediBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Add your styles here */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        .confirmation-card {
            max-width: 800px;
            margin: 50px auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.1);
            background: white;
        }
        .order-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .order-header i {
            font-size: 4rem;
            color: #3bb77e;
            margin-bottom: 20px;
        }
        .order-item {
            display: flex;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }
        .order-item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
            margin-right: 15px;
        }
        .total-row {
            font-size: 1.1rem;
            font-weight: 500;
        }
        .grand-total {
            font-size: 1.3rem;
            font-weight: 600;
            color: #253d4e;
        }
    </style>
</head>
<body>
<?php include 'templates/header.php'; ?>
    
    <div class="confirmation-card">
        <div class="order-header">
            <i class="fas fa-check-circle"></i>
            <h1>Thank You for Your Order!</h1>
            <p class="lead">Your order has been placed successfully</p>
            <p>Order ID: <strong>#<?= $order_id ?></strong></p>
        </div>
        
        <div class="row mb-4">
            <div class="col-md-6">
                <h4>Delivery Information</h4>
                <p>
                    <strong>Status:</strong> <?= ucfirst($order['status']) ?><br>
                    <strong>Shop:</strong> <?= htmlspecialchars($order['shop_name']) ?><br>
                    <strong>Estimated Delivery:</strong> Within some hours
                </p>
            </div>
            <div class="col-md-6">
                <h4>Payment Information</h4>
                <p>
                    <strong>Payment Method:</strong> 
                    <?= $order['payment_method'] === 'credit_card' 
                        ? 'Credit Card ending with ' . $order['card_last4'] 
                        : 'Cash on Delivery' ?><br>
                    <strong>Total Paid:</strong> ₹<?= number_format($total, 2) ?><br>
                    <strong>Order Date:</strong> <?= date('F j, Y', strtotime($order['order_date'])) ?>
                </p>
            </div>
        </div>
        
        <h4>Order Items</h4>
        <div class="order-items mb-4">
            <?php foreach ($items as $item): ?>
                <div class="order-item">
                    <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'default-product.jpg') ?>" 
                         alt="<?= htmlspecialchars($item['product_name']) ?>"
                         onerror="this.src='images/default-product.jpg'">
                    <div style="flex: 1;">
                        <h5><?= htmlspecialchars($item['product_name']) ?></h5>
                        <div>Qty: <?= $item['quantity'] ?></div>
                    </div>
                    <div class="text-end">
                        <div>₹<?= number_format($item['price'], 2) ?> each</div>
                        <div class="fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="row justify-content-end">
            <div class="col-md-6">
                <div class="total-row d-flex justify-content-between">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                <div class="total-row d-flex justify-content-between">
                    <span>Shipping</span>
                    <span>₹<?= number_format($order['shipping_charge'], 2) ?></span>
                </div>
                <div class="total-row grand-total d-flex justify-content-between mt-2">
                    <span>Total</span>
                    <span>₹<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5">
            <a href="user.php" class="btn btn-primary btn-lg">
                <i class="fas fa-home me-2"></i> Back to Home
            </a>
            <a href="orders.php" class="btn btn-outline-primary btn-lg ms-3">
                <i class="fas fa-list me-2"></i> View All Orders
            </a>
        </div>
    </div>
    <?php include 'templates/footer.php'; ?>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>