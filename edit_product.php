<?php
include '../scripts/connect.php';
session_start();

// Verify shop_id is available in session
if (!isset($_SESSION['shop_id'])) {
    die('{"status": "error", "message": "Shop not identified"}');
}

$shop_id = $_SESSION['shop_id'];

// Get product ID from URL
if (!isset($_GET['id'])) {
    die('{"status": "error", "message": "Product ID not specified"}');
}

$product_id = $_GET['id'];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    // Update product information
    if (isset($_POST['update_product'])) {
        try {
            $sql = "UPDATE products SET 
                    category = :category,
                    product_name = :product_name,
                    price = :price,
                    quantity = :quantity,
                    prescription_required = :prescription_required,
                    manufactured_date = :manufactured_date,
                    expiry_date = :expiry_date,
                    description = :description
                    WHERE id = :id AND shop_id = :shop_id";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':category' => $_POST['category'],
                ':product_name' => $_POST['product_name'],
                ':price' => $_POST['price'],
                ':quantity' => $_POST['quantity'],
                ':prescription_required' => $_POST['prescription_required'],
                ':manufactured_date' => $_POST['manufactured_date'],
                ':expiry_date' => $_POST['expiry_date'],
                ':description' => $_POST['description'],
                ':id' => $product_id,
                ':shop_id' => $shop_id
            ]);
            
            echo json_encode(['status' => 'success', 'message' => 'Product updated successfully']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating product: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Update product image
    if (isset($_POST['update_image'])) {
        try {
            // Handle file upload
            if ($_FILES['product_img']['error'] === UPLOAD_ERR_OK) {
                $allowedTypes = ['image/jpeg', 'image/png'];
                $fileType = $_FILES['product_img']['type'];
            
                if (in_array($fileType, $allowedTypes)) {
                    $uploadDir = 'uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
            
                    $fileName = uniqid() . '_' . basename($_FILES['product_img']['name']);
                    $filePath = $uploadDir . $fileName;
            
                    if (move_uploaded_file($_FILES['product_img']['tmp_name'], $filePath)) {
                        // Update database with new image path
                        $sql = "UPDATE products SET product_img = :product_img 
                                WHERE id = :id AND shop_id = :shop_id";
                        $stmt = $db->prepare($sql);
                        $stmt->execute([
                            ':product_img' => $filePath,
                            ':id' => $product_id,
                            ':shop_id' => $shop_id
                        ]);
                        
                        echo json_encode([
                            'status' => 'success', 
                            'message' => 'Image updated successfully',
                            'image_path' => $filePath
                        ]);
                        exit();
                    }
                }
            }
            
            echo json_encode(['status' => 'error', 'message' => 'Error uploading image']);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error updating image: ' . $e->getMessage()]);
            exit();
        }
    }
    
    // Delete product
    if (isset($_POST['delete_product'])) {
        try {
            $sql = "DELETE FROM products WHERE id = :id AND shop_id = :shop_id";
            $stmt = $db->prepare($sql);
            $stmt->execute([
                ':id' => $product_id,
                ':shop_id' => $shop_id
            ]);
            
            echo json_encode([
                'status' => 'success', 
                'message' => 'Product deleted successfully',
                'redirect' => 'inventory.php?success=Product deleted successfully'
            ]);
            exit();
        } catch (PDOException $e) {
            echo json_encode(['status' => 'error', 'message' => 'Error deleting product: ' . $e->getMessage()]);
            exit();
        }
    }
}

// Fetch product details
try {
    $stmt = $db->prepare("SELECT p.*, c.CategoryName 
                         FROM products p 
                         JOIN category c ON p.category = c.C_Id
                         WHERE p.id = :id AND p.shop_id = :shop_id");
    $stmt->execute([':id' => $product_id, ':shop_id' => $shop_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$product) {
        die('{"status": "error", "message": "Product not found"}');
    }
} catch (PDOException $e) {
    die('{"status": "error", "message": "Error fetching product"}');
}

// Fetch categories for dropdown
try {
    $stmt1 = $db->prepare("SELECT * FROM category");
    $stmt1->execute();
    $categories = $stmt1->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die('{"status": "error", "message": "Error fetching categories"}');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - MediBridge</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

    .card {
        border: none;
        border-radius: var(--card-radius);
        box-shadow: var(--card-shadow);
        margin-bottom: 30px;
        overflow: hidden;
        transition: var(--transition);
    }

    .card-header {
        background-color: var(--primary-color);
        color: white;
        padding: 20px;
        border-bottom: none;
    }

    .card-header h4 {
        margin: 0;
        font-weight: 500;
        display: flex;
        align-items: center;
    }

    .card-header h4 i {
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

    .product-image {
        max-width: 100%;
        max-height: 300px;
        border-radius: 8px;
        border: 1px solid #ddd;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .form-section {
        margin-bottom: 30px;
    }

    .section-title {
        color: var(--primary-color);
        border-bottom: 2px solid var(--primary-color);
        padding-bottom: 8px;
        margin-bottom: 20px;
        font-weight: 600;
        display: flex;
        align-items: center;
    }

    .section-title i {
        margin-right: 10px;
    }

    .alert-container {
        position: fixed;
        top: 20px;
        right: 20px;
        z-index: 1100;
        min-width: 300px;
    }

    .detail-item {
        margin-bottom: 15px;
    }

    .detail-label {
        font-weight: 500;
        color: var(--secondary-color);
        margin-bottom: 5px;
    }

    .detail-value {
        font-weight: 500;
    }

    .action-buttons {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
    }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">
                        <h4><i class="fas fa-edit me-2"></i>Edit Product</h4>
                        <a href="inventory.php" class="btn btn-outline-light btn-sm position-absolute top-20 end-20">
                            <i class="fas fa-arrow-left me-1"></i> Back to Inventory
                        </a>
                    </div>
                    <div class="card-body">
                        <div id="alertContainer" class="alert-container"></div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h5 class="section-title"><i class="fas fa-info-circle me-2"></i>Product Information</h5>
                                    <form id="productForm" method="POST">
                                        <input type="hidden" name="update_product" value="1">
                                        
                                        <div class="detail-item">
                                            <label for="category" class="detail-label">Category</label>
                                            <select class="form-select" id="category" name="category" required>
                                                <option value="">Select Category</option>
                                                <?php foreach ($categories as $cat): ?>
                                                    <option value="<?= $cat['C_Id'] ?>" <?= $cat['C_Id'] == $product['category'] ? 'selected' : '' ?>>
                                                        <?= htmlspecialchars($cat['CategoryName']) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        
                                        <div class="detail-item">
                                            <label for="product_name" class="detail-label">Product Name</label>
                                            <input type="text" class="form-control" id="product_name" name="product_name" 
                                                   value="<?= htmlspecialchars($product['product_name']) ?>" required>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label for="price" class="detail-label">Price (₹)</label>
                                                    <input type="number" class="form-control" id="price" name="price" 
                                                           step="0.01" min="0" value="<?= $product['price'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label for="quantity" class="detail-label">Quantity</label>
                                                    <input type="number" class="form-control" id="quantity" name="quantity" 
                                                           min="0" value="<?= $product['quantity'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-item">
                                            <label for="prescription_required" class="detail-label">Prescription Required</label>
                                            <select class="form-select" id="prescription_required" name="prescription_required" required>
                                                <option value="                                                <option value="Yes" <?= $product['prescription_required'] == 'Yes' ? 'selected' : '' ?>>Yes</option>
                                                <option value="No" <?= $product['prescription_required'] == 'No' ? 'selected' : '' ?>>No</option>
                                            </select>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label for="manufactured_date" class="detail-label">Manufactured Date</label>
                                                    <input type="date" class="form-control" id="manufactured_date" name="manufactured_date" 
                                                           value="<?= $product['manufactured_date'] ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="detail-item">
                                                    <label for="expiry_date" class="detail-label">Expiry Date</label>
                                                    <input type="date" class="form-control" id="expiry_date" name="expiry_date" 
                                                           value="<?= $product['expiry_date'] ?>" required>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-item">
                                            <label for="description" class="detail-label">Description</label>
                                            <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
                                        </div>
                                        
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-save me-1"></i> Save Changes
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-section">
                                    <h5 class="section-title"><i class="fas fa-image me-2"></i>Product Image</h5>
                                    <div class="text-center mb-4">
                                        <img src="<?= $product['product_img'] ?>" alt="Product Image" class="product-image img-fluid mb-3" id="productImageDisplay">
                                    </div>
                                    
                                    <form id="imageForm" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="update_image" value="1">
                                        <div class="detail-item">
                                            <label for="product_img" class="detail-label">Upload New Image</label>
                                            <input type="file" class="form-control" id="product_img" name="product_img" 
                                                   accept="image/jpeg, image/png">
                                            <div class="form-text">Only JPG/PNG images allowed. Max size 2MB.</div>
                                        </div>
                                        
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fas fa-upload me-1"></i> Update Image
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                
                                <div class="form-section">
                                    <h5 class="section-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone</h5>
                                    <div class="alert alert-danger">
                                        <strong>Warning:</strong> Deleting this product cannot be undone. All data will be permanently removed.
                                    </div>
                                    
                                    <form id="deleteForm" method="POST">
                                        <input type="hidden" name="delete_product" value="1">
                                        <div class="action-buttons">
                                            <button type="submit" class="btn btn-danger">
                                                <i class="fas fa-trash-alt me-1"></i> Delete Product
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    $(document).ready(function() {
        // Handle product form submission with AJAX
        $('#productForm').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');
            submitBtn.prop('disabled', true);
            
            // Client-side validation
            const mfgDate = new Date($('#manufactured_date').val());
            const expiryDate = new Date($('#expiry_date').val());
            
            if (expiryDate <= mfgDate) {
                showAlert('danger', 'Expiry date must be after manufactured date');
                resetButton(submitBtn, originalText);
                return false;
            }
            
            if ($('#price').val() <= 0) {
                showAlert('danger', 'Price must be greater than 0');
                resetButton(submitBtn, originalText);
                return false;
            }
            
            if ($('#quantity').val() < 0) {
                showAlert('danger', 'Quantity cannot be negative');
                resetButton(submitBtn, originalText);
                return false;
            }
            
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status === 'success') {
                            showAlert('success', data.message);
                        } else {
                            showAlert('danger', data.message);
                        }
                    } catch (e) {
                        showAlert('danger', 'Invalid response from server');
                    }
                },
                error: function(xhr) {
                    showAlert('danger', 'Error: Could not update product. Please try again.');
                },
                complete: function() {
                    resetButton(submitBtn, originalText);
                }
            });
        });
        
        // Handle image update with AJAX
        $('#imageForm').on('submit', function(e) {
            e.preventDefault();
            
            // Show loading state
            const submitBtn = $(this).find('button[type="submit"]');
            const originalText = submitBtn.html();
            submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Uploading...');
            submitBtn.prop('disabled', true);
            
            // Validate file size
            const fileInput = $('#product_img')[0];
            if (fileInput.files.length > 0 && fileInput.files[0].size > 2 * 1024 * 1024) {
                showAlert('danger', 'File size must be less than 2MB');
                resetButton(submitBtn, originalText);
                return false;
            }
            
            // Use FormData for file upload
            var formData = new FormData(this);
            
            // Send AJAX request
            $.ajax({
                url: window.location.href,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    try {
                        const data = typeof response === 'string' ? JSON.parse(response) : response;
                        if (data.status === 'success') {
                            showAlert('success', data.message);
                            $('#productImageDisplay').attr('src', data.image_path + '?' + new Date().getTime());
                        } else {
                            showAlert('danger', data.message);
                        }
                    } catch (e) {
                        showAlert('danger', 'Invalid response from server');
                    }
                },
                error: function(xhr) {
                    showAlert('danger', 'Error: Could not upload image. Please try again.');
                },
                complete: function() {
                    resetButton(submitBtn, originalText);
                }
            });
        });
        
        // Handle delete with AJAX
        $('#deleteForm').on('submit', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                // Show loading state
                const submitBtn = $(this).find('button[type="submit"]');
                const originalText = submitBtn.html();
                submitBtn.html('<i class="fas fa-spinner fa-spin me-1"></i> Deleting...');
                submitBtn.prop('disabled', true);
                
                // Send AJAX request
                $.ajax({
                    url: window.location.href,
                    type: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        try {
                            const data = typeof response === 'string' ? JSON.parse(response) : response;
                            if (data.status === 'success') {
                                showAlert('success', data.message);
                                if (data.redirect) {
                                    setTimeout(() => {
                                        window.location.href = data.redirect;
                                    }, 1500);
                                }
                            } else {
                                showAlert('danger', data.message);
                                resetButton(submitBtn, originalText);
                            }
                        } catch (e) {
                            showAlert('danger', 'Invalid response from server');
                            resetButton(submitBtn, originalText);
                        }
                    },
                    error: function(xhr) {
                        showAlert('danger', 'Error: Could not delete product. Please try again.');
                        resetButton(submitBtn, originalText);
                    }
                });
            }
        });
        
        // Image preview
        $('#product_img').change(function() {
            const file = this.files[0];
            if (file) {
                if (file.size > 2 * 1024 * 1024) {
                    showAlert('danger', 'File size must be less than 2MB');
                    $(this).val('');
                    return;
                }
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    $('#productImageDisplay').attr('src', e.target.result);
                }
                reader.readAsDataURL(file);
            }
        });
        
        // Helper functions
        function showAlert(type, message) {
            // Remove existing alerts
            $('#alertContainer').html('');
            
            // Icon mapping
            const icons = {
                'success': 'fa-check-circle',
                'danger': 'fa-times-circle',
                'info': 'fa-info-circle',
                'warning': 'fa-exclamation-triangle'
            };
            
            // Create new alert with icon
            var alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show d-flex align-items-center">
                <i class="fas ${icons[type] || 'fa-info-circle'} me-2"></i>
                <div>${message}</div>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>`;
            
            $('#alertContainer').html(alertHtml);
            
            // Auto-dismiss after 5 seconds (except for success messages that will redirect)
            if (type !== 'success' || !message.includes('deleted')) {
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        }
        
        function resetButton(button, originalText) {
            button.html(originalText);
            button.prop('disabled', false);
        }
    });
    </script>
</body>
</html>