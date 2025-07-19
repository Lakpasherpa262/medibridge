<?php
include '../scripts/connect.php';
session_start();

// Verify user is logged in and has a shop
if (!isset($_SESSION['id']) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_SESSION['shop_id'])) {
    header("Location: ../shop_owner.php?error=No shop associated with this account");
    exit();
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$product_id) {
    header("Location: inventory.php?error=No product specified");
    exit();
}

try {
    // First verify the product belongs to this shop
    $stmt = $db->prepare("SELECT product_img FROM products WHERE id = ? AND shop_id = ?");
    $stmt->execute([$product_id, $_SESSION['shop_id']]);
    $product = $stmt->fetch();
    
    if (!$product) {
        throw new Exception("Product not found or you don't have permission to delete it.");
    }

    // Delete the product image if it's not the default
    if ($product['product_img'] !== 'uploads/default.png' && file_exists($product['product_img'])) {
        unlink($product['product_img']);
    }

    // Delete the product
    $stmt = $db->prepare("DELETE FROM products WHERE id = ? AND shop_id = ?");
    $stmt->execute([$product_id, $_SESSION['shop_id']]);
    
    if ($stmt->rowCount() > 0) {
        header("Location: inventory.php?success=Product deleted successfully");
    } else {
        throw new Exception("Failed to delete product or product not found.");
    }
    
} catch (Exception $e) {
    header("Location: inventory.php?error=" . urlencode($e->getMessage()));
    exit();
}
?>