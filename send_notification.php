<?php
include '../scripts/connect.php';
header('Content-Type: application/json');

// Initialize response
$response = ['success' => false, 'message' => ''];

try {
    // Validate required fields
    $required = ['user_id', 'pharmacy_id', 'prescription_id', 'message'];
    foreach ($required as $field) {
        if (empty($_POST[$field])) {
            throw new Exception("Missing required field: $field");
        }
        }

    // Sanitize input
    $userId = (int)$_POST['user_id'];
    $pharmacyId = (int)$_POST['pharmacy_id'];
    $prescriptionId = (int)$_POST['prescription_id'];
    $message = htmlspecialchars(trim($_POST['message']));

    // Insert notification
    $stmt = $db->prepare("
        INSERT INTO notifications (
            user_id,
            pharmacy_id,
            prescription_id,
            message,
            is_read,
            created_at
        ) VALUES (
            :user_id,
            :pharmacy_id,
            :prescription_id,
            :message,
            0,
            NOW()
        )
    ");

    $stmt->execute([
        ':user_id' => $userId,
        ':pharmacy_id' => $pharmacyId,
        ':prescription_id' => $prescriptionId,
        ':message' => $message
    ]);

    $response = [
        'success' => true,
        'message' => 'Notification sent successfully',
        'notificationId' => $db->lastInsertId()
    ];

} catch (Exception $e) {
    $response = [
        'success' => false,
        'message' => $e->getMessage()
    ];
}

echo json_encode($response);
?>