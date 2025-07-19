

<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: index.php");
    exit();
}

// Check if user is delivery personnel (role = 4)
if (!isset($_SESSION['role']) || $_SESSION['role'] != 4) {
    header("Location: unauthorized.php");
    exit();
}

include 'scripts/connect.php';
$delivery_person_id = $_SESSION['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $order_id = $_POST['order_id'];
            
            switch ($_POST['action']) {
                case 'mark_shipped':
                    $stmt = $db->prepare("UPDATE orders SET delivery_status = 'shipped' WHERE id = :id AND delivery_status = 'pending'");
                    $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $_SESSION['message'] = array('type' => 'success', 'text' => 'Order marked as shipped');
                    break;
                    
                case 'mark_delivered':
                    // Update both delivery status and payment status if COD
                    $stmt = $db->prepare("UPDATE orders 
                                         SET delivery_status = 'delivered', 
                                             delivery_date = NOW(),
                                             payment_status = CASE 
                                                 WHEN LOWER(payment_method) = 'cod' THEN 'paid'
                                                 ELSE payment_status
                                             END
                                         WHERE id = :id AND delivery_status = 'shipped'");
                    $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $_SESSION['message'] = array('type' => 'success', 'text' => 'Order marked as delivered');
                    break;
                    
                case 'logout':
                    session_destroy();
                    header("Location: index.php");
                    exit();
                    break;
            }
            
            header("Location: delivery.php");
            exit();
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
}

// Get assigned shops for this delivery person
try {
    $assignedShopsStmt = $db->prepare("SELECT shop_id FROM delivery_assignments WHERE delivery_person_id = :delivery_person_id");
    $assignedShopsStmt->bindParam(':delivery_person_id', $delivery_person_id, PDO::PARAM_INT);
    $assignedShopsStmt->execute();
    $assignedShops = $assignedShopsStmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($assignedShops)) {
        $orders = [];
    } else {
        // Get orders that need delivery (approved orders with pending/shipped status from assigned shops)
        $shopPlaceholders = implode(',', array_fill(0, count($assignedShops), '?'));
        
        $sql = "SELECT 
            o.*, 
            CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) as customer_name,
            COUNT(oi.id) as number_of_items,
            SUM(oi.quantity) as total_quantity,
            u.email, u.phone, u.address, u.pincode as customer_pincode,
            s.shop_name, s.address as shop_address, s.pincode as shop_pincode,
            CASE 
                WHEN o.payment_method = 'COD' THEN 'Pending'
                ELSE o.payment_status
            END as display_payment_status
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        LEFT JOIN order_items oi ON o.id = oi.order_id
        LEFT JOIN shopdetails s ON o.shop_id = s.id
        WHERE o.delivery_status IN ('pending', 'shipped') 
        AND o.status = 'Approved'
        AND o.shop_id IN ($shopPlaceholders)
        GROUP BY o.id
        ORDER BY 
            CASE 
                WHEN o.delivery_status = 'pending' THEN 1
                WHEN o.delivery_status = 'shipped' THEN 2
                ELSE 3
            END,
            o.created_at DESC";
        
        $stmt = $db->prepare($sql);
        $stmt->execute($assignedShops);
        $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    die("Error fetching orders: " . $e->getMessage());
}

// Get order items for modal (if AJAX request)
if (isset($_GET['get_order_items'])) {
    try {
        $stmt = $db->prepare("SELECT 
            oi.*, 
            p.product_name, 
            p.product_img as image, 
            p.description,
            oi.price,
            oi.quantity,
            (oi.price * oi.quantity) as item_total
        FROM order_items oi
        LEFT JOIN products p ON oi.product_id = p.id
        WHERE oi.order_id = :order_id");
        $stmt->bindParam(':order_id', $_GET['order_id'], PDO::PARAM_INT);
        $stmt->execute();
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode(array('success' => true, 'items' => $items));
        exit();
    } catch (PDOException $e) {
        echo json_encode(array('success' => false, 'message' => 'Error loading order items: ' . $e->getMessage()));
        exit();
    }
}

// Get payment status filter from query string
$payment_filter = isset($_GET['payment_status']) ? $_GET['payment_status'] : 'all';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Dashboard | MediBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


    <style>
    :root {
        --primary-color: #1e293b;
        --primary-light: #1e293b;
        --secondary-color: #6c757d;
        --light-color: #f8f9fa;
        --dark-color: #343a40;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --info-color: #17a2b8;
        --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
        --transition: all 0.3s ease;
        --sidebar-bg: #1e293b;
        --sidebar-text: #e2e8f0;
        --sidebar-active: #334155;
        --card-radius: 12px;
        --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.1);
        --shadow-md: 0 4px 6px rgba(0, 0, 0, 0.1);
        --shadow-lg: 0 10px 15px rgba(0, 0, 0, 0.1);
    }

    body {
        font-family: 'Poppins', sans-serif;
        background-color: #f5f7fa;
        color: var(--dark-color);
        line-height: 1.6;
    }

    .sidebar {
        width: 280px;
        background: var(--sidebar-bg);
        color: var(--sidebar-text);
        height: 100vh;
        position: fixed;
        box-shadow: 2px 0 15px rgba(0,0,0,0.1);
        transition: var(--transition);
        z-index: 1000;
    }

    .sidebar-header {
        padding: 25px 20px;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.15);
    }

    .logo-img {
        width: 80px;
        height: 80px;
        object-fit: contain;
        margin-bottom: 15px;
        border-radius: 50%;
        background: white;
        padding: 5px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .logo-text {
        font-size: 22px;
        font-weight: 600;
        margin-top: 10px;
    }

    .nav-item {
        color: rgba(255,255,255,0.9);
        padding: 12px 25px;
        display: flex;
        align-items: center;
        transition: var(--transition);
        margin: 5px 10px;
        border-radius: 6px;
        text-decoration: none;
    }

    .nav-item:hover {
        background: rgba(255,255,255,0.15);
        color: white;
    }

    .nav-item.active {
        background: var(--sidebar-active);
        color: #e2e8f0;
        font-weight: 500;
    }

    .nav-item.logout {
        color: #ff6b6b;
    }

    .nav-item.logout:hover {
        background: rgba(255, 107, 107, 0.15);
    }

    .nav-item i {
        margin-right: 12px;
        width: 20px;
        text-align: center;
        font-size: 18px;
    }

    .main-content {
        margin-left: 280px;
        padding: 30px;
        transition: var(--transition);
    }

    .header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
    }

    .header h1 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
        color: var(--primary-color);
    }

    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 30px;
        overflow: hidden;
        transition: var(--transition);
    }

    .card:hover {
        box-shadow: var(--shadow-lg);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 15px 25px;
        border-bottom: none;
    }

    .card-header h5 {
        margin: 0;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .card-header h5 i {
        margin-right: 10px;
    }

    .table {
        margin-bottom: 0;
        border-collapse: separate;
        border-spacing: 0;
    }

    .table thead th {
        border-bottom: none;
        background-color: #f8f9fa;
        font-weight: 500;
        color: var(--secondary-color);
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.5px;
        padding: 12px 15px;
    }

    .table tbody tr {
        transition: var(--transition);
        cursor: pointer;
    }

    .table tbody tr:hover {
        background-color: rgba(30, 41, 59, 0.05);
        transform: translateY(-2px);
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        border-top: 1px solid #f0f0f0;
    }

    .badge {
        font-weight: 500;
        padding: 6px 12px;
        font-size: 0.75rem;
        border-radius: 20px;
        letter-spacing: 0.5px;
    }

    .status-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .status-processing {
        background-color: rgba(23, 162, 184, 0.1);
        color: var(--info-color);
    }

    .status-completed {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-cancelled {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    .status-shipped {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .status-delivered {
        background-color: rgba(111, 66, 193, 0.1);
        color: #6f42c1;
    }

    .payment-paid {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .payment-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .payment-failed {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
    }

    .delivery-pending {
        background-color: rgba(255, 193, 7, 0.1);
        color: var(--warning-color);
    }

    .delivery-shipped {
        background-color: rgba(13, 110, 253, 0.1);
        color: #0d6efd;
    }

    .delivery-delivered {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .modal-content {
        border: none;
        border-radius: var(--card-radius);
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .modal-header {
        background-color: var(--primary-color);
        color: white;
        padding: 20px;
    }

    .modal-title {
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .modal-title i {
        margin-right: 10px;
    }

    .modal-body {
        padding: 25px;
    }

    .customer-info {
        margin-bottom: 20px;
    }

    .customer-details h4 {
        margin: 0;
        color: var(--primary-color);
        font-weight: 600;
    }

    .customer-details p {
        margin: 5px 0 0;
        color: var(--secondary-color);
    }

    .detail-section {
        margin-bottom: 25px;
    }

    .detail-section h5 {
        color: var(--primary-color);
        margin-bottom: 15px;
        font-weight: 600;
        padding-bottom: 8px;
        border-bottom: 1px solid #eee;
        display: flex;
        align-items: center;
    }

    .detail-section h5 i {
        margin-right: 10px;
    }

    .detail-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 15px;
    }

    .detail-item {
        background: #f8f9fa;
        padding: 12px 15px;
        border-radius: 6px;
    }

    .detail-label {
        font-weight: 500;
        color: var(--secondary-color);
        font-size: 0.85rem;
        margin-bottom: 5px;
    }

    .detail-value {
        font-weight: 500;
        color: var(--dark-color);
    }

    .order-items {
        margin-top: 20px;
    }

    .order-item {
        display: flex;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }

    .order-item:last-child {
        border-bottom: none;
    }

    .order-item-img {
        width: 60px;
        height: 60px;
        object-fit: cover;
        border-radius: 6px;
        margin-right: 15px;
    }

    .order-item-details {
        flex: 1;
    }

    .order-item-name {
        font-weight: 500;
        margin-bottom: 5px;
    }

    .order-item-price {
        color: var(--secondary-color);
        font-size: 0.9rem;
    }

    .order-item-qty {
        color: var(--secondary-color);
        font-size: 0.9rem;
    }

    .order-summary {
        margin-top: 20px;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 6px;
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .summary-row.total {
        font-weight: 600;
        font-size: 1.1rem;
        border-top: 1px solid #eee;
        padding-top: 10px;
        margin-top: 10px;
    }

    .btn {
        border-radius: 6px;
        padding: 10px 20px;
        font-weight: 500;
        transition: var(--transition);
        letter-spacing: 0.5px;
    }

    .btn i {
        margin-right: 8px;
    }

    .btn-delivery {
        background-color: #6f42c1;
        border-color: #6f42c1;
        color: white;
    }

    .btn-delivery:hover {
        background-color: #5e38a8;
        border-color: #5e38a8;
        color: white;
    }

    .btn-mark-delivered {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .btn-mark-delivered:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
    }

    .btn-mark-paid {
        background-color: #17a2b8;
        border-color: #17a2b8;
        color: white;
    }

    .btn-mark-paid:hover {
        background-color: #138496;
        border-color: #117a8b;
        color: white;
    }

    .map-container {
        height: 250px;
        width: 100%;
        border-radius: var(--card-radius);
        overflow: hidden;
        margin-top: 15px;
    }

    @media (max-width: 992px) {
        .sidebar {
            width: 240px;
        }
        .main-content {
            margin-left: 240px;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        .main-content {
            margin-left: 0;
            padding: 20px;
        }
        
        .detail-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
	
	</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="text-center">
                <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
                <div class="logo-text">MediBridge</div>
            </div>
        </div>
        
        <div class="nav-item active">
            <i class="fas fa-truck"></i>
            <span>Delivery Dashboard</span>
        </div>
        
        <form method="post" action="delivery.php" class="nav-item logout">
            <input type="hidden" name="action" value="logout">
            <button type="submit" style="background: none; border: none; color: inherit; padding: 0; width: 100%; text-align: left;">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-truck me-2"></i>Delivery Dashboard</h1>
                <p class="text-muted mb-0">Manage your assigned deliveries</p>
                <hr style="border-top: 2px solid #1e293b; margin-top: 10px; opacity: 0.5;">
            </div>
        </div>    

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?> alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['message']['text']; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>No deliveries assigned to you at this time.
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5><i class="fas fa-list me-2"></i> Assigned Deliveries</h5>
                    <div>
                        <div class="btn-group" role="group">
                            <a href="delivery.php?payment_status=all" class="btn btn-sm <?= $payment_filter == 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">All</a>
                            <a href="delivery.php?payment_status=paid" class="btn btn-sm <?= $payment_filter == 'paid' ? 'btn-primary' : 'btn-outline-primary' ?>">Paid</a>
                            <a href="delivery.php?payment_status=pending" class="btn btn-sm <?= $payment_filter == 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>">Pending</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <table id="deliveriesTable" class="table table-hover" style="width:100%">
                        <thead>
                            <tr>
                                <th class="d-none">ID</th>
                                <th>Customer</th>
                                <th>Shop</th>
                                <th>Items</th>
                                <th>Total</th>
                                <th>Delivery Status</th>
                                <th>Payment</th>
                                <th>Method</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $order): 
                                // Apply payment filter
                                if ($payment_filter != 'all' && strtolower($order['display_payment_status']) != $payment_filter) {
                                    continue;
                                }
                                
                                // Delivery status handling
                                $delivery_status = !empty($order['delivery_status']) ? $order['delivery_status'] : 'pending';
                                $delivery_badge_class = 'delivery-' . strtolower($delivery_status);
                                
                                // Payment status
                                $payment_status = $order['display_payment_status'];
                                $payment_badge_class = 'payment-' . strtolower($payment_status);
                            ?>
                            <tr class="clickable-row" 
                                data-id="<?php echo $order['id']; ?>"
                                data-customer-name="<?php echo htmlspecialchars($order['customer_name']); ?>"
                                data-shop-name="<?php echo htmlspecialchars($order['shop_name']); ?>"
                                data-shop-address="<?php echo htmlspecialchars($order['shop_address']); ?>"
                                data-shop-pincode="<?php echo htmlspecialchars($order['shop_pincode']); ?>"
                                data-number-of-items="<?php echo $order['number_of_items']; ?>"
                                data-total-quantity="<?php echo $order['total_quantity']; ?>"
                                data-total-amount="<?php echo $order['total_amount']; ?>"
                                data-order-status="<?php echo htmlspecialchars($order['status']); ?>"
                                data-payment-status="<?php echo htmlspecialchars($payment_status); ?>"
                                data-payment-method="<?php echo htmlspecialchars($order['payment_method']); ?>"
                                data-order-date="<?php echo $order['created_at']; ?>"
                                data-delivery-status="<?php echo $delivery_status; ?>"
                                data-email="<?php echo htmlspecialchars($order['email']); ?>"
                                data-phone="<?php echo htmlspecialchars($order['phone']); ?>"
                                data-address="<?php echo htmlspecialchars($order['address']); ?>"
                                data-customer-pincode="<?php echo htmlspecialchars($order['customer_pincode']); ?>"
                                data-shipping-charge="<?php echo $order['shipping_charge']; ?>">
                                <td class="d-none"><?php echo $order['id']; ?></td>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($order['customer_name']); ?></div>
                                    <small class="text-muted"><?php echo $order['customer_pincode']; ?></small>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($order['shop_name']); ?></div>
                                    <small class="text-muted"><?php echo $order['shop_pincode']; ?></small>
                                </td>
                                <td><?php echo $order['total_quantity']; ?> (<?php echo $order['number_of_items']; ?>)</td>
                                <td>₹<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="badge <?php echo $delivery_badge_class; ?>">
                                        <?php echo ucfirst($delivery_status); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $payment_badge_class; ?>">
                                        <?php echo $payment_status; ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($order['payment_method']); ?></td>
                                <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Delivery Details Modal -->
    <div class="modal fade" id="deliveryModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-truck me-2"></i>Delivery Details</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="customer-info">
                        <div class="customer-details">
                            <h4 id="modalCustomerName"></h4>
                            <p>Order #<span id="modalOrderId"></span> • <span id="modalOrderDate"></span></p>
                            <div class="mt-2">
                                <div><i class="fas fa-envelope me-2"></i><span id="modalCustomerEmail"></span></div>
                                <div><i class="fas fa-phone me-2"></i><span id="modalCustomerPhone"></span></div>
                                <div><i class="fas fa-map-marker-alt me-2"></i><span id="modalCustomerAddress"></span> (Pincode: <span id="modalCustomerPincode"></span>)</div>
                                <div><i class="fas fa-store me-2"></i><span id="modalShopName"></span> - <span id="modalShopAddress"></span> (Pincode: <span id="modalShopPincode"></span>)</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="detail-section">
                                <h5><i class="fas fa-info-circle me-2"></i>Delivery Summary</h5>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <div class="detail-label">Delivery Status</div>
                                        <div class="detail-value"><span id="modalDeliveryStatusBadge" class="badge"></span></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Payment Method</div>
                                        <div class="detail-value" id="modalPaymentMethod"></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Payment Status</div>
                                        <div class="detail-value"><span id="modalPaymentStatusBadge" class="badge"></span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="detail-section">
                                <h5><i class="fas fa-receipt me-2"></i>Order Details</h5>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <div class="detail-label">Order Date</div>
                                        <div class="detail-value" id="modalOrderDateFull"></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Number of Items</div>
                                        <div class="detail-value" id="modalNumberOfItems"></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Total Quantity</div>
                                        <div class="detail-value" id="modalTotalQuantity"></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Total Amount</div>
                                        <div class="detail-value" id="modalTotalAmount"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="order-items mt-4">
                        <h5><i class="fas fa-boxes me-2"></i>Order Items</h5>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="orderItemsContainer">
                                    <!-- Items will be loaded here via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <div class="order-summary mt-4">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span id="modalSubtotalSummary">₹0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Shipping:</span>
                            <span id="modalShipping">₹0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Tax:</span>
                            <span id="modalTax">₹0.00</span>
                        </div>
                        <div class="summary-row total">
                            <span>Total:</span>
                            <span id="modalTotalAmountSummary">₹0.00</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <form method="post" action="delivery.php" class="d-inline">
                        <input type="hidden" name="order_id" id="shipOrderId">
                        <input type="hidden" name="action" value="mark_shipped">
                        <button type="submit" class="btn btn-delivery" id="shipBtn">
                            <i class="fas fa-truck"></i> Mark as Shipped
                        </button>
                    </form>
                    
                    <form method="post" action="delivery.php" class="d-inline">
                        <input type="hidden" name="order_id" id="deliverOrderId">
                        <input type="hidden" name="action" value="mark_delivered">
                        <button type="submit" class="btn btn-mark-delivered" id="deliverBtn">
                            <i class="fas fa-check-circle"></i> Mark as Delivered
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
$(document).ready(function() {
    var table = $('#deliveriesTable').DataTable({
        responsive: true,
        dom: '<"top"f>rt<"bottom"lip><"clear">',
        language: {
            search: "_INPUT_",
            searchPlaceholder: "Search deliveries...",
        },
        columnDefs: [
            { targets: [0], visible: false }
        ],
        order: [[8, 'desc']]
    });

    var currentOrderId = null;
    var currentDeliveryStatus = null;
    var currentPaymentMethod = null;
    var currentPaymentStatus = null;
    
    // Make rows clickable and show modal with details
    $('#deliveriesTable tbody').on('click', 'tr', function() {
        var row = $(this);
        currentOrderId = row.data('id');
        currentDeliveryStatus = row.data('delivery-status');
        currentPaymentMethod = row.data('payment-method');
        currentPaymentStatus = row.data('payment-status');
        
        // Set form IDs
        $('#shipOrderId, #deliverOrderId').val(currentOrderId);

        // Set customer info
        $('#modalCustomerName').text(row.data('customer-name'));
        $('#modalOrderId').text(currentOrderId);
        $('#modalCustomerEmail').text(row.data('email'));
        $('#modalCustomerPhone').text(row.data('phone'));
        $('#modalCustomerAddress').text(row.data('address'));
        $('#modalCustomerPincode').text(row.data('customer-pincode'));
        $('#modalShopName').text(row.data('shop-name'));
        $('#modalShopAddress').text(row.data('shop-address'));
        $('#modalShopPincode').text(row.data('shop-pincode'));

        // Set delivery status
        $('#modalDeliveryStatusBadge').removeClass().addClass('badge delivery-' + currentDeliveryStatus.toLowerCase()).text(currentDeliveryStatus.charAt(0).toUpperCase() + currentDeliveryStatus.slice(1));
        
        // Set payment status
        $('#modalPaymentStatusBadge').removeClass().addClass('badge payment-' + currentPaymentStatus.toLowerCase()).text(currentPaymentStatus);
        
        // Set payment method
        $('#modalPaymentMethod').text(currentPaymentMethod);
        
        // Format and display dates
        var orderDate = new Date(row.data('order-date'));
        $('#modalOrderDate').text(orderDate.toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric' 
        }));
        
        $('#modalOrderDateFull').text(orderDate.toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }));
        
        // Set item count and amounts
        $('#modalNumberOfItems').text(row.data('number-of-items'));
        $('#modalTotalQuantity').text(row.data('total-quantity'));
        
        var totalAmount = parseFloat(row.data('total-amount'));
        var shippingCharge = parseFloat(row.data('shipping-charge') || 0);
        $('#modalTotalAmount').text('₹' + totalAmount.toFixed(2));
        $('#modalSubtotalSummary').text('₹' + (totalAmount - shippingCharge).toFixed(2));
        $('#modalShipping').text('₹' + shippingCharge.toFixed(2));
        $('#modalTotalAmountSummary').text('₹' + totalAmount.toFixed(2));
        
        // Load order items via AJAX
        loadOrderItems(currentOrderId);
        
        // Show/hide buttons based on current status
        updateActionButtons(currentDeliveryStatus);
        
        // Show modal
        var modal = new bootstrap.Modal(document.getElementById('deliveryModal'));
        modal.show();
    });

    function loadOrderItems(orderId) {
        $.ajax({
            url: 'delivery.php?get_order_items=1&order_id=' + orderId,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    var container = $('#orderItemsContainer');
                    container.empty();
                    
                    if (response.items.length > 0) {
                        var subtotal = 0;
                        response.items.forEach(function(item) {
                            subtotal += parseFloat(item.item_total);
                            var itemHtml = `
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="${item.image || 'images/default-product.png'}" 
                                                 alt="${item.product_name}" 
                                                 class="order-item-img me-3" style="width: 50px; height: 50px;">
                                            <div>
                                                <div class="fw-bold">${item.product_name}</div>
                                                <small class="text-muted">${item.description || 'No description'}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>₹${parseFloat(item.price).toFixed(2)}</td>
                                    <td>${item.quantity}</td>
                                    <td>₹${parseFloat(item.item_total).toFixed(2)}</td>
                                </tr>
                            `;
                            container.append(itemHtml);
                        });
                        
                        // Update subtotal with actual calculated value
                        $('#modalSubtotalSummary').text('₹' + subtotal.toFixed(2));
                        $('#modalTotalAmountSummary').text('₹' + (subtotal + parseFloat($('#modalShipping').text().replace('₹', ''))).toFixed(2));
                    } else {
                        container.html('<tr><td colspan="4" class="text-center">No items found for this order</td></tr>');
                    }
                } else {
                    showToast(response.message || 'Error loading order items', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX Error:', status, error);
                showToast('Error loading order items: ' + error, 'error');
            }
        });
    }

    function updateActionButtons(deliveryStatus) {
        // Hide all buttons first
        $('#shipBtn, #deliverBtn').hide();
        
        // Show appropriate buttons based on status
        switch(deliveryStatus.toLowerCase()) {
            case 'pending':
                $('#shipBtn').show();
                break;
            case 'shipped':
                $('#deliverBtn').show();
                break;
            default:
                $('#shipBtn, #deliverBtn').hide();
        }
    }

    function showToast(message, type) {
        // Create toast element
        var toast = $(`
            <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
                <div class="toast align-items-center text-white bg-${type === 'success' ? 'success' : 'danger'} border-0" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">${message}</div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        `);
        
        // Add to DOM
        $('body').append(toast);
        
        // Initialize and show toast
        var bsToast = new bootstrap.Toast(toast.find('.toast')[0]);
        bsToast.show();
        
        // Remove toast after it's hidden
        toast.find('.toast').on('hidden.bs.toast', function() {
            toast.remove();
        });
    }
});
</script>
</body>
</html>
	
