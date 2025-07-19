<?php
include 'scripts/connect.php';
session_start();

if (!isset($_SESSION['shop_id'])) {
    die("Shop not identified.");
}

$shop_id = $_SESSION['shop_id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        try {
            $order_id = $_POST['order_id'];
            
            switch ($_POST['action']) {
                case 'approve':
                    $stmt = $db->prepare("UPDATE orders SET status = 'Approved' WHERE id = :id AND shop_id = :shop_id");
                    $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
                    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
                    $stmt->execute();
                    $_SESSION['message'] = ['type' => 'success', 'text' => 'Order approved successfully'];
                    break;
                    
               case 'cancel':
    try {
        $db->beginTransaction();
        
        // First get all order items to restore quantities
        $itemsStmt = $db->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = :order_id");
        $itemsStmt->bindParam(':order_id', $order_id, PDO::PARAM_INT);
        $itemsStmt->execute();
        $orderItems = $itemsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Restore product quantities
        foreach ($orderItems as $item) {
            $restoreStmt = $db->prepare("UPDATE products SET quantity = quantity + :quantity WHERE id = :product_id");
            $restoreStmt->bindParam(':quantity', $item['quantity'], PDO::PARAM_INT);
            $restoreStmt->bindParam(':product_id', $item['product_id'], PDO::PARAM_INT);
            $restoreStmt->execute();
        }
        
        // Then update the order status
        $stmt = $db->prepare("UPDATE orders SET status = 'Cancelled' WHERE id = :id AND shop_id = :shop_id");
        $stmt->bindParam(':id', $order_id, PDO::PARAM_INT);
        $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $db->commit();
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Order cancelled successfully and quantities restored'];
    } catch (Exception $e) {
        $db->rollBack();
        $_SESSION['message'] = ['type' => 'danger', 'text' => 'Error cancelling order: ' . $e->getMessage()];
    }
    break;
            }
            
            header("Location: order_management.php");
            exit();
        } catch (PDOException $e) {
            die("Database error: " . $e->getMessage());
        }
    }
}

// Get orders for the shop with proper payment status handling
try {
    $stmt = $db->prepare("SELECT 
        o.*, 
        CONCAT(u.first_name, ' ', IFNULL(u.middle_name, ''), ' ', u.last_name) as customer_name,
        COUNT(oi.id) as number_of_items,
        SUM(oi.quantity) as total_quantity,
        u.email, u.phone, u.address,
        CASE 
            WHEN o.payment_method = 'COD' THEN 'Pending'
            ELSE o.payment_status
        END as display_payment_status
    FROM orders o
    LEFT JOIN users u ON o.user_id = u.id
    LEFT JOIN order_items oi ON o.id = oi.order_id
    WHERE o.shop_id = :shop_id
    GROUP BY o.id
    ORDER BY o.created_at DESC");
    
    $stmt->bindParam(':shop_id', $shop_id, PDO::PARAM_INT);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
        
        echo json_encode(['success' => true, 'items' => $items]);
        exit();
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Error loading order items: ' . $e->getMessage()]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | MediBridge</title>
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

    .status-approved {
        background-color: rgba(40, 167, 69, 0.1);
        color: var(--success-color);
    }

    .status-cancelled {
        background-color: rgba(220, 53, 69, 0.1);
        color: var(--danger-color);
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

    .btn-approve {
        background-color: #28a745;
        border-color: #28a745;
        color: white;
    }

    .btn-approve:hover {
        background-color: #218838;
        border-color: #1e7e34;
        color: white;
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
        
        <a href="shop.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Dashboard</span>
        </a>
        
        <div class="nav-item active">
            <i class="fas fa-shopping-cart"></i>
            <span>Order Management</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <div>
                <h1><i class="fas fa-shopping-cart me-2"></i>Order Management</h1>
                <hr style="border-top: 2px solid #1e293b; margin-top: 10px; opacity: 0.5;">
            </div>
        </div>    

        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?= $_SESSION['message']['type'] ?> alert-dismissible fade show" role="alert">
                <?= $_SESSION['message']['text'] ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-list me-2"></i> Orders List</h5>
            </div>
            <div class="card-body">
                <table id="ordersTable" class="table table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="d-none">ID</th>
                            <th>Customer</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Payment</th>
                            <th>Method</th>
                            <th>Delivery</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): 
                            // Delivery status handling
                            $delivery_status = !empty($order['delivery_status']) ? $order['delivery_status'] : 'pending';
                            $delivery_badge_class = 'delivery-' . strtolower($delivery_status);
                            
                            // Payment status
                            $payment_status = $order['display_payment_status'];
                            $payment_badge_class = 'payment-' . strtolower($payment_status);
                        ?>
                        <tr class="clickable-row" 
                            data-id="<?= $order['id'] ?>"
                            data-customer-name="<?= htmlspecialchars($order['customer_name']) ?>"
                            data-number-of-items="<?= $order['number_of_items'] ?>"
                            data-total-quantity="<?= $order['total_quantity'] ?>"
                            data-total-amount="<?= $order['total_amount'] ?>"
                            data-order-status="<?= htmlspecialchars($order['status']) ?>"
                            data-payment-status="<?= htmlspecialchars($payment_status) ?>"
                            data-payment-method="<?= htmlspecialchars($order['payment_method']) ?>"
                            data-order-date="<?= $order['created_at'] ?>"
                            data-delivery-status="<?= $delivery_status ?>"
                            data-email="<?= htmlspecialchars($order['email']) ?>"
                            data-phone="<?= htmlspecialchars($order['phone']) ?>"
                            data-address="<?= htmlspecialchars($order['address']) ?>"
                            data-shipping-charge="<?= $order['shipping_charge'] ?>">
                            <td class="d-none"><?= $order['id'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($order['customer_name']) ?></div>
                            </td>
                            <td><?= $order['total_quantity'] ?> (<?= $order['number_of_items'] ?>)</td>
                            <td>₹<?= number_format($order['total_amount'], 2) ?></td>
                            <td>
                                <span class="badge status-<?= strtolower($order['status']) ?>">
                                    <?= $order['status'] ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge <?= $payment_badge_class ?>">
                                    <?= $payment_status ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($order['payment_method']) ?></td>
                            <td>
                                <span class="badge <?= $delivery_badge_class ?>">
                                    <?= ucfirst($delivery_status) ?>
                                </span>
                            </td>
                            <td><?= date('M d, Y h:i A', strtotime($order['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-file-invoice me-2"></i>Order Details</h5>
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
                                <div><i class="fas fa-map-marker-alt me-2"></i><span id="modalCustomerAddress"></span></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="detail-section">
                                <h5><i class="fas fa-info-circle me-2"></i>Order Summary</h5>
                                <div class="detail-grid">
                                    <div class="detail-item">
                                        <div class="detail-label">Order Status</div>
                                        <div class="detail-value"><span id="modalOrderStatusBadge"></span></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Payment Method</div>
                                        <div class="detail-value" id="modalPaymentMethod"></div>
                                    </div>
                                    
                                    <div class="detail-item">
                                        <div class="detail-label">Payment Status</div>
                                        <div class="detail-value"><span id="modalPaymentStatusBadge" class="badge"></span></div>
                                    </div>
                                    <div class="detail-item">
                                        <div class="detail-label">Delivery Status</div>
                                        <div class="detail-value"><span id="modalDeliveryStatusBadge" class="badge"></span></div>
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
                    <form method="post" action="order_management.php" class="d-inline">
                        <input type="hidden" name="order_id" id="approveOrderId">
                        <input type="hidden" name="action" value="approve">
                        <button type="submit" class="btn btn-approve" id="approveBtn">
                            <i class="fas fa-check"></i> Approve Order
                        </button>
                    </form>
                    
                    <form method="post" action="order_management.php" class="d-inline">
                        <input type="hidden" name="order_id" id="cancelOrderId">
                        <input type="hidden" name="action" value="cancel">
                        <button type="submit" class="btn btn-danger" id="cancelBtn" onclick="return confirm('Are you sure you want to cancel this order?')">
                            <i class="fas fa-times"></i> Cancel Order
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
        var table = $('#ordersTable').DataTable({
            responsive: true,
            dom: '<"top"f>rt<"bottom"lip><"clear">',
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search orders...",
            },
            columnDefs: [
                { targets: [0], visible: false }
            ],
            order: [[8, 'desc']]
        });

        var currentOrderId = null;
        var currentOrderStatus = null;
        var currentDeliveryStatus = null;
        
        // Make rows clickable and show modal with details
        $('#ordersTable tbody').on('click', 'tr', function() {
            var row = $(this);
            currentOrderId = row.data('id');
            currentOrderStatus = row.data('order-status');
            currentDeliveryStatus = row.data('delivery-status');
            var paymentStatus = row.data('payment-status');
            var paymentMethod = row.data('payment-method');
            
            // Set form IDs
            $('#approveOrderId, #cancelOrderId').val(currentOrderId);
            
            // Set customer info
            $('#modalCustomerName').text(row.data('customer-name'));
            $('#modalOrderId').text(currentOrderId);
            $('#modalCustomerEmail').text(row.data('email'));
            $('#modalCustomerPhone').text(row.data('phone'));
            $('#modalCustomerAddress').text(row.data('address'));
            
            // Set order status
            $('#modalOrderStatusBadge').removeClass().addClass('badge status-' + currentOrderStatus.toLowerCase()).text(currentOrderStatus);
            
            // Set payment status
            $('#modalPaymentStatusBadge').removeClass().addClass('badge payment-' + paymentStatus.toLowerCase()).text(paymentStatus);
            
            // Set payment method
            $('#modalPaymentMethod').text(paymentMethod);
            
            // Set delivery status
            $('#modalDeliveryStatusBadge').removeClass().addClass('badge delivery-' + currentDeliveryStatus.toLowerCase()).text(currentDeliveryStatus.charAt(0).toUpperCase() + currentDeliveryStatus.slice(1));
            
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
            updateActionButtons(currentOrderStatus);
            
            // Show modal
            var modal = new bootstrap.Modal(document.getElementById('orderModal'));
            modal.show();
        });

        function loadOrderItems(orderId) {
            $.ajax({
                url: 'order_management.php?get_order_items=1&order_id=' + orderId,
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

        function updateActionButtons(status) {
            // Hide all buttons first
            $('#approveBtn, #cancelBtn').hide();
            
            // Show appropriate buttons based on status
            if (status.toLowerCase() === 'pending') {
                $('#approveBtn, #cancelBtn').show();
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