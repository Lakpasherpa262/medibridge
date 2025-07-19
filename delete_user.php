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

    // First, delete related records in other tables to maintain referential integrity
    // Example: delete user's prescriptions, orders, etc.
    // Adjust these queries based on your database schema
    
    // Delete prescriptions associated with this user
    $stmt = $db->prepare("DELETE FROM prescriptions WHERE user_id = ?");
    $stmt->execute([$userId]);
    
    // Delete any other related records (orders, appointments, etc.)
    // $stmt = $db->prepare("DELETE FROM orders WHERE user_id = ?");
    // $stmt->execute([$userId]);
    
    // Finally, delete the user
    $stmt = $db->prepare("DELETE FROM users WHERE id = ? AND role = 5");
    $stmt->execute([$userId]);
    
    // Check if any row was actually deleted
    if ($stmt->rowCount() > 0) {
        $db->commit();
        echo json_encode(['success' => true]);
    } else {
        $db->rollBack();
        echo json_encode(['success' => false, 'error' => 'User not found or not a normal user']);
    }
} catch (PDOException $e) {
    $db->rollBack();
    error_log("Error deleting user: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>