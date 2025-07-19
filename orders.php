<?php
// orders.php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'scripts/connect.php';

// Get user details
$userStmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get all orders for the user with payment method
$ordersStmt = $db->prepare("
    SELECT o.id, o.order_date, o.total_amount, o.payment_method,
           COALESCE(NULLIF(o.status, ''), o.delivery_status) as status, 
           o.delivery_status as delivery_status,
           COUNT(oi.id) as item_count, 
           GROUP_CONCAT(DISTINCT s.shop_name SEPARATOR ', ') as shops
    FROM orders o
    JOIN order_items oi ON o.id = oi.order_id
    JOIN products p ON oi.product_id = p.id
    JOIN shopdetails s ON p.shop_id = s.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.order_date DESC
");
$ordersStmt->execute([$_SESSION['id']]);
$orders = $ordersStmt->fetchAll(PDO::FETCH_ASSOC);

// Function to get order items (used in the order card template)
function getOrderItems($db, $orderId) {
    $stmt = $db->prepare("
        SELECT oi.*, p.product_name, p.product_img, p.price, s.shop_name
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN shopdetails s ON p.shop_id = s.id
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$orderId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Function to check if order can be cancelled
function canCancelOrder($status, $deliveryStatus, $paymentMethod) {
    $status = strtolower($status);
    $deliveryStatus = strtolower($deliveryStatus);
    $paymentMethod = strtolower($paymentMethod);
    
    // Already cancelled
    if ($status === 'cancelled' || $deliveryStatus === 'cancelled') {
        return false;
    }
    
    // COD orders can be cancelled unless delivered
    if ($paymentMethod === 'cod' && $deliveryStatus !== 'delivered') {
        return true;
    }
    
    // Non-COD orders can only be cancelled when pending or processing
    return in_array($status, ['pending', 'processing']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Orders - MediBridge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2a7fba;
            --secondary-color: #3bb77e;
            --accent-color: #ff7e33;
            --dark-color: #253d4e;
            --light-color: #f7f8fa;
            --text-color: #7e7e7e;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .order-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .order-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .order-header {
            border-bottom: 1px solid #eee;
            padding: 15px 20px;
        }
        
        .order-body {
            padding: 20px;
        }
        
        .order-item {
            padding: 15px 0;
            border-bottom: 1px solid #f5f5f5;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .product-img {
            max-height: 120px;
            width: auto;
            object-fit: contain;
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .status-badge .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }
        
        .status-processing {
            background-color: #fff3cd;
            color: #856404;
        }
        
        .status-processing .status-dot {
            background-color: #ffc107;
        }
        
        .status-shipped {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-shipped .status-dot {
            background-color: #0d6efd;
        }
        
        .status-delivered {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-delivered .status-dot {
            background-color: #198754;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled .status-dot {
            background-color: #dc3545;
        }
        
        .order-timeline {
            position: relative;
            padding-left: 30px;
            margin-top: 20px;
        }
        
        .order-timeline::before {
            content: '';
            position: absolute;
            left: 10px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #eee;
        }
        
        .timeline-step {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-step::before {
            content: '';
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: #ddd;
        }
        
        .timeline-step.active::before {
            background-color: var(--secondary-color);
        }
        
        .timeline-step.completed::before {
            background-color: var(--secondary-color);
        }
        
        .timeline-step.cancelled::before {
            background-color: #dc3545;
        }
        
        .order-details-btn {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
        }
        
        .order-details-btn:hover {
            text-decoration: underline;
        }
        
        .empty-orders {
            text-align: center;
            padding: 50px 0;
        }
    </style>
</head>
<body>
    <div id="header">
        <?php include 'templates/header.php'; ?>
    </div>

    <!-- Orders Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-12">
                <h2 class="fw-bold mb-4">Your Orders</h2>
                
                <?php if (empty($orders)): ?>
                    <div class="empty-orders">
                        <i class="fas fa-box-open fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">You haven't placed any orders yet</h4>
                        <a href="user.php" class="btn btn-primary mt-3">Start Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <ul class="nav nav-tabs" id="ordersTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" type="button" role="tab">All Orders</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="current-tab" data-bs-toggle="tab" data-bs-target="#current" type="button" role="tab">Current</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="completed-tab" data-bs-toggle="tab" data-bs-target="#completed" type="button" role="tab">Completed</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="cancelled-tab" data-bs-toggle="tab" data-bs-target="#cancelled" type="button" role="tab">Cancelled</button>
                            </li>
                        </ul>
                        
                        <div class="tab-content py-3" id="ordersTabContent">
                            <div class="tab-pane fade show active" id="all" role="tabpanel">
                                <?php foreach ($orders as $order): ?>
                                    <div class="order-card" id="order-<?= $order['id'] ?>">
                                        <div class="order-header d-flex justify-content-between align-items-center">
                                            <div>
                                                <span class="fw-bold">Order #<?= $order['id'] ?></span>
                                                <span class="text-muted ms-3">Placed on <?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                                                <?php if ($order['payment_method'] === 'cod'): ?>
                                                    <span class="badge bg-warning ms-2">COD</span>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <?php
                                                $statusToShow = !empty($order['delivery_status']) ? $order['delivery_status'] : $order['status'];
                                                $statusClass = '';
                                                $statusDotColor = '';
                                                switch (strtolower($statusToShow)) {
                                                    case 'pending':
                                                    case 'processing':
                                                        $statusClass = 'status-processing';
                                                        $statusDotColor = '#ffc107';
                                                        break;
                                                    case 'shipped':
                                                        $statusClass = 'status-shipped';
                                                        $statusDotColor = '#0d6efd';
                                                        break;
                                                    case 'delivered':
                                                        $statusClass = 'status-delivered';
                                                        $statusDotColor = '#198754';
                                                        break;
                                                    case 'cancelled':
                                                        $statusClass = 'status-cancelled';
                                                        $statusDotColor = '#dc3545';
                                                        break;
                                                    default:
                                                        $statusClass = 'status-processing';
                                                        $statusDotColor = '#ffc107';
                                                }
                                                ?>
                                                <span class="status-badge <?= $statusClass ?>">
                                                    <span class="status-dot"></span>
                                                    <?= ucfirst($statusToShow) ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="order-body">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    <?php 
                                                    $orderItems = getOrderItems($db, $order['id']);
                                                    foreach (array_slice($orderItems, 0, 2) as $item): ?>
                                                        <div class="order-item row">
                                                            <div class="col-2">
                                                                <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'uploads/default_product.jpg') ?>" 
                                                                     alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                                                     class="img-fluid product-img">
                                                            </div>
                                                            <div class="col-6">
                                                                <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                                                <p class="text-muted mb-1">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                                                                <p class="text-success fw-bold mb-0">₹<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                                            </div>
                                                            <div class="col-4 text-end">
                                                                <p class="fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                    
                                                    <?php if (count($orderItems) > 2): ?>
                                                        <div class="text-center py-2">
                                                            <span class="text-muted">+ <?= count($orderItems) - 2 ?> more items</span>
                                                        </div>
                                                    <?php endif; ?>
                                                    
                                                    <div class="mt-3">
                                                        <?php if (canCancelOrder($order['status'], $order['delivery_status'], $order['payment_method'])): ?>
                                                            <button class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="<?= $order['id'] ?>">
                                                                Cancel Order
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-4">
                                                    <div class="order-timeline">
                                                        <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'completed' : (strtolower($statusToShow) === 'delivered' ? 'completed' : 'active') ?>">
                                                            <h6 class="mb-1">Order Placed</h6>
                                                            <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                                                        </div>
                                                        
                                                        <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'cancelled' : (strtolower($statusToShow) === 'delivered' ? 'completed' : (strtolower($statusToShow) === 'shipped' ? 'active' : '')) ?>">
                                                            <h6 class="mb-1">Shipped</h6>
                                                            <?php if (in_array(strtolower($statusToShow), ['delivered', 'shipped'])): ?>
                                                                <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 172800) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'cancelled' : (strtolower($statusToShow) === 'delivered' ? 'completed' : '') ?>">
                                                            <h6 class="mb-1">Delivered</h6>
                                                            <?php if (strtolower($statusToShow) === 'delivered'): ?>
                                                                <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 259200) ?></p>
                                                            <?php endif; ?>
                                                        </div>
                                                        
                                                        <?php if (strtolower($statusToShow) === 'cancelled'): ?>
                                                            <div class="timeline-step cancelled">
                                                                <h6 class="mb-1">Cancelled</h6>
                                                                <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 86400) ?></p>
                                                            </div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Replace the commented section for Current, Completed, and Cancelled tabs with this code -->
<div class="tab-pane fade" id="current" role="tabpanel">
    <?php 
    $currentOrders = array_filter($orders, function($order) {
        $status = strtolower($order['delivery_status'] ?: strtolower($order['status']));
        return in_array($status, ['pending', 'processing', 'shipped']);
    });
    
    if (empty($currentOrders)): ?>
        <div class="text-center py-4 text-muted">
            No current orders
        </div>
    <?php else: ?>
        <?php foreach ($currentOrders as $order): 
            $statusToShow = !empty($order['delivery_status']) ? $order['delivery_status'] : $order['status'];
            $statusClass = '';
            $statusDotColor = '';
            switch (strtolower($statusToShow)) {
                case 'pending':
                case 'processing':
                    $statusClass = 'status-processing';
                    $statusDotColor = '#ffc107';
                    break;
                case 'shipped':
                    $statusClass = 'status-shipped';
                    $statusDotColor = '#0d6efd';
                    break;
                case 'delivered':
                    $statusClass = 'status-delivered';
                    $statusDotColor = '#198754';
                    break;
                case 'cancelled':
                    $statusClass = 'status-cancelled';
                    $statusDotColor = '#dc3545';
                    break;
                default:
                    $statusClass = 'status-processing';
                    $statusDotColor = '#ffc107';
            }
            ?>
            <div class="order-card" id="order-<?= $order['id'] ?>">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">Order #<?= $order['id'] ?></span>
                        <span class="text-muted ms-3">Placed on <?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                        <?php if ($order['payment_method'] === 'cod'): ?>
                            <span class="badge bg-warning ms-2">COD</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status-badge <?= $statusClass ?>">
                            <span class="status-dot"></span>
                            <?= ucfirst($statusToShow) ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php 
                            $orderItems = getOrderItems($db, $order['id']);
                            foreach (array_slice($orderItems, 0, 2) as $item): ?>
                                <div class="order-item row">
                                    <div class="col-2">
                                        <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'uploads/default_product.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="img-fluid product-img">
                                    </div>
                                    <div class="col-6">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <p class="text-muted mb-1">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                                        <p class="text-success fw-bold mb-0">₹<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p class="fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($orderItems) > 2): ?>
                                <div class="text-center py-2">
                                    <span class="text-muted">+ <?= count($orderItems) - 2 ?> more items</span>
                                </div>
                            <?php endif; ?>
                            
                            <div class="mt-3">
                                <?php if (canCancelOrder($order['status'], $order['delivery_status'], $order['payment_method'])): ?>
                                    <button class="btn btn-sm btn-outline-danger cancel-order-btn" data-order-id="<?= $order['id'] ?>">
                                        Cancel Order
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="order-timeline">
                                <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'completed' : (strtolower($statusToShow) === 'delivered' ? 'completed' : 'active') ?>">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                                </div>
                                
                                <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'cancelled' : (strtolower($statusToShow) === 'delivered' ? 'completed' : (strtolower($statusToShow) === 'shipped' ? 'active' : '')) ?>">
                                    <h6 class="mb-1">Shipped</h6>
                                    <?php if (in_array(strtolower($statusToShow), ['delivered', 'shipped'])): ?>
                                        <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 172800) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="timeline-step <?= strtolower($statusToShow) === 'cancelled' ? 'cancelled' : (strtolower($statusToShow) === 'delivered' ? 'completed' : '') ?>">
                                    <h6 class="mb-1">Delivered</h6>
                                    <?php if (strtolower($statusToShow) === 'delivered'): ?>
                                        <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 259200) ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if (strtolower($statusToShow) === 'cancelled'): ?>
                                    <div class="timeline-step cancelled">
                                        <h6 class="mb-1">Cancelled</h6>
                                        <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 86400) ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="tab-pane fade" id="completed" role="tabpanel">
    <?php 
    $completedOrders = array_filter($orders, function($order) {
        $status = strtolower($order['delivery_status'] ?: strtolower($order['status']));
        return $status === 'delivered';
    });
    
    if (empty($completedOrders)): ?>
        <div class="text-center py-4 text-muted">
            No completed orders
        </div>
    <?php else: ?>
        <?php foreach ($completedOrders as $order): 
            $statusToShow = !empty($order['delivery_status']) ? $order['delivery_status'] : $order['status'];
            $statusClass = 'status-delivered';
            $statusDotColor = '#198754';
            ?>
            <div class="order-card" id="order-<?= $order['id'] ?>">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">Order #<?= $order['id'] ?></span>
                        <span class="text-muted ms-3">Placed on <?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                        <?php if ($order['payment_method'] === 'cod'): ?>
                            <span class="badge bg-warning ms-2">COD</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status-badge <?= $statusClass ?>">
                            <span class="status-dot"></span>
                            <?= ucfirst($statusToShow) ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php 
                            $orderItems = getOrderItems($db, $order['id']);
                            foreach (array_slice($orderItems, 0, 2) as $item): ?>
                                <div class="order-item row">
                                    <div class="col-2">
                                        <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'uploads/default_product.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="img-fluid product-img">
                                    </div>
                                    <div class="col-6">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <p class="text-muted mb-1">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                                        <p class="text-success fw-bold mb-0">₹<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p class="fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($orderItems) > 2): ?>
                                <div class="text-center py-2">
                                    <span class="text-muted">+ <?= count($orderItems) - 2 ?> more items</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="order-timeline">
                                <div class="timeline-step completed">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                                </div>
                                
                                <div class="timeline-step completed">
                                    <h6 class="mb-1">Shipped</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 172800) ?></p>
                                </div>
                                
                                <div class="timeline-step completed">
                                    <h6 class="mb-1">Delivered</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 259200) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div class="tab-pane fade" id="cancelled" role="tabpanel">
    <?php 
    $cancelledOrders = array_filter($orders, function($order) {
        $status = strtolower($order['delivery_status'] ?: strtolower($order['status']));
        return $status === 'cancelled';
    });
    
    if (empty($cancelledOrders)): ?>
        <div class="text-center py-4 text-muted">
            No cancelled orders
        </div>
    <?php else: ?>
        <?php foreach ($cancelledOrders as $order): 
            $statusToShow = !empty($order['delivery_status']) ? $order['delivery_status'] : $order['status'];
            $statusClass = 'status-cancelled';
            $statusDotColor = '#dc3545';
            ?>
            <div class="order-card" id="order-<?= $order['id'] ?>">
                <div class="order-header d-flex justify-content-between align-items-center">
                    <div>
                        <span class="fw-bold">Order #<?= $order['id'] ?></span>
                        <span class="text-muted ms-3">Placed on <?= date('M d, Y', strtotime($order['order_date'])) ?></span>
                        <?php if ($order['payment_method'] === 'cod'): ?>
                            <span class="badge bg-warning ms-2">COD</span>
                        <?php endif; ?>
                    </div>
                    <div>
                        <span class="status-badge <?= $statusClass ?>">
                            <span class="status-dot"></span>
                            <?= ucfirst($statusToShow) ?>
                        </span>
                    </div>
                </div>
                
                <div class="order-body">
                    <div class="row">
                        <div class="col-md-8">
                            <?php 
                            $orderItems = getOrderItems($db, $order['id']);
                            foreach (array_slice($orderItems, 0, 2) as $item): ?>
                                <div class="order-item row">
                                    <div class="col-2">
                                        <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'uploads/default_product.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="img-fluid product-img">
                                    </div>
                                    <div class="col-6">
                                        <h6 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h6>
                                        <p class="text-muted mb-1">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                                        <p class="text-success fw-bold mb-0">₹<?= number_format($item['price'], 2) ?> × <?= $item['quantity'] ?></p>
                                    </div>
                                    <div class="col-4 text-end">
                                        <p class="fw-bold">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                            
                            <?php if (count($orderItems) > 2): ?>
                                <div class="text-center py-2">
                                    <span class="text-muted">+ <?= count($orderItems) - 2 ?> more items</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="order-timeline">
                                <div class="timeline-step completed">
                                    <h6 class="mb-1">Order Placed</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date'])) ?></p>
                                </div>
                                
                                <div class="timeline-step cancelled">
                                    <h6 class="mb-1">Cancelled</h6>
                                    <p class="text-muted small mb-0"><?= date('M d, Y', strtotime($order['order_date']) + 86400) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div id="footer">
        <?php include 'templates/footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Cancel order button
        $(document).on('click', '.cancel-order-btn', function() {
            if (confirm('Are you sure you want to cancel this order?')) {
                const orderId = $(this).data('order-id');
                const orderCard = $(this).closest('.order-card');
                const button = $(this);
                
                button.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Cancelling...');
                
                $.ajax({
                    url: 'scripts/cancel_order.php',
                    method: 'POST',
                    data: { 
                        order_id: orderId 
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Create and show success message
                            const successMsg = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                                                '<strong>Success!</strong> Order cancelled successfully.' +
                                                '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                                '</div>');
                            
                            // Insert the message above the orders list
                            $('#ordersTabContent').prepend(successMsg);
                            
                            // Update the order card UI
                            orderCard.find('.status-badge')
                                .removeClass('status-processing status-shipped status-pending')
                                .addClass('status-cancelled')
                                .find('.status-dot')
                                .css('background-color', '#dc3545');
                                
                            orderCard.find('.status-badge').text('Cancelled');
                            orderCard.find('.cancel-order-btn').remove();
                            
                            // Update timeline
                            orderCard.find('.timeline-step').removeClass('active');
                            orderCard.find('.timeline-step:contains("Cancelled")').addClass('completed');
                            
                            // Move the order card to the cancelled section
                            const cancelledTab = $('#cancelled');
                            orderCard.appendTo(cancelledTab);
                            
                            // Check if cancelled section was empty
                            if (cancelledTab.find('.text-center.py-4.text-muted').length > 0) {
                                cancelledTab.find('.text-center.py-4.text-muted').remove();
                            }
                            
                            // Auto-hide the success message after 5 seconds
                            setTimeout(() => {
                                successMsg.alert('close');
                            }, 5000);
                            
                        } else {
                            button.prop('disabled', false).text('Cancel Order');
                            const errorMsg = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                              '<strong>Error!</strong> ' + (response.message || 'Failed to cancel order') +
                                              '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                              '</div>');
                            $('#ordersTabContent').prepend(errorMsg);
                            setTimeout(() => {
                                errorMsg.alert('close');
                            }, 5000);
                        }
                    },
                    error: function(xhr, status, error) {
                        button.prop('disabled', false).text('Cancel Order');
                        const errorMsg = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                                          '<strong>Error!</strong> Failed to cancel order. Please try again.' +
                                          '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                                          '</div>');
                        $('#ordersTabContent').prepend(errorMsg);
                        setTimeout(() => {
                            errorMsg.alert('close');
                        }, 5000);
                        console.error('AJAX Error:', status, error);
                    }
                });
            }
        });
    });
    </script>
</body>
</html>