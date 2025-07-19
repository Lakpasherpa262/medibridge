<script>
// Cart management functions
function getCart() {
    const userId = getUserId();
    if (!userId) return { items: [], shippingCharge: 100 };
    
    // Try to get from localStorage first
    const cartKey = `cart_${userId}`;
    const localCart = localStorage.getItem(cartKey);
    if (localCart) {
        try {
            return JSON.parse(localCart);
        } catch (e) {
            console.error('Error parsing local cart', e);
        }
    }
    
    // Fallback to cookie
    const cookieValue = document.cookie
        .split('; ')
        .find(row => row.startsWith(`${cartKey}=`));
    
    if (cookieValue) {
        try {
            const decodedValue = decodeURIComponent(cookieValue.split('=')[1]);
            return JSON.parse(decodedValue);
        } catch (e) {
            console.error('Error parsing cookie cart', e);
        }
    }
    
    return { userId, items: [], shippingCharge: 100 };
}

function saveCart(cart) {
    const userId = getUserId();
    if (!userId) return false;
    
    const cartKey = `cart_${userId}`;
    const cartString = JSON.stringify(cart);
    
    // Save to localStorage
    localStorage.setItem(cartKey, cartString);
    
    // Save to cookie
    const expires = new Date();
    expires.setDate(expires.getDate() + 30);
    document.cookie = `${cartKey}=${encodeURIComponent(cartString)}; expires=${expires.toUTCString()}; path=/; Secure; SameSite=Lax`;
    
    // Sync with server session
    $.ajax({
        url: '../includes/update_cart_session.php',
        method: 'POST',
        data: { cart: cartString },
        dataType: 'json'
    }).fail(function(jqXHR, textStatus, errorThrown) {
        console.error('Failed to sync cart with server:', textStatus, errorThrown);
    });
    
    return true;
}

function getUserId() {
    // This should be set in your PHP template
    return typeof currentUserId !== 'undefined' ? currentUserId : null;
}

function addToCart(productId, shopId, quantity) {
    if (!getUserId()) {
        showLoginRequired();
        return false;
    }
    
    const cart = getCart();
    const existingItem = cart.items.find(item => 
        item.productId == productId && item.shopId == shopId
    );
    
    if (existingItem) {
        existingItem.quantity += quantity;
    } else {
        cart.items.push({
            productId: productId,
            shopId: shopId,
            quantity: quantity,
            addedAt: new Date().toISOString()
        });
    }
    
    saveCart(cart);
    updateCartDisplay();
    showToast('success', 'Item added to cart!');
    animateCartIcon();
    return true;
}

function updateCartDisplay() {
    const cart = getCart();
    const cartCount = cart.items.reduce((total, item) => total + item.quantity, 0);
    
    $('.cart-count').text(cartCount).toggle(cartCount > 0);
    $('.cart-total-items').text(cartCount);
}

function animateCartIcon() {
    $('.cart-icon').addClass('animate__animated animate__bounce');
    setTimeout(() => {
        $('.cart-icon').removeClass('animate__animated animate__bounce');
    }, 1000);
}

function showToast(type, message) {
    const toast = $(`
        <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1100">
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
    setTimeout(() => toast.remove(), 3000);
}

function showLoginRequired() {
    $('#loginRequiredModal').modal('show');
}

// Initialize cart on page load
$(document).ready(function() {
    updateCartDisplay();
    
    // Handle add to cart buttons
    $(document).on('click', '.add-to-cart', function(e) {
        e.preventDefault();
        const button = $(this);
        const productId = button.data('pid');
        const shopId = button.data('shop-id');
        const quantity = parseInt(button.closest('.product-actions').find('.quantity-input').val() || 1;
        
        addToCart(productId, shopId, quantity);
    });
    
    // Quantity controls
    $(document).on('click', '.quantity-plus', function() {
        const input = $(this).siblings('.quantity-input');
        let value = parseInt(input.val());
        const max = parseInt(input.attr('max')) || 10;
        if (value < max) input.val(value + 1);
    });
    
    $(document).on('click', '.quantity-minus', function() {
        const input = $(this).siblings('.quantity-input');
        let value = parseInt(input.val());
        if (value > 1) input.val(value - 1);
    });
});
</script>