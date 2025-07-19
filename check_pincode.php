<?php
// Include database connection
include '../scripts/connect.php';

// Get pincode from POST data and trim whitespace
$pincode = trim($_POST['pincode'] ?? '');

// Validate pincode (must be 6 digits)
if (!preg_match('/^\d{6}$/', $pincode)) {
    echo json_encode(['status' => 'invalid', 'message' => 'Please enter a valid 6-digit pincode']);
    exit;
}

try {
    // Check if pincode exists in database
    $stmt = $db->prepare("SELECT number FROM pincode WHERE number = :pincode LIMIT 1");
    $stmt->bindParam(':pincode', $pincode);
    $stmt->execute();
    
    if ($stmt->rowCount() > 0) {
        echo json_encode(['status' => 'available', 'message' => 'Delivery available at this pincode']);
    } else {
        echo json_encode(['status' => 'unavailable', 'message' => 'We currently do not deliver to this pincode']);
    }
} catch (PDOException $e) {
    error_log("Pincode check error: " . $e->getMessage());
    echo json_encode(['status' => 'error', 'message' => 'Error checking pincode availability']);
}
?>