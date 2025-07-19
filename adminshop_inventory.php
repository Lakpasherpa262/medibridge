<?php
session_start();
require 'scripts/connect.php'; // This provides $db as PDO connection

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

// Base SQL query
$sql = "SELECT * FROM products WHERE shop_id = :shop_id AND is_listed = 1";
$count_sql = "SELECT COUNT(*) as total FROM products WHERE shop_id = :shop_id AND is_listed = 1";
$params = [':shop_id' => $shop_id];

// Apply filters
if (!empty($search_term)) {
    $sql .= " AND (LOWER(product_name) LIKE :search OR LOWER(description) LIKE :search)";
    $count_sql .= " AND (LOWER(product_name) LIKE :search OR LOWER(description) LIKE :search)";
    $params[':search'] = "%".strtolower($search_term)."%";
}

if (!empty($category_filter)) {
    $sql .= " AND category = :category";
    $count_sql .= " AND category = :category";
    $params[':category'] = $category_filter;
}

if ($price_min !== null) {
    $sql .= " AND price >= :price_min";
    $count_sql .= " AND price >= :price_min";
    $params[':price_min'] = $price_min;
}

if ($price_max !== null) {
    $sql .= " AND price <= :price_max";
    $count_sql .= " AND price <= :price_max";
    $params[':price_max'] = $price_max;
}

if ($stock_min !== null) {
    $sql .= " AND quantity >= :stock_min";
    $count_sql .= " AND quantity >= :stock_min";
    $params[':stock_min'] = $stock_min;
}

if (!empty($expiry_filter)) {
    $today = date('Y-m-d');
    switch ($expiry_filter) {
        case 'expired':
            $sql .= " AND expiry_date < :today";
            $count_sql .= " AND expiry_date < :today";
            $params[':today'] = $today;
            break;
        case 'expiring_soon':
            $soon = date('Y-m-d', strtotime('+30 days'));
            $sql .= " AND expiry_date BETWEEN :today AND :soon";
            $count_sql .= " AND expiry_date BETWEEN :today AND :soon";
            $params[':today'] = $today;
            $params[':soon'] = $soon;
            break;
        case 'valid':
            $sql .= " AND expiry_date >= :today";
            $count_sql .= " AND expiry_date >= :today";
            $params[':today'] = $today;
            break;
    }
}

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
?>

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
        
        .product-img {
    width: 36px;
    height: 36px;
    object-fit: cover;
    border-radius: 4px;
    border: 1px solid #ddd;
    margin-right: 8px;
}

.product-img.bg-light {
    background-color: #f8f9fa !important;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
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
  <div class="sidebar" id="sidebar">
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
<!-- Main Content -->
<main class="main-content" id="mainContent">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="shop-name-heading">
                    <i class="fas fa-boxes me-2"></i>Shop Inventory
                </h2>
                <a href="admin_unlisted_products.php?shop_id=<?= $shop_id ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-eye-slash me-1"></i> View Unlisted Products
                </a>
            </div>

            <!-- Search and Filter Section -->
            <div class="filter-section mb-4">
                <form method="GET" id="searchForm">
                    <input type="hidden" name="shop_id" value="<?= $shop_id ?>">
                    
                    <div class="row g-2">
                        <!-- Search Box -->
                        <div class="col-md-4">
                            <div class="position-relative">
                                <label class="filter-label">Search Products</label>
                                <input type="text" name="search" id="liveSearch" class="form-control form-control-sm" 
                                       placeholder="Type to search..." value="<?= htmlspecialchars($search_term) ?>">
                                <div class="search-results" id="searchResults"></div>
                            </div>
                        </div>
                        
                        <!-- Category Filter -->
                        <div class="col-md-2">
                            <label class="filter-label">Category</label>
                            <select name="category" class="form-select form-select-sm">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?= htmlspecialchars($category) ?>" <?= $category === $category_filter ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Price Range -->
                        <div class="col-md-3">
                            <label class="filter-label">Price Range</label>
                            <div class="price-range-inputs">
                                <input type="number" name="price_min" class="form-control form-control-sm" 
                                       placeholder="Min" step="0.01" value="<?= $price_min !== null ? htmlspecialchars($price_min) : '' ?>">
                                <span class="align-self-center">to</span>
                                <input type="number" name="price_max" class="form-control form-control-sm" 
                                       placeholder="Max" step="0.01" value="<?= $price_max !== null ? htmlspecialchars($price_max) : '' ?>">
                            </div>
                        </div>
                        
                        <!-- Stock Filter -->
                        <div class="col-md-2">
                            <label class="filter-label">Min Stock</label>
                            <input type="number" name="stock_min" class="form-control form-control-sm" 
                                   placeholder="Min Qty" value="<?= $stock_min !== null ? htmlspecialchars($stock_min) : '' ?>">
                        </div>
                        
                        <!-- Expiry Filter -->
                        <div class="col-md-2">
                            <label class="filter-label">Expiry Status</label>
                            <select name="expiry" class="form-select form-select-sm">
                                <option value="">All</option>
                                <option value="expired" <?= $expiry_filter === 'expired' ? 'selected' : '' ?>>Expired</option>
                                <option value="expiring_soon" <?= $expiry_filter === 'expiring_soon' ? 'selected' : '' ?>>Expiring Soon</option>
                                <option value="valid" <?= $expiry_filter === 'valid' ? 'selected' : '' ?>>Valid</option>
                            </select>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="col-12 mt-2">
                            <button type="submit" class="btn btn-primary btn-sm me-2">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="adminshop_inventory.php?shop_id=<?= $shop_id ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-sync-alt"></i> Reset
                            </a>
                        </div>
                    </div>
                </form>
            </div>

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
            <?php
            $expiry_status = '';
            $expiry_class = 'bg-secondary';
            if (!empty($product['expiry_date'])) {
                $today = new DateTime();
                $expiry_date = new DateTime($product['expiry_date']);
                
                if ($today > $expiry_date) {
                    $expiry_status = 'Expired';
                    $expiry_class = 'bg-danger';
                } elseif ($today->diff($expiry_date)->days <= 30) {
                    $expiry_status = 'Expiring Soon';
                    $expiry_class = 'bg-warning text-dark';
                } else {
                    $expiry_status = 'Valid';
                    $expiry_class = 'bg-success';
                }
            }
            ?>
            <tr>
                <td class="text-muted">#<?= $product['id'] ?></td>

                <td>
    <div class="d-flex align-items-center">
        <?php 
        // Check if product has an image
        if (!empty($product['product_img'])):
            // Construct the full image path
            $image_path = 'includes/uploads/' . htmlspecialchars($product['product_img']);
            
            // Check if file exists and is readable
            if (file_exists($image_path) && is_readable($image_path)):
        ?>
                <img src="<?= $image_path ?>" 
                     class="product-img" 
                     alt="<?= htmlspecialchars($product['product_name']) ?>"
                     onerror="this.onerror=null; this.src='images/default-product.png'">
        <?php 
            else: // If image file doesn't exist
        ?>
                <div class="product-img bg-light d-flex align-items-center justify-content-center">
                    <i class="fas fa-image text-muted"></i>
                </div>
        <?php 
            endif;
        else: // If no image is specified in database
        ?>
            <div class="product-img bg-light d-flex align-items-center justify-content-center">
                <i class="fas fa-image text-muted"></i>
            </div>
        <?php endif; ?>
        
        <div>
            <div class="fw-bold"><?= htmlspecialchars($product['product_name']) ?></div>
            <small class="text-muted"><?= htmlspecialchars($product['category'] ?? 'No category') ?></small>
        </div>
    </div>
</td>

                <td class="text-end">₹<?= number_format($product['price'] ?? 0, 2) ?></td>
                <td class="text-center">
                    <span class="badge <?= ($product['quantity'] ?? 0) > 0 ? 'bg-success' : 'bg-danger' ?>">
                        <?= $product['quantity'] ?? 0 ?>
                    </span>
                </td>
                <td class="text-center">
                    <?php if (!empty($product['expiry_date'])): ?>
                        <span class="badge <?= $expiry_class ?>">
                            <?= date('d M Y', strtotime($product['expiry_date'])) ?>
                            <?php if (!empty($expiry_status)): ?>
                            <small>(<?= $expiry_status ?>)</small>
                            <?php endif; ?>
                        </span>
                    <?php else: ?>
                        <span class="badge bg-secondary">N/A</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <div class="btn-group btn-group-sm">
                        <button class="btn btn-outline-danger btn-sm" 
                                onclick="unlistProduct(<?= $product['id'] ?>)">
                            <i class="fas fa-eye-slash"></i> Unlist
                        </button>
                    </div>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="6" class="text-center py-4">
                <i class="fas fa-box-open fa-2x mb-3 text-muted"></i>
                <h5>No products found</h5>
                <p class="text-muted">Try adjusting your search or filters</p>
            </td>
        </tr>
    <?php endif; ?>
</tbody>

                </table>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="Page navigation">
                    <ul class="pagination justify-content-center mt-3">
                        <!-- Previous Page -->
                        <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($page - 1) ?>">&laquo;</a>
                        </li>
                        
                        <!-- Page Numbers -->
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?= $i == $page ? 'active' : '' ?>">
                                <a class="page-link" href="<?= buildPaginationLink($i) ?>"><?= $i ?></a>
                            </li>
                        <?php endfor; ?>
                        
                        <!-- Next Page -->
                        <li class="page-item <?= $page >= $total_pages ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= buildPaginationLink($page + 1) ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Sidebar toggle
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('collapsed');
        document.getElementById('mainContent').classList.toggle('expanded');
        this.classList.toggle('collapsed');
    });

    // Live search
    document.getElementById('liveSearch').addEventListener('input', function() {
        const term = this.value.trim();
        const resultsContainer = document.getElementById('searchResults');
        
        if (term.length < 2) {
            resultsContainer.style.display = 'none';
            return;
        }
        
        fetch(`includes/search_products.php?shop_id=<?= $shop_id ?>&term=${encodeURIComponent(term)}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(products => {
                resultsContainer.innerHTML = '';
                
                if (products.success && products.data.length > 0) {
                    products.data.forEach(product => {
                        const item = document.createElement('div');
                        item.className = 'search-result-item p-2 border-bottom';
                        item.textContent = product.product_name;
                        item.style.cursor = 'pointer';
                        item.addEventListener('click', () => {
                            document.getElementById('liveSearch').value = product.product_name;
                            document.getElementById('searchForm').submit();
                        });
                        resultsContainer.appendChild(item);
                    });
                    resultsContainer.style.display = 'block';
                } else {
                    resultsContainer.innerHTML = '<div class="p-2 text-muted">No matches found</div>';
                    resultsContainer.style.display = 'block';
                }
            })
            .catch(error => {
                console.error('Error during search:', error);
                resultsContainer.innerHTML = '<div class="p-2 text-danger">Error loading results</div>';
                resultsContainer.style.display = 'block';
            });
    });

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('#liveSearch') && !e.target.closest('#searchResults')) {
            document.getElementById('searchResults').style.display = 'none';
        }
    });
    function unlistProduct(productId) {
    if (confirm('Are you sure you want to unlist this product?')) {
        fetch('includes/product_actions.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=unlist_product&product_id=${productId}`
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(text || 'Failed to unlist product');
                });
            }
            return response.text();
        })
        .then(result => {
            if (result === 'success') {
                // Refresh only the table instead of whole page
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
<?php
// Helper function to build pagination links with current filters
function buildPaginationLink($page) {
    $params = $_GET;
    $params['page'] = $page;
    return 'adminshop_inventory.php?' . http_build_query($params);
}
?>