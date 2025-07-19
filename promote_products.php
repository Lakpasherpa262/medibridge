<?php
session_start();
include 'scripts/connect.php';

// Clear any expired subscriptions and their featured products
try {
    $db->query("
        UPDATE products p
        JOIN featured_subscriptions fs ON p.shop_id = fs.shop_id
        SET p.is_featured = 0
        WHERE fs.end_date < NOW()
    ");
    $db->query("DELETE FROM featured_subscriptions WHERE end_date < NOW()");
    
} catch (PDOException $e) {
    error_log("Subscription cleanup error: " . $e->getMessage());
}

// Verify shop ID and owner
$shop_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['shop_id']) ? intval($_SESSION['shop_id']) : null);
$shopOwner_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

if (!$shop_id || !$shopOwner_id) {
    header("Location: login.php");
    exit();
}

// Process payment if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $selected_products = isset($_POST['selected_products']) ? json_decode($_POST['selected_products'], true) : [];
        $plan_type = $_POST['plan_type'];
        $payment_method = $_POST['payment_method'];
        
        if (empty($selected_products)) {
            throw new Exception("No products selected");
        }
        
        // Validate exactly 20 products (5 per category)
        $categoryCounts = [];
        foreach ($selected_products as $product_id) {
            $stmt = $db->prepare("SELECT category FROM products WHERE id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($product) {
                $category_id = $product['category'];
                if (!isset($categoryCounts[$category_id])) {
                    $categoryCounts[$category_id] = 0;
                }
                $categoryCounts[$category_id]++;
            }
        }
        
        // Check if we have exactly 5 products from each of the 4 categories
        if (count($categoryCounts) !== 4) {
            throw new Exception("Please select products from all 4 required categories");
        }
        
        foreach ($categoryCounts as $category_id => $count) {
            if ($count !== 5) {
                $stmt = $db->prepare("SELECT CategoryName FROM category WHERE C_Id = ?");
                $stmt->execute([$category_id]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                throw new Exception("Please select exactly 5 products from the " . $category['CategoryName'] . " category");
            }
        }
        
        // Calculate end date based on plan type
        $duration_days = 7; // default to standard plan
        if ($plan_type === 'premium') {
            $duration_days = 14;
        } elseif ($plan_type === 'enterprise') {
            $duration_days = 30;
        }
        
        $end_date = date('Y-m-d H:i:s', strtotime("+$duration_days days"));
        $payment_amount = calculateTotal($plan_type);
        
        // First, clear any existing promotions for this shop that are expired
        $clearStmt = $db->prepare("UPDATE products SET is_featured = 0 WHERE shop_id = ? AND featured_end_date < NOW()");
        $clearStmt->execute([$shop_id]);
        
        // Update products to be featured
        $updateStmt = $db->prepare("UPDATE products SET is_featured = 1, featured_start_date = NOW(), featured_end_date = ? WHERE id = ? AND shop_id = ?");
        
        foreach ($selected_products as $product_id) {
            $updateStmt->execute([$end_date, $product_id, $shop_id]);
        }
        
        // Create featured subscription record (updated to use payment_amount)
        $subscriptionStmt = $db->prepare("
            INSERT INTO featured_subscriptions (shop_id, start_date, end_date, plan_type, payment_method, payment_amount, status) 
            VALUES (?, NOW(), ?, ?, ?, ?, 'paid')
        ");
        $subscriptionStmt->execute([$shop_id, $end_date, $plan_type, $payment_method, $payment_amount]);
        
        // Clear selected products from session
        unset($_SESSION['selected_products']);
        
        // Set success message in session
        $_SESSION['promotion_success'] = "Payment processed successfully! Your products will be featured until " . date('F j, Y', strtotime($end_date));
        
        // Redirect to avoid form resubmission
        header("Location: promote_products.php?id=" . $shop_id);
        exit();
        
    } catch (Exception $e) {
        $_SESSION['promotion_error'] = $e->getMessage();
        header("Location: promote_products.php?id=" . $shop_id);
        exit();
    }
}

function calculateTotal($plan_type) {
    $prices = [
        'standard' => 999,
        'premium' => 1799,
        'enterprise' => 3999
    ];
    return $prices[$plan_type] ?? 0;
}

try {
    // Verify shop ownership
    $stmt = $db->prepare("SELECT id, shop_name, shop_image FROM shopdetails WHERE id = ? AND shopOwner_id = ?");
    $stmt->execute([$shop_id, $shopOwner_id]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shop) {
        throw new Exception("Shop not found or you don't have permission to view this shop.");
    }
    
    $_SESSION['shop_id'] = $shop['id'];

    // Get selected products from session
    $selectedProductIds = isset($_SESSION['selected_products']) ? $_SESSION['selected_products'] : [];
    
    // Get details of selected products
    $selectedProducts = [];
    if (!empty($selectedProductIds)) {
        $placeholders = implode(',', array_fill(0, count($selectedProductIds), '?'));
        $query = "SELECT id, product_name, price, product_img FROM products WHERE id IN ($placeholders)";
        $stmt = $db->prepare($query);
        $stmt->execute($selectedProductIds);
        $selectedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
} catch (PDOException $e) {
    $_SESSION['promotion_error'] = "Database error: " . $e->getMessage();
} catch (Exception $e) {
    $_SESSION['promotion_error'] = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Promote Products | <?php echo htmlspecialchars($shop['shop_name'] ?? 'Shop Dashboard'); ?> | MediBridge</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome@6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
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
      --card-shadow: 0 4px 6px rgba(0,0,0,0.1);
      --transition: all 0.3s ease;
      --sidebar-bg: #1e293b;
      --sidebar-text: #e2e8f0;
      --sidebar-active: #334155;
      --card-radius: 12px;
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
      background: #334155;
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
      padding-bottom: 20px;
      margin-bottom: 25px;
      border-bottom: 1px solid rgba(0,0,0,0.1);
    }
    
    .header h1 {
      margin: 0;
      font-size: 24px;
      font-weight: 600;
      color: var(--primary-color);
      display: flex;
      align-items: center;
    }
    
    .header h1 i {
      margin-right: 10px;
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
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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

    /* Promotion Page Specific Styles */
    .promotion-container {
      background: white;
      border-radius: var(--card-radius);
      padding: 30px;
      box-shadow: var(--card-shadow);
    }
    
    .promotion-header {
      text-align: center;
      margin-bottom: 40px;
      padding-bottom: 20px;
      border-bottom: 1px solid #eee;
    }
    
    .promotion-header h2 {
      color: var(--primary-color);
      font-weight: 600;
      margin-bottom: 10px;
    }
    
    .promotion-plans {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
      gap: 25px;
      margin-bottom: 40px;
    }
    
    .plan-card {
      border: 1px solid #e2e8f0;
      border-radius: var(--card-radius);
      padding: 25px;
      transition: var(--transition);
      position: relative;
      background: white;
    }
    
    .plan-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow-lg);
    }
    
    .plan-card.popular {
      border: 2px solid var(--accent-color);
    }
    
    .popular-badge {
      position: absolute;
      top: -12px;
      right: 20px;
      background: var(--accent-color);
      color: white;
      padding: 5px 15px;
      border-radius: 20px;
      font-size: 12px;
      font-weight: 600;
    }
    
    .plan-name {
      font-size: 22px;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 15px;
    }
    
    .plan-price {
      font-size: 28px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 20px;
    }
    
    .plan-price span {
      font-size: 16px;
      font-weight: 400;
      color: var(--medium-text);
    }
    
    .plan-features {
      list-style: none;
      padding: 0;
      margin-bottom: 25px;
    }
    
    .plan-features li {
      padding: 8px 0;
      display: flex;
      align-items: center;
      color: var(--dark-text);
    }
    
    .plan-features li i {
      color: var(--secondary-color);
      margin-right: 10px;
      width: 20px;
      text-align: center;
    }
    
    .select-plan-btn {
      width: 100%;
      padding: 12px;
      background: var(--primary-color);
      color: white;
      border: none;
      border-radius: var(--card-radius);
      font-weight: 600;
      transition: var(--transition);
      cursor: pointer;
    }
    
    .select-plan-btn:hover {
      background: var(--primary-dark);
    }
    
    .products-selection {
      margin: 40px 0;
    }
    
    .products-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }
    
    .product-select-card {
      border: 1px solid #e2e8f0;
      border-radius: var(--card-radius);
      padding: 15px;
      text-align: center;
      cursor: pointer;
      transition: var(--transition);
      position: relative;
      background: white;
    }
    
    .product-select-card:hover {
      border-color: var(--primary-color);
      box-shadow: var(--shadow-sm);
    }
    
    .product-select-card.selected {
      border: 2px solid var(--primary-color);
      background-color: rgba(37, 99, 235, 0.05);
    }
    
    .product-select-card .product-img {
      width: 100%;
      height: 120px;
      object-fit: contain;
      margin-bottom: 15px;
      border-radius: 4px;
      background: var(--light-bg);
    }
    
    .product-select-card .product-name {
      font-weight: 500;
      margin-bottom: 5px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      color: var(--dark-text);
    }
    
    .product-select-card .product-price {
      color: var(--primary-color);
      font-weight: 600;
    }
    
    .selection-counter {
      position: absolute;
      top: -10px;
      right: -10px;
      background: var(--accent-color);
      color: white;
      width: 25px;
      height: 25px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: 600;
    }
    
    .payment-section {
      margin-top: 40px;
      padding-top: 30px;
      border-top: 1px solid #eee;
    }
    
    .payment-methods {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
      gap: 15px;
      margin-top: 20px;
    }
    
    .payment-method {
      border: 1px solid #e2e8f0;
      border-radius: var(--card-radius);
      padding: 15px;
      display: flex;
      align-items: center;
      cursor: pointer;
      transition: var(--transition);
      background: white;
    }
    
    .payment-method:hover {
      border-color: var(--primary-color);
      box-shadow: var(--shadow-sm);
    }
    
    .payment-method.selected {
      border: 2px solid var(--primary-color);
      background-color: rgba(37, 99, 235, 0.05);
    }
    
    .payment-method img {
      height: 25px;
      margin-right: 10px;
    }
    
    .payment-method .payment-icon {
      font-size: 25px;
      margin-right: 10px;
      color: var(--medium-text);
    }
    
    .payment-method.selected .payment-icon {
      color: var(--primary-color);
    }
    
    .proceed-btn {
      display: block;
      width: 100%;
      max-width: 300px;
      margin: 40px auto 0;
      padding: 15px;
      background: var(--secondary-color);
      color: white;
      border: none;
      border-radius: var(--card-radius);
      font-size: 16px;
      font-weight: 600;
      transition: var(--transition);
    }
    
    .proceed-btn:hover {
      background: #0d9f6e;
      transform: translateY(-2px);
      box-shadow: var(--shadow-sm);
    }
    
    .proceed-btn:disabled {
      background: var(--light-text);
      cursor: not-allowed;
      transform: none;
      box-shadow: none;
    }
    
    /* Alert messages */
    .alert {
      border-radius: var(--card-radius);
      padding: 15px;
      margin-bottom: 20px;
      border: 1px solid transparent;
    }
    
    .alert-success {
      background-color: #d1fae5;
      color: #065f46;
      border-color: #a7f3d0;
    }
    
    .alert-danger {
      background-color: #fee2e2;
      color: #b91c1c;
      border-color: #fecaca;
    }
    
    .alert-warning {
      background-color: #fef3c7;
      color: #92400e;
      border-color: #fde68a;
    }
    
    /* Selected plan highlight */
    .plan-card.selected {
      border: 2px solid var(--primary-color);
      background-color: rgba(37, 99, 235, 0.05);
    }
    
    .selection-info {
      background-color: var(--light-bg);
      border-left: 4px solid var(--primary-color);
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
      color: var(--dark-text);
    }
    
    .selection-info h5 {
      color: var(--primary-color);
      margin-bottom: 10px;
    }
    
    /* Credit Card Form Styles */
    #creditCardDetails {
      margin-top: 20px;
      padding: 20px;
      background: #f8f9fa;
      border-radius: var(--card-radius);
      border: 1px solid #e2e8f0;
      display: none;
    }
    
    #creditCardDetails.active {
      display: block;
    }
    
    .form-label {
      font-weight: 500;
      color: var(--dark-color);
      margin-bottom: 8px;
    }
    
    .form-control {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid #e2e8f0;
      transition: var(--transition);
    }
    
    .form-control:focus {
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(30, 41, 59, 0.25);
    }
    
    .invalid-feedback {
      color: var(--danger-color);
      font-size: 0.875em;
      margin-top: 5px;
    }
    
    .is-invalid {
      border-color: var(--danger-color);
    }
    
    /* Responsive adjustments */
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
      
      .promotion-plans {
        grid-template-columns: 1fr;
      }
      
      .products-grid {
        grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
      }
      
      .payment-methods {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
    <!-- Sidebar -->
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
            <i class="fas fa-bullhorn"></i>
            <span>Promote Products</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-bullhorn me-2"></i>Promote Products</h1>
        </div>
        
        <div class="promotion-container">
            <?php if (isset($_SESSION['promotion_success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $_SESSION['promotion_success']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['promotion_success']); ?>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['promotion_error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?php echo $_SESSION['promotion_error']; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php unset($_SESSION['promotion_error']); ?>
            <?php endif; ?>
            
            <div class="promotion-header">
                <h2><i class="fas fa-rocket me-2"></i> Promote Your Products</h2>
                <p>Boost your sales by featuring your products prominently on our homepage. Select a promotion plan and choose which products to showcase.</p>
            </div>
            
            <form id="promotionForm" method="POST">
                <input type="hidden" id="selectedPlanInput" name="plan_type" value="">
                <input type="hidden" id="paymentMethodInput" name="payment_method" value="cod">
                <input type="hidden" name="selected_products" value='<?php echo json_encode($selectedProductIds); ?>'>
                
                <div class="selection-info">
                    <h5><i class="fas fa-info-circle text-primary me-2"></i>Promotion Requirements</h5>
                    <p>You must select exactly 5 products from each of these 4 categories: Antibiotics, Antipyretics, Skin care, and Vitamins (20 products total).</p>
                </div>
                
                <h3 class="mb-4">Choose a Promotion Plan</h3>
                <div class="promotion-plans">
                    <div class="plan-card" id="standard-plan">
                        <div class="plan-name">Standard</div>
                        <div class="plan-price">₹999 <span>/ week</span></div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> 20 products featured (5 per category)</li>
                            <li><i class="fas fa-check"></i> Standard placement</li>
                            <li><i class="fas fa-check"></i> Basic product visibility</li>
                            <li><i class="fas fa-check"></i> 7 days duration</li>
                        </ul>
                        <button type="button" class="select-plan-btn" onclick="selectPlan('standard')">Select Plan</button>
                    </div>
                    
                    <div class="plan-card popular" id="premium-plan">
                        <div class="popular-badge">MOST POPULAR</div>
                        <div class="plan-name">Premium</div>
                        <div class="plan-price">₹1,799 <span>/ 2 weeks</span></div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> 20 products featured (5 per category)</li>
                            <li><i class="fas fa-check"></i> Priority placement</li>
                            <li><i class="fas fa-check"></i> Highlighted product cards</li>
                            <li><i class="fas fa-check"></i> 14 days duration</li>
                        </ul>
                        <button type="button" class="select-plan-btn" onclick="selectPlan('premium')">Select Plan</button>
                    </div>
                    
                    <div class="plan-card" id="enterprise-plan">
                        <div class="plan-name">Enterprise</div>
                        <div class="plan-price">₹3,999 <span>/ month</span></div>
                        <ul class="plan-features">
                            <li><i class="fas fa-check"></i> 20 products featured (5 per category)</li>
                            <li><i class="fas fa-check"></i> Top banner placement</li>
                            <li><i class="fas fa-check"></i> Premium product cards</li>
                            <li><i class="fas fa-check"></i> 30 days duration</li>
                        </ul>
                        <button type="button" class="select-plan-btn" onclick="selectPlan('enterprise')">Select Plan</button>
                    </div>
                </div>
                
                <!-- Selected Products Section -->
                <div class="products-selection">
                    <?php if (!empty($selectedProducts)): ?>
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3>Selected Products for Promotion</h3>
                            <span class="badge bg-primary"><?php echo count($selectedProducts); ?> selected (<?php echo count($selectedProducts)/5 ?> categories)</span>
                        </div>
                        
                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 mb-4">
                            <?php 
                            // Pagination logic
                            $itemsPerPage = 8;
                            $totalItems = count($selectedProducts);
                            $totalPages = ceil($totalItems / $itemsPerPage);
                            $currentPage = isset($_GET['page']) ? max(1, min($totalPages, intval($_GET['page']))) : 1;
                            $offset = ($currentPage - 1) * $itemsPerPage;
                            $paginatedProducts = array_slice($selectedProducts, $offset, $itemsPerPage);
                            
                            foreach ($paginatedProducts as $product): 
                                // Product Image Path Handling - Aligned with view_all.php
                                $imagePath = !empty($product['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_img'])) : 'inventory/uploads/default_product.jpg';
                            ?>
                                <div class="col">
                                    <div class="card h-100 product-card shadow-sm">
                                        <img src="<?php echo $imagePath; ?>" 
                                             class="card-img-top p-3" 
                                             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
                                             style="height: 180px; object-fit: contain;"
                                             onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                                        <div class="card-body">
                                            <h5 class="card-title product-name"><?php echo htmlspecialchars($product['product_name']); ?></h5>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <span class="text-primary fw-bold">₹<?php echo number_format($product['price'], 2); ?></span>
                                                <span class="badge bg-success">Selected</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($totalPages > 1): ?>
                            <nav aria-label="Selected products pagination">
                                <ul class="pagination justify-content-center">
                                    <?php if ($currentPage > 1): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $currentPage - 1; ?>" aria-label="Previous">
                                                <span aria-hidden="true">&laquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                        <li class="page-item <?php echo $i == $currentPage ? 'active' : ''; ?>">
                                            <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <?php if ($currentPage < $totalPages): ?>
                                        <li class="page-item">
                                            <a class="page-link" href="?id=<?php echo $shop_id; ?>&page=<?php echo $currentPage + 1; ?>" aria-label="Next">
                                                <span aria-hidden="true">&raquo;</span>
                                            </a>
                                        </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                        <div class="text-center mt-4">
                            <a href="product_selection.php?id=<?php echo $shop_id; ?>" class="btn btn-outline-primary">
                                <i class="fas fa-edit me-2"></i> Change Selected Products
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <div class="alert alert-warning">
                                You haven't selected any products for promotion yet.
                            </div>
                            <a href="product_selection.php?id=<?php echo $shop_id; ?>" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i> Select Products
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Payment Section -->
                <div class="payment-section">
                    <h3>Payment Method</h3>
                    <p>Select your preferred payment option</p>
                    
                    <div class="payment-methods">
                        <div class="payment-method selected" data-payment-method="cod" onclick="selectPaymentMethod('cod', this)">
                            <i class="fas fa-money-bill-wave payment-icon"></i>
                            <span>Cash on Delivery</span>
                        </div>
                        <div class="payment-method" data-payment-method="credit_card" onclick="selectPaymentMethod('credit_card', this)">
                            <i class="far fa-credit-card payment-icon"></i>
                            <span>Credit/Debit Card</span>
                        </div>
                    </div>
                    
                    <!-- Credit Card Form (Hidden by default) -->
                    <div id="creditCardDetails">
                        <div class="row mt-3">
                            <div class="col-md-12 mb-3">
                                <label for="card_name" class="form-label">Name on Card</label>
                                <input type="text" class="form-control" id="card_name" placeholder="John Doe">
                                <div class="invalid-feedback">Please enter the name on card</div>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="card_number" class="form-label">Card Number</label>
                                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                                <div class="invalid-feedback">Please enter a valid 16-digit card number</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="expiry" class="form-label">Expiry Date</label>
                                <input type="text" class="form-control" id="expiry" placeholder="MM/YY" maxlength="5">
                                <div class="invalid-feedback">Please enter a valid expiry date (MM/YY)</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvc" class="form-label">CVC</label>
                                <input type="text" class="form-control" id="cvc" placeholder="123" maxlength="3">
                                <div class="invalid-feedback">Please enter a valid 3-digit CVC</div>
                            </div>
                        </div>
                    </div>
                    
                    <button type="submit" class="proceed-btn" id="proceedBtn" disabled>
                        <i class="fas fa-lock me-2"></i> Proceed to Payment
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Global variables
        let selectedPlan = null;
        let selectedPaymentMethod = 'cod';
        
        // Select promotion plan
        function selectPlan(planType) {
            selectedPlan = planType;
            document.getElementById('selectedPlanInput').value = planType;
            
            // Update UI
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });
            document.getElementById(`${planType}-plan`).classList.add('selected');
            
            // Enable proceed button if products are selected
            checkProceedButton();
        }
        
        // Select payment method
        function selectPaymentMethod(method, element) {
            selectedPaymentMethod = method;
            document.getElementById('paymentMethodInput').value = method;
            
            // Update UI
            document.querySelectorAll('.payment-method').forEach(el => {
                el.classList.remove('selected');
            });
            element.classList.add('selected');
            
            // Show/hide credit card form
            const creditCardForm = document.getElementById('creditCardDetails');
            if (method === 'credit_card') {
                creditCardForm.style.display = 'block';
            } else {
                creditCardForm.style.display = 'none';
            }
        }
        // Check if we can enable the proceed button
        function checkProceedButton() {
            const btn = document.getElementById('proceedBtn');
            const hasSelectedProducts = <?php echo !empty($selectedProducts) ? 'true' : 'false'; ?>;
            
            if (selectedPlan && hasSelectedProducts) {
                btn.disabled = false;
                btn.innerHTML = `<i class="fas fa-lock me-2"></i> Proceed to Payment (₹${calculateTotal(selectedPlan)})`;
            } else {
                btn.disabled = true;
				                btn.innerHTML = `<i class="fas fa-lock me-2"></i> Proceed to Payment`;
            }
        }
        
        // Calculate total based on plan type (matches PHP function)
        function calculateTotal(planType) {
            const prices = {
                'standard': 999,
                'premium': 1799,
                'enterprise': 3999
            };
            return prices[planType] || 0;
        }
        
        // Form validation before submission
        document.getElementById('promotionForm').addEventListener('submit', function(e) {
            if (!selectedPlan) {
                e.preventDefault();
                alert('Please select a promotion plan');
                return;
            }
            
            const selectedProducts = <?php echo json_encode($selectedProductIds); ?>;
            if (!selectedProducts || selectedProducts.length === 0) {
                e.preventDefault();
                alert('Please select products to promote');
                return;
            }
            
            if (selectedPaymentMethod === 'credit_card') {
                // Validate credit card details
                const cardName = document.getElementById('card_name').value.trim();
                const cardNumber = document.getElementById('card_number').value.replace(/\s/g, '');
                const expiry = document.getElementById('expiry').value.trim();
                const cvc = document.getElementById('cvc').value.trim();
                
                let isValid = true;
                
                // Validate card name
                if (cardName === '') {
                    document.getElementById('card_name').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('card_name').classList.remove('is-invalid');
                }
                
                // Validate card number (16 digits)
                if (!/^\d{16}$/.test(cardNumber)) {
                    document.getElementById('card_number').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('card_number').classList.remove('is-invalid');
                }
                
                // Validate expiry date (MM/YY)
                if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(expiry)) {
                    document.getElementById('expiry').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('expiry').classList.remove('is-invalid');
                }
                
                // Validate CVC (3 digits)
                if (!/^\d{3}$/.test(cvc)) {
                    document.getElementById('cvc').classList.add('is-invalid');
                    isValid = false;
                } else {
                    document.getElementById('cvc').classList.remove('is-invalid');
                }
                
                if (!isValid) {
                    e.preventDefault();
                    return;
                }
            }
        });
        
        // Format card number input
        document.getElementById('card_number').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\s/g, '');
            if (value.length > 16) value = value.substr(0, 16);
            
            let formatted = '';
            for (let i = 0; i < value.length; i++) {
                if (i > 0 && i % 4 === 0) formatted += ' ';
                formatted += value[i];
            }
            
            e.target.value = formatted;
        });
        
        // Format expiry date input
        document.getElementById('expiry').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 4) value = value.substr(0, 4);
            
            if (value.length > 2) {
                value = value.substr(0, 2) + '/' + value.substr(2);
            }
            
            e.target.value = value;
        });
        
        // Format CVC input
        document.getElementById('cvc').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/\D/g, '').substr(0, 3);
        });
        
        // Initialize the page
        document.addEventListener('DOMContentLoaded', function() {
            checkProceedButton();
        });
    </script>
</body>
</html>