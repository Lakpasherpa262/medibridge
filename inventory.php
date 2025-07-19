<?php
include '../scripts/connect.php';
session_start();

// Verify shop_id is available in session
if (!isset($_SESSION['shop_id'])) {
    die("Shop not identified. Please access through your shop dashboard.");
}

$shop_id = $_SESSION['shop_id'];

// Add Product
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_product'])) {
    $category = $_POST['category'] ?? '';
    $product_name = $_POST['product_name'] ?? '';
    $price = $_POST['price'] ?? 0;
    $quantity = $_POST['quantity'] ?? 0;
    $prescription_required = $_POST['prescription_required'] ?? '';
    $manufactured_date = $_POST['manufactured_date'] ?? '';
    $expiry_date = $_POST['expiry_date'] ?? '';
    $description = $_POST['description'] ?? '';

    // Validation
    if (empty($category)) {
        die("Category is required.");
    }

    if (empty($_FILES['product_img']['name'])) {
        die("Image is required.");
    }

    if (!is_numeric($quantity) || $quantity < 0) {
        die("Quantity must be a positive number.");
    }

    if (strtotime($expiry_date) <= strtotime($manufactured_date)) {
        die("Expiry date must be after manufactured date.");
    }

    $filePath = "uploads/default.png"; // Default image path

    // Handle file upload (required)
    if ($_FILES['product_img']['error'] === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png'];
        $fileType = $_FILES['product_img']['type'];
    
        if (in_array($fileType, $allowedTypes)) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
    
            // Use original filename only (remove uniqid)
            $fileName = basename($_FILES['product_img']['name']);
            $filePath = $uploadDir . $fileName;
    
            if (!move_uploaded_file($_FILES['product_img']['tmp_name'], $filePath)) {
                die("Failed to move uploaded file.");
            }
            
            // Store just the filename in database
            $product_img = $fileName;
        } else {
            die("Invalid file type. Only JPG and PNG are allowed.");
        }
    } else {
        // Use default image if upload failed
        $product_img = 'default.jpg';
    }

    // Insert into the database with the shop_id from session
    $sql = "INSERT INTO products (shop_id, product_name, product_img, category, price, quantity, prescription_required, manufactured_date, expiry_date, description) 
            VALUES (:shop_id, :product_name, :product_img, :category, :price, :quantity, :prescription_required, :manufactured_date, :expiry_date, :description)";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':shop_id' => $shop_id,
        ':product_name' => $product_name,
        ':product_img' => $filePath,
        ':category' => $category,
        ':price' => $price,
        ':quantity' => $quantity,
        ':prescription_required' => $prescription_required,
        ':manufactured_date' => $manufactured_date,
        ':expiry_date' => $expiry_date,
        ':description' => $description
    ]);
    
    // Redirect to prevent form resubmission
    header("Location: inventory.php?success=Product added successfully");
    exit();
}

// Fetch categories
try {
    $stmt1 = $db->prepare("SELECT * FROM category");
    $stmt1->execute();
    $cat = $stmt1->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching categories: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MediBridge - Product Inventory</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        width: 40px;
        height: 40px;
        object-fit: cover;
        border-radius: 4px;
        border: 1px solid #eee;
    }

    .in-stock { color: var(--success-color); }
    .low-stock { color: var(--warning-color); }
    .out-of-stock { color: var(--danger-color); }
    .expired { color: var(--danger-color); font-weight: bold; }
    .active { color: var(--primary-color); font-weight: bold; }

    .hidden-column {
        display: none !important;
    }

    .clickable-row {
        cursor: pointer;
    }

    .modal-content {
        border: none;
        border-radius: var(--card-radius);
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
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
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <div class="text-center">
                <img src="../images/logo.png" alt="MediBridge Logo" class="logo-img">
                <div class="logo-text">MediBridge</div>
            </div>
        </div>
        
        <a href="../shop.php" class="nav-item">
            <i class="fas fa-arrow-left"></i>
            <span>Back to Shop</span>
        </a>
        
        <div class="nav-item active">
            <i class="fas fa-pills"></i>
            <span>Inventory</span>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="header">
            <h1><i class="fas fa-pills me-2"></i>Product Inventory</h1>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                <i class="fas fa-plus me-2"></i> Add Product
            </button>
        </div>
        
        <!-- Category Tables -->
        <?php foreach ($cat as $category): ?>
        <?php
            $C_ID = $category['C_Id'];
            try {
                $stmt2 = $db->prepare("SELECT p.`id`, p.`shop_id`, p.`product_name`, p.`price`, p.`quantity`, 
                                      p.`expiry_date`, p.`description`, p.`prescription_required`, c.`CategoryName` 
                                      FROM `products` p
                                      JOIN `category` c ON p.category = c.C_Id
                                      WHERE p.category = :C_ID AND p.Shop_Id = :S_Id");
                $stmt2->bindValue(':S_Id', $shop_id, PDO::PARAM_STR);
                $stmt2->bindValue(':C_ID', $C_ID, PDO::PARAM_STR);
                $stmt2->execute();
                $products = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                die("Error fetching products: " . $e->getMessage());
            }
        ?>
        <div class="card">
            <div class="card-header">
                <h5><i class="fas fa-tag me-2"></i><?php echo $category['CategoryName']; ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover category-table" style="width:100%">
                        <thead>
                            <tr>
                                <th class="hidden-column">P_ID</th>
                                <th>Product Name</th>
                                <th>Price</th>
                                <th>Stock</th>
                                <th>Expiry Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($products)): ?>
                                <?php foreach ($products as $product): 
                                    $is_expired = strtotime($product['expiry_date']) < time();
                                    $stock_class = '';
                                    $status_text = '';
                                    
                                    if ($product['quantity'] > 10) {
                                        $stock_class = 'in-stock';
                                        $status_text = 'In Stock';
                                    } elseif ($product['quantity'] > 0) {
                                        $stock_class = 'low-stock';
                                        $status_text = 'Low Stock';
                                    } else {
                                        $stock_class = 'out-of-stock';
                                        $status_text = 'Out of Stock';
                                    }
                                    
                                    if ($is_expired) {
                                        $status_text = 'Expired';
                                        $status_class = 'expired';
                                    } else {
                                        $status_class = 'active';
                                    }
                                ?>
                                <tr class="clickable-row" data-id="<?= $product['id'] ?>">
                                    <td class="hidden-column"><?php echo $product['id']; ?></td>
                                    <td><?= htmlspecialchars($product['product_name']) ?></td>
                                    <td>₹<?= number_format($product['price'], 2) ?></td>
                                    <td class="<?= $stock_class ?>"><?= $product['quantity'] ?></td>
                                    <td><?= date('d/m/Y', strtotime($product['expiry_date'])) ?></td>
                                    <td class="<?= $status_class ?>"><?= $status_text ?></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">No products found for this category</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- Add Product Modal -->
        <div class="modal fade" id="addProductModal" tabindex="-1" aria-labelledby="addProductModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addProductModalLabel"><i class="fas fa-plus-circle me-2"></i> Add Product</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form method="POST" enctype="multipart/form-data" id="productForm">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="category" class="form-label"><i class="fas fa-tags me-2"></i>Category</label>
                                        <select class="form-select" id="category" name="category" required>
                                            <option value="">Select Category</option>
                                            <?php foreach ($cat as $category): ?>
                                                <option value="<?= htmlspecialchars($category['C_Id']) ?>">
                                                    <?= htmlspecialchars($category['CategoryName']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="product_name" class="form-label"><i class="fas fa-pills me-2"></i>Product Name</label>
                                        <input type="text" class="form-control" id="product_name" name="product_name" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="price" class="form-label"><i class="fas fa-rupee-sign me-2"></i>Price</label>
                                                <input type="number" class="form-control" id="price" name="price" step="0.01" min="0" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="quantity" class="form-label"><i class="fas fa-cubes me-2"></i>Quantity</label>
                                                <input type="number" class="form-control" id="quantity" name="quantity" min="0" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="product_img" class="form-label"><i class="fas fa-camera me-2"></i>Product Image</label>
                                        <input type="file" class="form-control" id="product_img" name="product_img" accept="image/jpeg, image/png" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="prescription_required" class="form-label"><i class="fas fa-file-prescription me-2"></i>Prescription Required</label>
                                        <select class="form-select" id="prescription_required" name="prescription_required" required>
                                            <option value="">Select</option>
                                            <option value="Yes">Yes</option>
                                            <option value="No">No</option>
                                        </select>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="manufactured_date" class="form-label"><i class="fas fa-calendar-alt me-2"></i>Mfg Date</label>
                                                <input type="date" class="form-control" id="manufactured_date" name="manufactured_date" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="expiry_date" class="form-label"><i class="fas fa-calendar-times me-2"></i>Expiry Date</label>
                                                <input type="date" class="form-control" id="expiry_date" name="expiry_date" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label"><i class="fas fa-align-left me-2"></i>Description</label>
                                <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" form="productForm" name="add_product" class="btn btn-primary">Add Product</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // Initialize DataTables for each category table
        $('.category-table').each(function() {
            var table = $(this).DataTable({
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
                    { 
                        targets: 0, // Hide P_ID column (index 0)
                        visible: false,
                        searchable: true
                    }
                ]
            });
            
            // Make rows clickable - this works with DataTables
            $(this).on('click', 'tbody tr', function(e) {
                // Check if the click was on a link or button inside the row
                if ($(e.target).is('a, button, :input') || $(e.target).closest('a, button, :input').length) {
                    return;
                }
                
                var rowData = table.row(this).data();
                if (rowData) {
                    var productId = $(this).data('id') || rowData[0]; // Use data-id attribute or first column
                    if (productId) {
                        window.location.href = 'edit_product.php?id=' + productId;
                    }
                }
            });
        });

        // Client-side validation for dates
        $('#productForm').on('submit', function(e) {
            const mfgDate = new Date($('#manufactured_date').val());
            const expiryDate = new Date($('#expiry_date').val());
            
            if (expiryDate <= mfgDate) {
                alert('Expiry date must be after manufactured date');
                e.preventDefault();
                return false;
            }
            
            if ($('#price').val() <= 0) {
                alert('Price must be greater than 0');
                e.preventDefault();
                return false;
            }
            
            if ($('#quantity').val() < 0) {
                alert('Quantity cannot be negative');
                e.preventDefault();
                return false;
            }
            
            return true;
        });
    });
    </script>
</body>
</html>