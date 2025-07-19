<?php
require_once __DIR__ . '/../scripts/connect.php';

// Set JSON content type header
header('Content-Type: application/json');

// Get and validate parameters
$shop_id = filter_input(INPUT_GET, 'shop_id', FILTER_VALIDATE_INT);
$term = trim(filter_input(INPUT_GET, 'term', FILTER_SANITIZE_STRING));

// Validate inputs
if (!$shop_id || $shop_id <= 0 || !$term || strlen($term) < 2) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid parameters. Shop ID required and search term must be at least 2 characters.'
    ]);
    exit;
}

try {
    // Prepare search query
    $query = "SELECT 
                p.id,
                p.product_name,
                p.description,
                p.price,
                p.quantity,
                p.category,
                p.product_img,
                p.expiry_date
              FROM products p
              WHERE p.shop_id = :shop_id
              AND p.is_listed = 1
              AND (p.product_name LIKE :term OR p.description LIKE :term)
              ORDER BY p.product_name ASC
              LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':shop_id', $shop_id, PDO::PARAM_INT);
    $stmt->bindValue(':term', '%' . $term . '%', PDO::PARAM_STR);
    $stmt->execute();

    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Process results with proper image paths
    $base_path = '/includes/uploads/products/'; // Adjust based on your structure
    foreach ($products as &$product) {
        // Set image URL or default
        if (!empty($product['product_img']) {
            $image_path = __DIR__ . '/../uploads/products/' . $product['product_img'];
            $product['image_url'] = file_exists($image_path) 
                ? $base_path . $product['product_img']
                : '/images/default-product.png';
        } else {
            $product['image_url'] = '/images/default-product.png';
        }

        // Calculate expiry status
        if (!empty($product['expiry_date'])) {
            $today = new DateTime();
            $expiry = new DateTime($product['expiry_date']);
            $interval = $today->diff($expiry);
            
            if ($today > $expiry) {
                $product['expiry_status'] = 'expired';
            } elseif ($interval->days <= 30) {
                $product['expiry_status'] = 'expiring_soon';
            } else {
                $product['expiry_status'] = 'valid';
            }
        }
    }

    // Return successful response
    echo json_encode([
        'success' => true,
        'data' => $products,
        'count' => count($products)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in search_products.php: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database error occurred while searching products.'
    ]);
}
?>