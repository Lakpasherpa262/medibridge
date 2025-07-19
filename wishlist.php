<?php
session_start();
// Include database connection
include 'scripts/connect.php';

// Initialize user variable
$user = null;

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Get user details
$userStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get cart count for the user
$cartCount = 0;
$cartStmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
$cartStmt->execute([$_SESSION['id']]);
$cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
$cartCount = $cartData['total'] ?? 0;

// Get wishlist items
$wishlistItems = [];
$wishlistStmt = $db->prepare("
    SELECT p.id, p.product_name, p.price, p.product_img, p.quantity as stock, 
           s.shop_name, c.CategoryName as category
    FROM wishlist w
    JOIN products p ON w.product_id = p.id
    JOIN shopdetails s ON p.shop_id = s.id
    JOIN category c ON p.category = c.C_Id
    WHERE w.user_id = ?
");
$wishlistStmt->execute([$_SESSION['id']]);
$wishlistItems = $wishlistStmt->fetchAll(PDO::FETCH_ASSOC);

// Handle move to cart action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['move_to_cart'])) {
    $productId = $_POST['product_id'];
    
    try {
        // Begin transaction
        $db->beginTransaction();

        // Get the shop_id of the product
        $shopStmt = $db->prepare("SELECT shop_id FROM products WHERE id = ?");
        $shopStmt->execute([$productId]);
        $shopData = $shopStmt->fetch(PDO::FETCH_ASSOC);

        if (!$shopData) {
            throw new Exception("Product not found.");
        }

        $shopId = $shopData['shop_id'];

        // Check if product already exists in cart
        $checkStmt = $db->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$_SESSION['id'], $productId]);
        $existingItem = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($existingItem) {
            // Update quantity if already in cart
            $updateStmt = $db->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
            $updateStmt->execute([$existingItem['id']]);
        } else {
            // Add new item to cart with shop_id
            $insertStmt = $db->prepare("INSERT INTO cart (user_id, product_id, shop_id, quantity) VALUES (?, ?, ?, 1)");
            $insertStmt->execute([$_SESSION['id'], $productId, $shopId]);
        }

        // Remove from wishlist
        $deleteStmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$_SESSION['id'], $productId]);

        // Commit transaction
        $db->commit();

        // Refresh the page
        header("Location: wishlist.php");
        exit();
    } catch (PDOException $e) {
        $db->rollBack();
        $error = "Error moving item to cart: " . $e->getMessage();
    } catch (Exception $e) {
        $db->rollBack();
        $error = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Wishlist - MediBridge</title>
  
  <!-- Use the same styles as in your user.php -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <style>
    /* Use the same root variables and styles from your user.php */
    :root {
      --primary-color: #2a7fba;
      --secondary-color: #3bb77e;
      --accent-color: #ff7e33;
      --dark-color: #253d4e;
      --light-color: #f7f8fa;
      --text-color: #7e7e7e;
      --heading-color: #253d4e;
    }
    
    body {
      font-family: 'Poppins', sans-serif;
      color: var(--text-color);
      background-color: #ffffff;
    }
    
    .wishlist-item {
      border: 1px solid #eee;
      border-radius: 8px;
      transition: all 0.3s ease;
      margin-bottom: 20px;
    }
    
    .wishlist-item:hover {
      box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }
    
    .wishlist-img {
      height: 150px;
      object-fit: contain;
      padding: 15px;
    }
    
    .btn-move-to-cart {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
    }
    
    .btn-move-to-cart:hover {
      background-color: #2fa36b;
      border-color: #2fa36b;
    }
    
    .empty-wishlist {
      text-align: center;
      padding: 50px 0;
    }
    
    .empty-wishlist i {
      font-size: 60px;
      color: #ddd;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <?php include 'templates/header.php'; ?>

  <!-- Main Content -->
  <main class="py-5">
    <div class="container">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Wishlist</h1>
        <a href="view_all.php" class="btn btn-outline-primary">Continue Shopping</a>
      </div>
      
      <?php if (isset($error)): ?>
        <div class="alert alert-danger"><?= $error ?></div>
      <?php endif; ?>
      
      <?php if (empty($wishlistItems)): ?>
        <div class="empty-wishlist">
          <i class="far fa-heart"></i>
          <h3>Your wishlist is empty</h3>
          <p>You haven't added any items to your wishlist yet.</p>
          <a href="view_all.php" class="btn btn-primary">Browse Products</a>
        </div>
      <?php else: ?>
        <div class="row">
          <?php foreach ($wishlistItems as $item): 
            $imagePath = !empty($item['product_img']) ? 'inventory/' . htmlspecialchars($item['product_img']) : 'inventory/uploads/default_product.jpg';
          ?>
            <div class="col-md-6 col-lg-4 mb-4">
              <div class="wishlist-item h-100 p-3">
                <div class="row g-3">
                  <div class="col-md-4">
                    <img src="<?= $imagePath ?>" 
                         alt="<?= htmlspecialchars($item['product_name']) ?>" 
                         class="img-fluid wishlist-img"
                         onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                  </div>
                  <div class="col-md-8">
                    <h5 class="mb-2"><?= htmlspecialchars($item['product_name']) ?></h5>
                    <p class="text-muted small mb-1">Category: <?= htmlspecialchars($item['category']) ?></p>
                    <p class="text-muted small mb-2">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                    <h5 class="text-primary mb-3">₹<?= number_format($item['price'], 2) ?></h5>
                    
                    <div class="d-flex flex-wrap gap-2">
                      <form method="post" class="d-inline">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" name="move_to_cart" class="btn btn-move-to-cart btn-sm">
                          <i class="fas fa-cart-plus me-1"></i> Move to Cart
                        </button>
                      </form>
                      
                      <form method="post" class="d-inline">
                        <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                        <button type="submit" name="remove_from_wishlist" class="btn btn-outline-danger btn-sm">
                          <i class="fas fa-trash-alt me-1"></i> Remove
                        </button>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  </main>

  <?php include 'templates/footer.php'; ?>


  <!-- Scripts (same as user.php with additional wishlist functionality) -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <!-- Previous HTML/PHP code remains the same until the JavaScript section -->
<script>
$(document).ready(function() {
    // Initialize DataTables for each category table
    $('.category-table').each(function() {
        $(this).DataTable({
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
    });

    // Make rows clickable - this is the fixed version
    $(document).on('click', '.clickable-row', function(e) {
        // Check if the click was on a link or button inside the row
        if ($(e.target).is('a, button, :input') || $(e.target).closest('a, button, :input').length) {
            return;
        }
        
        var productId = $(this).data('id');
        if (productId) {
            window.location.href = 'edit_product.php?id=' + productId;
        }
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