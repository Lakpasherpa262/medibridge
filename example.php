session_start();
include 'scripts/connect.php';

// Verify shop ID and owner
$shop_id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['shop_id']) ? intval($_SESSION['shop_id']) : null);
$shopOwner_id = isset($_SESSION['id']) ? intval($_SESSION['id']) : null;

if (!$shop_id || !$shopOwner_id) {
    header("Location: login.php");
    exit();
}

try {
    // Verify shop ownership
    $stmt = $db->prepare("SELECT id, shop_name, shop_image FROM shopdetails WHERE id = ? AND shopOwner_id = ?");
    $stmt->execute([$shop_id, $shopOwner_id]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shop) {
        throw new Exception("Shop not found or you don't have permission to view this shop.");
    }
    
    $_SESSION['shop_id'] = $shop['id'];

    // Get categories first
    $categoriesStmt = $db->prepare("
    SELECT c.C_Id AS id, c.CategoryName AS category_name
    FROM category c
    JOIN products p ON c.C_Id = p.category
    WHERE p.shop_id = ? 
    AND p.is_listed = 1
    AND (p.is_featured = 0 OR p.featured_end_date < NOW())
    GROUP BY c.C_Id
    ORDER BY c.CategoryName
");

    $categoriesStmt->execute([$shop_id]);
    $categories = $categoriesStmt->fetchAll(PDO::FETCH_ASSOC);

    // Get shop's active products that aren't already featured, grouped by category
    $productsByCategory = [];
    $totalAvailableProducts = 0;
    
    foreach ($categories as $category) {

        $productsStmt = $db->prepare("
        SELECT id, product_name, price, product_img as product_image 
        FROM products 
        WHERE shop_id = ? 
        AND category = ?
        AND is_listed = 1
        AND (is_featured = 0 OR featured_end_date < NOW())
        ORDER BY product_name
    ");
    $productsStmt->execute([$shop_id, $category['id']]);
    
        $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
        
        if (count($products) > 0) {
            $productsByCategory[] = [
                'category_id' => $category['id'],
                'category_name' => $category['category_name'],
                'products' => $products
            ];
            $totalAvailableProducts += count($products);
        }
    }

    // Rest of your existing code for processing payment...
    // [Keep all the existing payment processing code here]
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => "Database error: " . $e->getMessage()]);
    exit();
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
    exit();
}
?>
