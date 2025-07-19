<?php
include '../scripts/connect.php';
session_start();

// Verify admin access
if (!isset($_SESSION['id']) || $_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

// Check if shop ID is provided
if (!isset($_POST['id']) {
    echo json_encode(['success' => false, 'message' => 'Shop ID not provided']);
    exit;
}

$shopId = $_POST['id'];

try {
    // Begin transaction
    $db->beginTransaction();

    // First, get shop details to delete associated files
    $stmt = $db->prepare("SELECT shop_image, owner_signature FROM shopdetails WHERE id = ?");
    $stmt->execute([$shopId]);
    $shop = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$shop) {
        echo json_encode(['success' => false, 'message' => 'Shop not found']);
        exit;
    }

    // Delete associated files
    $filesToDelete = [];
    if (!empty($shop['shop_image'])) {
        $filesToDelete[] = '../includes/uploads/' . $shop['shop_image'];
    }
    if (!empty($shop['owner_signature'])) {
        $filesToDelete[] = '../includes/uploads/' . $shop['owner_signature'];
    }

    // Delete the shop from database
    $stmt = $db->prepare("DELETE FROM shopdetails WHERE id = ?");
    $stmt->execute([$shopId]);

    // Commit transaction
    $db->commit();

    // Delete files after successful database operation
    foreach ($filesToDelete as $file) {
        if (file_exists($file)) {
            unlink($file);
        }
    }

    echo json_encode(['success' => true, 'message' => 'Shop deleted successfully']);
} catch (PDOException $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>