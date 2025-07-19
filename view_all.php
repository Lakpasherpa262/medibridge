<?php
session_start();
// Include database connection
include 'scripts/connect.php';

// Check if user is logged in properly - make sure this matches your login system
$isLoggedIn = isset($_SESSION['id']) && !empty($_SESSION['id']); // Changed from 'user_id' to 'id'
$userId = $isLoggedIn ? $_SESSION['id'] : null;

// Initialize cart from cookie or session
$cart = [];
if ($isLoggedIn && isset($_COOKIE['cart_'.$userId])) {
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

// Get parameters from URL
$category_filter = isset($_GET['category']) ? intval($_GET['category']) : 0;

// Get all categories with product counts
$categories_query = "SELECT c.C_Id, c.CategoryName, COUNT(p.id) as product_count 
                    FROM category c
                    LEFT JOIN products p ON c.C_Id = p.category
                    GROUP BY c.C_Id, c.CategoryName
                    ORDER BY c.CategoryName";
$categories = $db->query($categories_query)->fetchAll(PDO::FETCH_ASSOC);

// Get featured products count across all categories
$featured_query = "SELECT COUNT(*) FROM products WHERE is_featured = 1";
$featured_count = $db->query($featured_query)->fetchColumn();

// Get total products count
$total_query = "SELECT COUNT(*) FROM products";
$total_products = $db->query($total_query)->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Categories - MediBridge</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.0/dist/cosmo/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .category-section {
            margin-bottom: 30px;
            padding: 20px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .category-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .product-card {
            transition: all 0.3s ease;
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 20px;
            border: 1px solid #e9ecef !important;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(58, 129, 191, 0.15);
            border-color: rgba(58, 129, 191, 0.3) !important;
        }
        
        .featured-product-card {
            border: 2px solid #3a81bf;
            box-shadow: 0 0 15px rgba(58, 129, 191, 0.2);
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
        
        .product-image-container {
            position: relative;
            height: 200px;
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
        
        .product-name {
            font-size: 1rem;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        
        .product-price {
            font-weight: bold;
            font-size: 1.1rem;
        }
        
        .view-all-btn-container {
            margin-top: 15px;
            text-align: center;
        }
        
        .quantity-selector {
            margin-bottom: 10px;
        }
        
        .quantity-input {
            text-align: center;
        }
        
        .filter-section {
            margin-bottom: 20px;
        }
        
        @media (max-width: 767px) {
            .category-header {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .product-count {
                margin-top: 0.5rem;
            }
            
            .filter-section .row > div {
                margin-bottom: 10px;
            }
        }
        
        /* Toast notification styles */
        .toast-container {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1100;
        }
        
        .toast {
            transition: all 0.3s ease;
        }
        
        /* Enhanced Login Modal Styles */
        .login-modal .modal-content {
            border-radius: 15px;
            overflow: hidden;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        
        .login-modal .modal-header {
            background: linear-gradient(135deg, #3a81bf 0%, #2b6c80 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }
        
        .login-modal .modal-title {
            font-weight: 600;
            font-size: 1.5rem;
        }
        
        .login-modal .modal-body {
            padding: 2rem;
        }
        
        .login-modal .form-control {
            border-radius: 8px;
            padding: 12px 15px;
            border: 1px solid #e0e0e0;
            transition: all 0.3s;
        }
        
        .login-modal .form-control:focus {
            border-color: #3a81bf;
            box-shadow: 0 0 0 0.25rem rgba(58, 129, 191, 0.25);
        }
        
        .login-modal .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #3a81bf 0%, #2b6c80 100%);
            border: none;
            border-radius: 8px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(58, 129, 191, 0.3);
        }
        
        .forgot-password {
            color: #3a81bf;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        
        .forgot-password:hover {
            color: #2b6c80;
            text-decoration: underline;
        }
        
        .register-link {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.95rem;
            color: #6c757d;
        }
        
        .register-link a {
            color: #3a81bf;
            font-weight: 500;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .register-link a:hover {
            color: #2b6c80;
            text-decoration: underline;
        }
        
        .form-check-input:checked {
            background-color: #3a81bf;
            border-color: #3a81bf;
        }
        
        .form-check-label {
            font-size: 0.9rem;
        }
        
        .login-modal .btn-close {
            filter: invert(1);
        }
        
        /* Social Login Buttons */
        .social-login {
            margin-top: 1.5rem;
            text-align: center;
        }
        
        .social-login p {
            position: relative;
            margin: 1.5rem 0;
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .social-login p::before,
        .social-login p::after {
            content: "";
            position: absolute;
            top: 50%;
            width: 30%;
            height: 1px;
            background-color: #e0e0e0;
        }
        
        .social-login p::before {
            left: 0;
        }
        
        .social-login p::after {
            right: 0;
        }
        
        .social-buttons {
            display: flex;
            justify-content: center;
            gap: 10px;
        }
        
        .social-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            transition: all 0.3s;
        }
        
        .social-btn:hover {
            transform: translateY(-3px);
        }
        
        .btn-google {
            background-color: #db4437;
        }
        
        .btn-facebook {
            background-color: #4267B2;
        }
        
        .btn-twitter {
            background-color: #1DA1F2;
        }
        
        /* Signup specific styles */
        .signup-logo-section {
            background: linear-gradient(135deg, #f0f7ff 0%, #e6f4ff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .signup-logo-content {
            text-align: center;
            padding: 20px;
        }
        
        .signup-benefits {
            text-align: left;
            margin-top: 20px;
        }
        
        .signup-form-section {
            padding: 30px;
        }
        
        /* Password strength indicator */
        #passwordHelp {
            font-size: 0.85rem;
        }
        
        /* Invalid feedback styling */
        .invalid-feedback {
            font-size: 0.85rem;
        }
        
        /* Toggle password visibility button */
        .toggle-password {
            cursor: pointer;
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
                  
                  <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="stayLoggedIn" name="stay_logged_in">
                    <label class="form-check-label" for="stayLoggedIn">Keep me logged in</label>
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
                        <p class="text-muted mb-0">Get medicines within hours</p>
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
                      <div>
                        <h6 class="mb-0">Wide Range</h6>
                        <p class="text-muted mb-0">Explore an extensive selection of healthcare products from
                             trusted brands, carefully chosen to meet diverse needs and preferences.</p>
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
<!-- Navigation -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container">
    <div class="logo-container">
      <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">  
      <a class="navbar-brand" href="index.php">Medi<span>Bridge</span></a>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="search-container me-3">
      <form action="search.php" method="GET" class="d-flex align-items-center">
        <i class="fas fa-search search-icon"></i>
        <input type="text" name="query" class="search-input form-control" placeholder="Search medicines...">
      </form>
    </div>
          
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item">
          <a class="nav-link active" href="index.php">Home</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="checkAuth('doctor_book.php')">Doctor Consultation</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="checkAuth('upload_prescription.php')">Upload Prescription</a>
        </li>
      </ul>
      
      <ul class="navbar-nav">
        <li class="nav-item ms-lg-3">
          <a class="btn btn-primary" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fas fa-user me-1"></i> Login
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

    
    <!-- Toast Notifications Container -->
    <div class="toast-container"></div>
    
    <div class="container mt-4">
        <div class="category-header">
            <h1 class="category-title">
                <i class="fas fa-list-alt me-2"></i>
                All Categories
            </h1>
            <div class="product-count">
                <?php echo $featured_count; ?> featured of <?php echo $total_products; ?> products
            </div>
        </div>
        
        <!-- Filter Section -->
        <div class="filter-section">
            <form id="filterForm" method="get" action="view_all.php">
                <div class="row">
                    <div class="col-md-4 offset-md-8">
                        <div class="input-group">
                            <select class="form-select" name="category" onchange="this.form.submit()">
                                <option value="0" <?php echo $category_filter == 0 ? 'selected' : ''; ?>>All Categories</option>
                                <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['C_Id']; ?>" 
                                        <?php echo $category_filter == $category['C_Id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($category['CategoryName']); ?>
                                        (<?php echo $category['product_count']; ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <?php if ($category_filter > 0): ?>
                                <button class="btn btn-outline-secondary" type="button" onclick="resetFilters()">
                                    <i class="fas fa-times"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        
        <?php 
        // Determine which categories to display based on filter
        $display_categories = $categories;
        if ($category_filter > 0) {
            $display_categories = array_filter($categories, function($cat) use ($category_filter) {
                return $cat['C_Id'] == $category_filter;
            });
        }
        
        if (empty($display_categories)): ?>
            <div class="alert alert-info">No categories found.</div>
        <?php else: ?>
            <?php foreach ($display_categories as $category): ?>
                <?php
                // Get products for this category
                $category_id = $category['C_Id'];
                
                // Build product query for this category
                $query_where = ["p.category = :category_id"];
                $params = [':category_id' => $category_id];
                
                $where_clause = "WHERE " . implode(" AND ", $query_where);
                
                // Always order by featured first
                $order_by = "ORDER BY p.is_featured DESC, p.featured_priority DESC, p.product_name";
                
                // Get products for this category (limited to 4 for preview when not filtered)
                $limit = $category_filter > 0 ? 100 : 4;
                $product_query = "
                    SELECT p.*, s.shop_name, s.id as shop_id 
                    FROM products p
                    JOIN shopdetails s ON p.shop_id = s.id
                    $where_clause
                    $order_by
                    LIMIT $limit
                ";
                
                $product_stmt = $db->prepare($product_query);
                foreach ($params as $key => $value) {
                    $product_stmt->bindValue($key, $value);
                }
                $product_stmt->execute();
                $products = $product_stmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Skip categories with no products
                if (empty($products)) continue;
                ?>
                
                <div class="category-section">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h3>
                            <i class="fas fa-pills me-2"></i>
                            <?php echo htmlspecialchars($category['CategoryName']); ?>
                            <small class="text-muted">(<?php echo $category['product_count']; ?> products)</small>
                        </h3>
                        <?php if ($category_filter == 0 && $category['product_count'] > 4): ?>
                            <a href="category.php?id=<?php echo $category_id; ?>" class="btn btn-sm btn-outline-primary">
                                View All <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                    
                    <div class="row g-3">
                        <?php foreach ($products as $product): 
                            // Product Image Path Handling
                            $imagePath = 'inventory/uploads/' . htmlspecialchars(basename($product['product_img']));
                            if (!file_exists($imagePath)) {
                                $imagePath = 'inventory/uploads/default.jpg';
                            }
                           ?>
                            <div class="col-6 col-md-3">
    <div class="card product-card h-100 <?php 
        echo $product['is_featured'] ? 'featured-product-card priority-' . ($product['featured_priority'] ?? 1) : ''; 
    ?>">
        <a href="description.php?id=<?php echo $product['id']; ?>" class="text-decoration-none text-dark">
            <div class="product-image-container">
                <img src="<?php echo $imagePath; ?>" 
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
        
        <div class="quantity-selector mb-3 px-3">
            <div class="input-group input-group-sm">
                <button class="btn btn-outline-secondary quantity-minus" type="button">-</button>
                <input type="number" class="form-control text-center quantity-input" 
                       value="1" min="1" max="<?php echo $product['quantity']; ?>">
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
                    
                    <?php if ($category_filter == 0 && $category['product_count'] > 4): ?>
                        <div class="view-all-btn-container">
                            <a href="category.php?id=<?php echo $category_id; ?>" class="btn btn-primary">
                                View All <?php echo $category['product_count']; ?> Products in <?php echo htmlspecialchars($category['CategoryName']); ?>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    <?php include 'templates/footer.php'; ?>

    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
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

// Call this on page load
$(document).ready(function() {
    updateWishlistCount();
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

        // ======================
// PASSWORD VALIDATION
// ======================
function validatePassword(password) {
    // Check password meets all requirements
    const hasUpperCase = /[A-Z]/.test(password);
    const hasLowerCase = /[a-z]/.test(password);
    const hasNumber = /\d/.test(password);
    const hasSpecialChar = /[@$!%*?&]/.test(password);
    const isValidLength = password.length >= 6 && password.length <= 10;
    
    return {
        isValid: hasUpperCase && hasLowerCase && hasNumber && hasSpecialChar && isValidLength,
        messages: [
            !hasUpperCase ? "At least one uppercase letter" : null,
            !hasLowerCase ? "At least one lowercase letter" : null,
            !hasNumber ? "At least one number" : null,
            !hasSpecialChar ? "At least one special character (@$!%*?&)" : null,
            !isValidLength ? "Between 6-10 characters" : null
        ].filter(msg => msg !== null)
    };
}

// ======================
// FORM VALIDATION HELPERS
// ======================
function showError(input, message) {
    // Show error message for a field
    const $input = $(input);
    $input.addClass('is-invalid');
    const $feedback = $input.siblings('.invalid-feedback');
    
    if ($feedback.length) {
        $feedback.text(message).removeClass('d-none');
    } else {
        $input.after(`<div class="invalid-feedback d-block">${message}</div>`);
    }
}

function clearError(input) {
    // Clear error message for a field
    const $input = $(input);
    $input.removeClass('is-invalid');
    $input.siblings('.invalid-feedback').addClass('d-none');
}

function clearAllErrors(form) {
    // Clear all errors in a form
    $(form).find('.is-invalid').removeClass('is-invalid');
    $(form).find('.invalid-feedback').addClass('d-none');
}

// ======================
// FIELD VALIDATION
// ======================
function validateEmail(email) {
    // Basic email validation
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function validatePhone(phone) {
    // 10 digit phone number validation
    return /^\d{10}$/.test(phone);
}

function validatePincode(pincode) {
    // 6 digit pincode validation
    return /^\d{6}$/.test(pincode);
}

// Login Form Validation
$('#loginForm').on('submit', function(e) {
    e.preventDefault();
    clearAllErrors(this);
    
    const email = $('#loginEmail').val().trim();
    const password = $('#loginPassword').val().trim();
    let isValid = true;
    
    // Email validation
    if (!email) {
        showError('#loginEmail', 'Email is required');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('#loginEmail', 'Please enter a valid email');
        isValid = false;
    }
    
    // Password validation
    if (!password) {
        showError('#loginPassword', 'Password is required');
        isValid = false;
    } else if (password.length < 6 || password.length > 10) {
        showError('#loginPassword', 'Password must be 6-10 characters');
        isValid = false;
    }
    
    if (!isValid) {
        showToast('warning', 'Please fix the errors in the form');
        return;
    }
    
    // Submit form via AJAX
    $.ajax({
        url: 'includes/login.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        beforeSend: function() {
            $('#loginForm button[type="submit"]').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Logging in...');
        },
        success: function(response) {
            if (response.status === 'success') {
                showToast('success', 'Login successful! Redirecting...');
                setTimeout(() => {
                    window.location.href = 'user.php';
                }, 1500);
            } else {
                showError('#loginPassword', response.message);
                showToast('danger', response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON || {};
            showToast('danger', response.message || 'Login failed. Please try again.');
        },
        complete: function() {
            $('#loginForm button[type="submit"]').prop('disabled', false)
                .text('Login to your account');
        }
    });
});

// Signup Form Validation
$('#signupForm').on('submit', function(e) {
    e.preventDefault();
    clearAllErrors(this);
    
    // Validate pincode first
    const pincode = $('#pincode').val().trim();
    const pincodeMessage = $('#pincodeResult').text().toLowerCase();
    
    if (pincode.length !== 6) {
        showError('#pincode', 'Please enter a valid 6-digit pincode');
        showToast('warning', 'Please check your pincode');
        return;
    }
    
    if (pincodeMessage.includes('unavailable') || pincodeMessage.includes('do not deliver')) {
        showError('#pincode', 'Delivery not available in your area');
        showToast('warning', 'We currently don\'t deliver to your area');
        return;
    }
    
    // Validate all required fields
    let isValid = true;
    
    // Name fields
    if (!$('#fname').val().trim()) {
        showError('#fname', 'First name is required');
        isValid = false;
    }
    
    if (!$('#lname').val().trim()) {
        showError('#lname', 'Last name is required');
        isValid = false;
    }
    
    // Phone validation
    const phone = $('#phone').val().trim();
    if (!phone) {
        showError('#phone', 'Phone number is required');
        isValid = false;
    } else if (!/^\d{10}$/.test(phone)) {
        showError('#phone', 'Please enter a valid 10-digit phone number');
        isValid = false;
    }
    
    // Email validation
    const email = $('#signupEmail').val().trim();
    if (!email) {
        showError('#signupEmail', 'Email is required');
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('#signupEmail', 'Please enter a valid email');
        isValid = false;
    }
    
    // Date of birth validation
    if (!$('#dob').val()) {
        showError('#dob', 'Date of birth is required');
        isValid = false;
    } else {
        // Check if user is at least 18 years old
        const dob = new Date($('#dob').val());
        const today = new Date();
        const minAgeDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
        
        if (dob > minAgeDate) {
            showError('#dob', 'You must be at least 18 years old');
            isValid = false;
        }
    }
    
    // Gender validation
    if (!$('#gender').val()) {
        showError('#gender', 'Gender is required');
        isValid = false;
    }
    
    // Address validation
    if (!$('#address').val().trim()) {
        showError('#address', 'Address is required');
        isValid = false;
    }
    
    // State/district validation
    if (!$('#state').val().trim()) {
        showError('#state', 'State is required');
        isValid = false;
    }
    
    if (!$('#district').val().trim()) {
        showError('#district', 'District is required');
        isValid = false;
    }
    
    // Password validation
    const password = $('#signupPassword').val().trim();
    if (!password) {
        showError('#signupPassword', 'Password is required');
        isValid = false;
    } else if (password.length < 6 || password.length > 10) {
        showError('#signupPassword', 'Password must be 6-10 characters');
        isValid = false;
    } else if (!/(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])/.test(password)) {
        showError('#signupPassword', 'Password must contain uppercase, lowercase, number and special character');
        isValid = false;
    }
    
    // Confirm password
    const confirmPassword = $('#confirmPassword').val().trim();
    if (password !== confirmPassword) {
        showError('#confirmPassword', 'Passwords do not match');
        isValid = false;
    }
    
    if (!isValid) {
        showToast('warning', 'Please fix the errors in the form');
        return;
    }
    
    // Submit form via AJAX
    $.ajax({
        url: 'includes/userSignup.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        beforeSend: function() {
            $('#signupForm button[type="submit"]').prop('disabled', true)
                .html('<span class="spinner-border spinner-border-sm"></span> Registering...');
        },
        success: function(response) {
            if (response.status === 'success') {
                showToast('success', 'Registration successful! Redirecting...');
                setTimeout(() => {
                    window.location.href = 'user.php';
                }, 1500);
            } else {
                if (response.field) {
                    showError('#' + response.field, response.message);
                } else {
                    showError('#signupPassword', response.message);
                }
                showToast('danger', response.message);
            }
        },
        error: function(xhr) {
            const response = xhr.responseJSON || {};
            showToast('danger', response.message || 'Registration failed. Please try again.');
        },
        complete: function() {
            $('#signupForm button[type="submit"]').prop('disabled', false)
                .text('Create your account');
        }
    });
});

// ======================
// PINCODE VALIDATION
// ======================
$('#checkPincodeBtn').click(function() {
    const pincode = $('#pincode').val().trim();
    
    if (pincode.length !== 6) {
        $('#pincodeResult').html('<span class="text-danger">Please enter a 6-digit pincode</span>');
        return;
    }
    
    $.ajax({
        url: 'includes/check_pincode.php',
        type: 'POST',
        data: { pincode: pincode },
        beforeSend: function() {
            $('#checkPincodeBtn').html('<span class="spinner-border spinner-border-sm"></span> Checking...');
        },
        success: function(response) {
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.status === 'available') {
                    $('#pincodeResult').html('<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>');
                } else {
                    $('#pincodeResult').html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + data.message + '</span>');
                }
            } catch (e) {
                // Handle non-JSON responses
                if (response.includes('available')) {
                    $('#pincodeResult').html('<span class="text-success"><i class="fas fa-check-circle"></i> ' + response + '</span>');
                } else {
                    $('#pincodeResult').html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + response + '</span>');
                }
            }
        },
        error: function() {
            $('#pincodeResult').html('<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Error checking pincode</span>');
        },
        complete: function() {
            $('#checkPincodeBtn').text('Check Availability');
        }
    });
});

// ======================
// TOGGLE PASSWORD VISIBILITY
// ======================
$('.toggle-password').click(function() {
    const input = $(this).closest('.input-group').find('input');
    const icon = $(this).find('i');
    
    input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    icon.toggleClass('fa-eye fa-eye-slash');
});

// ======================
// INPUT FORMATTING
// ======================
// Only allow letters in name fields
$('#fname, #mname, #lname, #state, #district').on('input', function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
});

// Only allow numbers in phone field (10 digits max)
$('#phone').on('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
});

// Only allow numbers in pincode field (6 digits max)
$('#pincode').on('input', function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
});

// ======================
// TOAST NOTIFICATIONS
// ======================
function showToast(type, message) {
    const toast = $(`
        <div class="toast show align-items-center text-white bg-${type} border-0 position-fixed bottom-0 end-0 m-3" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `);
    
    $('.toast-container').append(toast);
    
    setTimeout(() => {
        toast.remove();
    }, 5000);
}

// ======================
// DOCUMENT READY
// ======================
$(document).ready(function() {
    // Initialize Bootstrap tooltips
    $('[data-bs-toggle="tooltip"]').tooltip();
    
    // Password strength indicator
    $('#signupPassword').on('input', function() {
        const password = $(this).val();
        const validation = validatePassword(password);
        
        if (password.length > 0 && !validation.isValid) {
            $('#passwordHelp').removeClass('d-none').text(
                'Password requirements: ' + validation.messages.join(', ')
            );
        } else {
            $('#passwordHelp').addClass('d-none');
        }
    });
    
    // Confirm password validation
    $('#confirmPassword').on('input', function() {
        const password = $('#signupPassword').val();
        const confirm = $(this).val();
        
        if (confirm && confirm !== password) {
            showError(this, 'Passwords do not match');
        } else {
            clearError(this);
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
        
        // Reset filters
        function resetFilters() {
            window.location.href = 'view_all.php';
        }
    });
    </script>
</body>
</html>