<?php
include '../scripts/connect.php';

if (!isset($_GET['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'User ID is required']));
}

$user_id = $_GET['user_id'];

try {
    // Fetch patient details from users table
    $stmt = $db->prepare("SELECT dob, gender, address, pincode, landmark
                         FROM users 
                         WHERE id = :user_id");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$patient) {
        die(json_encode(['status' => 'error', 'message' => 'Patient not found']));
    }
    
    echo json_encode([
        'status' => 'success',
        'data' => [
            'dob' => $patient['dob'],
            'gender' => $patient['gender'],
            'address' => $patient['address'],
            'pincode' => $patient['pincode'],
            'landmark' => $patient['landmark']
        ]
    ]);
} catch (PDOException $e) {
    die(json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]));
}
?>