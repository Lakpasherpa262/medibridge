<?php
session_start();
include 'scripts/connect.php';

// Check if user is logged in
$isLoggedIn = isset($_SESSION['id']);
$userId = $isLoggedIn ? $_SESSION['id'] : null;

// Initialize cart from cookie or session
$cart = [];
if (isset($_COOKIE['cart_'.$userId])) {
    $cart = json_decode($_COOKIE['cart_'.$userId], true);
    if (!is_array($cart)) {
        $cart = [];
    }
} elseif (isset($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
    if (!is_array($cart)) {
        $cart = [];
    }
}
// Get category ID or name from URL
$categoryId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$categoryName = isset($_GET['category']) ? trim($_GET['category']) : '';

// Pagination settings
$productsPerPage = 12;
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $productsPerPage;

// Build base query
$query = "SELECT p.id, p.product_name, p.description, p.product_img, p.price, p.quantity, 
                 p.is_featured, p.featured_priority, s.shop_name, s.id as shop_id
          FROM products p
          JOIN shopdetails s ON p.shop_id = s.id";

$countQuery = "SELECT COUNT(*) FROM products p";

$whereClauses = [];
$params = [];
$countParams = [];

if ($categoryId > 0) {
    $whereClauses[] = "p.category = :category_id";
    $params[':category_id'] = $categoryId;
    $countParams[':category_id'] = $categoryId;
} elseif (!empty($categoryName)) {
    $query .= " JOIN category c ON p.category = c.C_Id";
    $countQuery .= " JOIN category c ON p.category = c.C_Id";
    $whereClauses[] = "c.CategoryName = :category_name";
    $params[':category_name'] = $categoryName;
    $countParams[':category_name'] = $categoryName;
}

// Add where clauses if any
if (!empty($whereClauses)) {
    $query .= " WHERE " . implode(" AND ", $whereClauses);
    $countQuery .= " WHERE " . implode(" AND ", $whereClauses);
}

// Get total products count
$totalProducts = $db->prepare($countQuery);
$totalProducts->execute($countParams);
$totalProducts = $totalProducts->fetchColumn();
$totalPages = ceil($totalProducts / $productsPerPage);

// Complete query with pagination
$query .= " ORDER BY p.is_featured DESC, p.featured_priority DESC, p.product_name
            LIMIT :limit OFFSET :offset";

// Prepare and execute
$stmt = $db->prepare($query);
$stmt->bindValue(':limit', $productsPerPage, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

foreach ($params as $key => $value) {
    $stmt->bindValue($key, $value);
}

$stmt->execute();
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get category name for display
$displayCategoryName = '';
if ($categoryId > 0) {
    $catStmt = $db->prepare("SELECT CategoryName FROM category WHERE C_Id = ?");
    $catStmt->execute([$categoryId]);
    $category = $catStmt->fetch(PDO::FETCH_ASSOC);
    $displayCategoryName = $category ? $category['CategoryName'] : '';
} elseif (!empty($categoryName)) {
    $displayCategoryName = $categoryName;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>All Categories - MediBridge</title>
  <!-- Bootswatch Cosmo Theme -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <!-- Animate.css -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <link rel="stylesheet" href="css/style.css">
  <style>
    /* Your existing CSS styles here */
    .product-card {
      border-radius: 10px;
      overflow: hidden;
      transition: all 0.3s ease;
      border: 1px solid #e9ecef !important;
      margin-bottom: 20px;
    }
    
    .product-card:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 20px rgba(58, 129, 191, 0.15);
      border-color: rgba(58, 129, 191, 0.3) !important;
    }
    
    .product-image-container {
      position: relative;
      height: 150px;
      background: #f8f9fa;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 20px;
    }
    
    .product-image-container img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
      transition: transform 0.3s ease;
    }
    
    .product-card:hover .product-image-container img {
      transform: scale(1.05);
    }
    
    .wishlist-icon {
      position: absolute;
      top: 8px;
      right: 8px;
      width: 28px;
      height: 28px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 2px 8px rgba(0,0,0,0.1);
      z-index: 2;
    }

    .featured-product-card {
      border: 2px solid #3a81bf;
      box-shadow: 0 0 15px rgba(58, 129, 191, 0.2);
    }
    
    .featured-badge {
      position: absolute;
      top: 10px;
      right: 10px;
      background: #e63946;
      color: white;
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 600;
      z-index: 2;
    }
    
    .priority-3 {
      border-color: #ff8a00;
      box-shadow: 0 0 15px rgba(255, 138, 0, 0.2);
    }
    
    .priority-2 {
      border-color: #4776E6;
      box-shadow: 0 0 15px rgba(71, 118, 230, 0.2);
    }
    
    .priority-1 {
      border-color: #00b09b;
      box-shadow: 0 0 15px rgba(0, 176, 155, 0.2);
    }
    
    .category-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .category-title {
      font-size: 28px;
      font-weight: 600;
      color: #2b6c80;
      margin: 0;
    }
    
    .featured-count {
      font-size: 14px;
      color: #6c757d;
    }
    
    /* Toast styles */
    .toast-container {
      position: fixed;
      bottom: 20px;
      right: 20px;
      z-index: 1100;
    }
    
    .toast {
      transition: all 0.3s ease;
    }
    
    /* Login modal */
    .login-modal .modal-content {
      border-radius: 10px;
      overflow: hidden;
    }
    
    .login-modal .modal-header {
      background-color: #3a81bf;
      color: white;
    }
    
    .login-modal .modal-body {
      padding: 30px;
    }
    
    .login-modal .modal-footer {
      justify-content: center;
    }
    
    .login-modal .form-control {
      padding: 10px 15px;
      border-radius: 5px;
    }
    
    .login-modal .btn-login {
      background-color: #3a81bf;
      color: white;
      width: 100%;
      padding: 10px;
      border-radius: 5px;
    }
    
    .login-modal .btn-login:hover {
      background-color: #2b6c80;
    }
    
    .login-modal .forgot-password {
      color: #3a81bf;
      text-decoration: none;
    }
    
    .login-modal .register-link {
      text-align: center;
      margin-top: 15px;
    }
    /* Wishlist icon styles */
.wishlist-icon {
    position: absolute;
    top: 10px;
    right: 10px;
    width: 32px;
    height: 32px;
    background: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 2;
    transition: all 0.3s ease;
}

.wishlist-icon:hover {
    transform: scale(1.1);
}

.wishlist-icon i {
    font-size: 16px;
    color: #6c757d; /* Default gray color */
    transition: all 0.3s ease;
}

.wishlist-icon i.active-wishlist {
    color: #e63946; /* Red color for active wishlist */
}

.wishlist-icon i.fa-spinner {
    color: #6c757d; /* Gray color for spinner */
}
  </style>
</head>
<body>
  <?php include 'templates/header.php'; ?>
  
    <!-- Login/Signup Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content overflow-hidden">
      <!-- Modal Header with Tabs -->
      <div class="modal-header p-0 border-0">
        <ul class="nav nav-tabs w-100" id="authTabs" role="tablist">
          <li class="nav-item w-50" role="presentation">
            <button class="nav-link active w-100 py-3 fs-5 fw-bold" id="login-tab" data-bs-toggle="tab" data-bs-target="#login-tab-pane" type="button" role="tab">
              <i class="fas fa-sign-in-alt me-2"></i> Login
            </button>
          </li>
          <li class="nav-item w-50" role="presentation">
            <button class="nav-link w-100 py-3 fs-5 fw-bold" id="signup-tab" data-bs-toggle="tab" data-bs-target="#signup-tab-pane" type="button" role="tab">
              <i class="fas fa-user-plus me-2"></i> Sign Up
            </button>
          </li>
        </ul>
        <button type="button" class="btn-close position-absolute end-0 top-0 m-2" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      
      <div class="modal-body p-4 pt-3">
        <div class="tab-content" id="authTabsContent">
          <!-- Login Tab -->
          <div class="tab-pane fade show active" id="login-tab-pane" role="tabpanel" tabindex="0">
            <div class="row">
              <div class="col-lg-6">
                <form id="loginForm" novalidate>
                  <div class="mb-4">
                    <label for="loginEmail" class="form-label fw-medium">Email Address</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                      <input type="email" class="form-control py-2" id="loginEmail" name="email" placeholder="Enter your email" required>
                    </div>
                    <div class="invalid-feedback">Please enter a valid email</div>
                  </div>
                  
                  <div class="mb-4">
                    <label for="loginPassword" class="form-label fw-medium">Password</label>
                    <div class="input-group">
                      <span class="input-group-text bg-light"><i class="fas fa-lock text-muted"></i></span>
                      <input type="password" class="form-control py-2" id="loginPassword" name="password" 
                            placeholder="Enter your password" required
                            maxlength="10"
                            onpaste="return false;"
                            oncopy="return false;"
                            oncut="return false;">
                      <button class="btn btn-outline-secondary toggle-password" type="button">
                        <i class="fas fa-eye-slash"></i>
                      </button>
                    </div>
                    <div class="invalid-feedback">Please enter your password</div>
                    <small class="text-muted">Password must be 6-10 characters</small>
                  </div>
                  
                  <button type="submit" class="btn btn-primary w-100 py-2 mb-3 fw-medium">
                    Login to your account
                  </button>
                </form>
              </div>
              
              <div class="col-lg-6 d-none d-lg-flex align-items-center justify-content-center bg-light rounded">
                <div class="text-center p-4">
                  <img src="https://cdn-icons-png.flaticon.com/512/3209/3209260.png" alt="Login" class="img-fluid mb-3" style="max-height: 180px;">
                  <h5 class="fw-bold mb-2">Welcome Back!</h5>
                  <p class="text-muted">Login to access your personalized dashboard and continue your healthcare journey.</p>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Signup Tab -->
          <div class="tab-pane fade" id="signup-tab-pane" role="tabpanel" tabindex="0">
            <div class="row">
              <!-- Logo on Left -->
              <div class="col-lg-5 signup-logo-section">
                <div class="signup-logo-content">
                  <img src="images/logo.png" alt="MediBridge Logo" class="img-fluid mb-2" style="max-height: 100px;">
                  <h4 class="fw-bold text-dark mb-3">Join MediBridge Today</h4>
                  
                  <div class="signup-benefits">
                    <div class="d-flex align-items-center mb-3">
                      <div class="bg-primary rounded-circle p-2 me-3">
                        <i class="fas fa-truck text-white"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Fast Delivery</h6>
                        <p class="text-muted mb-0">Get medicines within 24 hours</p>
                      </div>
                    </div>
                    
                    <div class="d-flex align-items-center mb-3">
                      <div class="bg-success rounded-circle p-2 me-3">
                        <i class="fas fa-user-md text-white"></i>
                      </div>
                      <div>
                        <h6 class="mb-0">Doctor Consultations</h6>
                        <p class="text-muted mb-0">Connect with certified doctors</p>
                      </div>
                    </div>
                    
                    <div class="d-flex align-items-center">
                      <div class="bg-warning rounded-circle p-2 me-3">
                        <i class="fas fa-pills text-white"></i>
                      </div>
                      
                    </div>
                  </div>
                </div>
              </div>
              
              <!-- Form on Right -->
              <div class="col-lg-6 signup-form-section">
                <form id="signupForm" novalidate>
                  <div class="row g-3">
                    <div class="col-md-6">
                      <label class="form-label fw-medium">First Name</label>
                      <input type="text" name="fname" id="fname" class="form-control py-2" placeholder="Enter first name" required>
                      <div class="invalid-feedback">First name is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label">Middle Name</label>
                      <input type="text" name="mname" id="mname" class="form-control py-2" placeholder="Enter middle name">
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Last Name</label>
                      <input type="text" name="lname" id="lname" class="form-control py-2" placeholder="Enter last name" required>
                      <div class="invalid-feedback">Last name is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Phone Number</label>
                      <input type="text" name="phone" id="phone" class="form-control py-2" placeholder="10-digit number" required maxlength="10">
                      <div class="invalid-feedback">Phone number is required</div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label fw-medium">Email</label>
                      <input type="email" name="email" id="signupEmail" class="form-control py-2" placeholder="Enter your email" required>
                      <div class="invalid-feedback">Email is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Date of Birth</label>
                      <input type="date" name="dob" id="dob" class="form-control py-2" required>
                      <div class="invalid-feedback">Date of birth is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">Gender</label>
                      <select id="gender" name="gender" class="form-select py-2" required>
                        <option value="" disabled selected>Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                      </select>
                      <div class="invalid-feedback">Gender is required</div>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label fw-medium">Full Address</label>
                      <input type="text" name="address" id="address" class="form-control py-2" placeholder="Enter your full address" required>
                      <div class="invalid-feedback">Address is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">State</label>
                      <input type="text" name="state" id="state" class="form-control py-2" placeholder="Enter your state" required>
                      <div class="invalid-feedback">State is required</div>
                    </div>
                    
                    <div class="col-md-6">
                      <label class="form-label fw-medium">District</label>
                      <input type="text" name="district" id="district" class="form-control py-2" placeholder="Enter your district" required>
                      <div class="invalid-feedback">District is required</div>
                    </div>
                    
                    <div class="col-md-8">
                      <label class="form-label fw-medium">Pincode</label>
                      <input type="text" name="pincode" id="pincode" class="form-control py-2" placeholder="6-digit pincode" required maxlength="6">
                      <div class="invalid-feedback">Pincode is required</div>
                      <div id="pincodeResult" class="small mt-1"></div>
                    </div>
                    
                    <div class="col-md-4 d-flex align-items-end">
                      <button class="btn btn-primary w-100 py-2 btn-sm" type="button" id="checkPincodeBtn">
                        <span class="d-none d-md-inline">Check Availability</span>
                        <span class="d-md-none">Check</span>
                      </button>
                    </div>
                    
                    <div class="col-12">
                      <label class="form-label">Landmark</label>
                      <input type="text" name="landmark" id="landmark" class="form-control py-2" placeholder="Enter a landmark">
                    </div>
                    
                    <div class="col-12 position-relative">
                      <label class="form-label fw-medium">Password</label>
                      <div class="input-group">
                        <input type="password" name="password" id="signupPassword" class="form-control py-2" 
                              placeholder="Create password" required
                              maxlength="10"
                              onpaste="return false;"
                              oncopy="return false;"
                              oncut="return false;"
                              pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,10}$">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="fas fa-eye-slash"></i>
                        </button>
                      </div>
                      <div id="passwordHelp" class="form-text text-danger small ps-2 pt-1 d-none">
                        Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.
                      </div>
                    </div>
                    
                    <div class="col-12 position-relative">
                      <label class="form-label fw-medium">Confirm Password</label>
                      <div class="input-group">
                        <input type="password" name="confirm_password" id="confirmPassword" class="form-control py-2" 
                              placeholder="Confirm password" required
                              maxlength="10"
                              onpaste="return false;"
                              oncopy="return false;"
                              oncut="return false;">
                        <button class="btn btn-outline-secondary toggle-password" type="button">
                          <i class="fas fa-eye-slash"></i>
                        </button>
                      </div>
                      <div class="invalid-feedback">Passwords do not match</div>
                    </div>
                    
                    <div class="col-12 mt-3">
                      <button type="submit" class="btn btn-success w-100 py-2 fw-medium">
                        Create your account
                      </button>
                    </div>
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

  <!-- Login Required Modal -->
  <div class="modal fade login-required-modal" tabindex="-1" id="loginRequiredModal">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Login Required</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <i class="fas fa-exclamation-circle"></i>
          <h4>You need to login first!</h4>
          <p>Please login to add items to your cart.</p>
          <div class="d-grid gap-2 mt-4">
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">Login</button>
            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
          </div>
        </div>
      </div>
    </div>
  </div>

  <main class="container mt-4">
    <div class="category-header">
      <h1 class="category-title">
        <i class="fas fa-pills me-2"></i>
        <?php echo $displayCategoryName ? htmlspecialchars($displayCategoryName) : 'All Categories'; ?>
      </h1>
      <?php if ($displayCategoryName): ?>
        <div class="featured-count">
          <?php 
          $featuredCount = array_reduce($products, function($carry, $item) {
            return $carry + ($item['is_featured'] ? 1 : 0);
          }, 0);
          ?>
          <?php echo $featuredCount; ?> featured products
        </div>
      <?php endif; ?>
    </div>
    
    <?php if (empty($products)): ?>
      <div class="alert alert-info">No products found in this category.</div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($products as $product): ?>
          <div class="col-6 col-md-4 col-lg-3">
  <div class="card product-card h-100 border-0 <?php 
    echo $product['is_featured'] ? 'featured-product-card priority-' . ($product['featured_priority'] ?? 1) : ''; 
  ?>">
    <!-- Add this anchor tag around the clickable content -->
    <a href="description.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
      <div class="product-image-container bg-light">
        <?php
        $imagePath = 'inventory/uploads/' . htmlspecialchars(basename($product['product_img']));
        if (!file_exists($imagePath)) {
          $imagePath = 'inventory/uploads/default.jpg';
        }
        ?>
        
        <img src="<?php echo $imagePath; ?>" 
             class="card-img-top" 
             alt="<?php echo htmlspecialchars($product['product_name']); ?>"
             onerror="this.onerror=null;this.src='inventory/uploads/default.jpg'">
        <?php if ($product['is_featured']): ?>
          <div class="featured-badge">Featured</div>
        <?php endif; ?>
        <div class="wishlist-icon"><i class="far fa-heart"></i></div>
      </div>
      
      <div class="card-body p-3">
        <div class="d-flex justify-content-between align-items-start mb-1">
          <h5 class="product-name mb-0"><?php echo htmlspecialchars($product['product_name']); ?></h5>
        </div>
        
        <?php if (!empty($product['shop_name'])): ?>
          <div class="product-shop text-muted small mb-2">
            <?php echo htmlspecialchars($product['shop_name']); ?>
          </div>
        <?php endif; ?>
        
        <div class="d-flex align-items-center mb-2">
          <div class="product-price text-primary">₹<?php echo number_format($product['price'], 2); ?></div>
        </div>
      </div>
    </a>
    
    <!-- Keep these elements outside the anchor tag -->
    <div class="quantity-selector mb-3 px-3">
      <div class="input-group input-group-sm">
        <button class="btn btn-outline-secondary quantity-minus" type="button">-</button>
        <input type="number" class="form-control text-center quantity-input" value="1" min="1" max="<?php echo $product['quantity']; ?>">
        <button class="btn btn-outline-secondary quantity-plus" type="button">+</button>
      </div>
    </div>
    
    <button class="btn btn-primary w-100 add-to-cart mx-3 mb-3" 
            data-pid="<?php echo $product['id']; ?>"
            data-shop-id="<?php echo $product['shop_id']; ?>">
      <i class="fas fa-cart-plus me-1"></i> Add to Cart
    </button>
  </div>
</div>
        <?php endforeach; ?>
      </div>
      
      <!-- Pagination -->
      <?php if ($totalPages > 1): ?>
        <nav aria-label="Page navigation" class="mt-5">
          <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
              <li class="page-item">
                <a class="page-link" 
                   href="category.php?<?php 
                   echo $categoryId ? 'id='.$categoryId : 'category='.urlencode($categoryName); 
                   ?>&page=<?php echo $page-1; ?>" 
                   aria-label="Previous">
                  <span aria-hidden="true">&laquo;</span>
                </a>
              </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?php echo $i == $page ? 'active' : ''; ?>">
                <a class="page-link" 
                   href="category.php?<?php 
                   echo $categoryId ? 'id='.$categoryId : 'category='.urlencode($categoryName); 
                   ?>&page=<?php echo $i; ?>">
                  <?php echo $i; ?>
                </a>
              </li>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" 
                   href="category.php?<?php 
                   echo $categoryId ? 'id='.$categoryId : 'category='.urlencode($categoryName); 
                   ?>&page=<?php echo $page+1; ?>" 
                   aria-label="Next">
                  <span aria-hidden="true">&raquo;</span>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </nav>
      <?php endif; ?>
    <?php endif; ?>
  </main>

  <!-- Toast Container -->
  <div class="toast-container"></div>

  <?php include 'templates/footer.php'; ?>
  
  <!-- Bootstrap JS Bundle with Popper -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
  <script>
$(document).ready(function() {
    const loginModal = new bootstrap.Modal(document.getElementById('loginModal'));
    const loginRequiredModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
    
    // IMPORTANT: Make sure this matches your PHP session variable
    const isLoggedIn = <?php echo $isLoggedIn ? 'true' : 'false'; ?>;
    
    // Debugging - log the login status to console
    console.log('User is logged in:', isLoggedIn);    
    
    // Featured product hover effects
    document.querySelectorAll('.featured-product-card').forEach(card => {
        card.addEventListener('mouseenter', function() {
            const priority = this.classList.contains('priority-3') ? 3 : 
                            this.classList.contains('priority-2') ? 2 : 1;
            const colors = {
                3: 'rgba(255, 138, 0, 0.3)',
                2: 'rgba(71, 118, 230, 0.3)',
                1: 'rgba(0, 176, 155, 0.3)'
            };
            this.style.boxShadow = `0 10px 20px ${colors[priority]}`;
        });
        
        card.addEventListener('mouseleave', function() {
            const priority = this.classList.contains('priority-3') ? 3 : 
                            this.classList.contains('priority-2') ? 2 : 1;
            const colors = {
                3: 'rgba(255, 138, 0, 0.2)',
                2: 'rgba(71, 118, 230, 0.2)',
                1: 'rgba(0, 176, 155, 0.2)'
            };
            this.style.boxShadow = `0 0 15px ${colors[priority]}`;
        });
    });

    // Quantity controls
    document.querySelectorAll('.quantity-plus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            const max = parseInt(input.getAttribute('max')) || 10;
            if (value < max) {
                input.value = value + 1;
            }
        });
    });
    
    document.querySelectorAll('.quantity-minus').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.quantity-input');
            let value = parseInt(input.value);
            if (value > 1) {
                input.value = value - 1;
            }
        });
    });
    
    // Input validation
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', function() {
            let value = parseInt(this.value);
            const max = parseInt(this.getAttribute('max')) || 10;
            if (isNaN(value)) this.value = 1;
            if (value < 1) this.value = 1;
            if (value > max) this.value = max;
        });
    });
    
   // Wishlist toggle functionality with red heart styling
$(document).on('click', '.wishlist-icon', function() {
    // Get the clicked wishlist icon element
    const icon = $(this);
    
    // Get the product card that contains this icon
    const productCard = $(this).closest('.product-card');
    
    // Get the product ID from the "Add to Cart" button in the same card
    const productId = productCard.find('.add-to-cart').data('pid');
    
    // Check if the icon is already active (red heart)
    const isActive = icon.find('i').hasClass('active-wishlist');
    
    // Check if user is logged in (using PHP session variable)
    const isLoggedIn = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;
    
    // If user is not logged in, show message and open login modal
    if (!isLoggedIn) {
        showToast('warning', 'Please login to manage your wishlist');
        $('#loginModal').modal('show');
        return false;
    }
    
    // Save the original icon HTML to restore later
    const originalIcon = icon.html();
    
    // Show loading state by replacing the icon with a spinner
    icon.html('<i class="fas fa-spinner fa-spin"></i>');
    
    // Make AJAX request to toggle wishlist status
    $.ajax({
        // URL of the wishlist handler script
        url: 'includes/toggle_wishlist.php',
        
        // Use POST method
        type: 'POST',
        
        // Expect JSON response
        dataType: 'json',
        
        // Data to send to server
        data: { 
            product_id: productId, 
            action: isActive ? 'remove' : 'add',
            user_id: <?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 'null'; ?>
        },
        
        // Success callback
        success: function(response) {
            // If the operation was successful
            if (response.status === 'success') {
                // Toggle heart icon between red and gray
                if (isActive) {
                    icon.html('<i class="far fa-heart"></i>');
                    showToast('success', 'Removed from wishlist');
                } else {
                    icon.html('<i class="fas fa-heart active-wishlist"></i>');
                    showToast('success', 'Added to wishlist');
                }
            } 
            // If login is required
            else if (response.status === 'login_required') {
                showToast('warning', 'Please login to manage your wishlist');
                $('#loginModal').modal('show');
                icon.html(originalIcon);
            } 
            // For other errors
            else {
                showToast('danger', response.message || 'Operation failed');
                // Reset icon to original state
                icon.html(originalIcon);
            }
        },
        
        // Error callback
        error: function(xhr, status, error) {
            showToast('danger', 'Error updating wishlist');
            console.error('Error:', error);
            // Reset icon to original state
            icon.html(originalIcon);
        }
    });
});
    // Add to cart functionality - fixed to properly check login status
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Debugging - log the click event
            console.log('Add to cart clicked, isLoggedIn:', isLoggedIn);
            
            if (!isLoggedIn) {
                loginRequiredModal.show();
                return;
            }
            
            const productId = this.dataset.pid;
            const quantity = parseInt(this.closest('.card-body').querySelector('.quantity-input').value);
            const button = this;
            
            // Show loading state
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
            button.disabled = true;
            
            // Create form data
            const formData = new FormData();
            formData.append('product_id', productId);
            formData.append('quantity', quantity);
            
            // Make AJAX request
            fetch('includes/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update cart count
                    const cartCountElements = document.querySelectorAll('.cart-count');
                    cartCountElements.forEach(el => {
                        el.textContent = data.cartCount;
                        el.style.display = data.cartCount > 0 ? 'block' : 'none';
                    });
                    
                    // Show success message
                    showToast('success', data.message);
                    animateCartIcon();
                } else {
                    showToast('error', data.message);
                }
            })
            .catch(error => {
                showToast('error', 'Error adding item to cart');
                console.error('Error:', error);
            })
            .finally(() => {
                // Reset button state
                button.innerHTML = originalText;
                button.disabled = false;
            });
        });
    });

    // Toggle password visibility
    $('.toggle-password').click(function() {
        const input = $(this).closest('.input-group').find('input');
        const icon = $(this).find('i');
        input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
        icon.toggleClass('fa-eye fa-eye-slash');
    });

    // Prevent copy/paste on password fields
    $('input[type="password"]').on('paste copy cut', function(e) {
        e.preventDefault();
        return false;
    });

    // Password strength indicator for signup form
    $('#signupPassword').on('input', function() {
        const password = $(this).val();
        if (password.length > 0 && !validatePassword(password)) {
            $('#passwordHelp').removeClass('d-none').text('Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character (6-10 chars)');
        } else {
            $('#passwordHelp').addClass('d-none');
        }
    });

    function validatePassword(password) {
        const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,10}$/;
        return regex.test(password);
    }

    // Login Form Submission
    $("#loginForm").submit(function(e) {
        e.preventDefault();
        clearErrors(this);

        let isValid = true;
        
        // Validate email
        const email = $("input[name='email']").val().trim();
        if (!email) {
            showError("input[name='email']", "Email is required");
            isValid = false;
        } else if (!/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(email)) {
            showError("input[name='email']", "Invalid email format");
            isValid = false;
        }
        
        // Validate password
        const password = $("input[name='password']").val().trim();
        if (!password) {
            showError("input[name='password']", "Password is required");
            isValid = false;
        }
        
        if (!isValid) return;

        const stayLoggedIn = $("#stayLoggedIn").is(":checked");
        const formData = $(this).serialize() + (stayLoggedIn ? "&stay_logged_in=1" : "");

        $.ajax({
            url: "includes/login.php",
            type: "POST",
            data: formData,
            beforeSend: function() {
                $("#loginForm button[type='submit']").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');
            },
            complete: function() {
                $("#loginForm button[type='submit']").prop("disabled", false).text("Login");
            },
            success: function(response) {
                if (response.startsWith("success:")) {
                    const role = response.split(":")[1];
                    showToast("success", "Login successful! Redirecting...");
                    
                    // Determine redirect URL based on role
                    let redirectUrl;
                    switch (role) {
                        case "1": redirectUrl = "admin_dashboard.php"; break;
                        case "2": redirectUrl = "shop_owner.php"; break;
                        case "3": redirectUrl = "doctor.php"; break;
                        case "4": redirectUrl = "delivery.php"; break;
                        case "5": redirectUrl = "user.php"; break;
                        default: 
                            showToast("error", "Unknown role detected. Please contact support.");
                            return;
                    }
                    
                    // Redirect after short delay
                    setTimeout(() => {
                        window.location.href = redirectUrl;
                    }, 1500);
                } else {
                    showError("#loginPassword", response || "Invalid login credentials");
                    showToast("error", response || "Invalid login credentials");
                }
            },
            error: function(xhr, status, error) {
                showToast("error", "An error occurred. Please try again.");
                console.error("Login error:", error);
            }
        });
    });

    // Helper functions
    function showError(input, message) {
        const $input = typeof input === "string" ? $(input) : $(input);
        const errorDiv = $input.siblings(".text-danger, .form-text");
        errorDiv.text(message).removeClass("d-none");
        $input.addClass('is-invalid');
    }

    function clearError(input) {
        const $input = typeof input === "string" ? $(input) : $(input);
        $input.siblings(".text-danger, .form-text").addClass("d-none");
        $input.removeClass('is-invalid');
    }

    function clearErrors(form) {
        $(form).find('.text-danger, .form-text').addClass('d-none');
        $(form).find('.is-invalid').removeClass('is-invalid');
    }

    // Animate cart icon
    function animateCartIcon() {
        const cartIcons = document.querySelectorAll('.fa-shopping-cart');
        cartIcons.forEach(icon => {
            icon.classList.add('animate__animated', 'animate__bounce');
            setTimeout(() => {
                icon.classList.remove('animate__animated', 'animate__bounce');
            }, 1000);
        });
    }
    
    // Toast notification
    function showToast(type, message) {
        const toastContainer = document.querySelector('.toast-container');
        const toast = document.createElement('div');
        toast.className = `toast show bg-${type} text-white`;
        toast.innerHTML = `
            <div class="toast-body d-flex justify-content-between align-items-center">
                <span>${message}</span>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;
        
        toastContainer.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
});
</script>
</body>
</html>