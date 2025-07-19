<?php
session_start();
// Include database connection
include 'scripts/connect.php';

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$product_id = $_GET['id'];

// Fetch product details
$productStmt = $db->prepare("
    SELECT p.*, s.shop_name, s.address, s.shop_number, s.email, c.CategoryName,
           p.manufactured_date, p.expiry_date
    FROM products p
    JOIN shopdetails s ON p.shop_id = s.id
    JOIN category c ON p.category = c.C_Id
    WHERE p.id = ?
");
$productStmt->execute([$product_id]);
$product = $productStmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    header("Location: index.php");
    exit();
}

// Fetch similar products from the same category
$similarStmt = $db->prepare("
    SELECT p.id, p.product_name, p.price, p.product_img 
    FROM products p
    WHERE p.category = ? AND p.id != ?
    ORDER BY RAND()
    LIMIT 4
");
$similarStmt->execute([$product['category'], $product_id]);
$similarProducts = $similarStmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize wishlist
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

// Check if product is in wishlist
$isInWishlist = in_array($product_id, $wishlist);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($product['product_name']) ?> | MediBridge</title>
  <meta name="description" content="<?= htmlspecialchars(substr($product['description'], 0, 150)) ?>">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Custom CSS -->
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
    }
    
    h1, h2, h3, h4, h5, h6 {
      font-family: 'Montserrat', sans-serif;
      color: var(--heading-color);
      font-weight: 600;
    }
    
    .navbar {
      background-color: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
      padding: 15px 0;
    }
    
    .product-image-container {
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      height: 400px;
      background-color: #f9f9f9;
    }
    
    .product-image-container img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }
    
    .product-thumbnails {
      display: flex;
      gap: 10px;
      margin-top: 15px;
    }
    
    .thumbnail {
      width: 70px;
      height: 70px;
      border: 1px solid #ddd;
      border-radius: 5px;
      padding: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .thumbnail:hover, .thumbnail.active {
      border-color: var(--primary-color);
    }
    
    .thumbnail img {
      width: 100%;
      height: 100%;
      object-fit: contain;
    }
    
    .product-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 10px;
    }
    
    .product-price {
      font-size: 24px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 15px;
    }
    
    .original-price {
      text-decoration: line-through;
      color: var(--text-color);
      font-size: 18px;
      margin-left: 10px;
    }
    
    .discount-badge {
      background-color: var(--accent-color);
      color: white;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 14px;
      margin-left: 10px;
    }
    
    .product-meta {
      margin-bottom: 20px;
    }
    
    .meta-item {
      display: flex;
      align-items: center;
      margin-bottom: 8px;
    }
    
    .meta-item i {
      width: 20px;
      color: var(--primary-color);
      margin-right: 10px;
    }
    
    .quantity-selector {
      display: flex;
      align-items: center;
      margin: 20px 0;
    }
    
    .quantity-btn {
      width: 40px;
      height: 40px;
      border: 1px solid #ddd;
      background: #f9f9f9;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      font-size: 18px;
    }
    
    .quantity-input {
      width: 60px;
      height: 40px;
      text-align: center;
      border: 1px solid #ddd;
      border-left: none;
      border-right: none;
    }
    
    .btn-action {
      padding: 12px 25px;
      font-weight: 500;
      border-radius: 8px;
      margin-right: 10px;
      margin-bottom: 10px;
    }
    
    .btn-wishlist {
      background-color: white;
      border: 1px solid var(--primary-color);
      color: var(--primary-color);
    }
    
    .btn-wishlist.active {
      background-color: var(--primary-color);
      color: white;
    }
    
    .btn-wishlist:hover {
      background-color: var(--primary-color);
      color: white;
    }
    
    .product-tabs {
      margin-top: 50px;
    }
    
    .nav-tabs .nav-link {
      color: var(--text-color);
      font-weight: 500;
      border: none;
      padding: 12px 20px;
    }
    
    .nav-tabs .nav-link.active {
      color: var(--primary-color);
      border-bottom: 3px solid var(--primary-color);
      background: none;
    }
    
    .tab-content {
      padding: 20px;
      border: 1px solid #eee;
      border-top: none;
      border-radius: 0 0 10px 10px;
    }
    
    .similar-product-card {
      border: 1px solid #eee;
      border-radius: 8px;
      overflow: hidden;
      transition: all 0.3s ease;
      height: 100%;
    }
    
    .similar-product-card:hover {
      box-shadow: 0 10px 20px rgba(0,0,0,0.1);
      transform: translateY(-5px);
    }
    
    .similar-product-image {
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px;
      background-color: #f9f9f9;
    }
    
    .similar-product-image img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }
    
    .similar-product-details {
      padding: 15px;
      border-top: 1px solid #eee;
    }
    
    .similar-product-name {
      font-size: 14px;
      font-weight: 500;
      margin-bottom: 5px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .similar-product-price {
      font-size: 16px;
      font-weight: 600;
      color: var(--primary-color);
    }
    
    .shop-info-card {
      border: 1px solid #eee;
      border-radius: 10px;
      padding: 20px;
      margin-bottom: 30px;
    }
    
    .shop-name {
      font-size: 20px;
      font-weight: 600;
      color: var(--primary-color);
      margin-bottom: 15px;
    }
    
    .shop-meta {
      margin-bottom: 15px;
    }
    
    .shop-meta i {
      color: var(--primary-color);
      margin-right: 10px;
      width: 20px;
    }
    
    .rating {
      color: #ffc107;
      margin-bottom: 15px;
    }
    
    .badge-availability {
      background-color: var(--secondary-color);
      color: white;
      padding: 5px 10px;
      border-radius: 20px;
      font-size: 14px;
    }
    
    @media (max-width: 767.98px) {
      .product-image-container {
        height: 300px;
      }
      
      .product-title {
        font-size: 24px;
      }
      
      .product-price {
        font-size: 20px;
      }
      
      .btn-action {
        padding: 10px 15px;
        font-size: 14px;
      }
    }
  </style>
</head>
<body>
<?php include 'templates/header.php'; ?>
  <!-- Product Details Section -->
  <section class="py-5">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="product-image-container">
            <?php 
            $imagePath = !empty($product['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_img'])) : 'inventory/uploads/default_product.jpg';
            ?>
            <img src="<?= $imagePath ?>" 
                 alt="<?= htmlspecialchars($product['product_name']) ?>"
                 id="mainProductImage"
                 onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
          </div>
          
          <div class="product-thumbnails">
            <!-- Main image as first thumbnail -->
            <div class="thumbnail active" onclick="changeImage('<?= $imagePath ?>')">
              <img src="<?= $imagePath ?>" 
                   alt="<?= htmlspecialchars($product['product_name']) ?>"
                   onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
            </div>
          </div>
        </div>
        
        <div class="col-lg-6">
          <h1 class="product-title"><?= htmlspecialchars($product['product_name']) ?></h1>
          
          <div class="product-meta">
    <div class="meta-item">
        <i class="fas fa-tag"></i>
        <span>Category: <?= htmlspecialchars($product['CategoryName']) ?></span>
    </div>
    <div class="meta-item">
        <i class="fas fa-cubes"></i>
        <span>Available Quantity: <?= htmlspecialchars($product['quantity']) ?></span>
    </div>
    <div class="meta-item">
        <i class="fas fa-calendar-alt"></i>
        <span>
            Manufactured: <?= !empty($product['manufactured_date']) ? date('F Y', strtotime($product['manufactured_date'])) : 'Not specified' ?><br>
            Expires: <?= !empty($product['expiry_date']) ? date('F Y', strtotime($product['expiry_date'])) : 'Not specified' ?>
        </span>
    </div>
</div>
          
          <div class="product-price">
            ₹<?= number_format($product['price'], 2) ?>
            <?php if ($product['price'] > $product['price']): ?>
              <span class="original-price">₹<?= number_format($product['original_price'], 2) ?></span>
              <span class="discount-badge">
                <?= round(($product['original_price'] - $product['price']) / $product['original_price'] * 100) ?>% OFF
              </span>
            <?php endif; ?>
          </div>
           
          <div class="quantity-selector">
            <button class="quantity-btn" id="quantityMinus">-</button>
            <input type="number" class="quantity-input" id="quantityInput" value="1" min="1" max="<?= $product['quantity'] ?>">
            <button class="quantity-btn" id="quantityPlus">+</button>
          </div>
          
          <div class="d-flex flex-wrap">
            <button class="btn btn-primary btn-action" id="addToCartBtn">
              <i class="fas fa-shopping-cart me-2"></i> Add to Cart
            </button>
            
            <button class="btn btn-wishlist btn-action <?= $isInWishlist ? 'active' : '' ?>" id="wishlistBtn">
              <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart me-2"></i> 
              <?= $isInWishlist ? 'In Wishlist' : 'Add to Wishlist' ?>
            </button>
          </div>
          
          <div class="shop-meta">
  <div class="meta-item">
    <i class="fas fa-map-marker-alt"></i>
    <span><?= !empty($product['address']) ? htmlspecialchars($product['address']) : 'Address not available' ?></span>
  </div>
  <div class="meta-item">
    <i class="fas fa-phone"></i>
    <span><?= !empty($product['shop_number']) ? htmlspecialchars($product['shop_number']) : 'Phone not available' ?></span>
  </div>
  <div class="meta-item">
    <i class="fas fa-envelope"></i>
    <span><?= !empty($product['email']) ? htmlspecialchars($product['email']) : 'Email not available' ?></span>
  </div>
</div>  
            <div class="d-flex justify-content-between align-items-center">
              <span class="badge-availability">
                <i class="fas fa-check-circle me-1"></i> In Stock
              </span>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Product Tabs -->
      <div class="product-tabs">
        <ul class="nav nav-tabs" id="productTabs" role="tablist">
          <li class="nav-item" role="presentation">
            <button class="nav-link active" id="description-tab" data-bs-toggle="tab" data-bs-target="#description" type="button" role="tab">
              Description
            </button>
          </li>
          <li class="nav-item" role="presentation">
            <button class="nav-link" id="specifications-tab" data-bs-toggle="tab" data-bs-target="#specifications" type="button" role="tab">
              Specifications
            </button>
          </li>
        </ul>
        
        <div class="tab-content" id="productTabsContent">
          <div class="tab-pane fade show active" id="description" role="tabpanel">
            <h4>Product Description</h4>
            <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
            
            <h4 class="mt-4">Key Benefits</h4>
            <ul>
              <li>High-quality pharmaceutical product</li>
              <li>Manufactured under strict quality control</li>
              <li>Effective for intended use</li>
              <li>Properly stored and handled</li>
            </ul>
          </div>
          
          <div class="tab-pane fade" id="specifications" role="tabpanel">
            <table class="table">
              <tbody>
                <tr>
                  <th scope="row">Product Name</th>
                  <td><?= htmlspecialchars($product['product_name']) ?></td>
                </tr>
                <tr>
                  <th scope="row">Category</th>
                  <td><?= htmlspecialchars($product['CategoryName']) ?></td>
                </tr>
                <tr>
                  <th scope="row">Manufactured Date</th>
                  <td><?= !empty($product['manufactured_date']) ? date('F Y', strtotime($product['manufactured_date'])) : 'Not specified' ?></td>

                </tr>
                <tr>
                  <th scope="row">Expiry Date</th>
                  <td><?= !empty($product['expiry_date']) ? date('F Y', strtotime($product['expiry_date'])) : 'Not specified' ?></td>
                </tr>
              </tbody>
            </table>
          </div>
          </div>
        </div>
      </div>
      
      <!-- Similar Products -->
      <div class="mt-5">
        <h3 class="mb-4">Similar Products</h3>
        
        <div class="row">
          <?php foreach ($similarProducts as $similar): 
            $similarImagePath = !empty($similar['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($similar['product_img'])) : 'inventory/uploads/default_product.jpg';
          ?>
          <div class="col-md-3 col-6 mb-4">
            <a href="description.php?id=<?= $similar['id'] ?>" class="text-decoration-none">
              <div class="similar-product-card">
                <div class="similar-product-image">
                  <img src="<?= $similarImagePath ?>" 
                       alt="<?= htmlspecialchars($similar['product_name']) ?>"
                       onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                </div>
                <div class="similar-product-details">
                  <h6 class="similar-product-name"><?= htmlspecialchars($similar['product_name']) ?></h6>
                  <div class="similar-product-price">₹<?= number_format($similar['price'], 2) ?></div>
                </div>
              </div>
            </a>
          </div>
          <?php endforeach; ?>
        </div>
      </div>
    </div>
  </section>
<?php include 'templates/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
  $(document).ready(function() {
    // Quantity controls
    $('#quantityPlus').click(function() {
      const input = $('#quantityInput');
      const max = parseInt(input.attr('max')) || 10;
      let value = parseInt(input.val());
      if (value < max) input.val(value + 1);
    });
    
    $('#quantityMinus').click(function() {
      const input = $('#quantityInput');
      let value = parseInt(input.val());
      if (value > 1) input.val(value - 1);
    });
    
    $('#quantityInput').change(function() {
      const max = parseInt($(this).attr('max')) || 10;
      let value = parseInt($(this).val());
      if (isNaN(value) || value < 1) $(this).val(1);
      if (value > max) $(this).val(max);
    });
    
    // Change main product image when thumbnail is clicked
    window.changeImage = function(src) {
      $('#mainProductImage').attr('src', src);
      $('.thumbnail').removeClass('active');
      $(`.thumbnail img[src="${src}"]`).parent().addClass('active');
    };
    
    // Rating stars
    $('.rating-input i').hover(function() {
      const rating = $(this).data('rating');
      $('.rating-input i').each(function(i) {
        if (i < rating) {
          $(this).removeClass('far').addClass('fas');
        } else {
          $(this).removeClass('fas').addClass('far');
        }
      });
    }).click(function() {
      const rating = $(this).data('rating');
      $('#reviewRating').val(rating);
    });
    
    // Add to cart
    $('#addToCartBtn').click(function() {
      if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        showToast('warning', 'Please login to add items to your cart');
        return false;
      }
      
      const button = $(this);
      const productId = <?= $product_id ?>;
      const quantity = $('#quantityInput').val();
      
      $.ajax({
        url: 'includes/add_to_cart.php',
        type: 'POST',
        data: { product_id: productId, quantity: quantity },
        beforeSend: function() {
          button.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
        },
        success: function(response) {
          if (response === 'success') {
            button.html('<i class="fas fa-check"></i> Added to Cart');
            showToast('success', 'Product added to cart');
            
            setTimeout(() => {
              button.html('<i class="fas fa-shopping-cart me-2"></i> Add to Cart');
            }, 2000);
          } else if (response === 'login_required') {
            button.html('<i class="fas fa-shopping-cart me-2"></i> Add to Cart');
            showToast('warning', 'Please login to add items to your cart');
          } else {
            button.html('<i class="fas fa-shopping-cart me-2"></i> Add to Cart');
            showToast('danger', response || 'Failed to add to cart');
          }
        },
        error: function() {
          button.html('<i class="fas fa-shopping-cart me-2"></i> Add to Cart');
          showToast('danger', 'Error adding to cart');
        }
      });
    });
    
    // Wishlist toggle
    $('#wishlistBtn').click(function() {
      if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        showToast('warning', 'Please login to manage your wishlist');
        return false;
      }
      
      const button = $(this);
      const productId = <?= $product_id ?>;
      const isActive = button.hasClass('active');
      
      $.ajax({
        url: 'includes/toggle_wishlist.php',
        type: 'POST',
        data: { product_id: productId, action: isActive ? 'remove' : 'add' },
        beforeSend: function() {
          button.prop('disabled', true);
        },
        complete: function() {
          button.prop('disabled', false);
        },
        success: function(response) {
          if (response === 'success') {
            button.toggleClass('active');
            button.find('i').toggleClass('far fas');
            
            if (isActive) {
              button.html('<i class="far fa-heart me-2"></i> Add to Wishlist');
              showToast('success', 'Removed from wishlist');
            } else {
              button.html('<i class="fas fa-heart me-2"></i> In Wishlist');
              showToast('success', 'Added to wishlist');
            }
          } else if (response === 'login_required') {
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
    
    // Review form submission
    $('#reviewForm').submit(function(e) {
      e.preventDefault();
      
      if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
        showToast('warning', 'Please login to submit a review');
        return false;
      }
      
      const rating = $('#reviewRating').val();
      if (rating == 0) {
        showToast('warning', 'Please select a rating');
        return false;
      }
      
      const formData = $(this).serialize();
      formData += '&product_id=<?= $product_id ?>';
      
      $.ajax({
        url: 'includes/submit_review.php',
        type: 'POST',
        data: formData,
        beforeSend: function() {
          $('#reviewForm button[type="submit"]').prop('disabled', true)
            .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');
        },
        complete: function() {
          $('#reviewForm button[type="submit"]').prop('disabled', false).text('Submit Review');
        },
        success: function(response) {
          if (response === 'success') {
            showToast('success', 'Review submitted successfully!');
            $('#reviewForm')[0].reset();
            $('.rating-input i').removeClass('fas').addClass('far');
            $('#reviewRating').val('0');
          } else {
            showToast('danger', response || 'Failed to submit review');
          }
        },
        error: function() {
          showToast('danger', 'Error submitting review');
        }
      });
    });
    
    // Check authentication before accessing protected pages
    window.checkAuth = function(page) {
      const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
      if (!isLoggedIn) {
        showToast('warning', 'Please login to access this page');
      } else {
        window.location.href = page;
      }
      return false;
    };
    
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
  </script>
</body>
</html>