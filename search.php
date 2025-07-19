<?php
session_start();
include 'scripts/connect.php';

// Initialize wishlist from cookie or session
$wishlist = [];
if (isset($_COOKIE['wishlist'])) {
    $wishlist = json_decode($_COOKIE['wishlist'], true);
    if (!is_array($wishlist)) {
        $wishlist = [];
    }
} elseif (isset($_SESSION['wishlist'])) {
    $wishlist = $_SESSION['wishlist'];
    if (!is_array($wishlist)) {
        $wishlist = [];
    }
}

// Get search query
$searchQuery = isset($_GET['query']) ? trim($_GET['query']) : '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Search Results - MediBridge</title>
  <meta name="description" content="Search results for <?= htmlspecialchars($searchQuery) ?>">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
  <style>
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
      overflow-x: hidden;
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      color: var(--heading-color);
      font-weight: 600;
    }
    
    .search-container {
      position: relative;
      margin-right: 15px;
      max-width: 300px;
    }
    
    .search-input {
      padding: 8px 15px 8px 40px;
      border-radius: 20px;
      border: 1px solid #ddd;
      width: 200px;
      transition: all 0.3s ease;
    }
    
    .search-input:focus {
      width: 250px;
      outline: none;
      border-color: var(--primary-color);
      box-shadow: 0 0 0 0.25rem rgba(42, 127, 186, 0.25);
    }
    
    .search-icon {
      position: absolute;
      left: 15px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-color);
    }
    
    .product-card {
      border: 1px solid #eee;
      border-radius: 8px;
      overflow: hidden;
      height: 100%;
      transition: all 0.3s ease;
      position: relative;
    }
    
    .product-card:hover {
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      transform: translateY(-5px);
    }
    
    .product-image-container {
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px;
      background-color: #f9f9f9;
    }
    
    .product-image-container img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }
    
    .product-details {
      padding: 15px;
      border-top: 1px solid #eee;
    }
    
    .product-name {
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 5px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .product-price {
      font-size: 16px;
      font-weight: 600;
      color: #2a7fba;
    }
    
    .wishlist-icon {
      position: absolute;
      top: 10px;
      right: 10px;
      color: #ccc;
      cursor: pointer;
      font-size: 20px;
      z-index: 2;
    }
    
    .wishlist-icon.active {
      color: #ff324d;
    }
    
    .quantity-selector {
      display: flex;
      align-items: center;
      margin: 10px 0;
    }
    
    .quantity-btn {
      width: 30px;
      height: 30px;
      border: 1px solid #ddd;
      background: #f9f9f9;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
    }
    
    .quantity-input {
      width: 40px;
      height: 30px;
      text-align: center;
      border: 1px solid #ddd;
      border-left: none;
      border-right: none;
    }
    
    .add-to-cart {
      width: 100%;
      padding: 8px;
      font-size: 14px;
    }
    
    .section-title {
      position: relative;
      margin-bottom: 50px;
      text-align: center;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: -15px;
      left: 50%;
      transform: translateX(-50%);
      width: 80px;
      height: 3px;
      background: var(--secondary-color);
    }
    
    /* Responsive adjustments */
    @media (max-width: 767.98px) {
      .search-container {
        margin-right: 0;
        margin-bottom: 15px;
        max-width: 100%;
      }
      
      .search-input {
        width: 100%;
      }
      
      .search-input:focus {
        width: 100%;
      }
    }
  </style>
</head>
<body>
<!-- Include the header -->
<?php include 'templates/header.php'; ?>

<!-- Search Results Section -->
<section class="py-5">
  <div class="container">
    <h2 class="section-title">
      Search Results for "<?= htmlspecialchars($searchQuery) ?>"
    </h2>
    
    <?php if(!empty($searchQuery)): ?>
      <div class="row g-4">
        <?php
        // Prepare the search query
        $searchTerm = '%' . $searchQuery . '%';
        
        // Get products matching the search (case-insensitive)
    $stmt = $db->prepare("
    SELECT p.id, p.product_name, p.price, p.product_img, p.quantity, 
           s.shop_name, s.id as shop_id
    FROM products p
    JOIN shopdetails s ON p.shop_id = s.id
    WHERE (LOWER(p.product_name) LIKE LOWER(:search) 
    OR LOWER(p.description) LIKE LOWER(:search))
    AND p.is_listed = 1
    ORDER BY p.created_at DESC
    LIMIT 50
");
        $stmt->bindValue(':search', $searchTerm, PDO::PARAM_STR);
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        if(count($products) > 0): 
          foreach($products as $product):
            $isInWishlist = in_array($product['id'], $wishlist);
            $imagePath = !empty($product['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_img'])) : 'inventory/uploads/default_product.jpg';
        ?>
        <div class="col-lg-3 col-md-4 col-sm-6">
          <div class="product-card h-100">
            <!-- Wishlist Icon -->
            <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart wishlist-icon" 
               data-product-id="<?= $product['id'] ?>"
               style="color: <?= $isInWishlist ? '#ff324d' : '#ccc' ?>"></i>
            
            <!-- Product Image -->
            <div class="product-image-container">
              <img src="<?= $imagePath ?>" 
                   alt="<?= htmlspecialchars($product['product_name']) ?>"
                   onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
            </div>
            
            <!-- Product Details -->
            <div class="product-details">
              <h6 class="product-name"><?= htmlspecialchars($product['product_name']) ?></h6>
              <small class="text-muted d-block"><?= htmlspecialchars($product['shop_name']) ?></small>
              <div class="product-price">₹<?= number_format($product['price'], 2) ?></div>
              
              <!-- Quantity Selector -->
              <div class="quantity-selector">
                <button class="quantity-minus btn btn-sm btn-outline-secondary">-</button>
                <input type="number" class="quantity-input form-control form-control-sm" value="1" min="1" max="<?= $product['quantity'] ?>">
                <button class="quantity-plus btn btn-sm btn-outline-secondary">+</button>
              </div>
              
              <!-- Add to Cart Button -->
              <button class="btn btn-primary btn-sm add-to-cart" data-pid="<?= $product['id'] ?>">
                <i class="fas fa-cart-plus me-1"></i> Add to Cart
              </button>
            </div>
          </div>
        </div>
        <?php 
          endforeach;
        else: 
        ?>
        <div class="col-12 text-center py-5">
          <div class="alert alert-info">
            <i class="fas fa-info-circle me-2"></i>
            No products found matching "<?= htmlspecialchars($searchQuery) ?>"
          </div>
          <a href="index.php" class="btn btn-primary mt-3">
            <i class="fas fa-arrow-left me-2"></i> Back to Home
          </a>
        </div>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <div class="col-12 text-center py-5">
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-triangle me-2"></i>
          Please enter a search term
        </div>
        <a href="index.php" class="btn btn-primary mt-3">
          <i class="fas fa-arrow-left me-2"></i> Back to Home
        </a>
      </div>
    <?php endif; ?>
  </div>
</section>

<!-- Include the footer -->
<footer class="footer">
  <div class="container">
    <div class="row">
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <h3 class="footer-title">MediBridge</h3>
        <p>Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions.</p>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <h3 class="footer-title">Quick Links</h3>
        <ul class="footer-links">
          <li><a href="index.php">Home</a></li>
          <li><a href="view_all.php">Products</a></li>
          <li><a href="templates/blog.php">Blog</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <h3 class="footer-title">Categories</h3>
        <ul class="footer-links">
          <li><a href="view_all.php?category_id=1&category_name=Antibiotics">Antibiotics</a></li>
          <li><a href="view_all.php?category_id=2&category_name=Antipyretics">Antipyretics</a></li>
          <li><a href="view_all.php?category_id=3&category_name=Skincare">Skincare</a></li>
        </ul>
      </div>
      <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
        <h3 class="footer-title">Contact Us</h3>
        <ul class="footer-links">
          <li><i class="fas fa-phone-alt me-2"></i> +91 98765 43210</li>
          <li><i class="fas fa-envelope me-2"></i> info@medibridge.com</li>
        </ul>
      </div>
    </div>
    <div class="copyright">
      <p class="mb-0">&copy; 2025 MediBridge. All Rights Reserved.</p>
    </div>
  </div>
</footer>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
$(document).ready(function() {
  // Quantity controls
  $(document).on('click', '.quantity-plus', function() {
    const input = $(this).siblings('.quantity-input');
    const max = parseInt(input.attr('max')) || 10;
    let value = parseInt(input.val());
    if (value < max) input.val(value + 1);
  });
  
  $(document).on('click', '.quantity-minus', function() {
    const input = $(this).siblings('.quantity-input');
    let value = parseInt(input.val());
    if (value > 1) input.val(value - 1);
  });
  
  // Input validation
  $(document).on('change', '.quantity-input', function() {
    const max = parseInt($(this).attr('max')) || 10;
    let value = parseInt($(this).val());
    if (isNaN(value) || value < 1) $(this).val(1);
    if (value > max) $(this).val(max);
  });
  
  // Wishlist toggle
  $(document).on('click', '.wishlist-icon', function() {
    if (!<?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>) {
      $('#loginModal').modal('show');
      showToast('warning', 'Please login to manage your wishlist');
      return false;
    }
    
    const icon = $(this);
    const productId = icon.data('product-id');
    const isActive = icon.hasClass('fas');
    
    $.ajax({
      url: 'includes/toggle_wishlist.php',
      type: 'POST',
      data: { product_id: productId, action: isActive ? 'remove' : 'add' },
      success: function(response) {
        if (response === 'success') {
          icon.toggleClass('fas far');
          icon.css('color', isActive ? '#ccc' : '#ff324d');
          showToast('success', isActive ? 'Removed from wishlist' : 'Added to wishlist');
        } else if (response === 'login_required') {
          $('#loginModal').modal('show');
          showToast('warning', 'Please login to manage your wishlist');
        } else {
          showToast('danger', 'Operation failed');
        }
      },
      error: function() {
        showToast('danger', 'Error updating wishlist');
      }
    });
  });
 
// Add to cart
$(document).on('click', '.add-to-cart', function() {
  if (!<?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>) {
    $('#loginModal').modal('show');
    showToast('warning', 'Please login to add items to your cart');
    return false;
  }
  
  const button = $(this);
  const productId = button.data('pid');
  const quantity = button.closest('.product-card').find('.quantity-input').val();
  
  $.ajax({
    url: 'includes/add_to_cart.php',
    type: 'POST',
    data: { product_id: productId, quantity: quantity },
    beforeSend: function() {
      button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
    },
    success: function(response) {
      // First parse the JSON response
      let jsonResponse;
      try {
        jsonResponse = JSON.parse(response);
      } catch (e) {
        // If response isn't JSON, treat it as a plain text response
        button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
        showToast('danger', 'Invalid server response');
        return;
      }

      // Handle the response based on status
      if (jsonResponse.status === 'success') {
        button.html('<i class="fas fa-check"></i> Added');
        button.removeClass('btn-primary').addClass('btn-success');
        showToast('success', jsonResponse.message || 'Item added to cart successfully');
        
        // Update cart count if provided
        if (jsonResponse.cartCount) {
          const cartCount = $('.cart-count');
          cartCount.text(jsonResponse.cartCount);
        }
        
        setTimeout(() => {
          button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
          button.removeClass('btn-success').addClass('btn-primary');
        }, 2000);
      } else {
        button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
        showToast('danger', jsonResponse.message || 'Failed to add to cart');
      }
    },
    error: function(xhr, status, error) {
      button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
      showToast('danger', 'Error adding to cart: ' + error);
    }
  });
});


  // Helper function to show toast messages
  function showToast(type, message) {
    const toast = $(`
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
          <div class="toast-header bg-${type} text-white">
            <strong class="me-auto">${type.charAt(0).toUpperCase() + type.slice(1)}</strong>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
          </div>
          <div class="toast-body">${message}</div>
        </div>
      </div>
    `);
    
    $('body').append(toast);
    setTimeout(() => toast.remove(), 5000);
  }
});
// Handle Enter key in search input
  $(document).on('keypress', '.search-input', function(e) {
    if (e.which === 13) { // Enter key
      e.preventDefault();
      const searchQuery = $(this).val().trim();
      if (searchQuery) {
        window.location.href = 'search.php?query=' + encodeURIComponent(searchQuery);
      } else {
        showToast('warning', 'Please enter a search term');
      }
    }
  });

  // Focus search input when clicking search icon
  $(document).on('click', '.search-icon', function() {
    $(this).siblings('input').focus();
  });
</script>
</body>
</html>