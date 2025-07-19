<?php
session_start();
include 'scripts/connect.php';

// Verify shop ID & owner
$shop_id = isset($_GET['id'])
    ? intval($_GET['id'])
    : (isset($_SESSION['shop_id'])
        ? intval($_SESSION['shop_id'])
        : null);
$shopOwner_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

if (!$shop_id || !$shopOwner_id) {
    header("Location: login.php");
    exit();
}

try {
    // Verify ownership
    $stmt = $db->prepare("SELECT id, shop_name, shop_image FROM shopdetails WHERE id = ? AND shopOwner_id = ?");
    $stmt->execute([$shop_id, $shopOwner_id]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$shop) {
        throw new Exception("Shop not found or access denied.");
    }
    $_SESSION['shop_id'] = $shop['id'];

    // Fetch only the required categories with listed products
    $categoriesStmt = $db->prepare("
        SELECT DISTINCT c.C_Id AS id, c.CategoryName AS category_name
        FROM category c
        JOIN products p ON c.C_Id = p.category
        WHERE p.shop_id = ? AND p.is_listed = 1 
        AND c.CategoryName IN ('Antibiotics', 'Antipyretics', 'Skin Care', 'Vitamin')
        ORDER BY c.CategoryName
    ");
    $categoriesStmt->execute([$shop_id]);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Group products by category
    $productsByCategory = [];
    foreach ($categories as $category) {
        $ps = $db->prepare("
            SELECT id, product_name, price, product_img AS product_image, quantity
            FROM products
            WHERE shop_id = ? AND category = ? AND is_listed = 1
            ORDER BY product_name
        ");
        $ps->execute([$shop_id, $category['id']]);
        $list = $ps->fetchAll(PDO::FETCH_ASSOC);
        if ($list) {
            $productsByCategory[] = [
                'category_id'   => $category['id'],
                'category_name' => $category['category_name'],
                'products'      => $list
            ];
        }
    }

} catch (PDOException $e) {
    die("DB error: " . htmlspecialchars($e->getMessage()));
} catch (Exception $e) {
    die(htmlspecialchars($e->getMessage()));
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['selected_products'])) {
    // Group selected products by category
    $selectedByCategory = [];
    foreach ($_POST['selected_products'] as $pid) {
        $pid = intval($pid);
        // Get category for each product
        $stmt = $db->prepare("SELECT category FROM products WHERE id = ? AND shop_id = ?");
        $stmt->execute([$pid, $shop_id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            $catId = $product['category'];
            if (!isset($selectedByCategory[$catId])) {
                $selectedByCategory[$catId] = [];
            }
            $selectedByCategory[$catId][] = $pid;
        }
    }
    
    // Validate exactly 5 products per category
    $isValid = true;
    $errorMessage = '';
    
    // Check if we have all 4 required categories
    if (count($selectedByCategory) !== 4) {
        $isValid = false;
        $errorMessage = "Please select products from all 4 required categories (Antibiotics, Antipyretics, Skin care, Vitamins)";
    } else {
        foreach ($selectedByCategory as $catId => $products) {
            if (count($products) !== 5) {
                $isValid = false;
                // Get category name for error message
                $stmt = $db->prepare("SELECT CategoryName FROM category WHERE C_Id = ?");
                $stmt->execute([$catId]);
                $category = $stmt->fetch(PDO::FETCH_ASSOC);
                $errorMessage = "Please select exactly 5 products from the " . htmlspecialchars($category['CategoryName']) . " category";
                break;
            }
        }
    }
    
    if ($isValid) {
        $_SESSION['selected_products'] = array_map('intval', $_POST['selected_products']);
        header("Location: promote_products.php?id=" . $shop_id);
        exit();
    } else {
        $_SESSION['selection_error'] = $errorMessage;
        header("Location: product_selection.php?id=" . $shop_id);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Select Products for Promotion | <?= htmlspecialchars($shop['shop_name']) ?></title>
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

    .table {
      margin-bottom: 0;
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
    }

    .table tbody tr:hover {
      background-color: rgba(43, 108, 128, 0.05);
    }

    .product-img {
      width: 60px;
      height: 60px;
      object-fit: contain;
      border-radius: 4px;
      border: 1px solid #eee;
    }

    .in-stock { color: var(--success-color); }
    .low-stock { color: var(--warning-color); }
    .out-of-stock { color: var(--danger-color); }
    .expired { color: var(--danger-color); font-weight: bold; }
    .active { color: var(--primary-color); font-weight: bold; }

    .clickable-row {
      cursor: pointer;
    }

    .selected-row {
      background-color: rgba(37, 99, 235, 0.1) !important;
    }

    .selection-checkbox {
      width: 20px;
      height: 20px;
    }

    .selection-info {
      background-color: #f8f9fa;
      border-left: 4px solid var(--primary-color);
      padding: 15px;
      margin-bottom: 20px;
      border-radius: 4px;
    }
    
    .category-status {
      display: flex;
      align-items: center;
      margin-bottom: 5px;
    }
    
    .category-status .badge {
      margin-left: 10px;
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
    }
  </style>
</head>
<body>
  <!-- Sidebar Navigation -->
 <div class="sidebar">
        <div class="sidebar-header">
            <div class="text-center">
                <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">
                <div class="logo-text">MediBridge</div>
            </div>
        </div>
    
    <a href="promote_products.php?id=<?php echo $shop_id; ?>" class="nav-item">
      <i class="fas fa-arrow-left"></i>
      <span>Back to Promotion</span>
    </a>
    
    <a href="../shop.php" class="nav-item">
      <i class="fas fa-home"></i>
      <span>Back to Dashboard</span>
    </a>
    
    <div class="nav-item active">
      <i class="fas fa-bullhorn"></i>
      <span>Select Products</span>
    </div>
  </div>

  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <h1><i class="fas fa-bullhorn me-2"></i>Select Products for Promotion</h1>
      <div>
        <span class="badge bg-primary" id="selectedCount">0 selected</span>
      </div>
    </div>
    
    <?php if (isset($_SESSION['selection_error'])): ?>
      <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $_SESSION['selection_error']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['selection_error']); ?>
    <?php endif; ?>
    
    <div class="selection-info">
      <h5><i class="fas fa-info-circle text-primary me-2"></i>Selection Requirements</h5>
      <p>Please select exactly 5 products from each of the following categories:</p>
      <div id="categoryStatus">
        <?php foreach ($productsByCategory as $category): ?>
          <div class="category-status">
            <span><?= htmlspecialchars($category['category_name']) ?></span>
            <span class="badge bg-secondary" id="status-<?= $category['category_id'] ?>">0/5 selected</span>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <form id="productSelectionForm" method="POST">
      <!-- Hidden inputs for selected products will be added here by JS -->
      <div id="hiddenInputs"></div>
      
      <?php foreach ($productsByCategory as $category): ?>
      <div class="card">
        <div class="card-header">
          <h5><i class="fas fa-tag me-2"></i><?= htmlspecialchars($category['category_name']) ?>
            <small class="text-light">(Select exactly 5)</small>
          </h5>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover category-table" style="width:100%">
              <thead>
                <tr>
                  <th style="width: 40px;"></th>
                  <th>Product</th>
                  <th>Price</th>
                  <th>Stock</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($category['products'] as $product): 
                  // Product Image Path Handling - Aligned with view_all.php
                  $imagePath = !empty($product['product_image']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_image'])) : 'inventory/uploads/default_product.jpg';
                ?>
                <tr class="clickable-row" data-id="<?= $product['id'] ?>">
                  <td>
                    <input type="checkbox" class="selection-checkbox" 
                           data-category="<?= $category['category_id'] ?>"
                           name="product_<?= $product['id'] ?>">
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <div class="me-3">
                        <img src="<?= $imagePath ?>" 
                             class="product-img" 
                             alt="<?= htmlspecialchars($product['product_name']) ?>"
                             onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                      </div>
                      <div>
                        <div class="fw-bold"><?= htmlspecialchars($product['product_name']) ?></div>
                        <small class="text-muted">ID: <?= $product['id'] ?></small>
                      </div>
                    </div>
                  </td>
                  <td>₹<?= number_format($product['price'], 2) ?></td>
                  <td>
                    <?php if ($product['quantity'] > 10): ?>
                      <span class="badge bg-success">In Stock (<?= $product['quantity'] ?>)</span>
                    <?php elseif ($product['quantity'] > 0): ?>
                      <span class="badge bg-warning text-dark">Low Stock (<?= $product['quantity'] ?>)</span>
                    <?php else: ?>
                      <span class="badge bg-danger">Out of Stock</span>
                    <?php endif; ?>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
      
      <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary btn-lg" id="submitBtn" disabled>
          <i class="fas fa-arrow-right me-2"></i>Proceed to Promotion
        </button>
      </div>
    </form>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
  
  <script>
    $(document).ready(function() {
      const selected = new Set();
      const countByCat = {};
      const requiredCategories = <?= json_encode(array_column($productsByCategory, 'category_id')) ?>;
      const requiredCount = 5;
      
      // Initialize DataTables
      $('.category-table').DataTable({
        responsive: true,
        language: {
          search: "_INPUT_",
          searchPlaceholder: "Search products...",
          info: "Showing _START_ to _END_ of _TOTAL_ entries",
          infoEmpty: "Showing 0 to 0 of 0 entries",
          lengthMenu: "Show _MENU_ entries",
          paginate: {
            previous: "Previous",
            next: "Next"
          }
        },
        dom: '<"top"lf>rt<"bottom"ip>',
        lengthMenu: [[5, 10, 25, 50, -1], [5, 10, 25, 50, "All"]],
        columnDefs: [
          { orderable: false, targets: 0 } // Disable sorting for checkbox column
        ]
      });
      
      // Initialize category counts
      requiredCategories.forEach(cid => {
        countByCat[cid] = 0;
      });
      
      // Product selection
      $(document).on('click', '.clickable-row', function(e) {
        if ($(e.target).is('input[type="checkbox"]')) return;
        
        const checkbox = $(this).find('.selection-checkbox');
        checkbox.prop('checked', !checkbox.prop('checked')).trigger('change');
      });
      
      $(document).on('change', '.selection-checkbox', function() {
        const pid = $(this).closest('tr').data('id');
        const cid = $(this).data('category');
        const isChecked = $(this).is(':checked');
        
        if (isChecked) {
          // Enforce 5 per category
          if (countByCat[cid] >= requiredCount) {
            $(this).prop('checked', false);
            alert(`You can only select exactly ${requiredCount} products from this category`);
            return;
          }
          
          selected.add(pid);
          countByCat[cid]++;
          $(this).closest('tr').addClass('selected-row');
        } else {
          selected.delete(pid);
          countByCat[cid]--;
          $(this).closest('tr').removeClass('selected-row');
        }
        
        // Update UI
        updateSelectionUI();
      });
      
      function updateSelectionUI() {
        // Update selected count
        $('#selectedCount').text(selected.size + ' selected');
        
        // Update category status
        requiredCategories.forEach(cid => {
          $(`#status-${cid}`).text(`${countByCat[cid]}/${requiredCount} selected`);
          if (countByCat[cid] === requiredCount) {
            $(`#status-${cid}`).removeClass('bg-secondary').addClass('bg-success');
          } else {
            $(`#status-${cid}`).removeClass('bg-success').addClass('bg-secondary');
          }
        });
        
        // Enable/disable submit button
        const allCategoriesComplete = requiredCategories.every(cid => countByCat[cid] === requiredCount);
        $('#submitBtn').prop('disabled', !allCategoriesComplete);
        
        // Rebuild hidden inputs
        const container = $('#hiddenInputs');
        container.empty();
        selected.forEach(id => {
          container.append(`<input type="hidden" name="selected_products[]" value="${id}">`);
        });
      }
    });
  </script>
</body>
</html>