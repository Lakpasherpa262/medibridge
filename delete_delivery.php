<?php
require_once '../scripts/connect.php';

// Verify admin access
session_start();
if (!isset($_SESSION['id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized access']);
    exit;
}

// Check if user ID is provided
if (!isset($_POST['id'])) {
    echo json_encode(['success' => false, 'error' => 'User ID not provided']);
    exit;
}

$userId = $_POST['id'];

try {
    // Begin transaction
    $db->beginTransaction();

    // First, check if the user exists and is a delivery personnel (role = 4)
    $stmt = $db->prepare("SELECT id FROM users WHERE id = ? AND role = 4");
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode(['success' => false, 'error' => 'Delivery personnel not found']);
        exit;
    }

    // Delete any related records (adjust based on your database schema)
    // Example: Delete from prescriptions table if delivery personnel have associated records
    $stmt = $db->prepare("DELETE FROM prescriptions WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Finally, delete the user
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    
    // Check if deletion was successful
    if ($stmt->rowCount() > 0) {
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'Failed to delete user']);
    }
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Error deleting delivery personnel: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>