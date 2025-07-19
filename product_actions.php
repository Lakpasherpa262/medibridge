<?php
require_once __DIR__ . '/../scripts/connect.php';

// Get the action and product ID
$action = $_POST['action'] ?? '';
$product_id = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);

if (!$product_id) {
    http_response_code(400);
    die('Invalid product ID');
}

try {
    if ($action === 'unlist_product') {
        $stmt = $db->prepare("UPDATE products SET is_listed = 0 WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo 'success';
        } else {
            http_response_code(404);
            echo 'Product not found';
        }
    } elseif ($action === 'relist_product') {
        $stmt = $db->prepare("UPDATE products SET is_listed = 1 WHERE id = :id");
        $stmt->execute([':id' => $product_id]);
        
        if ($stmt->rowCount() > 0) {
            echo 'success';
        } else {
            http_response_code(404);
            echo 'Product not found';
        }
    } else {
        http_response_code(400);
        echo 'Invalid action';
    }
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Product action error: " . $e->getMessage());
    echo 'Database error';
}
?>