<?php
session_start();
// Include database connection
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>MediBridge - Your Trusted Healthcare Partner</title>
  <meta name="description" content="Bridging the gap between pharmacies and communities with accessible, affordable healthcare solutions">
  
  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  
  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <!-- Swiper CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
  
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
    
    .logo-container {
      display: flex;
      align-items: center;
      margin-top: -5px; /* Adjusted to move logo up */
    }
    
    .logo-img {
      height: 40px;
      width: auto;
      margin-right: 5px; /* Added spacing between logo and text */
    }
    
    .navbar-brand {
      font-weight: 700;
      font-size: 28px;
      color: var(--primary-color) !important;
      margin-top: 3px; /* Fine-tuned vertical alignment */
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
    
    .hero-section {
      background: linear-gradient(135deg, rgba(42, 127, 186, 0.1) 0%, rgba(59, 183, 126, 0.1) 100%);
      padding: 80px 0;
      position: relative;
      overflow: hidden;
      height:510px;
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
    
    .shop-header {
      padding: 12px 15px;
      background: #2a7fba;
      color: white;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .swiper {
      padding: 10px;
      height: 350px;
    }
    
    .swiper-slide {
      height: auto;
    }
    
    .product-card {
      border: 1px solid #eee;
      border-radius: 8px;
      overflow: hidden;
      height: 100%;
      transition: all 0.3s ease;
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
    
    .swiper-pagination {
      position: relative;
      margin-top: 10px;
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
    .floating-animation {
    animation: float 3s ease-in-out infinite;
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
      justify-content: space-between;
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
    }
    /* Skincare Promo Card Styles */
  .skincare-promo-card {
    height: 100%;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
  }
  
  .skincare-promo-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.15);
  }
  
  /* Luxury Card (Retinol) */
  .luxury-card {
    background: linear-gradient(135deg, #2c3e50 0%, #4a6491 100%);
    color: white;
  }
  
  .bg-gradient-light {
    background: linear-gradient(to right, rgba(255,255,255,0.1), rgba(255,255,255,0.3));
  }
  
  .text-gold {
    color: #d4af37;
  }
  /* Brightening Card (Vitamin C) */
  .brightening-card {
    background: white;
    border: 1px solid rgba(255,215,0,0.2);
  }
  
  .bg-light-yellow {
    background-color: #fffaf0;
  }
  
  /* Treatment Card (Niacinamide) */
  .treatment-card {
    background: white;
    border: 1px solid rgba(0,128,0,0.1);
  }
  
  .bg-light-green {
    background-color: #f0fff0;
  }
  
    /* Product swiper styles */
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
    .  .skincare-banner {
    border: 1px solid rgba(0,0,0,0.05);
  }
  .bg-light-blue {
    background-color: #2a7fba;
  }
  h3 {
    color: #253d4e;
    font-size: 1.4rem;
  }
  
    /* Modal Custom Styles */
    #loginModal .modal-content {
      border-radius: 12px;
      overflow: hidden;
      border: none;
      box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    }

    #loginModal .modal-header {
      padding: 0;
      border-bottom: none;
    }

    #loginModal .nav-tabs {
      border-bottom: none;
    }

    #loginModal .nav-tabs .nav-link {
      padding: 12px;
      border: none;
      border-radius: 0;
      color: #6c757d;
      background-color: #f8f9fa;
      transition: all 0.3s ease;
    }

    #loginModal .nav-tabs .nav-link.active {
      color: #2a7fba;
      background-color: white;
      border-bottom: 3px solid #2a7fba;
    }

    #loginModal .nav-tabs .nav-link:hover:not(.active) {
      background-color: rgba(42, 127, 186, 0.1);
    }

    #loginModal .btn-close {
      position: absolute;
      right: 15px;
      top: 15px;
      z-index: 10;
    }

    #loginModal .form-control, 
    #loginModal .form-select {
      border-radius: 8px;
      padding: 10px 15px;
      border: 1px solid #dee2e6;
      transition: all 0.3s ease;
    }

    #loginModal .form-control:focus, 
    #loginModal .form-select:focus {
      border-color: #2a7fba;
      box-shadow: 0 0 0 0.25rem rgba(42, 127, 186, 0.25);
    }

    #loginModal .input-group-text {
      background-color: #f8f9fa;
      border-color: #dee2e6;
    }

    #loginModal .btn {
      border-radius: 8px;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    #loginModal .btn-primary {
      background-color: #2a7fba;
      border-color: #2a7fba;
    }

    #loginModal .btn-primary:hover {
      background-color: #1f6a9a;
      border-color: #1f6a9a;
    }

    #loginModal .btn-success {
      background-color: #3bb77e;
      border-color: #3bb77e;
    }

    #loginModal .btn-success:hover {
      background-color: #2fa36b;
      border-color: #2fa36b;
    }

    #loginModal .toggle-password {
      border-top-right-radius: 8px;
      border-bottom-right-radius: 8px;
    }

    #loginModal .invalid-feedback {
      font-size: 0.85rem;
    }

    #loginModal .text-muted {
      font-size: 0.85rem;
    }

    #loginModal .illustration-img {
      max-height: 200px;
      margin-bottom: 20px;
    }

    @media (max-width: 991.98px) {
      #loginModal .modal-dialog {
        max-width: 500px;
      }
      
      #loginModal .illustration-col {
        display: none;
      }
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
      
        <div class="d-flex align-items-center">
          <div class="search-container">
            <i class="fas fa-search search-icon"></i>
            <input type="text" class="search-input form-control" placeholder="Search medicines...">
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
    </div>
  </nav>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container">
      <div class="row align-items-center">
        <div class="col-lg-6">
          <div class="hero-content">
            <h1 class="hero-title">MediBridge <br>Your Trusted Healthcare Partner</h1>
            <p class="hero-subtitle">Access to quality medicines, healthcare products and doctor consultations all in one place.</p>
            <div class="d-flex gap-3">
              <a href="view_all.php" class="btn btn-primary">Shop Now</a>
              <a href="#" class="btn btn-outline-primary" onclick="checkAuth('doctor.php')">Consult Doctor</a>
            </div>
          </div>
        </div>
        <div class="col-lg-6">
          <div class="hero-image text-center">
            <img src="images/doc.png" alt="Healthcare products" class="img-fluid">
          </div>
        </div>
      </div>
    </div>
  </section>


  <!-- Features Section -->
  <section class="py-5">
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-truck"></i>
            </div>
            <h3>Fast Delivery</h3>
            <p>Get your medicines delivered to your doorstep within 24 hours.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-stethoscope"></i>
            </div>
            <h3>Doctor Consultations</h3>
            <p>Connect with certified doctors online for medical advice.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-card">
            <div class="feature-icon">
              <i class="fas fa-pills"></i>
            </div>
            <h3>Wide Range</h3>
            <p>Over 10,000 healthcare products from trusted brands.</p>
          </div>
        </div>
      </div>
    </div>
  </section>
<!-- Health Products Promo Slider -->
<section class="py-5">
  <div class="container">
    <div id="healthProductSlider" class="carousel slide" data-bs-ride="carousel">
      <div class="carousel-inner rounded-4 overflow-hidden shadow-lg">
        
        <!-- Slide 1: Multivitamin Complex -->
        <div class="carousel-item active" data-bs-interval="4000">
          <div class="row g-0 align-items-center" style="background: linear-gradient(135deg, #f8f4ff 0%, #e3eeff 100%);">
            <div class="col-md-6 p-5">
              <span class="badge bg-purple text-white mb-3">DAILY ESSENTIAL</span>
              <h2 class="text-dark-blue mb-3">Multivitamin Complex</h2>
              <p class="lead text-muted mb-4">Complete nutritional support with 23 essential vitamins and minerals for optimal health.</p>
              <ul class="list-unstyled mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Immune system support</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Energy metabolism boost</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Antioxidant protection</极>
              </ul>
              <div class="d-flex align-items-center">
                <span class="h4 text-primary mb-0">₹499</span>
                <span class="text-decoration-line-through text-muted ms-3">₹699</span>
                <span class="badge bg-danger ms-3">28% OFF</span>
              </div>
              <a href="#" class="btn btn-primary mt-3 px-4 py-2">Shop Now</a>
            </div>
            <div class="col-md-6 text-center p-4">
              <img src="images/Multivitamin.png" alt="Multivitamin Complex" 
                   class="img-fluid floating-animation" style="max-height: 280px;">
            </div>
          </div>
        </div>
        
        <!-- Slide 2: Ashwagandha -->
        <div class="carousel-item" data-bs-interval="4000">
          <div class="row g-0 align-items-center" style="background: linear-gradient(135deg, #fff8f5 0%, #fff0e8 100%);">
            <div class="col-md-6 p-5 order-md-2">
              <span class="badge bg-orange text-white mb-3">AYURVEDIC</span>
              <h2 class="text-dark-brown mb-3">Ashwagandha Extract</h2>
              <p class="lead text-muted mb-4">Ancient Ayurvedic herb for stress relief, vitality and overall wellbeing.</p>
              <ul class="list-unstyled mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Reduces stress & anxiety</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Enhances energy levels</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Supports cognitive function</li>
              </ul>
              <div class="d-flex align-items-center">
                <span class="h4 text-primary mb-0">₹399</span>
                <span class="text-decoration-line-through text-muted ms-3">₹549</span>
                <span class="badge bg-danger ms-3">27% OFF</span>
              </div>
              <a href="#" class="btn btn-warning mt-3 px-4 py-2">Shop NOw</a>
            </div>
            <div class="col-md-6 text-center p-4 order-md-1">
              <img src="images/Immunityy.png" alt="Ashwagandha Extract" 
                   class="img-fluid floating-animation" style="max-height: 280px;">
            </div>
          </div>
        </div>
        
        <!-- Slide 3: Aloe Vera & Neem -->
        <div class="carousel-item" data-bs-interval="4000">
          <div class="row g-0 align-items-center" style="background: linear-gradient(135deg, #f5fff7 0%, #e8f5eb 100%);">
            <div class="col-md-6 p-5">
              <span class="badge bg-green text-white mb-3">DETOX</span>
              <h2 class="text-dark-green mb-3">Aloe Vera & Neem</h2>
              <p class="lead text-muted mb-4">Powerful natural detoxifier for skin health and internal cleansing.</p>
              <ul class="list-unstyled mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Promotes clear skin</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Supports digestion</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Natural blood purifier</li>
              </ul>
              <div class="d-flex align-items-center">
                <span class="h4 text-primary mb-0">₹349</span>
                <span class="text-decoration-line-through text-muted ms-3">₹499</span>
                <span class="badge bg-danger ms-3">30% OFF</span>
              </div>
              <a href="#" class="btn btn-success mt-3 px极 py-2">Shop Now</a>
            </div>
            <div class="col-md-6 text-center p-4">
              <img src="images/Skin.png" alt="Aloe Vera & Neem" 
                   class="img-fluid floating-animation" style="max-height: 280px;">
            </div>
          </div>
        </div>
      </div>
      
      <!-- Indicators -->
      <div class="carousel-indicators position-static mt-4 justify-content-center">
        <button type="button" data-bs-target="#healthProductSlider" data-bs-slide-to="0" class="active bg-purple" aria-current="true"></button>
        <button type="button" data-bs-target="#healthProductSlider" data-bs-slide-to="1" class="bg-orange"></button>
        <button type="button" data-bs-target="#healthProductSlider" data-bs-slide-to="2" class="bg-green"></button>
      </div>
    </div>
  </div>
</section>
  <!-- Mission Section -->
  <section class="mission-section">
    <div class="container">
      <div class="mission-content">
        <h2 class="mission-title">Our Mission</h2>
        <p class="mission-text">Bridging the gap between pharmacies and local communities with accessible, affordable healthcare solutions for everyone.</p>
      </div>
    </div>
  </section>

  <!-- Product Categories Section -->
<section class="container my-5">
  <!-- Featured Products Section -->
  <div class="category-section mb-5">
    <div class="category-header">
      <h2 class="category-title"><i class="fas fa-star me-2"></i> Featured Products</h2>
      <a href="view_all.php" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    
    <div class="promo-slots">
      <?php
      // Get featured shops with active subscriptions
      $shopStmt = $db->prepare("
          SELECT s.id, s.shop_name 
          FROM shopdetails s
          JOIN featured_subscriptions fs ON s.id = fs.shop_id
          WHERE fs.end_date > NOW()
          ORDER BY RAND() 
          LIMIT 4
      ");
      $shopStmt->execute();
      $shops = $shopStmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($shops as $shop) {
          // Get 5 random products from this shop
          $productStmt = $db->prepare("
              SELECT id, product_name, price, product_img, quantity 
              FROM products 
              WHERE shop_id = ?
              ORDER BY RAND()
              LIMIT 5
          ");
          $productStmt->execute([$shop['id']]);
          $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

          if (count($products) > 0) {
      ?>
      <div class="promo-slot">
        <div class="shop-header">
          <strong><?= htmlspecialchars($shop['shop_name']) ?></strong>
          <i class="fas fa-store"></i>
        </div>
        
        <div class="swiper">
          <div class="swiper-wrapper">
            <?php foreach ($products as $product) {
                $isInWishlist = in_array($product['id'], $wishlist);
                $imagePath = !empty($product['product_img']) ? 'inventory/' . htmlspecialchars($product['product_img']) : 'inventory/uploads/default_product.jpg';
            ?>
            <div class="swiper-slide">
              <div class="product-card" onclick="window.location.href='description.php?id=<?= $product['id'] ?>'">
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
            <?php } ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
      <?php 
          }
      } 
      ?>
    </div>
  </div>
</section>
<!-- Skincare Promo Section -->
<section class="py-5">
  <div class="container">
    <h2 class="section-title text-center mb-5">
      <i class="fas fa-spa me-2"></i> Premium Skincare Solutions
    </h2>
    
   <div class="row g-4">
      <!-- Retinol Night Cream - Luxury Design -->
      <div class="col-md-6">
        <div class="skincare-promo-card luxury-card rounded-4 overflow-hidden position-relative">
          <div class="row g-0 h-100">
            <div class="col-md-6 p-4 d-flex flex-column">
              <span class="badge bg-dark mb-2">CLINICAL STRENGTH</span>
              <h3 class="text-white mb-3">Retinol24 Night Cream</h3>
              <p class="text-light mb-4">Advanced anti-aging formula with encapsulated retinol for overnight renewal.</p>
              <ul class="list-unstyled text-white mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i> Reduces fine lines & wrinkles</li>
                <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i> Improves skin texture</li>
                <li class="mb-2"><i class="fas fa-check-circle text-gold me-2"></i> Non-irritating formula</li>
              </ul>
              <div class="mt-auto">
                <div class="d-flex align-items-center mb-3">
                  <span class="h4 text-gold mb-0">₹1,299</span>
                  <span class="text-decoration-line-through text-light ms-3">₹1,799</span>
                  <span class极="badge bg-danger ms-3">28% OFF</span>
                </div>
                <a href="#" class="btn btn-outline-light">Shop Now <i class="fas fa-arrow-right ms-2"></i></a>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-gradient-light">
              <img src="images/RetinolCream.png" alt="Retinol Night Cream" 
                   class="img-fluid floating-animation" style="max-height: 220px;">
            </div>
          </div>
        </div>
      </div>
    
      
      <!-- Hyaluronic Acid Serum - Clean & Hydrating Design -->
      <div class="col-md-6">
        <div class="skincare-promo-card hydration-card rounded-4 overflow-hidden position-relative">
          <div class="row g-0 h-100">
            <div class="col-md-6 p-4 order-md-2">
              <span class="badge bg-info mb-2">HYDRATION BOOST</span>
              <h3 class="text-dark mb-3">Hyaluronic Acid Serum</h3>
              <p class="text-muted mb-4">Ultra-hydrating formula with 3 molecular weights for multi-depth hydration.</p>
              <ul class="list-unstyled text-dark mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Plumps & hydrates skin</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Reduces fine lines</li>
                <li class="mb-2"><i class="fas fa-check-circle text-primary me-2"></i> Lightweight texture</li>
              </ul>
              <div class="mt-auto">
                <div class="d-flex align-items-center mb-3">
                  <span class="h4 text-primary mb-0">₹899</span>
                  <span class="text-decoration-line-through text-muted ms-3">₹1,299</span>
                  <span class="badge bg-danger ms-3">30% OFF</span>
                </div>
                <a href="#" class="btn btn-primary">Shop Now <i class="fas fa-arrow-right ms-2"></i></a>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light-blue">
              <img src="images/Hyaluronic.png" alt="Hyaluronic Acid Serum" 
                   class="img-fluid floating-animation" style="max-height: 220px;">
            </div>
          </div>
        </div>
      </div>
      
      <!-- Vitamin C Brightening - Radiant Design -->
      <div class="col-md-6">
        <div class="skincare-promo-card brightening-card rounded-4 overflow-hidden position-relative">
          <div class="row g-0 h-100">
            <div class="col-md-6 p-4 d-flex flex-column">
              <span class="badge bg-warning text-dark mb-2">BRIGHTENING</span>
              <h3 class="text-dark mb-3">Vitamin C + E Serum</h3>
              <p class="text-muted mb-4">Potent antioxidant combo to brighten skin and protect from environmental damage.</p>
              <ul class="list-unstyled text-dark mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> Evens skin tone</li>
                <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> Fades dark spots</li>
                <li class="mb-2"><i class="fas fa-check-circle text-warning me-2"></i> Boosts collagen</li>
              </ul>
              <div class="mt-auto">
                <div class="d-flex align-items-center mb-3">
                  <span class="h4 text-warning mb-0">₹999</span>
                  <span class="text-decoration-line-through text-muted ms-3">₹1,499</span>
                  <span class="badge bg-danger ms-3">33% OFF</span>
                </div>
                <a href="#" class="btn btn-warning text-white">Illuminate Skin <i class="fas fa-bolt ms-2"></i></a>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light-yellow">
              <img src="images/vitaminC.png" alt="Vitamin C Serum" 
                   class="img-fluid floating-animation" style="max-height: 220px;">
            </div>
          </div>
        </div>
      </div>
      
      <!-- Niacinamide Treatment - Clinical Design -->
      <div class="col-md-6">
        <div class="skincare-promo-card treatment-card rounded-4 overflow-hidden position-relative">
          <div class="row g-0 h-100">
            <div class="col-md-6 p-4 d-flex flex-column">
              <span class="badge bg-success mb-2">BESTSELLER</span>
              <h3 class="text-dark mb-3">10% Niacinamide Serum</h3>
              <p class="text-muted mb-4">Dermatologist-recommended for acne-prone and sensitive skin types.</p>
              <ul class="list-unstyled text-dark mb-4">
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Reduces redness</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Minimizes pores</li>
                <li class="mb-2"><i class="fas fa-check-circle text-success me-2"></i> Controls oil production</li>
              </ul>
              <div class="mt-auto">
                <div class="d-flex align-items-center mb-3">
                  <span class="h4 text-success mb-0">₹799</span>
                  <span class="text-decoration-line-through text-muted ms-3">₹1,099</span>
                  <span class="badge bg-danger ms-3">27% OFF</span>
                </div>
                <a href="#" class="btn btn-success">Treat Skin <i class="fas fa-heart ms-2"></i></a>
              </div>
            </div>
            <div class="col-md-6 d-flex align-items-center justify-content-center bg-light-green">
              <img src="images/NiacinamideSerum.png" alt="Niacinamide Serum" 
                   class="img-fluid floating-animation" style="max-height: 220px;">
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<!-- Skincare Section -->
<section class="category-section mt-5 py-4 bg-white rounded-3 shadow-sm">
  <div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4 px-3">
      <h2 class="category-title text-primary"><i class="fas fa-spa me-2"></i> Skincare</h2>
      <div class="btn-group">
        <a href="view_all.php?category_id=3&category_name=Skincare" class="btn btn-outline-primary view-all-btn">
          View All <i class="fas fa-arrow-right ms-1"></i>
        </a>
      </div>
    </div>
    
    <div class="promo-slots">
      <?php
      // Get featured shops with skincare products that have active subscriptions
      $shopStmt = $db->prepare("
          SELECT DISTINCT s.id, s.shop_name
          FROM shopdetails s
          JOIN products p ON s.id = p.shop_id
          JOIN featured_subscriptions fs ON s.id = fs.shop_id
          WHERE p.category IN (SELECT C_Id FROM category WHERE CategoryName LIKE '%Skin%' OR CategoryName LIKE '%Care%')
          AND fs.end_date > NOW()
          ORDER BY RAND()
          LIMIT 4
      ");
      $shopStmt->execute();
      $shops = $shopStmt->fetchAll(PDO::FETCH_ASSOC);

      foreach ($shops as $shop) {
          // Get 5 random products from this shop
          $productStmt = $db->prepare("
              SELECT id, product_name, price, product_img, quantity 
              FROM products 
              WHERE category IN (SELECT C_Id FROM category WHERE CategoryName LIKE '%Skin%' OR CategoryName LIKE '%Care%')
              AND shop_id = ?
              ORDER BY RAND()
              LIMIT 5
          ");
          $productStmt->execute([$shop['id']]);
          $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

          if (count($products) > 0) {
      ?>
      <div class="promo-slot">
      <div class="shop-header d-flex align-items-center p-2 rounded-top">
                  <strong class="ms-2 small"><?= htmlspecialchars($shop['shop_name']) ?></strong>
          <i class="fas fa-spa ms-auto"></i>
        </div>
        
        <div class="swiper promo-swiper" style="height: 320px;">
          <div class="swiper-wrapper">
            <?php foreach ($products as $product) {
              $isInWishlist = is_array($wishlist) && in_array($product['id'], $wishlist);
              $imagePath = !empty($product['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_img'])) : 'inventory/uploads/default_product.jpg';
            ?>
            <div class="swiper-slide p-2">
              <div class="product-card h-100 d-flex flex-column" style="border: 1px solid #eee;">
                <!-- Wishlist Icon -->
                <div class="position-absolute top-0 end-0 m-2 z-3">
                  <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart fa-lg wishlist-icon" 
                     data-product-id="<?= $product['id'] ?>"
                     style="color: <?= $isInWishlist ? '#dc3545' : '#6c757d' ?>; cursor: pointer;"></i>
                </div>
                
                <!-- Product Image -->
                <div class="product-image-container d-flex align-items-center justify-content-center p-2" style="height: 140px;">
                  <img src="<?= $imagePath ?>" 
                       class="img-fluid mx-auto" 
                       alt="<?= htmlspecialchars($product['product_name']) ?>"
                       style="max-height: 100%; max-width: 100%;"
                       onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                </div>
                
                <!-- Product Details with Quantity Selector -->
                <div class="card-body p-2 pt-0 d-flex flex-column" style="flex-grow: 1;">
                  <div style="flex-grow: 1;">
                    <h6 class="product-name mb-1 text-truncate small"><?= htmlspecialchars($product['product_name']) ?></h6>
                    <div class="product-price text-primary mb-2">₹<?= number_format($product['price'], 2) ?></div>
                    
                    <!-- Quantity Selector -->
                    <div class="quantity-selector mb-2">
                      <div class="input-group input-group-sm" style="width: 110px;">
                        <button class="btn btn-outline-secondary quantity-minus px-1" type="button">-</button>
                        <input type="number" class="form-control text-center quantity-input px-0" value="1" min="1" max="<?= $product['quantity'] ?>">
                        <button class="btn btn-outline-secondary quantity-plus px-1" type="button">+</button>
                      </div>
                    </div>
                  </div>
                  
                  <!-- Add to Cart Button -->
                  <button class="btn btn-primary w-100 add-to-cart mt-auto py-2" 
                          data-pid="<?= $product['id'] ?>"
                          style="font-size: 13px; border-radius: 4px; margin-top: 8px !important;">
                    <i class="fas fa-cart-plus me-1"></i> Add to Cart
                  </button>
                </div>
              </div>
            </div>
            <?php } ?>
          </div>
          <div class="swiper-pagination"></div>
        </div>
      </div>
      <?php 
          }
      } 
      ?>
    </div>
  </div>
</section>
    <!-- Vitamins Section -->
    <section class="category-section mt-5 py-4 bg-white rounded-3 shadow-sm">
      <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4 px-3">
          <h2 class="category-title text-primary"><i class="fas fa-capsules me-2"></i> Vitamins & Supplements</h2>
          <div class="btn-group">
            <a href="view_all.php?category_id=4&category_name=Vitamins" class="btn btn-outline-primary view-all-btn">
              View All <i class="fas fa-arrow-right ms-1"></i>
            </a>
          </div>
        </div>
        
        <div class="promo-slots">
          <?php
          $shopStmt = $db->prepare("
              SELECT DISTINCT s.id, s.shop_name
              FROM shopdetails s
              JOIN products p ON s.id = p.shop_id
              JOIN featured_subscriptions fs ON s.id = fs.shop_id
              WHERE p.category IN (SELECT C_Id FROM category WHERE CategoryName LIKE '%Vitamin%' OR CategoryName LIKE '%Supplement%')
              AND fs.end_date > NOW()
          ");
          $shopStmt->execute();
          $shops = $shopStmt->fetchAll(PDO::FETCH_ASSOC);

          foreach ($shops as $shop) {
              $productStmt = $db->prepare("
                  SELECT id, product_name, price, product_img, quantity 
                  FROM products 
                  WHERE category IN (SELECT C_Id FROM category WHERE CategoryName LIKE '%Vitamin%' OR CategoryName LIKE '%Supplement%')
                  AND shop_id = ?
                  ORDER BY created_at DESC 
                  LIMIT 5
              ");
              $productStmt->execute([$shop['id']]);
              $products = $productStmt->fetchAll(PDO::FETCH_ASSOC);

              if (count($products) > 0) {
          ?>
          <div class="promo-slot">
            <div class="shop-header d-flex align-items-center p-2 rounded-top">
              <strong class="ms-2 small"><?= htmlspecialchars($shop['shop_name']) ?></strong>
            </div>
            
            <div class="swiper promo-swiper" style="height: 320px;">
              <div class="swiper-wrapper">
                <?php foreach ($products as $product) {
                  $isInWishlist = is_array($wishlist) && in_array($product['id'], $wishlist);
                  $imagePath = !empty($product['product_img']) ? 'inventory/uploads/' . htmlspecialchars(basename($product['product_img'])) : 'inventory/uploads/default_product.jpg';
                ?>
                <div class="swiper-slide p-2">
                  <div class="product-card h-100 d-flex flex-column" style="border: 1px solid #eee;">
                    <!-- Wishlist Icon -->
                    <div class="position-absolute top-0 end-0 m-2 z-3">
                      <i class="<?= $isInWishlist ? 'fas' : 'far' ?> fa-heart fa-lg wishlist-icon" 
                         data-product-id="<?= $product['id'] ?>"
                         style="color: <?= $isInWishlist ? '#dc3545' : '#6c757d' ?>; cursor: pointer;"></i>
                    </div>
                    
                    <!-- Product Image -->
                    <div class="product-image-container d-flex align-items-center justify-content-center p-2" style="height: 140px;">
                      <img src="<?= $imagePath ?>" 
                           class="img-fluid mx-auto" 
                           alt="<?= htmlspecialchars($product['product_name']) ?>"
                           style="max-height: 100%; max-width: 100%;"
                           onerror="this.onerror=null;this.src='inventory/uploads/default_product.jpg'">
                    </div>
                    
                    <!-- Product Details with Quantity Selector -->
                    <div class="card-body p-2 pt-0 d-flex flex-column" style="flex-grow: 1;">
                      <div style="flex-grow: 1;">
                        <h6 class="product-name mb-1 text-truncate small"><?= htmlspecialchars($product['product_name']) ?></h6>
                        <div class="product-price text-primary mb-2">₹<?= number_format($product['price'], 2) ?></div>
                        
                        <!-- Quantity Selector -->
                        <div class="quantity-selector mb-2">
                          <div class="input-group input-group-sm" style="width: 110px;">
                            <button class="btn btn-outline-secondary quantity-minus px-1" type="button">-</button>
                            <input type="number" class="form-control text-center quantity-input px-0" value="1" min="1" max="<?= $product['quantity'] ?>">
                            <button class="btn btn-outline-secondary quantity-plus px-1" type="button">+</button>
                          </div>
                        </div>
                      </div>
                      
                      <!-- Add to Cart Button -->
                      <button class="btn btn-primary w-100 add-to-cart mt-auto py-2" 
                              data-pid="<?= $product['id'] ?>"
                              style="font-size: 13px; border-radius: 4px; margin-top: 8px !important;">
                        <i class="fas fa-cart-plus me-1"></i> Add to Cart
                      </button>
                    </div>
                  </div>
                </div>
                <?php } ?>
              </div>
              <div class="swiper-pagination"></div>
            </div>
          </div>
          <?php 
              }
          } 
          ?>
        </div>
      </div>
    </section>
  </main>

  <!-- Footer -->
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
    <li><a href="templates/blog.php">Blog</a></li>
    <li><a href="templates/aboutus.php">About Us</a></li> 
  </ul>
</div>
        <div class="col-lg-3 col-md-6 mb-4 mb-lg-0">
          <h3 class="footer-title">Categories</h3>
          <ul class="footer-links">
            <li><a href="view_all.php?category_id=1&category_name=Antibiotics">Antibiotics</a></li>
            <li><a href="view_all.php?category_id=2&category_name=Antipyretics">Antipyretics</a></li>
            <li><a href="view_all.php?category_id=3&category_name=Skincare">Skincare</a></li>
            <li><a href="view_all.php?category_id=4&category_name=Vitamins">Vitamins</a></li>
            <li><a href="view_all.php?category_id=5&category_name=Diabetes">Diabetes Care</a></li>
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
  // Initialize all Swiper sliders with pause on hover
  $('.swiper').each(function() {
    const swiper = new Swiper(this, {
      loop: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
        pauseOnMouseEnter: true
      },
      slidesPerView: 1,
      spaceBetween: 10,
      pagination: {
        el: $(this).find('.swiper-pagination')[0],
        clickable: true,
      },
    });
    
    // Additional hover control for better reliability
    $(this).hover(
      function() {
        swiper.autoplay.stop();
      },
      function() {
        swiper.autoplay.start();
      }
    );
  });

  // Pincode availability check
  $("#checkPincodeBtn").click(function() {
    checkPincodeAvailability();
  });

  $("#pincode").on("input", function() {
    if ($(this).val().length === 6) {
      checkPincodeAvailability();
    } else {
      $("#pincodeResult").html("");
    }
  });

  // Form validations
  $("#fname, #mname, #lname, #state, #district").on("input", function() {
    this.value = this.value.replace(/[^a-zA-Z\s]/g, '');
  });

  $("#phone").on("input", function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 10);
  });

  $("#pincode").on("input", function() {
    this.value = this.value.replace(/\D/g, '').slice(0, 6);
  });

  $("#dob").on("change", function() {
    const dob = new Date(this.value);
    const today = new Date();
    const minAgeDate = new Date(today.getFullYear() - 18, today.getMonth(), today.getDate());
    
    if (dob > minAgeDate) {
      showError(this, "You must be at least 18 years old");
    } else {
      clearError(this);
    }
  });

  $("#signupPassword").on("input", function() {
    const password = $(this).val();
    if (password.length > 0 && !validatePassword(password)) {
      $("#passwordHelp").removeClass("d-none").text("Password must contain at least 1 uppercase, 1 lowercase, 1 number, and one special character");
    } else {
      $("#passwordHelp").addClass("d-none");
    }
  });

  $("#confirmPassword").on("input", function() {
    const password = $("#signupPassword").val();
    const confirm = $(this).val();
    
    if (confirm !== password) {
      showError(this, "Passwords do not match");
    } else {
      clearError(this);
    }
  });

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
  
  $(document).on('change', '.quantity-input', function() {
    const max = parseInt($(this).attr('max')) || 10;
    let value = parseInt($(this).val());
    if (isNaN(value) || value < 1) $(this).val(1);
    if (value > max) $(this).val(max);
  });
  
  // Wishlist toggle
  $(document).on('click', '.wishlist-icon', function() {
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
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
          icon.css('color', isActive ? '#6c757d' : '#dc3545');
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
    if (!<?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>) {
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
        if (response === 'success') {
          button.html('<i class="fas fa-check"></i> Added');
          button.removeClass('btn-primary').addClass('btn-success');
          showToast('success', 'Product added to cart');
          
          const cartCount = $('.cart-count');
          cartCount.text((parseInt(cartCount.text()) || 0) + parseInt(quantity));
          
          setTimeout(() => {
            button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
            button.removeClass('btn-success').addClass('btn-primary');
          }, 2000);
        } else if (response === 'login_required') {
          button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
          showToast('warning', 'Please login to add items to your cart');
        } else {
          button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
          showToast('danger', response || 'Failed to add to cart');
        }
      },
      error: function() {
        button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
        showToast('danger', 'Error adding to cart');
      }
    });
  });

  // Toggle password visibility
  $('.toggle-password').click(function() {
    const input = $(this).closest('.input-group').find('input');
    const icon = $(this).find('i');
    input.attr('type', input.attr('type') === 'password' ? 'text' : 'password');
    icon.toggleClass('fa-eye fa-eye-slash');
  });

  // Check authentication before accessing protected pages
  window.checkAuth = function(page) {
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (!isLoggedIn) {
      $('#loginModal').modal('show');
      showToast('warning', 'Please login to access this page');
    } else {
      window.location.href = page;
    }
    return false;
  };

  // Helper functions
  function checkPincodeAvailability() {
    const pincode = $("#pincode").val().trim();
    
    if (pincode.length !== 6) {
      $("#pincodeResult").html('<span class="text-danger">Please enter a 6-digit pincode</span>');
      return;
    }
    
    $.ajax({
      url: "includes/check_pincode.php",
      type: "POST",
      dataType: "json",
      data: { pincode: pincode },
      beforeSend: function() {
        $("#checkPincodeBtn").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Checking...');
      },
      complete: function() {
        $("#checkPincodeBtn").text("Check Availability");
      },
      success: function(response) {
        if (response.status === "available") {
          $("#pincodeResult").html('<span class="text-success">' + response.message + '</span>');
        } else {
          $("#pincodeResult").html('<span class="text-danger">' + response.message + '</span>');
        }
      },
      error: function() {
        $("#pincodeResult").html('<span class="text-danger">Error checking pincode availability</span>');
      }
    });
  }

  function validatePassword(password) {
    const regex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{6,10}$/;
    return regex.test(password);
  }

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

  function showError(input, message) {
    const $input = $(input);
    const errorDiv = $input.siblings(".invalid-feedback");
    errorDiv.text(message).removeClass("d-none");
    $input.addClass('is-invalid');
  }

  function clearError(input) {
    const $input = $(input);
    $input.siblings(".invalid-feedback").addClass("d-none");
    $input.removeClass('is-invalid');
  }

  // Form submissions
  $("#signupForm").submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    $("#signupForm input[required], #signupForm select[required]").each(function() {
      if (!$(this).val().trim()) {
        showError(this, "This field is required");
        isValid = false;
      }
    });

    const password = $("#signupPassword").val();
    if (!validatePassword(password)) {
      showError("#signupPassword", "Password must meet requirements");
      isValid = false;
    }

    if ($("#confirmPassword").val() !== password) {
      showError("#confirmPassword", "Passwords do not match");
      isValid = false;
    }

    if (!isValid) {
      showToast("danger", "Please fix the errors in the form");
      return false;
    }

    const formData = $(this).serialize();
    
    $.ajax({
      url: "includes/userSignup.php",
      type: "POST",
      data: formData,
      beforeSend: function() {
        $("#signupForm button[type='submit']").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Registering...');
      },
      complete: function() {
        $("#signupForm button[type='submit']").prop("disabled", false).text("Create your account");
      },
      success: function(response) {
        if (response.trim() === "success") {
          showToast("success", "Registration successful! Redirecting...");
          setTimeout(() => {
            window.location.href = "user.php";
          }, 1500);
        } else {
          showToast("danger", response || "Registration failed");
        }
      },
      error: function() {
        showToast("danger", "An error occurred. Please try again.");
      }
    });
  });

  $("#loginForm").submit(function(e) {
    e.preventDefault();
    
    let isValid = true;
    
    const email = $("#loginEmail").val().trim();
    if (!email) {
      showError("#loginEmail", "Email is required");
      isValid = false;
    }
    
    const password = $("#loginPassword").val().trim();
    if (!password) {
      showError("#loginPassword", "Password is required");
      isValid = false;
    }

    if (!isValid) return;

    const formData = $(this).serialize();
    
    $.ajax({
      url: "includes/login.php",
      type: "POST",
      data: formData,
      beforeSend: function() {
        $("#loginForm button[type='submit']").prop("disabled", true).html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Logging in...');
      },
      complete: function() {
        $("#loginForm button[type='submit']").prop("disabled", false).text("Login to your account");
      },
      success: function(response) {
        if (response.startsWith("success:")) {
          const role = response.split(":")[1];
          let redirectPage = "user.php";
          
          switch(role) {
            case "1": redirectPage = "admin_dashboard.php"; break;
            case "2": redirectPage = "shop_owner.php"; break;
            case "3": redirectPage = "doctor.php"; break;
            case "4": redirectPage = "delivery.php"; break;
          }
          
          showToast("success", "Login successful! Redirecting...");
          setTimeout(() => {
            window.location.href = redirectPage;
          }, 1500);
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
