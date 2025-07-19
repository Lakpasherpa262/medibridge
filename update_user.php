<?php
require_once 'scripts/connect.php';

header('Content-Type: application/json');

try {
    $data = $_POST;
    
    $stmt = $db->prepare("UPDATE users SET 
        first_name = ?, 
        last_name = ?, 
        email = ?, 
        phone = ?, 
        role = ?, 
        verification = ? 
        WHERE id = ?");
    
    $success = $stmt->execute([
        $data['first_name'],
        $data['last_name'],
        $data['email'],
        $data['phone'],
        $data['role'],
        $data['verification'],
        $data['id']
    ]);

    echo json_encode(["success" => $success]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}
?>