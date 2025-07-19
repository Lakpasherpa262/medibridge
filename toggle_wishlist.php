<?php
session_start();
require_once '../scripts/connect.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!isset($_SESSION['id'])) {
    echo json_encode(['status' => 'login_required', 'message' => 'Please login to manage your wishlist']);
    exit();
}

// Get POST data
$productId = filter_input(INPUT_POST, 'product_id', FILTER_VALIDATE_INT);
$action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
$userId = $_SESSION['id'];

if (!$productId || !in_array($action, ['add', 'remove'])) {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request']);
    exit();
}

try {
    if ($action === 'add') {
        // Check if product already exists in wishlist
        $checkStmt = $db->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
        $checkStmt->execute([$userId, $productId]);
        
        if ($checkStmt->rowCount() === 0) {
            // Add to wishlist
            $insertStmt = $db->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
            $insertStmt->execute([$userId, $productId]);
        }
    } else {
        // Remove from wishlist
        $deleteStmt = $db->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
        $deleteStmt->execute([$userId, $productId]);
    }
    
    // Get updated wishlist count
    $countStmt = $db->prepare("SELECT COUNT(*) as count FROM wishlist WHERE user_id = ?");
    $countStmt->execute([$userId]);
    $count = $countStmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    echo json_encode([
        'status' => 'success',
        'wishlistCount' => $count
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'status' => 'error',
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}