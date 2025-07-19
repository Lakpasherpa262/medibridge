<?php
// shop.php
include 'scripts/connect.php';

$shopId = $_GET['id'] ?? 0;

try {
    // Get shop details
    $stmt = $db->prepare("SELECT * FROM shopdetails WHERE ShopID = ?");
    $stmt->execute([$shopId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$shop) {
        header("Location: index.php");
        exit;
    }
    
    // Get products for this shop
    $productsStmt = $db->prepare("
        SELECT p.* 
        FROM products p
        JOIN shop_products sp ON p.id = sp.product_id
        WHERE sp.shop_id = ?
    ");
    $productsStmt->execute([$shopId]);
    $products = $productsStmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Handle error
}

// Include your header
include 'templates/header.php';
?>

<!-- Shop Page Content -->
<main class="container mt-4">
    <div class="row">
        <div class="col-md-12">
            <h1><?= htmlspecialchars($shop['ShopName']) ?></h1>
            <p class="text-muted"><?= htmlspecialchars($shop['Address']) ?></p>
            
            <?php if (!empty($products)): ?>
                <div class="row mt-4">
                    <?php foreach ($products as $product): ?>
                        <!-- Your product card HTML here -->
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info mt-4">This pharmacy currently has no products listed.</div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php include 'templates/footer.php'; ?>