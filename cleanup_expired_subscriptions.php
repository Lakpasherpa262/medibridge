<?php
include 'scripts/connect.php';

try {
    // Get current date/time
    $now = date('Y-m-d H:i:s');
    
    // 1. Find all expired subscriptions
    $expiredSubscriptions = $db->query("
        SELECT shop_id 
        FROM featured_subscriptions 
        WHERE end_date < '$now'
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($expiredSubscriptions) > 0) {
        $shopIds = array_column($expiredSubscriptions, 'shop_id');
        $placeholders = implode(',', array_fill(0, count($shopIds), '?'));
        
        // 2. Update products to remove featured status
        $db->prepare("
            UPDATE products 
            SET is_featured = 0 
            WHERE shop_id IN ($placeholders)
        ")->execute($shopIds);
        
        // 3. Delete the expired subscriptions
        $db->prepare("
            DELETE FROM featured_subscriptions 
            WHERE shop_id IN ($placeholders)
        ")->execute($shopIds);
        
        // Log the cleanup
        error_log("Cleaned up " . count($shopIds) . " expired subscriptions");
    }
} catch (PDOException $e) {
    error_log("Cleanup error: " . $e->getMessage());
}
?>