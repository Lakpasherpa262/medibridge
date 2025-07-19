<?php
session_start();
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

include 'scripts/connect.php';

// Handle form submission for updating user details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_details'])) {
    try {
$updateStmt = $db->prepare("UPDATE users SET first_name=?, middle_name=?, last_name=?, email=?, phone=?, address=?, landmark=? WHERE id=?");
$updateStmt->execute([
    $_POST['first_name'],
    $_POST['middle_name'],
    $_POST['last_name'],
    $_POST['email'],
    $_POST['phone'],
    $_POST['address'],
    $_POST['landmark'],
    $_SESSION['id']
]);
        header("Location: checkout.php");
        exit();
    } catch (PDOException $e) {
        die("Error updating user details: " . $e->getMessage());
    }
}

// Fetch user details
try {
$userStmt = $db->prepare("SELECT first_name, middle_name, last_name, email, phone, address, landmark FROM users WHERE id = ?");
    $userStmt->execute([$_SESSION['id']]);
    $user = $userStmt->fetch(PDO::FETCH_ASSOC) ?: [];
} catch (PDOException $e) {
    die("Error fetching user details: " . $e->getMessage());
}

$fullName = implode(' ', array_filter([
    $user['first_name'] ?? '',
    $user['middle_name'] ?? '',
    $user['last_name'] ?? ''
]));

// Fetch cart items
try {
    $cartStmt = $db->prepare("
        SELECT c.*, p.product_name, p.price, s.shop_name 
        FROM cart c
        JOIN products p ON c.product_id = p.id
        JOIN shopdetails s ON c.shop_id = s.id
        WHERE c.user_id = ?
    ");
    $cartStmt->execute([$_SESSION['id']]);
    $cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);
    
    $productImages = [];
    foreach ($cartItems as $item) {
        $imgStmt = $db->prepare("SELECT product_img FROM products WHERE id = ?");
        $imgStmt->execute([$item['product_id']]);
        $imgData = $imgStmt->fetch(PDO::FETCH_ASSOC);
        
        $imagePath = 'inventory/' . ($imgData['product_img'] ?? 'default-product.jpg');
        if (!file_exists($imagePath)) {
            $imagePath = 'images/default-product.jpg';
        }
        $productImages[$item['product_id']] = $imagePath;
    }
} catch (PDOException $e) {
    die("Error fetching cart items: " . $e->getMessage());
}

$subtotal = array_reduce($cartItems, fn($carry, $item) => $carry + ($item['price'] * $item['quantity']), 0);
$shippingCharge = 100;
$total = $subtotal + $shippingCharge;

$cartCount = 0;
if (isset($_SESSION['id'])) {
    $cartStmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $cartStmt->execute([$_SESSION['id']]);
    $cartData = $cartStmt->fetch(PDO::FETCH_ASSOC);
    $cartCount = $cartData['total'] ?? 0;
}

$userAvatar = '';
if (isset($_SESSION['id'])) {
    $avatarStmt = $db->prepare("SELECT first_name FROM users WHERE id = ?");
    $avatarStmt->execute([$_SESSION['id']]);
    $avatarData = $avatarStmt->fetch(PDO::FETCH_ASSOC);
    $userAvatar = strtoupper(substr($avatarData['first_name'] ?? '', 0, 1));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Checkout | MediBridge</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Montserrat:wght@400;500;600;700&display=swap">
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
        
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-img {
            height: 40px;
            width: auto;
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
        
        .checkout-header {
            background: linear-gradient(135deg, #f0f5ff, #e6f0ff);
            color: black;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 20px 20px;
            box-shadow: 0 4px 20px rgba(67, 97, 238, 0.15);
        }
        
        .checkout-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .checkout-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            padding: 2rem;
            margin-bottom: 2rem;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .section-title {
            color: var(--dark-color);
            font-weight: 600;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.5rem;
            display: flex;
            align-items: center;
        }
        
        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 50px;
            height: 3px;
            background: var(--accent-color);
            border-radius: 3px;
        }
        
        .section-title i {
            margin-right: 12px;
            color: var(--accent-color);
            font-size: 1.2rem;
        }
        
        .edit-form {
            display: none;
        }
        
        .edit-form.active {
            display: block;
        }
        
        .info-display {
            display: block;
        }
        
        .info-display.hidden {
            display: none;
        }
        
        .order-summary {
            position: sticky;
            top: 20px;
        }
        
        .order-item {
            display: flex;
            padding: 1rem 0;
            border-bottom: 1px solid #f0f0f0;
            align-items: center;
        }
        
        .order-item:last-child {
            border-bottom: none;
        }
        
        .item-img {
            width: 60px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            margin-right: 15px;
            border: 1px solid #eee;
        }
        
        .item-details {
            flex: 1;
        }
        
        .item-name {
            font-weight: 500;
            margin-bottom: 0.25rem;
        }
        
        .item-shop {
            font-size: 0.85rem;
            color: #6c757d;
        }
        
        .item-qty {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .item-price {
            font-weight: 600;
            color: var(--dark-color);
            min-width: 80px;
            text-align: right;
        }
        
        .divider {
            height: 1px;
            background: #f0f0f0;
            margin: 1rem 0;
        }
        
        .total-row {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            font-size: 1.1rem;
        }
        
        .grand-total {
            font-weight: 700;
            font-size: 1.25rem;
            color: var(--dark-color);
        }
        
        .btn-checkout {
            background: var(--secondary-color);
            color: white;
            font-weight: 600;
            padding: 1rem;
            border: none;
            border-radius: 10px;
            width: 100%;
            transition: all 0.3s;
            margin-top: 1.5rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-size: 1rem;
        }
        
        .btn-checkout:hover {
            background: #2fa36b;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(59, 183, 126, 0.3);
        }
        
        .step-indicator {
            display: flex;
            justify-content: space-between;
            margin-bottom: 2rem;
            position: relative;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #6c757d;
            margin-bottom: 0.5rem;
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: var(--primary-color);
            color: white;
            box-shadow: 0 5px 15px rgba(42, 127, 186, 0.3);
        }
        
        .step.completed .step-number {
            background: var(--secondary-color);
            color: white;
        }
        
        .step-title {
            font-size: 0.9rem;
            font-weight: 500;
            color: #6c757d;
        }
        
        .step.active .step-title {
            color: var(--dark-color);
            font-weight: 600;
        }
        
        .step-line {
            position: absolute;
            top: 20px;
            left: 0;
            right: 0;
            height: 2px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .invalid-feedback {
            display: none;
            color: #dc3545;
            font-size: 0.875em;
        }
        
        .is-invalid ~ .invalid-feedback {
            display: block;
        }
        
        .payment-method {
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .payment-method:hover {
            border-color: var(--primary-color);
        }
        
        .payment-method.selected {
            border-color: var(--primary-color);
            background-color: rgba(42, 127, 186, 0.05);
        }
        
        .payment-method input[type="radio"] {
            margin-right: 10px;
        }
        
        .info-group {
            margin-bottom: 1rem;
        }
        
        .info-label {
            font-weight: 500;
            color: var(--dark-color);
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
            border-left: 3px solid var(--accent-color);
        }
        
        @media (max-width: 768px) {
            .checkout-card {
                padding: 1.5rem;
            }
            
            .section-title {
                font-size: 1.25rem;
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

  <!-- Checkout Content -->
  <header class="checkout-header">
    <div class="container text-center">
      <h1><i class="fas fa-shopping-bag me-2"></i>Checkout</h1>
      <p class="mb-0">Complete your purchase</p>
    </div>
  </header>

  <div class="checkout-container">
    <div class="step-indicator">
      <div class="step active">
        <div class="step-number">1</div>
        <div class="step-title">Delivery Info</div>
      </div>
      <div class="step">
        <div class="step-number">2</div>
        <div class="step-title">Payment</div>
      </div>
      <div class="step">
        <div class="step-number">3</div>
        <div class="step-title">Confirmation</div>
      </div>
      <div class="step-line"></div>
    </div>

    <div class="row">
      <div class="col-lg-8">
        <div class="checkout-card">
          <h3 class="section-title">
            <i class="fas fa-truck"></i>Delivery Information
            <button class="btn btn-outline-primary btn-sm ms-auto" id="toggleEditBtn">
              <i class="fas fa-edit me-1"></i> Edit
            </button>
          </h3>
          
          <!-- Display Mode -->
          <div class="info-display" id="infoDisplay">
            <div class="row">
              <div class="col-md-12 mb-3">
                <div class="info-group">
                  <div class="info-label">Full Name</div>
                  <div class="info-value"><?= htmlspecialchars($fullName) ?></div>
                </div>
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Phone Number</div>
                  <div class="info-value"><?= htmlspecialchars($user['phone'] ?? 'Not provided') ?></div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Email Address</div>
                  <div class="info-value"><?= htmlspecialchars($user['email'] ?? 'Not provided') ?></div>
                </div>
              </div>
            </div>
            
            <div class="info-group">
              <div class="info-label">Complete Address</div>
              <div class="info-value"><?= htmlspecialchars($user['address'] ?? 'Address not provided') ?></div>
            </div>
            
            <div class="row">
              
              <div class="col-md-6">
                <div class="info-group">
                  <div class="info-label">Landmark</div>
                  <div class="info-value"><?= htmlspecialchars($user['landmark'] ?? 'Not specified') ?></div>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Edit Mode -->
          <form method="POST" class="edit-form" id="editForm">
            <div class="row">
              <div class="col-md-4 mb-3">
                <label for="first_name" class="form-label">First Name</label>
                <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($user['first_name'] ?? '') ?>" required>
              </div>
              <div class="col-md-4 mb-3">
                <label for="middle_name" class="form-label">Middle Name</label>
                <input type="text" class="form-control" id="middle_name" name="middle_name" value="<?= htmlspecialchars($user['middle_name'] ?? '') ?>">
              </div>
              <div class="col-md-4 mb-3">
                <label for="last_name" class="form-label">Last Name</label>
                <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($user['last_name'] ?? '') ?>">
              </div>
            </div>
            
            <div class="row">
              <div class="col-md-6 mb-3">
                <label for="phone" class="form-label">Phone Number</label>
                <input type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                <div class="invalid-feedback">Please enter a valid phone number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="email" class="form-label">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                <div class="invalid-feedback">Please enter a valid email address</div>
              </div>
            </div>
            
            <div class="mb-3">
              <label for="address" class="form-label">Complete Address</label>
              <textarea class="form-control" id="address" name="address" rows="3" required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
            </div>
            
            <div class="row">
              
              <div class="col-md-6 mb-3">
                <label for="landmark" class="form-label">Landmark</label>
                <input type="text" class="form-control" id="landmark" name="landmark" value="<?= htmlspecialchars($user['landmark'] ?? '') ?>">
              </div>
            </div>
            
            
            <div class="d-flex justify-content-end mt-3">
              <button type="button" class="btn btn-outline-secondary me-2" id="cancelEditBtn">Cancel</button>
              <button type="submit" name="update_details" class="btn btn-primary">Save Changes</button>
            </div>
          </form>
        </div>

        <div class="checkout-card">
          <h3 class="section-title"><i class="fas fa-credit-card"></i>Payment Method</h3>
          
          <div class="payment-method selected">
            <input type="radio" id="cod" name="payment_method" value="cod" checked>
            <label for="cod" class="fw-bold">Cash on Delivery</label>
            <p class="mb-0 text-muted">Pay when you receive your order</p>
          </div>
          
          <div class="payment-method">
            <input type="radio" id="credit_card" name="payment_method" value="credit_card">
            <label for="credit_card" class="fw-bold">Credit/Debit Card</label>
            <p class="mb-0 text-muted">Pay securely with your card</p>
          </div>
          
          <div id="creditCardDetails" class="mt-4" style="display: none;">
            <div class="row">
              <div class="col-md-12 mb-3">
                <label for="card_name" class="form-label">Name on Card</label>
                <input type="text" class="form-control" id="card_name" placeholder="John Doe" required>
                <div class="invalid-feedback">Please enter the name on card</div>
              </div>
              <div class="col-md-12 mb-3">
                <label for="card_number" class="form-label">Card Number</label>
                <input type="text" class="form-control" id="card_number" placeholder="1234 5678 9012 3456" maxlength="19" required>
                <div class="invalid-feedback">Please enter a valid 16-digit card number</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="expiry" class="form-label">Expiry Date</label>
                <input type="text" class="form-control" id="expiry" placeholder="MM/YY" maxlength="5" required>
                <div class="invalid-feedback">Please enter a valid expiry date (MM/YY)</div>
              </div>
              <div class="col-md-6 mb-3">
                <label for="cvc" class="form-label">CVC</label>
                <input type="text" class="form-control" id="cvc" placeholder="123" maxlength="3" required>
                <div class="invalid-feedback">Please enter a valid 3-digit CVC</div>
              </div>
            </div>
          </div>
          
          <button class="btn-checkout" id="placeOrderBtn">
            <i class="fas fa-lock me-2"></i> Complete Your Order
          </button>

          <!-- Add a hidden form for order submission -->
          <form id="orderForm" style="display: none;">
            <input type="hidden" name="user_id" value="<?= $_SESSION['id'] ?>">
            <input type="hidden" name="payment_method" id="finalPaymentMethod">
            <input type="hidden" name="card_name" id="finalCardName">
            <input type="hidden" name="card_number" id="finalCardNumber">
            <input type="hidden" name="expiry" id="finalExpiry">
            <input type="hidden" name="cvc" id="finalCvc">
            <input type="hidden" name="total_amount" value="<?= $total ?>">
            <input type="hidden" name="shipping_charge" value="<?= $shippingCharge ?>">
          </form>

          <!-- Add a modal for order confirmation -->
          <div class="modal fade" id="orderConfirmationModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
              <div class="modal-content">
                <div class="modal-header bg-success text-white">
                  <h5 class="modal-title">Order Confirmation</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                  <i class="fas fa-check-circle text-success mb-3" style="font-size: 4rem;"></i>
                  <h4>Order confirmed!</h4>
                  <p>Thanks for shopping with us. We'll notify you once your order is on the way.</p>
                </div>
                <div class="modal-footer justify-content-center">
                  <button type="button" class="btn btn-success" data-bs-dismiss="modal">Continue Shopping</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      
        <!-- Order Summary Section -->
        <div class="col-lg-4">
            <div class="checkout-card order-summary">
                <h3 class="section-title"><i class="fas fa-receipt"></i>Order Summary</h3>
                
                <div class="order-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="order-item">
                            <img src="<?= $productImages[$item['product_id']] ?>" 
                                 alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                 class="item-img"
                                 onerror="this.src='images/default-product.jpg'">
                            <div class="item-details">
                                <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                <div class="item-shop"><?= htmlspecialchars($item['shop_name']) ?></div>
                                <div class="item-qty">Qty: <?= $item['quantity'] ?></div>
                            </div>
                            <div class="item-price">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <div class="divider"></div>
                
                <div class="total-row">
                    <span>Subtotal</span>
                    <span>₹<?= number_format($subtotal, 2) ?></span>
                </div>
                
                <div class="total-row">
                    <span>Shipping</span>
                    <span>₹<?= number_format($shippingCharge, 2) ?></span>
                </div>
                
                <div class="divider"></div>
                
                <div class="total-row grand-total">
                    <span>Total</span>
                    <span>₹<?= number_format($total, 2) ?></span>
                </div>
            </div>
        </div>
    </div> 
   </div>
<?php include 'templates/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  
 <script>
   $(document).ready(function() {
    // Toggle edit mode for delivery information
    $('#toggleEditBtn').click(function() {
        $('#infoDisplay').addClass('hidden');
        $('#editForm').addClass('active');
    });
    
    $('#cancelEditBtn').click(function() {
        $('#infoDisplay').removeClass('hidden');
        $('#editForm').removeClass('active');
    });

    // Payment method selection
    $('.payment-method').click(function() {
        $('.payment-method').removeClass('selected');
        $(this).addClass('selected');
        $(this).find('input[type="radio"]').prop('checked', true);
        
        if ($('#credit_card').is(':checked')) {
            $('#creditCardDetails').slideDown();
        } else {
            $('#creditCardDetails').slideUp();
        }
    });

    // Validate name fields (no numbers allowed)
    $('#first_name, #middle_name, #last_name, #card_name').on('input', function() {
        this.value = this.value.replace(/[0-9]/g, '');
    });

    // Validate phone number (exactly 10 digits)
    $('#phone').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substr(0, 10);
    });

    // Validate email format
    $('#email').on('blur', function() {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(this.value)) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Format card inputs
    $('#card_number').on('input', function() {
        this.value = this.value.replace(/\D/g, '')
            .replace(/(\d{4})(?=\d)/g, '$1 ')
            .substr(0, 19);
    });
    
    $('#expiry').on('input', function() {
        this.value = this.value.replace(/\D/g, '')
            .replace(/^(\d{2})/, '$1/')
            .substr(0, 5);
    });
    
    $('#cvc').on('input', function() {
        this.value = this.value.replace(/\D/g, '').substr(0, 3);
    });

    // Validate delivery information form
    $('#editForm').on('submit', function(e) {
        let isValid = true;
        
        // Validate first name
        if ($('#first_name').val().trim() === '') {
            $('#first_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#first_name').removeClass('is-invalid');
        }
        
        // Validate phone number
        const phone = $('#phone').val();
        if (phone.length !== 10 || !/^\d+$/.test(phone)) {
            $('#phone').addClass('is-invalid');
            isValid = false;
        } else {
            $('#phone').removeClass('is-invalid');
        }
        
        // Validate email
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test($('#email').val())) {
            $('#email').addClass('is-invalid');
            isValid = false;
        } else {
            $('#email').removeClass('is-invalid');
        }
        
        // Validate address
        if ($('#address').val().trim() === '') {
            $('#address').addClass('is-invalid');
            isValid = false;
        } else {
            $('#address').removeClass('is-invalid');
        }
        
        if (!isValid) {
            e.preventDefault();
            return false;
        }
        
        return true;
    });

    // Credit card validation
    function validateCard() {
        let isValid = true;
        
        // Validate card name (no numbers)
        const cardName = $('#card_name').val();
        if (cardName.trim() === '' || /[0-9]/.test(cardName)) {
            $('#card_name').addClass('is-invalid');
            isValid = false;
        } else {
            $('#card_name').removeClass('is-invalid');
        }
        
        // Validate card number (16 digits)
        const cardNumber = $('#card_number').val().replace(/\s/g, '');
        if (!/^\d{16}$/.test(cardNumber)) {
            $('#card_number').addClass('is-invalid');
            isValid = false;
        } else {
            $('#card_number').removeClass('is-invalid');
        }
        
        // Validate expiry date (MM/YY format)
        const expiry = $('#expiry').val();
        if (!/^(0[1-9]|1[0-2])\/?([0-9]{2})$/.test(expiry)) {
            $('#expiry').addClass('is-invalid');
            isValid = false;
        } else {
            $('#expiry').removeClass('is-invalid');
        }
        
        // Validate CVC (3 digits)
        const cvc = $('#cvc').val();
        if (!/^\d{3}$/.test(cvc)) {
            $('#cvc').addClass('is-invalid');
            isValid = false;
        } else {
            $('#cvc').removeClass('is-invalid');
        }
        
        return isValid;
    }

    // Check stock availability
    function checkStock() {
        let allInStock = true;
        $('.order-item').each(function() {
            const availableText = $(this).find('.text-danger');
            if (availableText.length > 0) {
                allInStock = false;
                return false; // break out of loop
            }
        });
        return allInStock;
    }

    // Place order validation
    $('#placeOrderBtn').click(function(e) {
        e.preventDefault();
        
        // First check stock availability
        if (!checkStock()) {
            alert('Some items in your cart are not available in the requested quantity. Please update your cart before proceeding.');
            return false;
        }
        
        const paymentMethod = $('input[name="payment_method"]:checked').val();
        
        if (!paymentMethod) {
            alert('Please choose a payment method');
            return false;
        }
        
        if (paymentMethod === 'credit_card' && !validateCard()) {
            return false;
        }
        
        // Set payment status based on method
        const paymentStatus = (paymentMethod === 'cod') ? 'pending' : 'paid';
        
        // Prepare form data
        const formData = {
            payment_method: paymentMethod,
            payment_status: paymentStatus,
            total_amount: <?= $total ?>,
            shipping_charge: <?= $shippingCharge ?>,
            user_id: <?= $_SESSION['id'] ?? 0 ?>
        };
        
        // Include card details if credit card payment
        if (paymentMethod === 'credit_card') {
            formData.card_name = $('#card_name').val();
            formData.card_number = $('#card_number').val().replace(/\s/g, '');
            formData.expiry = $('#expiry').val();
            formData.cvc = $('#cvc').val();
        }
        
        // Submit via AJAX
        $.ajax({
            url: 'includes/process_order.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            beforeSend: function() {
                $('#placeOrderBtn').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i> Processing...');
            },
            success: function(response) {
                if (response.success) {
                    $('#orderConfirmationModal').modal('show');
                    $('#orderConfirmationModal').on('hidden.bs.modal', function() {
                        window.location.href = 'order_confirmation.php?order_id=' + response.order_id;
                    });
                } else {
                    alert('Error: ' + response.message);
                    $('#placeOrderBtn').prop('disabled', false)
                        .html('<i class="fas fa-lock me-2"></i> Complete Your Order');
                }
            },
            error: function(xhr, status, error) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    alert('Error: ' + (response.message || 'Unknown error occurred'));
                } catch (e) {
                    alert('An error occurred while processing your order. Please try again.');
                }
                $('#placeOrderBtn').prop('disabled', false)
                    .html('<i class="fas fa-lock me-2"></i> Complete Your Order');
            }
        });
    });
});
    </script>
</body>
</html>