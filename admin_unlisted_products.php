<?php
session_start();
require 'scripts/connect.php';

$shop_id = $_SESSION['shop_id'] ?? $_GET['shop_id'] ?? 0;
$shop_id = filter_var($shop_id, FILTER_VALIDATE_INT);

if (!$shop_id) {
    die('Invalid shop ID');
}

// Get filter parameters
$search_term = $_GET['search'] ?? '';
$category_filter = $_GET['category'] ?? '';
$price_min = isset($_GET['price_min']) ? floatval($_GET['price_min']) : null;
$price_max = isset($_GET['price_max']) ? floatval($_GET['price_max']) : null;
$stock_min = isset($_GET['stock_min']) ? intval($_GET['stock_min']) : null;
$expiry_filter = $_GET['expiry'] ?? '';
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 20;

// Base SQL query for UNLISTED products
$sql = "SELECT * FROM products WHERE shop_id = :shop_id AND is_listed = 0";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE shop_id = :shop_id AND is_listed = 0";
$params = [':shop_id' => $shop_id];

// Apply filters
if (!empty($search_term)) {
    $sql .= " AND (product_name LIKE :search OR description LIKE :search)";
    $count_sql .= " AND (product_name LIKE :search OR description LIKE :search)";
    $params[':search'] = "%$search_term%";
}

// ... (rest of your filter code remains the same)

try {
    // Get total count
    $stmt = $db->prepare($count_sql);
    $stmt->execute($params);
    $total_rows = $stmt->fetchColumn();
    $total_pages = ceil($total_rows / $per_page);

    // Apply pagination
    $sql .= " LIMIT :limit OFFSET :offset";
    $params[':limit'] = $per_page;
    $params[':offset'] = ($page - 1) * $per_page;

    // Get products
    $stmt = $db->prepare($sql);
    foreach ($params as $key => &$val) {
        if ($key === ':limit' || $key === ':offset') {
            $stmt->bindValue($key, $val, PDO::PARAM_INT);
        } else {
            $stmt->bindValue($key, $val);
        }
    }
    $stmt->execute();
    $products = $stmt->fetchAll();

    // Get categories
    $cat_stmt = $db->prepare("SELECT DISTINCT category FROM products WHERE shop_id = :shop_id AND category IS NOT NULL");
    $cat_stmt->execute([':shop_id' => $shop_id]);
    $categories = $cat_stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    error_log("Database error: " . $e->getMessage());
    die("An error occurred while fetching products.");
}

function buildPaginationLink($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'admin_unlisted_products.php?' . http_build_query($params);
}
?>

<!-- HTML remains the same as before, just change the action buttons to "Relist" -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shop Inventory</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2b6c80;
            --secondary-color: #3a9db5;
            --accent-color: #4db6ac;
            --light-bg: #f8f9fa;
            --dark-text: #212529;
            --white: #ffffff;
            --shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --sidebar-bg: #2b6c80;
            --sidebar-text: #ffffff;
            --card-shadow: 0 4px 12px rgba(0,0,0,0.08);
            --gradient: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            --heading-color: #1a4b5f; /* Dark blue for headings */
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f7fa;
            color: var(--dark-text);
            min-height: 100vh;
        }
        
        /* Sidebar Styles */
        .sidebar {
            width: 280px;
            background: linear-gradient(to bottom, var(--sidebar-bg), var(--secondary-color));
            color: var(--sidebar-text);
            height: 100vh;
            position: fixed;
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: all 0.3s ease;
        }
        
        .sidebar.collapsed {
            transform: translateX(-280px);
        }
        
        .sidebar-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            margin-bottom: 1rem;
        }
        
        .logo-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }
        
        .brand-name {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: white;
        }
        
        .divider {
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            margin: 1rem 0;
        }
        
        .sidebar-nav {
            flex-grow: 1;
        }
        
        .nav-item {
            margin-bottom: 0.5rem;
        }
        
        .nav-link {
            color: var(--sidebar-text);
            padding: 0.75rem 1rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255, 255, 255, 0.1);
            color: var(--sidebar-text);
        }
        
        .nav-link i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .logout-btn {
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--sidebar-text);
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin-top: auto;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Main Content Styles */
        .main-content {
            margin-left: 280px;
            padding: 2rem;
            background: linear-gradient(to bottom right, #f8f9fa, #e9f2f5);
            transition: all 0.3s ease;
        }
        
        .main-content.expanded {
            margin-left: 0;
        }
        
        /* Toggle button */
        .sidebar-toggle {
            position: fixed;
            left: 280px;
            top: 10px;
            z-index: 1050;
            background: var(--secondary-color);
            color: white;
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 0 4px 4px 0;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
                .logout-btn {
            background-color: transparent;
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: var(--sidebar-text);
            width: 100%;
            padding: 0.75rem;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            margin-top: auto;
            }
            
            .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
            }
                .sidebar-toggle.collapsed {
            left: 0;
        }
        
        /* Headings */
        .shop-name-heading {
            color: var(--heading-color);
            font-weight: 600;
        }
        
        h2, h3, h4, h5, h6 {
            color: var(--heading-color);
        }
        
        /* Compact Table Styles */
        .table-container {
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: var(--card-shadow);
            overflow-x: auto;
        }
        
        .table {
            width: 100%;
            font-size: 0.82rem;
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 600;
            padding: 0.5rem 0.5rem;
            vertical-align: middle;
            position: sticky;
            top: 0;
            border: none;
            white-space: nowrap;
        }
        
        .table tbody tr {
            border-bottom: 1px solid #f0f0f0;
        }
        
        .table tbody tr:hover {
            background-color: rgba(58, 157, 181, 0.05);
        }
        
        .table td {
            padding: 0.5rem 0.5rem;
            vertical-align: middle;
            border-top: none;
            white-space: nowrap;
        }
        
        /* Specific column widths */
        .col-id {
            width: 70px;
        }
        
        .col-product {
            min-width: 180px;
            max-width: 220px;
        }
        
        .col-price {
            width: 90px;
        }
        
        .col-stock {
            width: 80px;
        }
        
        .col-expiry {
            width: 120px;
        }
        
        .col-actions {
            width: 100px;
        }
        
        /* Image in table */
        .product-img {
            width: 36px;
            height: 36px;
            object-fit: cover;
            border-radius: 4px;
            border: 1px solid #ddd;
            margin-right: 8px;
        }
        
        /* Badges */
        .badge {
            font-size: 0.7rem;
            font-weight: 500;
            padding: 0.3em 0.5em;
            min-width: 45px;
        }
        
        /* Buttons */
        .btn-sm {
            padding: 0.2rem 0.4rem;
            font-size: 0.7rem;
        }
        
        
        /* Responsive adjustments */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-280px);
            }
            
            .sidebar.collapsed {
                transform: translateX(0);
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .main-content.expanded {
                margin-left: 280px;
            }
            
            .sidebar-toggle {
                left: 0;
            }
            
            .sidebar-toggle.collapsed {
                left: 280px;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Toggle Button -->
    <button class="sidebar-toggle collapsed" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

  <!-- Sidebar Navigation -->
  <div class="sidebar">
    <!-- Replace this in the sidebar-header section -->
<div class="sidebar-header">
  <div class="logo">
    <img src="images/logo.png" alt="MediBridge Logo" class="logo-img"> 
  </div>
  <div class="brand-name">MediBridge</div>
</div>
    <div class="divider"></div>
    
    <div class="sidebar-nav">
      <div class="nav-item">
        <a href="admin_dashboard.php" class="nav-link">
          <i class="fas fa-tachometer-alt"></i> Dashboard
        </a>
      </div>
      <div class="nav-item">
        <a href="user_management.php" class="nav-link">
          <i class="fas fa-users-cog"></i> User Management
        </a>
      </div>
      <div class="nav-item">
        <a href="registration.php" class="nav-link active">
          <i class="fas fa-user-plus"></i> Add New Users
        </a>
      </div>
      <div class="nav-item">
        <a href="inventory_management.php" class="nav-link">
          <i class="fas fa-boxes"></i> Inventory
        </a>
      </div>
    </div>
    
    <div class="divider"></div>
    
    <button class="logout-btn" onclick="window.location.href='index.php'">
  <i class="fas fa-arrow-left me-2"></i> Logout
</button>
  </div>

    <main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="shop-name-heading">
                    <i class="fas fa-eye-slash me-2"></i>Unlisted Products
                </h2>
                <a href="adminshop_inventory.php?shop_id=<?= $shop_id ?>" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-boxes me-1"></i> View Listed Products
                </a>
            </div>

            <!-- Same search and filter section as adminshop_inventory.php -->

            <!-- Products Table -->
            <div class="table-container">
                <table class="table table-hover align-middle">
                    <thead>
                        <tr>
                            <th class="col-id">ID</th>
                            <th class="col-product">Product</th>
                            <th class="col-price text-end">Price</th>
                            <th class="col-stock text-center">Stock</th>
                            <th class="col-expiry text-center">Expiry</th>
                            <th class="col-actions text-center">Actions</th>
                        </tr>
                    </thead>


                    <tbody>
    <?php if (!empty($products)): ?>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= htmlspecialchars($product['id']) ?></td>
                <td>
                    <div class="d-flex align-items-center">
                        <?php if (!empty($product['image_url'])): ?>
                            <img src="<?= htmlspecialchars($product['image_url']) ?>" 
                                 alt="Product image" class="product-img me-2">
                        <?php else: ?>
                            <div class="product-img bg-light d-flex align-items-center justify-content-center me-2">
                                <i class="fas fa-pills text-muted"></i>
                            </div>
                        <?php endif; ?>
                        <div>
                            <div class="fw-semibold"><?= htmlspecialchars($product['product_name']) ?></div>
                            <small class="text-muted"><?= htmlspecialchars($product['category'] ?? 'Uncategorized') ?></small>
                        </div>
                    </div>
                </td>
                <td class="text-end"><?= number_format($product['price'], 2) ?></td>
                <td class="text-center">
                    <span class="badge bg-<?= ($product['stock'] > 10) ? 'success' : ($product['stock'] > 0 ? 'warning' : 'danger') ?>">
                        <?= $product['stock'] ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if (!empty($product['expiry_date'])): ?>
                        <?php 
                            $expiry = new DateTime($product['expiry_date']);
                            $today = new DateTime();
                            $interval = $today->diff($expiry);
                            $days = $interval->format('%r%a');
                        ?>
                        <span class="badge bg-<?= ($days > 30) ? 'success' : ($days > 0 ? 'warning' : 'danger') ?>">
                            <?= $expiry->format('M d, Y') ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">N/A</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-success btn-sm" 
                                onclick="relistProduct(<?= $product['id'] ?>)">
                            <i class="fas fa-eye"></i> Relist
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-eye-slash fa-2x mb-3 text-muted"></i>
                <h5>No unlisted products found</h5>
            </td>
        </tr>
    <?php endif; ?>
</tbody>

                </table>

                <!-- Same pagination as adminshop_inventory.php -->
            </div>
        </div>
    </main>

    <script>
    // Add relist function
    function relistProduct(productId) {
        if (confirm('Are you sure you want to relist this product?')) {
            fetch('includes/product_actions.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=relist_product&product_id=${productId}`
            })
            .then(response => {
                if (!response.ok) {
                    return response.text().then(text => {
                        throw new Error(text || 'Failed to relist product');
                    });
                }
                return response.text();
            })
            .then(result => {
                if (result === 'success') {
                    location.reload();
                } else {
                    alert('Error: ' + result);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message);
            });
        }
    }
    </script>
</body>
</html>