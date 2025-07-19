<?php
include 'scripts/connect.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBridge - Shop Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        --gradient: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }
    
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background-color: #f5f7fa;
        color: var(--dark-text);
        min-height: 100vh;
        display: flex;
    }
    
    /* Sidebar Styles */
    .sidebar {
        width: 280px;
        background: linear-gradient(to bottom, #2b6c80, #3a9db5);
        color: var(--sidebar-text);
        height: 100vh;
        position: fixed;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
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
        transition: var(--transition);
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
    
    .dashboard-btn {
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
    .dashboard-btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
    }
    
    /* Main Content Styles */
    .main-content {
        flex-grow: 1;
        margin-left: 280px;
        padding: 2rem;
        background: linear-gradient(to bottom right, #f8f9fa, #e9f2f5);
    }
    
    .btn-primary {
        background: var(--gradient) !important;
        border: none !important;
        box-shadow: 0 2px 5px rgba(43, 108, 128, 0.3);
        transition: all 0.2s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(43, 108, 128, 0.3);
    }

    .search-container, .header {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        margin-bottom: 20px;
    }

    .badge-primary {
        background: var(--gradient);
    }

    .no-shops {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: var(--card-shadow);
        text-align: center;
    }

    .info-text {
        color: var(--secondary-color);
    }
    
    /* Table Styles */
    .table-container {
        background: white;
        border-radius: 10px;
        box-shadow: var(--card-shadow);
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .table {
        margin-bottom: 0;
    }
    
    .table-hover tbody tr {
        cursor: pointer;
        transition: var(--transition);
    }
    
    .table-hover tbody tr:hover {
        background-color: rgba(58, 157, 181, 0.1);
    }
    
    .pagination {
        justify-content: center;
    }
    
    .page-item.active .page-link {
        background: var(--gradient);
        border-color: var(--primary-color);
    }
    
    .page-link {
        color: var(--primary-color);
    }
    
    @media (max-width: 768px) {
        .sidebar {
            width: 100%;
            height: auto;
            position: relative;
        }
        
        .main-content {
            margin-left: 0;
        }
    }
    </style>
</head>
<body>
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
    
  
    <!-- Main Content -->
    <div class="main-content">
        <div class="header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0"><i class="fas fa-store"></i> Shop Inventory Management</h1>
        </div>

        <!-- Search Section -->
        <div class="search-container">
            <form method="GET">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Search by Name</label>
                        <div class="input-group">
                            <input type="text" class="form-control" name="search" value="<?= htmlspecialchars($inventoryManager->searchTerm) ?>">
                            <?php if (!empty($inventoryManager->searchTerm)): ?>
                                <a href="inventory_management.php" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Filter by District</label>
                        <select class="form-select" name="location">
                            <option value="">All Districts</option>
                            <?php foreach ($inventoryManager->districts as $row): ?>
                                <?php $selected = ($row['district'] == $inventoryManager->locationFilter) ? 'selected' : ''; ?>
                                <option value="<?= htmlspecialchars($row['district']) ?>" <?= $selected ?>>
                                    <?= htmlspecialchars($row['district']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Filter by Products</label>
                        <select class="form-select" name="product">
                            <option value="">All Shops</option>
                            <option value="with_products" <?= $inventoryManager->productFilter === 'with_products' ? 'selected' : '' ?>>With Products</option>
                            <option value="without_products" <?= $inventoryManager->productFilter === 'without_products' ? 'selected' : '' ?>>Without Products</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <button class="btn btn-primary me-2" type="submit"><i class="fas fa-search"></i> Search</button>
                        <a href="inventory_management.php" class="btn btn-outline-secondary"><i class="fas fa-sync-alt"></i> Reset</a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Shops Table -->
        <div class="table-container">
            <?php if (count($inventoryManager->result) > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Shop Name</th>
                                <th>District</th>
                                <th>Contact</th>
                                <th>Products</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($inventoryManager->result as $shop): ?>
                                <tr onclick="window.location='adminshop_inventory.php?shop_id=<?= $shop['id'] ?>'">
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-store me-2 text-secondary"></i>
                                            <?= htmlspecialchars($shop['shop_name']) ?>
                                        </div>
                                    </td>
                                    <td><?= htmlspecialchars($shop['district']) ?></td>
                                    <td><?= htmlspecialchars($shop['shop_number']) ?></td>
                                    <td>
                                        <span class="badge bg-primary rounded-pill">
                                            <?= $shop['product_count'] ?> Product<?= $shop['product_count'] != 1 ? 's' : '' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if ($shop['product_count'] > 0): ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">No Products</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            <!-- Pagination -->
<?php if ($inventoryManager->totalPages > 1): ?>
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center mt-4">
        <!-- Previous Page -->
        <li class="page-item <?= ($inventoryManager->currentPage == 1) ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=<?= $inventoryManager->currentPage - 1 ?><?= !empty($inventoryManager->searchTerm) ? '&search='.urlencode($inventoryManager->searchTerm) : '' ?><?= !empty($inventoryManager->locationFilter) ? '&location='.urlencode($inventoryManager->locationFilter) : '' ?><?= !empty($inventoryManager->productFilter) ? '&product='.urlencode($inventoryManager->productFilter) : '' ?>" 
               aria-label="Previous">
                <span aria-hidden="true">&laquo;</span>
            </a>
        </li>
        
        <!-- Page Numbers -->
        <?php for ($i = 1; $i <= $inventoryManager->totalPages; $i++): ?>
            <li class="page-item <?= ($i == $inventoryManager->currentPage) ? 'active' : '' ?>">
                <a class="page-link" 
                   href="?page=<?= $i ?><?= !empty($inventoryManager->searchTerm) ? '&search='.urlencode($inventoryManager->searchTerm) : '' ?><?= !empty($inventoryManager->locationFilter) ? '&location='.urlencode($inventoryManager->locationFilter) : '' ?><?= !empty($inventoryManager->productFilter) ? '&product='.urlencode($inventoryManager->productFilter) : '' ?>">
                    <?= $i ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <!-- Next Page -->
        <li class="page-item <?= ($inventoryManager->currentPage == $inventoryManager->totalPages) ? 'disabled' : '' ?>">
            <a class="page-link" 
               href="?page=<?= $inventoryManager->currentPage + 1 ?><?= !empty($inventoryManager->searchTerm) ? '&search='.urlencode($inventoryManager->searchTerm) : '' ?><?= !empty($inventoryManager->locationFilter) ? '&location='.urlencode($inventoryManager->locationFilter) : '' ?><?= !empty($inventoryManager->productFilter) ? '&product='.urlencode($inventoryManager->productFilter) : '' ?>" 
               aria-label="Next">
                <span aria-hidden="true">&raquo;</span>
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
            <?php else: ?>
                <div class="no-shops">
                    <i class="fas fa-store-slash fa-3x mb-3 info-text"></i>
                    <h4 class="mb-2">No Shops Found</h4>
                    <p class="text-muted">Try adjusting your search or filters to find what you're looking for.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener("DOMContentLoaded", function () {
        const searchInput = document.querySelector('input[name="search"]');
        const filterForm = document.querySelector('form');
        
        // Live search functionality
        if (searchInput) {
            searchInput.addEventListener("input", function () {
                clearTimeout(this.timer);
                this.timer = setTimeout(() => filterForm.submit(), 500);
            });
        }
        
        // Clear search button
        const clearBtn = document.querySelector('.btn-outline-secondary');
        if (clearBtn) {
            clearBtn.addEventListener('click', function(e) {
                e.preventDefault();
                window.location.href = 'inventory_management.php';
            });
        }
        
        // Make entire table row clickable
        document.querySelectorAll('tbody tr').forEach(row => {
            row.addEventListener('click', (e) => {
                // Don't navigate if user clicked on a link or button inside the row
                if (e.target.tagName === 'A' || e.target.tagName === 'BUTTON' || e.target.parentElement.tagName === 'A') {
                    return;
                }
                window.location = row.getAttribute('onclick').match(/window\.location='([^']+)'/)[1];
            });
        });
    });
    </script>
</body>
</html>