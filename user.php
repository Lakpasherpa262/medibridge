<?php
session_start();
// Include database connection
include 'scripts/connect.php';

// Initialize user variable
$user = null;

// Get user details if logged in
if (isset($_SESSION['id'])) {
    $userStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC);
    
    // Get wishlist items for logged in user
    $wishlistStmt = $db->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $wishlistStmt->execute([$_SESSION['id']]);
    $wishlist = array_column($wishlistStmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
} else {
    $wishlist = [];
}

// Get cart count for the user
$cartCount = 0;
if (isset($_SESSION['id'])) {
    $cartStmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartStmt->execute([$_SESSION['id']]);
    $cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $cartData['total'] ?? 0;
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
  width: 250px; /* Fixed width */
  transition: all 0.3s ease;
}

/* Remove the :focus width change */
.search-input:focus {
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
      width: 100%;
      padding: 8px;
      font-size: 14px;
   } 
    .add-to-cart:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 10px rgba(0, 0, 0, 0.1);
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
:root {
  --purple: #6f42c1;
  --orange: #fd7e14;
  --green: #28a745;
  --dark-blue: #253d4e;
  --dark-brown: #5c3d0c;
  --dark-green: #1e5631;
}

/* Product-specific badges */
.bg-purple { background-color: var(--purple) !important; }
.bg-orange { background-color: var(--orange) !important; }
.bg-green { background-color: var(--green) !important; }

.text-dark-blue { color: var(--dark-blue); }
.text-dark-brown { color: var(--dark-brown); }
.text-dark-green { color: var(--dark-green); }

/* Slider Customizations */
#healthProductSlider .carousel-item {
  transition: transform 0.6s ease-in-out;
}

#healthProductSlider .carousel-control-prev,
#healthProductSlider .carousel-control-next {
  width: 40px;
  height: 40px;
  background: rgba(0,0,0,0.2);
  border-radius: 50%;
  top: 50%;
  transform: translateY(-50%);
  opacity: 1;
  transition: all 0.3s ease;
}

#healthProductSlider .carousel-control-prev:hover,
#healthProductSlider .carousel-control-next:hover {
  background: rgba(0,0,0,0.4);
}

#healthProductSlider .carousel-indicators button {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: none;
  margin: 0 8px;
  opacity: 0.7;
  transition: all 0.3s ease;
}

#healthProductSlider .carousel-indicators button.active {
  opacity: 1;
  transform: scale(1.2);
}

/* Floating Animation */
.floating-animation {
  animation: float 6s ease-in-out infinite;
}

/* Animation for cart icon */
.animate__animated {
  animation-duration: 1s;
  animation-fill-mode: both;
}

.animate__bounce {
  animation-name: bounce;
}

@keyframes bounce {
  0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
  40% {transform: translateY(-10px);}
  60% {transform: translateY(-5px);}
}

/* Loading spinner */
.fa-spinner {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}
@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  #healthProductSlider .row {
    flex-direction: column-reverse !important;
  }
  #healthProductSlider .col-md-6 {
    padding: 2rem !important;
    text-align: center !important;
  }
  #healthProductSlider img {
    max-height: 200px !important;
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
  
  /* Hydration Card (Hyaluronic Acid) */
  .hydration-card {
    background: white;
    border: 1px solid rgba(0,0,0,0.05);
  }
  
  .bg-light-blue {
    background-color: #f0f8ff;
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
  
  /* Responsive Adjustments */
  @media (max-width: 767.98px) {
    .skincare-promo-card .row > div {
      width: 100%;
    }
    
    .skincare-promo-card .col-md-6 {
      padding: 1.5rem !important;
      text-align: center;
    }
    
    .skincare-promo-card img {
      max-height: 180px !important;
      margin-bottom: 1rem;
    }
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
    /* Custom Color Variables */
:root {
  --purple: #6f42c1;
  --orange: #fd7e14;
  --green: #28a745;
  --dark-blue: #253d4e;
  --dark-brown: #5c3d0c;
  --dark-green: #1e5631;
}

/* Product-specific badges */
.bg-purple { background-color: var(--purple) !important; }
.bg-orange { background-color: var(--orange) !important; }
.bg-green { background-color: var(--green) !important; }

.text-dark-blue { color: var(--dark-blue); }
.text-dark-brown { color: var(--dark-brown); }
.text-dark-green { color: var(--dark-green); }

/* Slider Customizations */
#healthProductSlider .carousel-item {
  transition: transform 0.6s ease-in-out;
}

#healthProductSlider .carousel-control-prev,
#healthProductSlider .carousel-control-next {
  width: 40px;
  height: 40px;
  background: rgba(0,0,0,0.2);
  border-radius: 50%;
  top: 50%;
  transform: translateY(-50%);
  opacity: 1;
  transition: all 0.3s ease;
}

#healthProductSlider .carousel-control-prev:hover,
#healthProductSlider .carousel-control-next:hover {
  background: rgba(0,0,0,0.4);
}

#healthProductSlider .carousel-indicators button {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: none;
  margin: 0 8px;
  opacity: 0.7;
  transition: all 0.3s ease;
}

#healthProductSlider .carousel-indicators button.active {
  opacity: 1;
  transform: scale(1.2);
}

/* Floating Animation */
.floating-animation {
  animation: float 6s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-15px); }
}

/* Responsive Adjustments */
@media (max-width: 768px) {
  #healthProductSlider .row {
    flex-direction: column-reverse !important;
  }
  #healthProductSlider .col-md-6 {
    padding: 2rem !important;
    text-align: center !important;
  }
  #healthProductSlider img {
    max-height: 200px !important;
  }
}

  </style>
</head>
<body>
  <!-- Navigation Bar -->
  <nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
      <div class="logo-container">
<a href="user.php">
  <img src="images/logo.png" alt="MediBridge Logo" class="logo-img"></a>
        <a class="navbar-brand" href="user.php">Medi<span>Bridge</span></a>
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
           <a class="nav-link active" href="user.php">Home</a>
         </li>
          <li class="nav-item">
            <a class="nav-link" href="doctor_book.php">Doctor Consultation</a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="prescription.php">Upload Prescription</a>
          </li>
        </ul>
        
          <?php if(isset($_SESSION['id']) && $user): ?>
            <div class="user-avatar" id="userAvatar">
              <?= strtoupper(substr($user['first_name'], 0, 1)) ?>
            </div>
          <?php else: ?>
            
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
        <i class="fas fa-sign-out-alt"></i>
        <span>Logout</span>
      </div>
    </div>
  </div>

  <div class="overlay" id="sidebarOverlay"></div>
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
            <p>Get your medicines delivered to your doorstep within hours.</p>
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
 <p>Explore an extensive selection of healthcare products
               from trusted brands, carefully chosen to meet diverse needs and preferences.</p>
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
            <div class="shop-header d-flex align-items-center p-2 bg-light rounded-top">
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
             <li><a href="view_all.php">Products</a></li>
            <li><a href="blog.php">Blog</a></li>
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
  
<!-- Add this script at the end of your file, before the closing </body> tag -->
<script>
$(document).ready(function() {
  // Initialize all Swiper sliders with autoplay and pause on hover
  document.querySelectorAll('.swiper').forEach(swiperEl => {
    const swiper = new Swiper(swiperEl, {
      loop: true,
      autoplay: {
        delay: 3000,
        disableOnInteraction: false,
      },
      slidesPerView: 1,
      spaceBetween: 10,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
    });

    // Pause on hover
    swiperEl.addEventListener('mouseenter', () => {
      swiper.autoplay.stop();
    });

    swiperEl.addEventListener('mouseleave', () => {
      swiper.autoplay.start();
    });
  });

  // Toggle sidebar when clicking user avatar
  document.getElementById('userAvatar').addEventListener('click', function() {
    document.getElementById('userSidebar').classList.add('show');
    document.getElementById('sidebarOverlay').classList.add('show');
  });

  // Close sidebar when clicking close button
  document.getElementById('closeSidebar').addEventListener('click', function() {
    document.getElementById('userSidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
  });

  // Close sidebar when clicking overlay
  document.getElementById('sidebarOverlay').addEventListener('click', function() {
    document.getElementById('userSidebar').classList.remove('show');
    document.getElementById('sidebarOverlay').classList.remove('show');
  });

  // Close sidebar when clicking a sidebar item (optional)
  document.querySelectorAll('.sidebar-item').forEach(item => {
    item.addEventListener('click', function() {
      // Get the text of the clicked item
      const itemText = this.querySelector('span').textContent;
      
      // Perform different actions based on the clicked item
      switch(itemText) {
        case 'Profile':
          window.location.href = 'profile.php';
          break;
        case 'Cart':
          window.location.href = 'cart.php';
          break;
        case 'Orders':
          window.location.href = 'orders.php';
          break;
        case 'Wishlist':
          window.location.href = 'wishlist.php';
          break;
        case 'Settings':
          window.location.href = 'settings.php';
          break;
        case 'Logout':
          window.location.href = 'includes/logout.php';
          break;
      }
      
      // Close the sidebar
      document.getElementById('userSidebar').classList.remove('show');
      document.getElementById('sidebarOverlay').classList.remove('show');
    });
  });

  // Check authentication before accessing protected pages
  function checkAuth(page) {
    const isLoggedIn = <?php echo isset($_SESSION['user_id']) ? 'true' : 'false'; ?>;
    if (!isLoggedIn) {
      $('#loginModal').modal('show');
      showToast('warning', 'Please login to access this page');
    } else {
      window.location.href = page;
    }
    return false;
  }

  // Toast notification function
  function showToast(type, message) {
    const toast = $(`
      <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
        <div class="toast show custom-toast" role="alert" aria-live="assertive" aria-atomic="true">
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

  // Add to cart button handler
  $(document).on('click', '.add-to-cart', function(e) {
    e.preventDefault();
    
    const button = $(this);
    const productId = button.data('pid');
    const quantity = parseInt(button.closest('.product-details').find('.quantity-input').val());
    
    // Show loading state
    button.html('<i class="fas fa-spinner fa-spin"></i> Adding...');
    button.prop('disabled', true);
    
    $.ajax({
      url: 'includes/add_to_cart.php',
      method: 'POST',
      data: {
        product_id: productId,
        quantity: quantity
      },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          // Update cart count display
          $('.cart-count').text(response.cartCount);
          $('.cart-count').toggle(response.cartCount > 0);
          
          // Show success message
          showToast('success', response.message);
          animateCartIcon();
        } else {
          showToast('error', response.message);
        }
        
        // Reset button state
        button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
        button.prop('disabled', false);
      },
      error: function() {
        showToast('error', 'Error adding item to cart');
        button.html('<i class="fas fa-cart-plus me-1"></i> Add to Cart');
        button.prop('disabled', false);
      }
    });
  });

  // Function to animate cart icon
  function animateCartIcon() {
    const cartIcon = $('.fa-shopping-cart');
    cartIcon.addClass('animate__animated animate__bounce');
    setTimeout(() => {
      cartIcon.removeClass('animate__animated animate__bounce');
    }, 1000);
  }

  // Quantity controls
  $(document).on('click', '.quantity-minus', function() {
    const input = $(this).siblings('.quantity-input');
    let value = parseInt(input.val());
    if (value > 1) {
      input.val(value - 1);
    }
  });

  $(document).on('click', '.quantity-plus', function() {
    const input = $(this).siblings('.quantity-input');
    let value = parseInt(input.val());
    const max = parseInt(input.attr('max')) || 100;
    if (value < max) {
      input.val(value + 1);
    }
  });

  // Wishlist toggle - Updated code
$(document).on('click', '.wishlist-icon', function() {
    const icon = $(this);
    const productId = icon.data('product-id');
    const isActive = icon.hasClass('fas');
    
    // Check if user is logged in using the correct session variable name
    const isLoggedIn = <?php echo isset($_SESSION['id']) ? 'true' : 'false'; ?>;
    
    if (!isLoggedIn) {
        showToast('warning', 'Please login to manage your wishlist');
        return false;
    }
    
    $.ajax({
        url: 'includes/toggle_wishlist.php',
        type: 'POST',
        data: { 
            product_id: productId, 
            action: isActive ? 'remove' : 'add',
            user_id: <?php echo isset($_SESSION['id']) ? $_SESSION['id'] : 'null'; ?>
        },
        success: function(response) {
            if (response.status === 'success') {
                icon.toggleClass('fas far');
                icon.css('color', isActive ? '#6c757d' : '#dc3545');
                showToast('success', isActive ? 'Removed from wishlist' : 'Added to wishlist');
                
                // Update wishlist count if needed
                if (response.wishlistCount !== undefined) {
                    $('.wishlist-count').text(response.wishlistCount);
                }
            } else if (response.status === 'login_required') {
                showToast('warning', 'Please login to manage your wishlist');
            } else {
                showToast('danger', response.message || 'Operation failed');
            }
        },
        error: function(xhr, status, error) {
            showToast('danger', 'Error updating wishlist: ' + error);
        }
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
});
</script>
    </body>
    </html>