<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    header("Location: login.php");
    exit();
}

// Include database connection
include 'scripts/connect.php';

// Get user details
$userStmt = $db->prepare("SELECT first_name, last_name FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);

// Get cart items from database
$cartStmt = $db->prepare("
    SELECT c.*, p.product_name, p.price, p.product_img, s.shop_name 
    FROM cart c
    JOIN products p ON c.product_id = p.id
    JOIN shopdetails s ON c.shop_id = s.id
    WHERE c.user_id = ?
");
$cartStmt->execute([$_SESSION['id']]);
$cartItems = $cartStmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate totals
$subtotal = 0;
$selectedSubtotal = 0;
foreach ($cartItems as $item) {
    $itemTotal = $item['price'] * $item['quantity'];
    $subtotal += $itemTotal;
    // Initially consider all items as selected
    $selectedSubtotal += $itemTotal;
}
$shippingCharge = 100;
$total = $subtotal + $shippingCharge;
$selectedTotal = $selectedSubtotal + $shippingCharge;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Cart - MediBridge</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #2a7fba;
            --secondary-color: #3bb77e;
            --accent-color: #ff7e33;
            --dark-color: #253d4e;
            --light-color: #f7f8fa;
            --text-color: #7e7e7e;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f8f9fa;
        }
        
        .cart-item {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }
        
        .cart-item:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-img {
            max-height: 120px;
            object-fit: contain;
        }
        
        .quantity-input {
            width: 60px;
            text-align: center;
        }
        
        .summary-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            position: sticky;
            top: 20px;
        }
        
        .btn-checkout {
            background-color: var(--secondary-color);
            color: white;
            font-weight: 500;
            padding: 10px;
        }
        
        .btn-checkout:hover {
            background-color: #2fa36b;
            color: white;
        }
        
        .selected-item {
            background-color: #f8f9fa;
            border-left: 4px solid var(--primary-color);
        }
        
        .unselected-item {
            opacity: 0.6;
        }
    </style>
</head>
<body>
    <div id="header">
        <?php include 'templates/header.php'; ?>
    </div>

    <!-- Cart Content -->
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold">Your Shopping Cart</h2>
                    <span class="text-muted"><?= count($cartItems) ?> items</span>
                </div>
                
                <?php if (empty($cartItems)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4 class="text-muted">Your cart is empty</h4>
                        <a href="user.php" class="btn btn-primary mt-3">Continue Shopping</a>
                    </div>
                <?php else: ?>
                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="selectAllItems" checked>
                            <label class="form-check-label" for="selectAllItems">
                                Select All Items
                            </label>
                        </div>
                    </div>
                    
                    <div id="cartItemsContainer">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="cart-item mb-3 p-3 selected-item" id="cartItem-<?= $item['product_id'] ?>">
                                <div class="row align-items-center">
                                    <div class="col-md-1">
                                        <input type="checkbox" class="form-check-input item-checkbox" 
                                               value="<?= $item['product_id'] ?>" 
                                               data-price="<?= $item['price'] ?>"
                                               data-quantity="<?= $item['quantity'] ?>"
                                               checked>
                                    </div>
                                    <div class="col-md-2">
                                        <img src="inventory/<?= htmlspecialchars($item['product_img'] ?? 'uploads/default_product.jpg') ?>" 
                                             alt="<?= htmlspecialchars($item['product_name']) ?>" 
                                             class="img-fluid product-img">
                                    </div>
                                    <div class="col-md-4">
                                        <h5 class="mb-1"><?= htmlspecialchars($item['product_name']) ?></h5>
                                        <p class="text-muted mb-1">Shop: <?= htmlspecialchars($item['shop_name']) ?></p>
                                        <p class="text-success fw-bold mb-0">₹<?= number_format($item['price'], 2) ?></p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-minus" 
                                                    data-product-id="<?= $item['product_id'] ?>">-</button>
                                            <input type="number" class="form-control quantity-input mx-2" 
                                                   value="<?= $item['quantity'] ?>" min="1"
                                                   data-product-id="<?= $item['product_id'] ?>">
                                            <button type="button" class="btn btn-sm btn-outline-secondary quantity-plus" 
                                                    data-product-id="<?= $item['product_id'] ?>">+</button>
                                        </div>
                                    </div>
                                    <div class="col-md-2 text-end">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-item" 
                                                data-product-id="<?= $item['product_id'] ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <div class="fw-bold mt-2 item-total">₹<?= number_format($item['price'] * $item['quantity'], 2) ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="col-lg-4">
                <div class="summary-card p-4">
                    <h4 class="fw-bold mb-4">Order Summary</h4>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal (<?= count($cartItems) ?> items)</span>
                        <span class="fw-bold" id="selectedSubtotal">₹<?= number_format($selectedSubtotal, 2) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Shipping</span>
                        <span class="fw-bold">₹<?= number_format($shippingCharge, 2) ?></span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span class="fw-bold">Total</span>
                        <span class="fw-bold text-primary" id="selectedTotal">₹<?= number_format($selectedTotal, 2) ?></span>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-checkout w-100 <?= empty($cartItems) ? 'disabled' : '' ?>">
                        Proceed to Checkout
                    </a>
                    
                    <div class="text-center mt-3">
                        <a href="user.php" class="text-primary">Continue Shopping</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="footer">
        <?php include 'templates/footer.php'; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Calculate selected items total
            function calculateSelectedTotal() {
                let selectedSubtotal = 0;
                let selectedCount = 0;
                
                $('.item-checkbox:checked').each(function() {
                    const price = parseFloat($(this).data('price'));
                    const quantity = parseInt($(this).data('quantity'));
                    selectedSubtotal += price * quantity;
                    selectedCount++;
                });
                
                const selectedTotal = selectedSubtotal + <?= $shippingCharge ?>;
                
                // Update UI
                $('#selectedSubtotal').text('₹' + selectedSubtotal.toFixed(2));
                $('#selectedTotal').text('₹' + selectedTotal.toFixed(2));
                
                // Update item count in summary
                $('.summary-card .text-muted').html(`Subtotal (${selectedCount} items)`);
            }
            
            // Update cart item quantity
            function updateCartItem(productId, quantity) {
                $.ajax({
                    url: 'includes/update_cart.php',
                    method: 'POST',
                    data: {
                        action: 'update',
                        product_id: productId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Update quantity data attribute
                            $(`#cartItem-${productId} .item-checkbox`).data('quantity', quantity);
                            
                            // Update item total display
                            $(`#cartItem-${productId} .item-total`).text(
                                '₹' + (response.itemPrice * quantity).toFixed(2)
                            );
                            
                            // Update cart count in header
                            $('.cart-count').text(response.cartCount);
                            $('.cart-count').toggle(response.cartCount > 0);
                            
                            // Recalculate selected total if item is selected
                            if ($(`#cartItem-${productId} .item-checkbox`).is(':checked')) {
                                calculateSelectedTotal();
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Error updating cart');
                    }
                });
            }
            
            // Remove cart item
            function removeCartItem(productId) {
                $.ajax({
                    url: 'includes/update_cart.php',
                    method: 'POST',
                    data: {
                        action: 'remove',
                        product_ids: productId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            // Remove item from UI
                            $(`#cartItem-${productId}`).remove();
                            
                            // Update cart count in header
                            $('.cart-count').text(response.cartCount);
                            $('.cart-count').toggle(response.cartCount > 0);
                            
                            // Recalculate totals
                            calculateSelectedTotal();
                            
                            // If cart is empty, show empty message
                            if (response.cartCount === 0) {
                                $('#cartItemsContainer').html(`
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                                        <h4 class="text-muted">Your cart is empty</h4>
                                        <a href="user.php" class="btn btn-primary mt-3">Continue Shopping</a>
                                    </div>
                                `);
                            }
                        } else {
                            alert(response.message);
                        }
                    },
                    error: function() {
                        alert('Error removing item from cart');
                    }
                });
            }
            
            // Quantity minus button
            $(document).on('click', '.quantity-minus', function() {
                const productId = $(this).data('product-id');
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                
                if (value > 1) {
                    input.val(value - 1);
                    updateCartItem(productId, value - 1);
                }
            });
            
            // Quantity plus button
            $(document).on('click', '.quantity-plus', function() {
                const productId = $(this).data('product-id');
                const input = $(this).siblings('.quantity-input');
                let value = parseInt(input.val());
                
                input.val(value + 1);
                updateCartItem(productId, value + 1);
            });
            
            // Quantity input change
            $(document).on('change', '.quantity-input', function() {
                const productId = $(this).data('product-id');
                const value = parseInt($(this).val());
                
                if (value >= 1) {
                    updateCartItem(productId, value);
                } else {
                    $(this).val(1);
                }
            });
            
            // Remove item button
            $(document).on('click', '.remove-item', function() {
                if (confirm('Are you sure you want to remove this item from your cart?')) {
                    const productId = $(this).data('product-id');
                    removeCartItem(productId);
                }
            });
            
            // Select all checkbox
            $('#selectAllItems').change(function() {
                const isChecked = $(this).prop('checked');
                $('.item-checkbox').prop('checked', isChecked);
                
                // Update UI for selected items
                $('.cart-item').toggleClass('selected-item', isChecked);
                $('.cart-item').toggleClass('unselected-item', !isChecked);
                
                // Recalculate totals
                calculateSelectedTotal();
            });
            
            // Individual item checkbox
            $(document).on('change', '.item-checkbox', function() {
                const allChecked = $('.item-checkbox:checked').length === $('.item-checkbox').length;
                $('#selectAllItems').prop('checked', allChecked);
                
                // Update UI for selected item
                const cartItem = $(this).closest('.cart-item');
                cartItem.toggleClass('selected-item', $(this).is(':checked'));
                cartItem.toggleClass('unselected-item', !$(this).is(':checked'));
                
                // Recalculate totals
                calculateSelectedTotal();
            });
        });
    </script>
</body>
</html>