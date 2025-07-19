<?php
// Include database connection
include 'scripts/connect.php';

// Initialize user variable
$user = null;

// Get user details if logged in
if (isset($_SESSION['id'])) {
    $userStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
}
?>
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
    
    .navbar {
      background-color: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
      padding: 15px 0;
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 28px;
      color: var(--primary-color) !important;
      margin-left: 10px;
    }
    
    .navbar-brand span {
      color: var(--secondary-color);
    }
    
    .nav-link {
      font-weight: 500;
      color: var(--dark-color) !important;
      padding: 8px 15px !important;
      transition: all 0.3s ease;
    }
    
    .nav-link:hover, .nav-link.active {
      color: var(--primary-color) !important;
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
    
        .btn-primary {
  background-color: var(--primary-color);
  border-color: var(--primary-color);
  padding: 10px 25px;
  font-weight: 500;
  border-radius: 8px;
}

    
    .btn-primary:hover {
      background-color: #1f6a9a;
      border-color: #1f6a9a;
    }
    
    .btn-secondary {
      background-color: var(--secondary-color);
      border-color: var(--secondary-color);
      padding: 10px 25px;
      font-weight: 500;
      border-radius: 8px;
    }

    .btn-secondary:hover {
      background-color: #2fa36b;
      border-color: #2fa36b;
    }
    
    /* Sidebar Styles */
    .sidebar {
      position: fixed;
      top: 0;
      right: -350px;
      width: 350px;
      height: 100vh;
      background-color: white;
      box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
      z-index: 1050;
      transition: right 0.3s ease;
      overflow-y: auto;
    }
    
    .sidebar.show {
      right: 0;
    }
    
    .sidebar-header {
      padding: 25px;
      border-bottom: 1px solid #eee;
    }
    
    .sidebar-body {
      padding: 20px;
    }
    
    .sidebar-item {
      padding: 12px 15px;
      border-radius: 8px;
      margin-bottom: 5px;
      cursor: pointer;
      transition: all 0.3s ease;
      display: flex;
      align-items: center;
    }
    
    .sidebar-item:hover {
      background-color: #f8f9fa;
    }
    
    .sidebar-item i {
      margin-right: 15px;
      color: var(--primary-color);
      width: 20px;
      text-align: center;
    }
    
    .overlay {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-color: rgba(0, 0, 0, 0.5);
      z-index: 1040;
      display: none;
    }
    
    .overlay.show {
      display: block;
    }
    
    .user-avatar {
      width: 40px;
      height: 40px;
      border-radius: 50%;
      background-color: var(--primary-color);
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 600;
      cursor: pointer;
      margin-left: 15px;
    }
    
    .hero-section {
      background: linear-gradient(135deg, rgba(42, 127, 186, 0.1) 0%, rgba(59, 183, 126, 0.1) 100%);
      padding: 80px 0;
      position: relative;
      overflow: hidden;
    }
    
    .hero-content {
      position: relative;
      z-index: 2;
    }
    
    .hero-title {
      font-size: 48px;
      font-weight: 700;
      margin-bottom: 20px;
      line-height: 1.2;
    }
    
    .hero-subtitle {
      font-size: 18px;
      margin-bottom: 30px;
      color: var(--text-color);
    }
    
    .hero-image img {
      width: 100%;
      max-width: 500px;
      height: auto;
      object-fit: contain;
      animation: float 6s ease-in-out infinite;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-20px); }
      100% { transform: translateY(0px); }
    }
    
    .feature-card {
      background: white;
      border-radius: 12px;
      padding: 30px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: all 0.3s ease;
      height: 100%;
      border: 1px solid rgba(0, 0, 0, 0.03);
    }
    
    .feature-card:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }
    
    .feature-icon {
      width: 70px;
      height: 70px;
      background: rgba(42, 127, 186, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 20px;
      color: var(--primary-color);
      font-size: 30px;
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
    
    .product-card {
      border-radius: 10px;
      overflow: hidden;
      border: 1px solid #eee;
      transition: all 0.3s ease;
      height: 100%;
    }
    
    .product-card:hover {
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
      transform: translateY(-5px);
    }
    
    .product-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      background: var(--accent-color);
      color: white;
      padding: 3px 10px;
      border-radius: 4px;
      font-size: 12px;
      font-weight: 500;
    }
    
    .wishlist-btn {
      position: absolute;
      top: 10px;
      right: 10px;
      width: 36px;
      height: 36px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
      color: #ccc;
      cursor: pointer;
      transition: all 0.3s ease;
    }
    
    .wishlist-btn.active {
      color: #ff324d;
    }
    
    .wishlist-btn:hover {
      color: #ff324d;
      transform: scale(1.1);
    }
    
    .product-img {
      height: 200px;
      object-fit: contain;
      padding: 20px;
    }
    
    .product-info {
      padding: 20px;
      border-top: 1px solid #eee;
    }
    
    .product-title {
      font-size: 16px;
      font-weight: 600;
      margin-bottom: 5px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }
    
    .product-price {
      font-size: 18px;
      font-weight: 700;
      color: var(--primary-color);
    }
    
    .product-old-price {
      font-size: 14px;
      color: #999;
      text-decoration: line-through;
      margin-left: 5px;
    }
    
    .quantity-selector {
      display: flex;
      align-items: center;
      margin: 15px 0;
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
      user-select: none;
    }
    
    .quantity-input {
      width: 50px;
      height: 30px;
      text-align: center;
      border: 1px solid #ddd;
      border-left: none;
      border-right: none;
    }
    
    .promo-banner {
      background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
      border-radius: 12px;
      padding: 40px;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .promo-banner:before {
      content: '';
      position: absolute;
      top: -50px;
      right: -50px;
      width: 200px;
      height: 200px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .promo-banner:after {
      content: '';
      position: absolute;
      bottom: -80px;
      right: -80px;
      width: 250px;
      height: 250px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
    }
    
    .promo-title {
      font-size: 28px;
      font-weight: 700;
      margin-bottom: 15px;
    }
    
    .promo-subtitle {
      font-size: 16px;
      margin-bottom: 25px;
      opacity: 0.9;
    }
    
    .special-offer-banner {
      background: linear-gradient(to right, #ff7e33, #ff4d4d);
      border-radius: 12px;
      padding: 40px;
      color: white;
      position: relative;
      overflow: hidden;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .special-offer-banner .product-image {
      max-height: 200px;
      object-fit: contain;
      animation: pulse 2s infinite;
    }
    
    @keyframes pulse {
      0% { transform: scale(1); }
      50% { transform: scale(1.05); }
      100% { transform: scale(1); }
    }
    
    .mission-section {
      background: url('images/medical-pattern.png') center/cover no-repeat;
      padding: 80px 0;
      text-align: center;
      position: relative;
    }
    
    .mission-section:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(255, 255, 255, 0.9);
    }
    
    .mission-content {
      position: relative;
      z-index: 2;
    }
    
    .mission-title {
      font-size: 36px;
      font-weight: 700;
      color: var(--primary-color);
      margin-bottom: 20px;
    }
    
    .mission-text {
      font-size: 18px;
      max-width: 800px;
      margin: 0 auto;
      color: var(--dark-color);
    }
    
    .category-header {
      display: flex;
      justify-content-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .category-title {
      font-size: 24px;
      font-weight: 700;
      color: var(--dark-color);
      margin: 0;
    }
    
    .view-all-btn {
      font-weight: 500;
      color: var(--primary-color);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .view-all-btn:hover {
      color: var(--secondary-color);
    }
    
    .view-all-btn i {
      transition: transform 0.3s ease;
    }
    
    .view-all-btn:hover i {
      transform: translateX(5px);
    }
    
    .pharmacy-card {
      background: white;
      border-radius: 10px;
      padding: 20px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      margin-bottom: 30px;
    }
    
    .pharmacy-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
      padding-bottom: 15px;
      border-bottom: 1px solid #eee;
    }
    
    .pharmacy-name {
      font-size: 18px;
      font-weight: 600;
      color: var(--dark-color);
      margin: 0;
    }
    
    .pharmacy-icon {
      width: 40px;
      height: 40px;
      background: rgba(59, 183, 126, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      color: var(--secondary-color);
    }
    
    .footer {
      background: var(--dark-color);
      color: white;
      padding: 60px 0 30px;
    }
    
    .footer-title {
      font-size: 20px;
      font-weight: 600;
      margin-bottom: 25px;
      color: white;
    }
    
    .footer-links {
      list-style: none;
      padding: 0;
    }
    
    .footer-links li {
      margin-bottom: 10px;
    }
    
    .footer-links a {
      color: rgba(255, 255, 255, 0.7);
      text-decoration: none;
      transition: all 0.3s ease;
    }
    
    .footer-links a:hover {
      color: white;
      padding-left: 5px;
    }
    
    .social-links a {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 40px;
      height: 40px;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 50%;
      color: white;
      margin-right: 10px;
      transition: all 0.3s ease;
    }
    
    .social-links a:hover {
      background: var(--primary-color);
      transform: translateY(-3px);
    }
    
    .copyright {
      border-top: 1px solid rgba(255, 255, 255, 0.1);
      padding-top: 30px;
      margin-top: 30px;
      text-align: center;
      color: rgba(255, 255, 255, 0.7);
    }
    
    .mission-section {
      background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                  url('https://images.unsplash.com/photo-1579684385127-1ef15d508118?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
      background-size: cover;
      background-position: center;
      color: white;
      padding: 4rem 2rem;
      text-align: center;
      border-radius: 16px;
      margin: 2rem 0;
      box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }
    
    .mission-title {
      font-size: 2.5rem;
      font-weight: 700;
      margin-bottom: 1rem;
      text-shadow: 0 2px 4px rgba(0,0,0,0.3);
    }

    .mission-text {
      font-size: 1.5rem;
      max-width: 800px;
      margin: 0 auto;
      line-height: 1.6;
      text-shadow: 0 1px 2px rgba(0,0,0,0.3);
    }

    /* Cart button styles */
    .cart-btn {
      position: relative;
      margin-left: 10px;
    }
    
    .cart-count {
      position: absolute;
      top: -5px;
      right: -5px;
      background-color: var(--accent-color);
      color: white;
      border-radius: 50%;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 12px;
      font-weight: bold;
    }
    
    /* Logo container styles */
    .logo-container {
      display: flex;
      align-items: center;
    }
    
    .logo-img {
      height: 40px;
      width: auto;
    }
    
    /* Promo slots */
    .promo-slots {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 20px;
      margin-bottom: 30px;
    }
    
    .promo-slot {
      background: white;
      border-radius: 10px;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      overflow: hidden;
    }
    
    .shop-header {
      padding: 15px;
      background: var(--primary-color);
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .swiper {
      padding: 10px;
    }
    
    .swiper-slide {
      height: auto;
    }
    
    .swiper-pagination {
      position: relative;
      margin-top: 10px;
    }
    
    .product-image-container {
      height: 150px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px;
    }
    
    .product-image-container img {
      max-height: 100%;
      max-width: 100%;
      object-fit: contain;
    }
    
    .add-to-cart {
      transition: all 0.3s ease;
    }
    
    .add-to-cart:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
    }
    
    .wishlist-icon {
      transition: all 0.3s ease;
    }
    
    .wishlist-icon:hover {
      transform: scale(1.1);
    }
    
    .skincare-banner {
      border: 1px solid rgba(0,0,0,0.05);
    }
    
    .bg-light-blue {
      background-color: #2a7fba;
    }
    
    h3 {
      color: #253d4e;
      font-size: 1.4rem;
    }
    
    /* Subtle floating animation */
    .floating-animation {
      animation: float 3s ease-in-out infinite;
    }
    /* Sidebar Styles */
.sidebar {
    position: fixed;
    top: 0;
    right: -350px;
    width: 350px;
    height: 100vh;
    background-color: white;
    box-shadow: -5px 0 15px rgba(0, 0, 0, 0.1);
    z-index: 1050;
    transition: right 0.3s ease;
    overflow-y: auto;
}

.sidebar.show {
    right: 0;
}

.sidebar-header {
    padding: 25px;
    border-bottom: 1px solid #eee;
}

.sidebar-body {
    padding: 20px;
}

.sidebar-item {
    padding: 12px 15px;
    border-radius: 8px;
    margin-bottom: 5px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
}

.sidebar-item:hover {
    background-color: #f8f9fa;
}

.sidebar-item i {
    margin-right: 15px;
    color: var(--primary-color);
    width: 20px;
    text-align: center;
}

.overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.5);
    z-index: 1040;
    display: none;
}

.overlay.show {
    display: block;
}

.user-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background-color: var(--primary-color);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    cursor: pointer;
    margin-left: 15px;
}
    @keyframes float {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    /* Responsive adjustments */
    @media (max-width: 991.98px) {
      .hero-title {
        font-size: 36px;
      }
      
      .hero-subtitle {
        font-size: 16px;
      }
      
      .section-title {
        margin-bottom: 30px;
      }
      
      .banner-title {
        font-size: 2rem;
      }
      
      .banner-subtitle {
        font-size: 1.25rem;
      }
      
      .banner-text {
        font-size: 1rem;
      }
      
      .banner-product-image {
        max-height: 250px;
      }
    }
    
    @media (max-width: 767.98px) {
      .navbar-brand {
        font-size: 24px;
      }
      
      .hero-title {
        font-size: 28px;
      }
      
      .hero-image {
        margin-top: 30px;
      }
      
      .feature-card {
        margin-bottom: 20px;
      }
      
      .promo-banner {
        padding: 30px 20px;
      }
      
      .promo-title {
        font-size: 22px;
      }
      
      .mission-title {
        font-size: 28px;
      }
      
      .mission-text {
        font-size: 16px;
      }
      
      .skincare-banner .row {
        flex-direction: column-reverse;
      }
      
      .banner-content {
        padding: 2rem 1rem;
        text-align: center;
      }
      
      .banner-text {
        margin-left: auto;
        margin-right: auto;
      }
      
      .d-flex {
        justify-content: center;
      }
      
      .sidebar {
        width: 300px;
      }
    }
    
    @media (max-width: 575.98px) {
      .hero-section {
        padding: 60px 0;
      }
      
      .hero-title {
        font-size: 24px;
      }
      
      .btn {
        padding: 8px 20px;
        font-size: 14px;
      }
      
      .category-header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .view-all-btn {
        margin-top: 10px;
      }
      
      .promo-banner {
        padding: 20px;
      }
      
      .promo-title {
        font-size: 20px;
      }
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
                        <h6 class="mb-0">Wide Product Range</h6>
                        <p class="text-muted mb-0">Variety of  healthcare products</p>
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

<!-- Navigation Bar -->
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
  <div class="container">
    <div class="logo-container">
      <img src="images/logo.png" alt="MediBridge Logo" class="logo-img">  
      <a class="navbar-brand" href="user.php">Medi<span>Bridge</span></a>
    </div>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    
    <!-- In header.php, replace the search container div with this: -->
<div class="search-container me-3">
    <i class="fas fa-search search-icon"></i>
    <form action="search.php" method="get" class="d-flex">
        <input type="text" name="query" class="search-input form-control" placeholder="Search medicines..." required>
        <button type="submit" class="btn btn-link p-0" style="display: none;"></button>
    </form>
</div>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto">
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'user.php' ? 'active' : ''; ?>" href="user.php">Home</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'doctor_book.php' ? 'active' : ''; ?>" href="doctor_book.php">Doctor Consultation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'prescription.php' ? 'active' : ''; ?>" href="prescription.php">Upload Prescription</a>
          </li>
        </ul>
        
        <?php if(isset($_SESSION['id']) && $user): ?>
          <div class="user-avatar" id="userAvatar">
            <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
          </div>
        <?php else: ?>
          <div class="d-flex gap-2">
 <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#loginModal">
  <i class="fas fa-user me-1"></i>Login
</button>


          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

  <!-- Sidebar Overlay -->
  <div class="overlay" id="sidebarOverlay"></div>

  <!-- User Sidebar -->
  <div class="sidebar" id="userSidebar">
    <div class="sidebar-header">
      <h4>User Menu</h4>
      <button type="button" class="btn-close" id="closeSidebar"></button>
    </div>
    <div class="sidebar-body">
      <div class="sidebar-item">
        <i class="fas fa-user"></i>
        <span>Profile</span>
      </div>
      <div class="sidebar-item">
        <i class="fas fa-shopping-cart"></i>
        <span>Cart</span>
      </div>
      <div class="sidebar-item">
        <i class="fas fa-box"></i>
        <span>Orders</span>
      </div>
      <div class="sidebar-item">
        <i class="fas fa-heart"></i>
        <span>Wishlist</span>
      </div>
      <div class="sidebar-item">
        <i class="fas fa-cog"></i>
        <span>Settings</span>
      </div>
      <div class="sidebar-item">
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </div>
  </div>

  <div class="overlay" id="sidebarOverlay"></div>
    <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
  
 <script>
  // Toggle sidebar when clicking user avatar
  document.getElementById('userAvatar').addEventListener('click', function () {
    document.getElementById('userSidebar').classList.add('show');
    document.getElementById('sidebarOverlay').classList.add('show');
  });

  // Close sidebar when clicking close button
  document.getElementById('closeSidebar').addEventListener('click', function () {
    document.getElementById('userSidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
  });

  // Close sidebar when clicking overlay
  document.getElementById('sidebarOverlay').addEventListener('click', function () {
    document.getElementById('userSidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
  });

  // Handle sidebar item clicks
  document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', function () {
      const itemText = this.querySelector('span').textContent.trim();
      const routes = {
        'Profile': 'profile.php',
        'Cart': 'cart.php',
        'Orders': 'orders.php',
        'Wishlist': 'wishlist.php',
        'Settings': 'settings.php',
        'Logout': 'includes/logout.php'
      };
      if (routes[itemText]) window.location.href = routes[itemText];

      // Close the sidebar
      document.getElementById('userSidebar').classList.remove('show');
      document.getElementById('sidebarOverlay').classList.remove('show');
    });
  });

  // Search input enter key trigger
  $(document).on('keypress', '.search-input', function (e) {
    if (e.which === 13) {
      e.preventDefault();
      const query = $(this).val().trim();
      if (query) {
        window.location.href = '../search.php?query=' + encodeURIComponent(query);
      } else {
        showToast('warning', 'Please enter a search term');
      }
    }
  });

  // Focus input when clicking search icon
  $(document).on('click', '.search-icon', function () {
    $(this).siblings('input').focus();
  });

  // Show toast
  function showToast(type, message) {
    const toast = $(`
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
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

  // Error display helpers
  function showError(input, message) {
    const $input = $(input);
    $input.addClass('is-invalid');
    $input.siblings('.invalid-feedback').text(message).removeClass('d-none');
  }

  function clearError(input) {
    const $input = $(input);
    $input.removeClass('is-invalid');
    $input.siblings('.invalid-feedback').addClass('d-none');
  }

// Password validation helper function
function validatePassword(password) {
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

// Phone number validation
function validatePhoneNumber(phone) {
    return /^\d{10}$/.test(phone);
}

// Pincode validation
function validatePincode(pincode) {
    return /^\d{6}$/.test(pincode);
}

// Email validation
function validateEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

// Show error message for a field
function showError(input, message) {
    const $input = $(input);
    $input.addClass('is-invalid');
    const $feedback = $input.siblings('.invalid-feedback');
    if ($feedback.length) {
        $feedback.text(message).removeClass('d-none');
    } else {
        $input.after(`<div class="invalid-feedback d-block">${message}</div>`);
    }
}

// Clear error message for a field
function clearError(input) {
    const $input = $(input);
    $input.removeClass('is-invalid');
    $input.siblings('.invalid-feedback').addClass('d-none');
}

// Validate individual form fields on blur
$("#signupForm input, #signupForm select").on('blur', function() {
    const $input = $(this);
    const value = $input.val().trim();
    const isRequired = $input.prop('required');
    
    if (isRequired && !value) {
        showError(this, "This field is required");
        return;
    }
    
    // Field-specific validation
    switch(this.id) {
        case 'signupEmail':
            if (value && !validateEmail(value)) {
                showError(this, "Please enter a valid email address");
            } else {
                clearError(this);
            }
            break;
            
        case 'phone':
            if (value && !validatePhoneNumber(value)) {
                showError(this, "Please enter a valid 10-digit phone number");
            } else {
                clearError(this);
            }
            break;
            
        case 'pincode':
            if (value && !validatePincode(value)) {
                showError(this, "Please enter a valid 6-digit pincode");
            } else {
                clearError(this);
            }
            break;
            
        case 'signupPassword':
            if (value) {
                const result = validatePassword(value);
                if (!result.isValid) {
                    showError(this, "Password requirements: " + result.messages.join(", "));
                } else {
                    clearError(this);
                }
            }
            break;
            
        case 'confirmPassword':
            const password = $("#signupPassword").val();
            if (value && value !== password) {
                showError(this, "Passwords do not match");
            } else {
                clearError(this);
            }
            break;
            
        default:
            if (value) clearError(this);
    }
});

// Toggle password visibility
$(".toggle-password").on('click', function() {
    const $input = $(this).siblings('input');
    const type = $input.attr('type') === 'password' ? 'text' : 'password';
    $input.attr('type', type);
    $(this).find('i').toggleClass('fa-eye fa-eye-slash');
});

// Signup form submission
$("#signupForm").on('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    
    // Validate all required fields
    $("#signupForm input[required], #signupForm select[required]").each(function() {
        const $input = $(this);
        const value = $input.val().trim();
        
        if (!value) {
            showError(this, "This field is required");
            isValid = false;
            return;
        }
        
        // Field-specific validation
        switch(this.id) {
            case 'signupEmail':
                if (!validateEmail(value)) {
                    showError(this, "Please enter a valid email address");
                    isValid = false;
                }
                break;
                
            case 'phone':
                if (!validatePhoneNumber(value)) {
                    showError(this, "Please enter a valid 10-digit phone number");
                    isValid = false;
                }
                break;
                
            case 'pincode':
                if (!validatePincode(value)) {
                    showError(this, "Please enter a valid 6-digit pincode");
                    isValid = false;
                }
                break;
                
            case 'signupPassword':
                const result = validatePassword(value);
                if (!result.isValid) {
                    showError(this, "Password requirements: " + result.messages.join(", "));
                    isValid = false;
                }
                break;
                
            case 'confirmPassword':
                const password = $("#signupPassword").val();
                if (value !== password) {
                    showError(this, "Passwords do not match");
                    isValid = false;
                }
                break;
        }
    });
    
    if (!isValid) {
        showToast("danger", "Please fix the errors in the form");
        return false;
    }
    
    // If validation passes, submit the form via AJAX
    const formData = $(this).serialize();
    $.ajax({
        url: "../includes/userSignup.php",
        type: "POST",
        data: formData,
        beforeSend: function() {
            $("#signupForm button[type='submit']")
                .prop("disabled", true)
                .html('<span class="spinner-border spinner-border-sm"></span> Registering...');
        },
        complete: function() {
            $("#signupForm button[type='submit']")
                .prop("disabled", false)
                .text("Create your account");
        },
        success: function(response) {
            if (response.trim() === "success") {
                showToast("success", "Registration successful! Redirecting...");
                setTimeout(() => window.location.href = "user.php", 1500);
            } else {
                showToast("danger", response || "Registration failed");
            }
        },
        error: function() {
            showToast("danger", "An error occurred. Please try again.");
        }
    });
});

// Login form submission
$("#loginForm").on('submit', function(e) {
    e.preventDefault();
    let isValid = true;
    
    const email = $("#loginEmail").val().trim();
    const password = $("#loginPassword").val().trim();
    
    if (!email) {
        showError("#loginEmail", "Email is required");
        isValid = false;
    } else if (!validateEmail(email)) {
        showError("#loginEmail", "Please enter a valid email address");
        isValid = false;
    }
    
    if (!password) {
        showError("#loginPassword", "Password is required");
        isValid = false;
    }
    
    if (!isValid) {
        showToast("danger", "Please fix the errors in the form");
        return;
    }
    
    $.ajax({
        url: "../includes/login.php",
        type: "POST",
        data: $(this).serialize(),
        beforeSend: function() {
            $("#loginForm button[type='submit']")
                .prop("disabled", true)
                .html('<span class="spinner-border spinner-border-sm"></span> Logging in...');
        },
        complete: function() {
            $("#loginForm button[type='submit']")
                .prop("disabled", false)
                .text("Login to your account");
        },
        success: function(response) {
            if (response.startsWith("success:")) {
                const role = response.split(":")[1];
                const redirectMap = {
                    "1": "admin_dashboard.php",
                    "2": "shop_owner.php",
                    "3": "doctor.php",
                    "4": "delivery.php"
                };
                const redirect = redirectMap[role] || "user.php";
                showToast("success", "Login successful! Redirecting...");
                setTimeout(() => window.location.href = redirect, 1500);
            } else {
                showError("#loginPassword", "Invalid email or password");
                showToast("danger", "Invalid login credentials");
            }
        },
        error: function() {
            showToast("danger", "An error occurred. Please try again.");
        }
    });
});

// Show toast notification
function showToast(type, message) {
    const toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1060;">
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
</script>
